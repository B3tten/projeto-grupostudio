<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ListaContasReceber;
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

    public function store(Request $request)
    {
        ContaReceber::create($this->validatedData($request));

        return $this->respostaOperacao($request, 'Conta a receber cadastrada com sucesso.');
    }

    public function edit(ContaReceber $conta)
    {
        return redirect()->route('contas-receber.index', ['editar' => $conta->id]);
    }

    public function update(Request $request, ContaReceber $conta)
    {
        $conta->update($this->validatedData($request));

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

    /**
     * Validate and normalize the request data.
     */
    private function validatedData(Request $request): array
    {
        // The money mask submits "1.234,56"; convert it to a database-friendly number.
        $request->merge(['valor' => $this->normalizarValor($request->input('valor'))]);

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

    private function normalizarValor($valor)
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $valor = trim(preg_replace('/[^\d,.\-]/', '', $valor));

        if ($valor === '') {
            return $valor;
        }

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return $valor;
    }
}
