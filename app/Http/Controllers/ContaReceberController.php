<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListaContasReceber;
use App\Http\Requests\StoreContaReceberRequest;
use App\Http\Requests\UpdateContaReceberRequest;
use App\Models\ContaReceber;
use Illuminate\Http\Request;

class ContaReceberController extends Controller
{
    use ListaContasReceber;

    public function index(Request $request)
    {
        $filtros = $this->filtrosListagem($request);
        [$contas, $resumo] = $this->dadosListagem($filtros, $request->integer('page', 1));

        $editarId = old('_conta_id') ?: $request->integer('editar');
        $contaEdicao = $editarId ? ContaReceber::find($editarId) : null;

        return view('contas-receber.index', compact('contas', 'filtros', 'resumo', 'contaEdicao'));
    }

    public function create()
    {
        return redirect()->route('contas-receber.index', ['nova' => 1]);
    }

    public function store(StoreContaReceberRequest $request)
    {
        ContaReceber::create($request->dataPreparada());

        return $this->respostaOperacao($request, 'Conta a receber cadastrada com sucesso.');
    }

    public function edit(ContaReceber $conta)
    {
        return redirect()->route('contas-receber.index', ['editar' => $conta->id]);
    }

    public function update(UpdateContaReceberRequest $request, ContaReceber $conta)
    {
        $conta->update($request->dataPreparada());

        return $this->respostaOperacao($request, 'Conta a receber atualizada com sucesso.');
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
            return $this->respostaOperacao($request, 'Esta conta já está recebida.', 'warning');
        }

        if ($conta->status === 'cancelado') {
            return $this->respostaOperacao($request, 'Contas canceladas não podem ser recebidas.', 'warning');
        }

        $conta->registrarRecebimento($data['data_pagamento'] ?? null);

        return $this->respostaOperacao($request, 'Recebimento registrado com sucesso.');
    }

    public function destroy(Request $request, ContaReceber $conta)
    {
        $conta->delete();

        return $this->respostaOperacao($request, 'Conta a receber removida com sucesso.');
    }
}
