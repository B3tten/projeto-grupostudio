<?php

namespace Database\Factories;

use App\Models\ContaReceber;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContaReceberFactory extends Factory
{
    protected $model = ContaReceber::class;

    public function definition(): array
    {
        return [
            'cliente' => $this->faker->company(),
            'descricao' => 'Venda de mercadorias',
            'documento' => 'NF-' . $this->faker->unique()->numberBetween(1, 99999),
            'valor' => $this->faker->randomFloat(2, 100, 5000),
            'data_vencimento' => $this->faker->dateTimeBetween('-2 months', '+2 months')->format('Y-m-d'),
            'data_pagamento' => null,
            'status' => 'pendente',
            'forma_pagamento' => $this->faker->randomElement(ContaReceber::FORMAS_PAGAMENTO),
            'observacao' => null,
        ];
    }

    public function recebido(): static
    {
        return $this->state(fn () => [
            'status' => 'pago',
            'data_pagamento' => today()->toDateString(),
        ]);
    }

    public function vencida(): static
    {
        return $this->state(fn () => [
            'data_vencimento' => today()->subDays(10)->toDateString(),
        ]);
    }

    public function cancelada(): static
    {
        return $this->state(fn () => [
            'status' => 'cancelado',
        ]);
    }
}
