<?php

namespace App\Http\Controllers;

use App\Models\ContaReceber;
use Illuminate\Http\Request;

class ContaReceberController extends Controller
{
    public function index(Request $request)
    {
        $filtros = collect($request->only(['busca', 'status', 'vencimento_de', 'vencimento_ate']))
            ->map(fn ($valor) => is_string($valor) ? trim($valor) : null)
            ->filter(fn ($valor) => $valor !== null && $valor !== '')
            ->all();

        $query = ContaReceber::query()->filtros($filtros);

        $resumo = [
            'total' => (float) (clone $query)->sum('valor'),
        ];

        $contas = $query
            ->orderByRaw("CASE WHEN status = 'pendente' THEN 0 WHEN status = 'pago' THEN 1 ELSE 2 END")
            ->orderBy('data_vencimento')
            ->paginate(10)
            ->withQueryString();

        $editarId = old('_conta_id') ?: $request->integer('editar');
        $contaEdicao = $editarId ? ContaReceber::find($editarId) : null;

        return view('contas-receber.index', compact('contas', 'filtros', 'resumo', 'contaEdicao'));
    }

    public function create()
    {
        return redirect()->route('contas-receber.index', ['nova' => 1]);
    }

    public function store(Request $request)
    {
        ContaReceber::create($this->validatedData($request));

        return redirect()
            ->route('contas-receber.index')
            ->with('success', 'Conta a receber cadastrada com sucesso.');
    }

    public function edit(ContaReceber $conta)
    {
        return redirect()->route('contas-receber.index', ['editar' => $conta->id]);
    }

    public function update(Request $request, ContaReceber $conta)
    {
        $conta->update($this->validatedData($request));

        return redirect()
            ->route('contas-receber.index')
            ->with('success', 'Conta a receber atualizada com sucesso.');
    }

    /**
     * One-click receipt action available from the listing.
     */
    public function registrarRecebimento(Request $request, ContaReceber $conta)
    {
        $data = $request->validate([
            'data_pagamento' => 'nullable|date',
        ], [
            'date' => 'Informe uma data válida em :attribute.',
        ], [
            'data_pagamento' => 'pagamento',
        ]);

        if ($conta->status === 'pago') {
            return back()->with('warning', 'Esta conta já está recebida.');
        }

        if ($conta->status === 'cancelado') {
            return back()->with('warning', 'Contas canceladas não podem ser recebidas.');
        }

        $conta->registrarRecebimento($data['data_pagamento'] ?? null);

        return back()->with('success', 'Recebimento registrado com sucesso.');
    }

    public function destroy(ContaReceber $conta)
    {
        $conta->delete();

        return redirect()
            ->route('contas-receber.index')
            ->with('success', 'Conta a receber removida com sucesso.');
    }

    /**
     * Validate and normalize the request data.
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'cliente' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
            'documento' => 'nullable|string|max:60',
            'valor' => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'status' => 'required|in:pendente,pago,cancelado',
            'forma_pagamento' => 'nullable|string|max:50',
            'observacao' => 'nullable|string|max:1000',
        ], [
            'required' => 'Preencha o campo :attribute.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'date' => 'Informe uma data válida em :attribute.',
            'in' => 'Selecione uma opção válida em :attribute.',
            'min' => 'O campo :attribute deve ser maior que zero.',
            'max' => 'O campo :attribute é muito longo.',
            'string' => 'O campo :attribute é inválido.',
        ], [
            'cliente' => 'cliente',
            'descricao' => 'descrição',
            'documento' => 'documento',
            'valor' => 'valor',
            'data_vencimento' => 'vencimento',
            'data_pagamento' => 'pagamento',
            'status' => 'status',
            'forma_pagamento' => 'forma de pagamento',
            'observacao' => 'observação',
        ]);

        if ($data['status'] === 'pago' && empty($data['data_pagamento'])) {
            $data['data_pagamento'] = today()->toDateString();
        }

        // Keep status and payment date coherent when the status changes back.
        if ($data['status'] !== 'pago') {
            $data['data_pagamento'] = null;
        }

        return $data;
    }
}
