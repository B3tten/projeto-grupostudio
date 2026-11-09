<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ContaReceberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // The money mask submits "1.234,56"; convert it to a database-friendly number.
        $valor = $this->input('valor');

        if (is_string($valor)) {
            $valor = trim(preg_replace('/[^\d,.\-]/', '', $valor));

            if ($valor !== '' && str_contains($valor, ',')) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            }
        }

        $this->merge(['valor' => $valor]);
    }

    public function rules(): array
    {
        return [
            'cliente' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
            'documento' => 'nullable|string|max:60',
            'valor' => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'status' => ['required', Rule::in(array_keys(\App\Models\ContaReceber::STATUS))],
            'forma_pagamento' => 'nullable|string|max:50',
            'observacao' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Preencha o campo :attribute.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'date' => 'Informe uma data válida em :attribute.',
            'in' => 'Selecione uma opção válida em :attribute.',
            'min' => 'O campo :attribute deve ser maior que zero.',
            'max' => 'O campo :attribute é muito longo.',
            'string' => 'O campo :attribute é inválido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'cliente' => 'cliente',
            'descricao' => 'descrição',
            'documento' => 'documento',
            'valor' => 'valor',
            'data_vencimento' => 'vencimento',
            'data_pagamento' => 'pagamento',
            'status' => 'status',
            'forma_pagamento' => 'forma de pagamento',
            'observacao' => 'observação',
        ];
    }

    /**
     * Validated data adjusted so status and payment date stay coherent.
     */
    public function dataPreparada(): array
    {
        $data = $this->validated();

        if ($data['status'] === 'pago' && empty($data['data_pagamento'])) {
            $data['data_pagamento'] = today()->toDateString();
        }

        if ($data['status'] !== 'pago') {
            $data['data_pagamento'] = null;
        }

        return $data;
    }
}
