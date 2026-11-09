@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 gap-lg-3 mb-3">
        <div>
            <p class="page-eyebrow mb-1">Visão geral</p>
            <h1 class="page-title mb-0">Dashboard</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="d-none d-sm-inline-block text-muted small">
                <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('contas-receber.index', ['status' => 'a_receber']) }}"
               class="stat-card-link h-100"
               aria-label="Ver contas a receber">
                <div class="card card-surface stat-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Total a receber</p>
                                <div class="stat-value" id="kpi-receber">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</div>
                            </div>
                            <span class="stat-icon bg-success-subtle text-success-emphasis">
                                <i class="bi bi-wallet2"></i>
                            </span>
                        </div>
                        <p class="stat-meta">
                            <strong id="kpi-receber-qtd">{{ $quantidadePendente }}</strong> contas pendentes
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('contas-receber.index', ['status' => 'pago']) }}"
               class="stat-card-link h-100"
               aria-label="Ver contas recebidas">
                <div class="card card-surface stat-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Total recebido</p>
                                <div class="stat-value" id="kpi-recebido">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</div>
                            </div>
                            <span class="stat-icon bg-primary-subtle text-primary-emphasis">
                                <i class="bi bi-check2-circle"></i>
                            </span>
                        </div>
                        <p class="stat-meta">
                            <strong id="kpi-recebido-qtd">{{ $quantidadeRecebida }}</strong> contas recebidas
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('contas-receber.index', ['status' => 'vencido']) }}"
               class="stat-card-link h-100"
               aria-label="Ver contas vencidas">
                <div class="card card-surface stat-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Total vencido</p>
                                <div class="stat-value text-danger" id="kpi-vencido">R$ {{ number_format($totalVencido, 2, ',', '.') }}</div>
                            </div>
                            <span class="stat-icon bg-danger-subtle text-danger-emphasis">
                                <i class="bi bi-exclamation-triangle"></i>
                            </span>
                        </div>
                        <p class="stat-meta">
                            <strong id="kpi-vencido-qtd">{{ $quantidadeVencida }}</strong> contas vencidas
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('contas-receber.index', ['status' => 'pendente']) }}"
               class="stat-card-link h-100"
               aria-label="Ver contas a vencer">
                <div class="card card-surface stat-card h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Total a vencer</p>
                                <div class="stat-value" id="kpi-a-vencer">R$ {{ number_format($totalAVencer, 2, ',', '.') }}</div>
                            </div>
                            <span class="stat-icon bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-hourglass-split"></i>
                            </span>
                        </div>
                        <p class="stat-meta">
                            <strong id="kpi-a-vencer-qtd">{{ $quantidadeAVencer }}</strong> contas a vencer
                        </p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 g-xl-4 align-items-stretch">
        <div class="col-12 col-lg-8 col-xl-5">
            <div class="card card-surface h-100">
                <div class="card-body p-3 p-lg-4 d-flex flex-column">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="section-title mb-0">Recebimentos por mês</h2>
                            <p class="section-subtitle">Últimos 6 meses</p>
                        </div>
                        <a href="{{ route('contas-receber.index', ['status' => 'pago']) }}"
                           class="small fw-semibold text-decoration-none"
                           style="color: var(--brand-dark);">
                            Ver recebidos <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="chart chart-compact flex-grow-1">
                        @foreach ($recebimentosPorMes as $mes)
                            <div class="chart-col">
                                <div class="chart-value">R$ {{ number_format($mes['total'], 0, ',', '.') }}</div>
                                <div class="chart-track">
                                    <div class="chart-bar"
                                         style="height: {{ $mes['altura'] }}%;"
                                         title="{{ $mes['label'] }}: R$ {{ number_format($mes['total'], 2, ',', '.') }}">
                                    </div>
                                </div>
                                <div class="chart-label">{{ $mes['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card card-surface h-100">
                <div class="card-body p-3 p-lg-4 d-flex flex-column">
                    <div class="mb-3">
                        <h2 class="section-title mb-0">Composição</h2>
                        <p class="section-subtitle">Situação da carteira</p>
                    </div>

                    <div class="flex-grow-1 d-flex flex-column justify-content-center">
                        <div class="donut donut-sm mb-3 mx-auto" id="composicao-donut" style="background: {{ $grafico }};">
                            <div class="donut-hole">
                                <span>Total</span>
                                <strong id="composicao-total">R$ {{ number_format($totalGeral, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div>
                            <div class="legend-row">
                                <span><span class="legend-dot" style="background:#0f9f6e;"></span>Recebido</span>
                                <span class="text-muted" id="percentual-recebido">{{ $percentualRecebido }}%</span>
                            </div>
                            <div class="legend-row">
                                <span><span class="legend-dot" style="background:#ef4444;"></span>Vencido</span>
                                <span class="text-muted" id="percentual-vencido">{{ $percentualVencido }}%</span>
                            </div>
                            <div class="legend-row">
                                <span><span class="legend-dot" style="background:#f59e0b;"></span>A vencer</span>
                                <span class="text-muted" id="percentual-a-vencer">{{ $percentualAVencer }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-3 gap-xl-4">
                <div class="card card-surface">
                    <div class="d-flex align-items-start justify-content-between gap-2 px-3 pt-3 pb-1">
                        <h2 class="section-title mb-0">Próximos vencimentos</h2>
                        <a href="{{ route('contas-receber.index', ['status' => 'pendente']) }}"
                           class="small fw-semibold text-decoration-none text-nowrap"
                           style="color: var(--brand-dark);">
                            Ver todas
                        </a>
                    </div>

                    <div class="rail-scroll px-3 pb-2" tabindex="0" aria-label="Próximos vencimentos">
                        @forelse ($proximosVencimentos as $conta)
                            <div class="list-row">
                                <span class="list-icon bg-success-subtle text-success-emphasis">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold small text-truncate">{{ $conta->cliente }}</div>
                                    <div class="text-muted text-truncate" style="font-size: .72rem;">
                                        {{ $conta->data_vencimento->isToday() ? 'Vence hoje' : 'Vence em ' . $conta->data_vencimento->format('d/m') }}
                                        @if ($conta->descricao)
                                            · {{ $conta->descricao }}
                                        @endif
                                    </div>
                                </div>
                                <div class="fw-semibold small text-nowrap">{{ $conta->valor_formatado }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <i class="bi bi-calendar-check"></i>
                                <p class="mb-0">Nenhuma conta a vencer.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card card-surface">
                    <div class="d-flex align-items-start justify-content-between gap-2 px-3 pt-3 pb-1">
                        <h2 class="section-title mb-0">Últimos recebimentos</h2>
                        <a href="{{ route('contas-receber.index', ['status' => 'pago']) }}"
                           class="small fw-semibold text-decoration-none text-nowrap"
                           style="color: var(--brand-dark);">
                            Ver todos
                        </a>
                    </div>

                    <div class="rail-scroll px-3 pb-2" tabindex="0" aria-label="Últimos recebimentos">
                        @forelse ($ultimosRecebimentos as $conta)
                            <div class="list-row">
                                <span class="list-icon bg-primary-subtle text-primary-emphasis">
                                    <i class="bi bi-check2-circle"></i>
                                </span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold small text-truncate">{{ $conta->cliente }}</div>
                                    <div class="text-muted text-truncate" style="font-size: .72rem;">
                                        Recebido em {{ $conta->data_pagamento?->format('d/m') }}
                                        @if ($conta->forma_pagamento)
                                            · {{ $conta->forma_pagamento }}
                                        @endif
                                    </div>
                                </div>
                                <div class="fw-semibold small text-success text-nowrap">{{ $conta->valor_formatado }}</div>
                            </div>
                        @empty
                            <div class="empty-state py-3">
                                <i class="bi bi-receipt-cutoff"></i>
                                <p class="mb-0">Nenhum recebimento ainda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .stat-card-link {
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .stat-card-link:focus-visible {
            outline: 2px solid var(--brand);
            outline-offset: 3px;
            border-radius: 16px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resumoUrl = @json(route('dashboard.resumo'));

            const set = function (id, valor) {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = valor;
                }
            };

            /* Refresh the totals without reloading the page: on load, on focus and every 30s. */
            const atualizar = function () {
                fetch(resumoUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(function (resposta) {
                        return resposta.ok ? resposta.json() : null;
                    })
                    .then(function (dados) {
                        if (!dados) {
                            return;
                        }

                        set('kpi-receber', dados.a_receber);
                        set('kpi-recebido', dados.recebido);
                        set('kpi-vencido', dados.vencido);
                        set('kpi-a-vencer', dados.aVencer);
                        set('kpi-receber-qtd', dados.quantidadePendente);
                        set('kpi-recebido-qtd', dados.quantidadeRecebida);
                        set('kpi-vencido-qtd', dados.quantidadeVencida);
                        set('kpi-a-vencer-qtd', dados.quantidadeAVencer);
                        set('composicao-total', dados.totalGeral);
                        set('percentual-recebido', dados.percentualRecebido + '%');
                        set('percentual-vencido', dados.percentualVencido + '%');
                        set('percentual-a-vencer', dados.percentualAVencer + '%');

                        const donut = document.getElementById('composicao-donut');

                        if (donut) {
                            donut.style.background = dados.grafico;
                        }
                    })
                    .catch(function () {
                        // Keep the server-rendered values when the refresh fails.
                    });
            };

            atualizar();
            window.addEventListener('focus', atualizar);
            window.setInterval(atualizar, 30000);
        });
    </script>
@endpush
