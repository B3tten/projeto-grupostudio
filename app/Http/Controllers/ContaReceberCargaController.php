<?php

namespace App\Http\Controllers;

use App\Services\ContaReceberImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContaReceberCargaController extends Controller
{
    public function create()
    {
        return redirect()->route('contas-receber.index', ['carga' => 1]);
    }

    public function store(Request $request, ContaReceberImportService $service)
    {
        $request->validate([
            'arquivo' => 'required|file|max:10240',
        ], [
            'arquivo.required' => 'Selecione um arquivo para importar.',
            'arquivo.file' => 'O upload não foi concluído. Tente novamente.',
            'arquivo.max' => 'O arquivo deve ter no máximo 10 MB.',
        ]);

        $arquivo = $request->file('arquivo');
        $extensao = strtolower($arquivo->getClientOriginalExtension());

        if (! in_array($extensao, ContaReceberImportService::EXTENSOES, true)) {
            throw ValidationException::withMessages([
                'arquivo' => 'Envie um arquivo .csv ou .ofx.',
            ]);
        }

        try {
            $resultado = $service->import($arquivo->getRealPath(), $extensao);
        } catch (\Throwable $e) {
            // Keep internal paths/SQL details out of the user-facing message.
            report($e);

            throw ValidationException::withMessages([
                'arquivo' => 'Não foi possível ler o arquivo. Verifique o formato e tente novamente.',
            ]);
        }

        if ($resultado['importadas'] === 0) {
            $detalhes = implode(' ', array_slice($resultado['erros'], 0, 3));

            throw ValidationException::withMessages([
                'arquivo' => 'Nenhuma conta válida foi encontrada. ' . $detalhes,
            ]);
        }

        $response = redirect()
            ->route('contas-receber.index')
            ->with('success', $resultado['importadas'] . ' conta(s) importada(s) com sucesso.');

        if (! empty($resultado['erros'])) {
            $detalhes = implode(' ', array_slice($resultado['erros'], 0, 5));

            if (count($resultado['erros']) > 5) {
                $detalhes .= ' ...';
            }

            $response->with('warning', 'Algumas linhas foram ignoradas: ' . $detalhes);
        }

        return $response;
    }

    public function modelo(string $formato)
    {
        abort_unless(in_array($formato, ContaReceberImportService::EXTENSOES, true), 404);

        $caminho = resource_path("examples/modelo-contas-receber.{$formato}");

        abort_unless(is_file($caminho), 404);

        return response()->download($caminho, "modelo-contas-receber.{$formato}");
    }
}
