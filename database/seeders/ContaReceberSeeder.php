<?php

namespace Database\Seeders;

use App\Models\ContaReceber;
use Illuminate\Database\Seeder;

class ContaReceberSeeder extends Seeder
{
    public function run()
    {
        if (ContaReceber::exists()) {
            return;
        }

        $clientes = [
            'Alfa Comércio Ltda',
            'Bela Vista Distribuidora',
            'Carlos Eduardo Martins',
            'Delta Serviços ME',
            'Estúdio Nova Ideia',
            'Fernanda Oliveira',
            'Global Tech Soluções',
            'Horizonte Construções',
            'Irmãos Silva & Cia',
            'Jardim das Flores Paisagismo',
            'Kaline Moda Boutique',
            'Lumina Design',
        ];

        $descricoes = [
            'Venda de mercadorias',
            'Prestação de serviços',
            'Consultoria mensal',
            'Manutenção de equipamentos',
            'Projeto de implantação',
            'Fornecimento de materiais',
        ];

        $formasPagamento = ['Pix', 'Boleto', 'Cartão de crédito', 'Transferência'];

        // Contas já recebidas (distribuídas nos últimos meses para o gráfico).
        $recebidas = [
            [3, 4200.00], [8, 1850.50], [14, 960.00],
            [36, 3100.00], [43, 780.00],
            [70, 5400.00], [78, 1290.90],
            [101, 2200.00], [112, 640.00],
        ];

        foreach ($recebidas as $indice => [$diasAtras, $valor]) {
            $vencimento = today()->subDays($diasAtras + 2);

            ContaReceber::create([
                'cliente' => $clientes[$indice % count($clientes)],
                'descricao' => $descricoes[$indice % count($descricoes)],
                'documento' => 'NF-' . str_pad((string) (1200 + $indice), 5, '0', STR_PAD_LEFT),
                'valor' => $valor,
                'data_vencimento' => $vencimento,
                'data_pagamento' => today()->subDays($diasAtras),
                'status' => 'pago',
                'forma_pagamento' => $formasPagamento[$indice % count($formasPagamento)],
            ]);
        }

        // Contas pendentes em atraso.
        $atrasadas = [
            [2, 1750.00], [5, 890.00], [9, 3450.00], [16, 620.00], [23, 2380.00],
        ];

        foreach ($atrasadas as $indice => [$diasAtras, $valor]) {
            ContaReceber::create([
                'cliente' => $clientes[($indice + 4) % count($clientes)],
                'descricao' => $descricoes[($indice + 2) % count($descricoes)],
                'documento' => 'NF-' . str_pad((string) (1300 + $indice), 5, '0', STR_PAD_LEFT),
                'valor' => $valor,
                'data_vencimento' => today()->subDays($diasAtras),
                'status' => 'pendente',
                'forma_pagamento' => $formasPagamento[$indice % count($formasPagamento)],
            ]);
        }

        // Contas pendentes a vencer.
        $aVencer = [
            [0, 4300.00], [2, 1250.00], [6, 2650.00], [11, 710.00],
            [17, 1980.00], [24, 3350.00], [31, 860.00],
        ];

        foreach ($aVencer as $indice => [$diasAFrente, $valor]) {
            ContaReceber::create([
                'cliente' => $clientes[($indice + 2) % count($clientes)],
                'descricao' => $descricoes[$indice % count($descricoes)],
                'documento' => 'NF-' . str_pad((string) (1400 + $indice), 5, '0', STR_PAD_LEFT),
                'valor' => $valor,
                'data_vencimento' => today()->addDays($diasAFrente),
                'status' => 'pendente',
                'forma_pagamento' => $formasPagamento[($indice + 1) % count($formasPagamento)],
            ]);
        }

        // Uma conta cancelada para demonstrar o status.
        ContaReceber::create([
            'cliente' => $clientes[9],
            'descricao' => 'Serviço cancelado pelo cliente',
            'documento' => 'NF-01500',
            'valor' => 540.00,
            'data_vencimento' => today()->addDays(9),
            'status' => 'cancelado',
            'forma_pagamento' => 'Boleto',
        ]);
    }
}
