@extends('layouts.app')

@section('title', 'Contas a Receber')

@section('content')
    @php
        $abrirCarga = request()->boolean('carga') || $errors->has('arquivo');
        $abrirEdicao = ! $abrirCarga && $contaEdicao !== null;
        $abrirConta = ! $abrirCarga && ! $abrirEdicao && (request()->boolean('nova') || $errors->any());
        $filtrosAtivos = count($filtros);
    @endphp

    <div class="d-flex flex-wrap align-items-start align-items-xl-center justify-content-between gap-3 mb-4">
        <div>
            <p class="page-eyebrow mb-2">Financeiro</p>
            <h1 class="page-title mb-1">Contas a Receber</h1>
            <p class="text-muted mb-0">Gerencie os recebíveis, filtre por status e acompanhe os vencimentos.</p>
        </div>

        <div class="header-actions">
            <form method="GET" action="{{ route('contas-receber.index') }}" class="header-search" role="search">
                @foreach (['status', 'vencimento_de', 'vencimento_ate'] as $campo)
                    @if (! empty($filtros[$campo]))
                        <input type="hidden" name="{{ $campo }}" value="{{ $filtros[$campo] }}">
                    @endif
                @endforeach

                <div class="input-group">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="search"
                           id="busca"
                           name="busca"
                           class="form-control border-start-0 ps-0"
                           value="{{ $filtros['busca'] ?? '' }}"
                           placeholder="Buscar cliente ou documento"
                           aria-label="Buscar contas a receber">
                </div>
            </form>

            <div class="dropdown flex-shrink-0">
                <button type="button"
                        class="btn btn-light-soft px-2"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        title="Baixar modelo carga CSV ou OFX"
                        aria-label="Baixar modelos de importação">
                    <i class="bi bi-download"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Modelos de carga</h6></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('contas-receber.modelo', 'csv') }}">
                            <i class="bi bi-filetype-csv me-2"></i> Exemplo CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('contas-receber.modelo', 'ofx') }}">
                            <i class="bi bi-file-earmark-text me-2"></i> Exemplo OFX
                        </a>
                    </li>
                </ul>
            </div>

            <button type="button"
                    class="btn btn-light-soft flex-shrink-0"
                    data-bs-toggle="modal"
                    data-bs-target="#cargaModal">
                <i class="bi bi-upload me-1"></i> Carga
            </button>

            <button type="button"
                    class="btn btn-brand flex-shrink-0"
                    data-bs-toggle="modal"
                    data-bs-target="#contaModal">
                <i class="bi bi-plus-lg me-1"></i> Nova conta
            </button>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="text-muted small">
            @if ($contas->total() > 0)
                Mostrando <strong class="text-dark">{{ $contas->firstItem() }}–{{ $contas->lastItem() }}</strong>
                de <strong class="text-dark">{{ $contas->total() }}</strong> contas
            @else
                Nenhuma conta encontrada
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button type="button"
                        class="btn btn-light-soft px-2 filter-toggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        aria-controls="filtrosPanel"
                        aria-label="Filtros"
                        title="Filtros">
                    <i class="bi bi-funnel"></i>
                    @if ($filtrosAtivos > 0)
                        <span class="filter-count" title="{{ $filtrosAtivos }} filtro(s) ativo(s)">{{ $filtrosAtivos }}</span>
                    @endif
                </button>

                <div class="dropdown-menu dropdown-menu-end filter-dropdown shadow" id="filtrosPanel">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <h2 class="section-title mb-0">Filtrar contas</h2>
                        <a href="{{ route('contas-receber.index') }}" class="small fw-semibold text-decoration-none" style="color: var(--brand-dark);">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Limpar tudo
                        </a>
                    </div>

                    <form method="GET" action="{{ route('contas-receber.index') }}">
                        @if (! empty($filtros['busca']))
                            <input type="hidden" name="busca" value="{{ $filtros['busca'] }}">
                        @endif

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-4">
                                <label for="filtro_status" class="form-label">Status</label>
                                <select id="filtro_status" name="status" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach (['pendente' => 'Pendente', 'pago' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'] as $valor => $rotulo)
                                        <option value="{{ $valor }}" @selected(($filtros['status'] ?? '') === $valor)>{{ $rotulo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-4">
                                <label for="filtro_vencimento_de" class="form-label">Vencimento de</label>
                                <input type="date"
                                       id="filtro_vencimento_de"
                                       name="vencimento_de"
                                       class="form-control"
                                       value="{{ $filtros['vencimento_de'] ?? '' }}">
                            </div>

                            <div class="col-6 col-md-4">
                                <label for="filtro_vencimento_ate" class="form-label">Vencimento até</label>
                                <input type="date"
                                       id="filtro_vencimento_ate"
                                       name="vencimento_ate"
                                       class="form-control"
                                       value="{{ $filtros['vencimento_ate'] ?? '' }}">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top mt-3 pt-3">
                            <a href="{{ route('contas-receber.index') }}" class="btn btn-light-soft">Limpar</a>
                            <button type="submit" class="btn btn-brand">
                                <i class="bi bi-check2 me-1"></i> Aplicar filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="filter-total">
                <span class="text-muted">Total filtrado</span>
                <strong>R$ {{ number_format($resumo['total'], 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div class="card card-surface overflow-hidden">
        <div class="table-responsive">
            <table class="table table-app align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contas as $conta)
                        <tr>
                            <td style="min-width: 240px;">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="client-avatar">{{ $conta->iniciais }}</span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold">{{ $conta->cliente }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 260px;">
                                            {{ $conta->descricao ?: 'Sem descrição' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small">{{ $conta->documento ?: '—' }}</td>
                            <td style="min-width: 150px;">
                                <div class="fw-semibold {{ $conta->isVencida() ? 'text-danger' : '' }}">
                                    {{ $conta->data_vencimento->format('d/m/Y') }}
                                </div>
                                <div class="small">
                                    @if ($conta->data_pagamento)
                                        <span class="text-success">Recebido em {{ $conta->data_pagamento->format('d/m/Y') }}</span>
                                    @elseif ($conta->isVencida())
                                        <span class="text-danger">{{ (int) $conta->data_vencimento->diffInDays(today()) }} dia(s) em atraso</span>
                                    @else
                                        @php($diasParaVencer = (int) today()->startOfDay()->diffInDays($conta->data_vencimento->startOfDay()))
                                        <span class="text-muted">
                                            {{ $diasParaVencer === 0 ? 'Vence hoje' : ($diasParaVencer === 1 ? 'Vence amanhã' : 'Vence em ' . $diasParaVencer . ' dias') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="fw-semibold text-nowrap">{{ $conta->valor_formatado }}</td>
                            <td>
                                <span class="badge rounded-pill text-bg-{{ $conta->situacao_badge }}">
                                    {{ $conta->situacao_label }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($conta->status === 'pendente')
                                    <button type="button"
                                            class="btn btn-sm btn-success"
                                            title="Registrar recebimento"
                                            data-bs-toggle="modal"
                                            data-bs-target="#receberModal"
                                            data-receber-action="{{ route('contas-receber.receber', ['conta' => $conta]) }}"
                                            data-cliente="{{ $conta->cliente }}"
                                            data-valor="{{ $conta->valor_formatado }}"
                                            data-vencimento="{{ $conta->data_vencimento->format('d/m/Y') }}">
                                        <i class="bi bi-check2-circle"></i>
                                    </button>
                                @endif
                                <button type="button"
                                        class="btn btn-sm btn-light-soft"
                                        title="Editar conta"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editarModal"
                                        data-conta-action="{{ route('contas-receber.update', ['conta' => $conta]) }}"
                                        data-conta-id="{{ $conta->id }}"
                                        data-cliente="{{ $conta->cliente }}"
                                        data-documento="{{ $conta->documento }}"
                                        data-descricao="{{ $conta->descricao }}"
                                        data-valor="{{ $conta->valor }}"
                                        data-vencimento="{{ $conta->data_vencimento->format('Y-m-d') }}"
                                        data-pagamento="{{ $conta->data_pagamento?->format('Y-m-d') }}"
                                        data-status="{{ $conta->status }}"
                                        data-forma="{{ $conta->forma_pagamento }}"
                                        data-observacao="{{ $conta->observacao }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Excluir conta"
                                        data-bs-toggle="modal"
                                        data-bs-target="#excluirModal"
                                        data-conta-action="{{ route('contas-receber.destroy', ['conta' => $conta]) }}"
                                        data-cliente="{{ $conta->cliente }}"
                                        data-documento="{{ $conta->documento }}"
                                        data-descricao="{{ $conta->descricao }}"
                                        data-valor="{{ $conta->valor_formatado }}"
                                        data-vencimento="{{ $conta->data_vencimento->format('d/m/Y') }}"
                                        data-pagamento="{{ $conta->data_pagamento?->format('d/m/Y') }}"
                                        data-status="{{ $conta->situacao_label }}"
                                        data-forma="{{ $conta->forma_pagamento }}"
                                        data-observacao="{{ $conta->observacao }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h6 class="mb-1">Nenhuma conta encontrada</h6>
                                    <p class="mb-3">Ajuste os filtros ou cadastre uma nova conta a receber.</p>
                                    <button type="button" class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#contaModal">
                                        <i class="bi bi-plus-lg me-1"></i> Cadastrar conta
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contas->hasPages())
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2 border-top bg-white px-4 py-3">
                <div class="text-muted small">
                    Página {{ $contas->currentPage() }} de {{ $contas->lastPage() }}
                </div>
                @include('partials.pagination', ['paginator' => $contas])
            </div>
        @endif
    </div>

    @include('contas-receber._modal-conta')
    @include('contas-receber._modal-editar')
    @include('contas-receber._modal-receber')
    @include('contas-receber._modal-excluir')
    @include('contas-receber._modal-carga')
@endsection

@push('styles')
    <style>
        .header-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: .5rem;
            width: 100%;
        }

        .header-search {
            flex: 1 1 100%;
            min-width: 0;
        }

        @media (min-width: 992px) {
            .header-actions {
                width: auto;
                flex-wrap: nowrap;
            }

            .header-search {
                flex: 0 0 auto;
                width: 320px;
            }
        }

        @media (min-width: 1400px) {
            .header-search {
                width: 380px;
            }
        }

        .filter-toggle {
            position: relative;
        }

        .filter-count {
            position: absolute;
            top: -.45rem;
            left: -.45rem;
            display: grid;
            place-items: center;
            min-width: 20px;
            height: 20px;
            padding: 0 .3rem;
            border-radius: 999px;
            background: var(--brand);
            color: #fff;
            font-size: .66rem;
            font-weight: 700;
            line-height: 1;
            box-shadow: 0 3px 8px rgba(15, 159, 110, .4);
            pointer-events: none;
        }

        .filter-dropdown {
            width: min(620px, calc(100vw - 1.5rem));
            padding: 1rem 1.1rem;
            border: 1px solid var(--line);
            border-radius: 14px;
        }

        .upload-zone {
            padding: 1.4rem;
            border: 1.5px dashed #cfd8e6;
            border-radius: 14px;
            background: #fbfcfe;
            text-align: center;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .upload-zone:hover,
        .upload-zone:focus-within {
            border-color: #8fd9c2;
            background: #f7fcfa;
        }

        .upload-zone > i {
            display: block;
            margin-bottom: .5rem;
            color: #98a2b3;
            font-size: 1.9rem;
        }

        .model-link {
            display: inline-flex;
            align-items: center;
            padding: .45rem .7rem;
            border: 1px solid var(--line);
            border-radius: 9px;
            color: #475467;
            font-size: .8rem;
            font-weight: 600;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .model-link:hover {
            border-color: #8fd9c2;
            background: #f7fcfa;
            color: var(--brand-dark);
        }

        .form-control[readonly] {
            border-color: #e7ebf3;
            background: #f8fafc;
            color: #344054;
        }

        .btn-danger {
            padding: .58rem 1rem;
            border-radius: 10px;
            font-size: .88rem;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(220, 53, 69, .18);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-autoshow]').forEach(function (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            });

            document.querySelectorAll('.header-search, #filtrosPanel form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    form.querySelectorAll('input[name], select[name]').forEach(function (campo) {
                        if (campo.value === '') {
                            campo.disabled = true;
                        }
                    });
                });
            });

            document.querySelectorAll('.modal').forEach(function (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    if (!event.relatedTarget) {
                        return;
                    }

                    modal.querySelectorAll('.alert-danger').forEach(function (alerta) {
                        alerta.remove();
                    });

                    modal.querySelectorAll('.is-invalid').forEach(function (campo) {
                        campo.classList.remove('is-invalid');
                    });
                });
            });

            const editarModal = document.getElementById('editarModal');

            if (editarModal) {
                editarModal.addEventListener('show.bs.modal', function (event) {
                    const botao = event.relatedTarget;

                    if (!botao || !botao.dataset.contaAction) {
                        return;
                    }

                    const form = editarModal.querySelector('form[data-conta-form]');
                    const campos = form.elements;
                    const preencher = function (nome, valor) {
                        if (campos[nome]) {
                            campos[nome].value = valor || '';
                        }
                    };

                    form.action = botao.dataset.contaAction;
                    preencher('_conta_id', botao.dataset.contaId);
                    preencher('cliente', botao.dataset.cliente);
                    preencher('documento', botao.dataset.documento);
                    preencher('descricao', botao.dataset.descricao);
                    preencher('valor', botao.dataset.valor);
                    preencher('data_vencimento', botao.dataset.vencimento);
                    preencher('data_pagamento', botao.dataset.pagamento);
                    preencher('status', botao.dataset.status);
                    preencher('forma_pagamento', botao.dataset.forma);
                    preencher('observacao', botao.dataset.observacao);

                    campos.status.dispatchEvent(new Event('change'));
                });
            }

            const receberModal = document.getElementById('receberModal');

            if (receberModal) {
                receberModal.addEventListener('show.bs.modal', function (event) {
                    const botao = event.relatedTarget;

                    if (!botao || !botao.dataset.receberAction) {
                        return;
                    }

                    const form = receberModal.querySelector('form');
                    form.action = botao.dataset.receberAction;

                    receberModal.querySelectorAll('[data-campo]').forEach(function (campo) {
                        campo.value = botao.dataset[campo.dataset.campo] || '—';
                    });

                    const dataPagamento = form.elements.data_pagamento;

                    if (dataPagamento) {
                        dataPagamento.value = receberModal.dataset.hoje || '';
                    }
                });
            }

            const excluirModal = document.getElementById('excluirModal');

            if (excluirModal) {
                excluirModal.addEventListener('show.bs.modal', function (event) {
                    const botao = event.relatedTarget;

                    if (!botao || !botao.dataset.contaAction) {
                        return;
                    }

                    excluirModal.querySelector('form').action = botao.dataset.contaAction;

                    excluirModal.querySelectorAll('[data-campo]').forEach(function (campo) {
                        campo.value = botao.dataset[campo.dataset.campo] || '—';
                    });
                });
            }

            const url = new URL(window.location.href);
            let alterou = false;

            ['nova', 'carga', 'editar'].forEach(function (parametro) {
                if (url.searchParams.has(parametro)) {
                    url.searchParams.delete(parametro);
                    alterou = true;
                }
            });

            if (alterou) {
                history.replaceState({}, '', url.pathname + (url.search || ''));
            }
        });
    </script>
@endpush
