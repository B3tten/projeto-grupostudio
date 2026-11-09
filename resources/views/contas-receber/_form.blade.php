@php
    // The prefix keeps element IDs unique when this form is rendered in both modals.
    $conta = $conta ?? null;
    $cancelDismiss = $cancelDismiss ?? false;
    $prefix = $prefix ?? 'conta';
    $campoId = fn (string $campo) => $prefix . '_' . $campo;
    $statusAtual = old('status', $conta->status ?? 'pendente');
    $vencimentoAtual = old('data_vencimento', $conta && $conta->data_vencimento ? $conta->data_vencimento->format('Y-m-d') : today()->format('Y-m-d'));
    $pagamentoAtual = old('data_pagamento', $conta && $conta->data_pagamento ? $conta->data_pagamento->format('Y-m-d') : '');
    $valorAtual = old('valor', $conta->valor ?? '');

    // Format the saved/old amount for the right-to-left mask; the server normalizes it back.
    if (is_numeric($valorAtual)) {
        $valorAtual = number_format((float) $valorAtual, 2, ',', '.');
    }

    if ($valorAtual === null || $valorAtual === '') {
        $valorAtual = '0,00';
    }
@endphp

<form action="{{ $action }}" method="POST" novalidate data-conta-form data-ajax-form data-form-mode="{{ $method === 'POST' ? 'create' : 'edit' }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="_conta_id" value="{{ old('_conta_id', $conta->id ?? '') }}">
    @include('contas-receber._filtros-ocultos', ['filtros' => $filtros ?? [], 'pagina' => $paginaAtual ?? 1])

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2 border-0 rounded-3">
            <i class="bi bi-exclamation-triangle-fill mt-1"></i>
            <div>
                <div class="fw-semibold mb-1">Verifique os campos abaixo.</div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-3 g-lg-4">
        <div class="col-12 col-md-8">
            <label for="{{ $campoId('cliente') }}" class="form-label">Cliente <span class="text-danger">*</span></label>
            <input type="text"
                   id="{{ $campoId('cliente') }}"
                   name="cliente"
                   class="form-control @error('cliente') is-invalid @enderror"
                   value="{{ old('cliente', $conta->cliente ?? '') }}"
                   placeholder="Nome do cliente">
            @error('cliente')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="{{ $campoId('documento') }}" class="form-label">Documento</label>
            <input type="text"
                   id="{{ $campoId('documento') }}"
                   name="documento"
                   class="form-control @error('documento') is-invalid @enderror"
                   value="{{ old('documento', $conta->documento ?? '') }}"
                   placeholder="Ex.: NF-00123">
            @error('documento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="{{ $campoId('descricao') }}" class="form-label">Descrição</label>
            <input type="text"
                   id="{{ $campoId('descricao') }}"
                   name="descricao"
                   class="form-control @error('descricao') is-invalid @enderror"
                   value="{{ old('descricao', $conta->descricao ?? '') }}"
                   placeholder="Ex.: Venda de mercadorias">
            @error('descricao')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="{{ $campoId('valor') }}" class="form-label">Valor <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">R$</span>
                <input type="text"
                       id="{{ $campoId('valor') }}"
                       name="valor"
                       inputmode="numeric"
                       autocomplete="off"
                       data-money
                       class="form-control text-end @error('valor') is-invalid @enderror"
                       value="{{ $valorAtual }}"
                       placeholder="0,00">
                @error('valor')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-12 col-md-4">
            <label for="{{ $campoId('data_vencimento') }}" class="form-label">Vencimento <span class="text-danger">*</span></label>
            <input type="date"
                   id="{{ $campoId('data_vencimento') }}"
                   name="data_vencimento"
                   class="form-control @error('data_vencimento') is-invalid @enderror"
                   value="{{ $vencimentoAtual }}">
            @error('data_vencimento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="{{ $campoId('status') }}" class="form-label">Status <span class="text-danger">*</span></label>
            <select id="{{ $campoId('status') }}" name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (\App\Models\ContaReceber::STATUS as $valor => $rotulo)
                    <option value="{{ $valor }}" @selected($statusAtual === $valor)>{{ $rotulo }}</option>
                @endforeach
            </select>
            <div class="form-text">Vencido é calculado automaticamente quando o vencimento passa.</div>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 col-md-4">
            <label for="{{ $campoId('forma_pagamento') }}" class="form-label">Forma de pagamento</label>
            <select id="{{ $campoId('forma_pagamento') }}" name="forma_pagamento" class="form-select @error('forma_pagamento') is-invalid @enderror">
                <option value="">Selecione (opcional)</option>
                @foreach (\App\Models\ContaReceber::FORMAS_PAGAMENTO as $forma)
                    <option value="{{ $forma }}" @selected(old('forma_pagamento', $conta->forma_pagamento ?? '') === $forma)>{{ $forma }}</option>
                @endforeach
            </select>
            @error('forma_pagamento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div id="{{ $prefix }}-pagamento-group" class="col-12 col-md-4 {{ $statusAtual === 'pago' ? '' : 'd-none' }}" data-pagamento-group>
            <label for="{{ $campoId('data_pagamento') }}" class="form-label">Data de pagamento</label>
            <input type="date"
                   id="{{ $campoId('data_pagamento') }}"
                   name="data_pagamento"
                   class="form-control @error('data_pagamento') is-invalid @enderror"
                   value="{{ $pagamentoAtual }}">
            <div class="form-text">Se deixar em branco, usaremos a data de hoje.</div>
            @error('data_pagamento')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="{{ $campoId('observacao') }}" class="form-label">Observação</label>
            <textarea id="{{ $campoId('observacao') }}"
                      name="observacao"
                      rows="3"
                      class="form-control @error('observacao') is-invalid @enderror"
                      placeholder="Informações adicionais sobre esta conta">{{ old('observacao', $conta->observacao ?? '') }}</textarea>
            @error('observacao')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-end gap-2 border-top mt-4 pt-3">
        @if ($cancelDismiss)
            <button type="button" class="btn btn-light-soft" data-bs-dismiss="modal">Cancelar</button>
        @else
            <a href="{{ route('contas-receber.index') }}" class="btn btn-light-soft">Cancelar</a>
        @endif
        <button type="submit" class="btn btn-brand">
            <i class="bi bi-check2 me-1"></i> {{ $submitLabel }}
        </button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-conta-form]').forEach(function (form) {
                if (form.dataset.pagamentoPronto) {
                    return;
                }

                form.dataset.pagamentoPronto = '1';

                const status = form.elements.status;
                const group = form.querySelector('[data-pagamento-group]');

                if (!status || !group) {
                    return;
                }

                const togglePaymentDate = function () {
                    group.classList.toggle('d-none', status.value !== 'pago');
                };

                status.addEventListener('change', togglePaymentDate);
                togglePaymentDate();
            });
        });
    </script>
@endpush
