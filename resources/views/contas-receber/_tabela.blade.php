@php($filtrosAtivos = count($filtros))

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
                                @foreach (['a_receber' => 'A receber', 'pendente' => 'Pendente', 'pago' => 'Recebido', 'vencido' => 'Vencido', 'cancelado' => 'Cancelado'] as $valor => $rotulo)
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
                                    data-valor="{{ number_format((float) $conta->valor, 2, ',', '.') }}"
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
