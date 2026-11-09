<?php

namespace App\Http\Controllers;

use App\Models\ContaReceber;

class DashboardController extends Controller
{
    private const MESES = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

    public function index()
    {
        $hoje = today();
        $pendentes = ContaReceber::where('status', 'pendente');

        // "A receber" is every pending account; "vencido" and "a vencer" split
        // that same base by due date, while "recebido" comes from paid accounts.
        $totalRecebido = (float) ContaReceber::where('status', 'pago')->sum('valor');
        $totalAReceber = (float) (clone $pendentes)->sum('valor');
        $totalVencido = (float) (clone $pendentes)->whereDate('data_vencimento', '<', $hoje)->sum('valor');
        $totalAVencer = (float) (clone $pendentes)->whereDate('data_vencimento', '>=', $hoje)->sum('valor');

        $quantidadePendente = (clone $pendentes)->count();
        $quantidadeVencida = (clone $pendentes)->whereDate('data_vencimento', '<', $hoje)->count();
        $quantidadeAVencer = (clone $pendentes)->whereDate('data_vencimento', '>=', $hoje)->count();
        $quantidadeRecebida = ContaReceber::where('status', 'pago')->count();

        $proximosVencimentos = (clone $pendentes)
            ->whereDate('data_vencimento', '>=', $hoje)
            ->orderBy('data_vencimento')
            ->limit(5)
            ->get();

        $ultimosRecebimentos = ContaReceber::where('status', 'pago')
            ->orderByDesc('data_pagamento')
            ->limit(5)
            ->get();

        $recebimentosPorMes = $this->recebimentosPorMes();

        // Cancelled accounts are intentionally excluded from the composition.
        $totalGeral = $totalRecebido + $totalAReceber;
        $percentualRecebido = $totalGeral > 0 ? (int) round($totalRecebido / $totalGeral * 100) : 0;
        $percentualVencido = $totalGeral > 0 ? (int) round($totalVencido / $totalGeral * 100) : 0;
        $percentualAVencer = $totalGeral > 0
            ? max(0, 100 - $percentualRecebido - $percentualVencido)
            : 0;

        $grafico = $totalGeral > 0
            ? "conic-gradient(#0f9f6e 0% {$percentualRecebido}%, #ef4444 {$percentualRecebido}% "
                . ($percentualRecebido + $percentualVencido) . "%, #f59e0b "
                . ($percentualRecebido + $percentualVencido) . "% 100%)"
            : 'conic-gradient(#e4e7ec 0% 100%)';

        return view('dashboard.index', [
            'totalRecebido' => $totalRecebido,
            'totalAReceber' => $totalAReceber,
            'totalVencido' => $totalVencido,
            'totalAVencer' => $totalAVencer,
            'quantidadePendente' => $quantidadePendente,
            'quantidadeVencida' => $quantidadeVencida,
            'quantidadeAVencer' => $quantidadeAVencer,
            'quantidadeRecebida' => $quantidadeRecebida,
            'proximosVencimentos' => $proximosVencimentos,
            'ultimosRecebimentos' => $ultimosRecebimentos,
            'recebimentosPorMes' => $recebimentosPorMes,
            'totalGeral' => $totalGeral,
            'percentualRecebido' => $percentualRecebido,
            'percentualVencido' => $percentualVencido,
            'percentualAVencer' => $percentualAVencer,
            'grafico' => $grafico,
        ]);
    }

    /**
     * Total received per month in the last six months.
     */
    private function recebimentosPorMes(): array
    {
        $inicio = today()->startOfMonth()->subMonths(5);

        $recebimentos = ContaReceber::where('status', 'pago')
            ->whereDate('data_pagamento', '>=', $inicio)
            ->get(['data_pagamento', 'valor'])
            ->groupBy(fn (ContaReceber $conta) => $conta->data_pagamento->format('Y-m'));

        $meses = [];
        $maiorTotal = 0;

        for ($i = 0; $i < 6; $i++) {
            $mes = $inicio->copy()->addMonths($i);
            $total = (float) ($recebimentos->get($mes->format('Y-m'))?->sum('valor') ?? 0);

            $maiorTotal = max($maiorTotal, $total);
            $meses[] = [
                'label' => self::MESES[$mes->month - 1],
                'total' => $total,
                'altura' => 0,
            ];
        }

        foreach ($meses as $indice => $mes) {
            $meses[$indice]['altura'] = $maiorTotal > 0
                ? max(4, (int) round($mes['total'] / $maiorTotal * 100))
                : 4;
        }

        return $meses;
    }
}
