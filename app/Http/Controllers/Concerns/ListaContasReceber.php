<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ContaReceber;
use Illuminate\Http\Request;

/**
 * Shared listing helpers so every response (full page or table-only AJAX) renders
 * the same filtered, paginated data and keeps the active filters in the URL.
 */
trait ListaContasReceber
{
    protected const FILTROS_LISTAGEM = ['busca', 'status', 'vencimento_de', 'vencimento_ate'];

    protected function filtrosListagem(Request $request): array
    {
        return $this->normalizarFiltros($request->only(self::FILTROS_LISTAGEM));
    }

    protected function filtrosOperacao(Request $request): array
    {
        $filtros = $request->input('_filtros', []);

        if (! is_array($filtros)) {
            $filtros = [];
        }

        return $this->normalizarFiltros(array_intersect_key($filtros, array_flip(self::FILTROS_LISTAGEM)));
    }

    protected function normalizarFiltros(array $filtros): array
    {
        return collect($filtros)
            ->map(fn ($valor) => is_string($valor) ? trim($valor) : null)
            ->filter(fn ($valor) => $valor !== null && $valor !== '')
            ->all();
    }

    protected function dadosListagem(array $filtros, int $pagina): array
    {
        $query = ContaReceber::query()->filtros($filtros);
        $resumo = ['total' => (float) (clone $query)->sum('valor')];

        $pagina = max(1, $pagina);
        $contas = $this->paginarListagem(clone $query, $filtros, $pagina);

        // Deleting the last row of the last page falls back to the previous page.
        if ($contas->lastPage() > 0 && $contas->currentPage() > $contas->lastPage()) {
            $contas = $this->paginarListagem($query, $filtros, $contas->lastPage());
        }

        return [$contas, $resumo];
    }

    private function paginarListagem($query, array $filtros, int $pagina)
    {
        return $query
            ->orderByRaw("CASE WHEN status = 'pendente' THEN 0 WHEN status = 'pago' THEN 1 ELSE 2 END")
            ->orderBy('data_vencimento')
            ->paginate(10, ['*'], 'page', $pagina)
            ->appends($filtros);
    }

    protected function urlListagem(array $filtros, int $pagina): string
    {
        $parametros = $filtros;

        if ($pagina > 1) {
            $parametros['page'] = $pagina;
        }

        return route('contas-receber.index', $parametros);
    }

    protected function respostaOperacao(Request $request, string $mensagem, string $tipo = 'success')
    {
        $filtros = $this->filtrosOperacao($request);
        $pagina = max(1, (int) $request->input('_page', 1));

        [$contas, $resumo] = $this->dadosListagem($filtros, $pagina);

        $url = $this->urlListagem($filtros, $contas->currentPage());

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('contas-receber._tabela', compact('contas', 'filtros', 'resumo'))->render(),
                'message' => $mensagem,
                'type' => $tipo,
                'url' => $url,
            ]);
        }

        return redirect()->to($url)->with($tipo, $mensagem);
    }
}
