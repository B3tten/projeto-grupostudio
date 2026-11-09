<div class="modal fade"
     id="receberModal"
     tabindex="-1"
     aria-labelledby="receberModalLabel"
     aria-hidden="true"
     data-hoje="{{ today()->format('Y-m-d') }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="receberModalLabel">Registrar recebimento</h2>
                    <p class="section-subtitle">A conta será marcada como recebida.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <form action="#" method="POST" data-ajax-form>
                    @csrf
                    @include('contas-receber._filtros-ocultos', ['filtros' => $filtros ?? [], 'pagina' => $paginaAtual ?? 1])

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="receber_cliente" class="form-label">Cliente</label>
                            <input type="text" id="receber_cliente" class="form-control" data-campo="cliente" readonly>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="receber_valor" class="form-label">Valor</label>
                            <input type="text" id="receber_valor" class="form-control" data-campo="valor" readonly>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="receber_vencimento" class="form-label">Vencimento</label>
                            <input type="text" id="receber_vencimento" class="form-control" data-campo="vencimento" readonly>
                        </div>

                        <div class="col-12">
                            <label for="receber_data_pagamento" class="form-label">Data do recebimento</label>
                            <input type="date"
                                   id="receber_data_pagamento"
                                   name="data_pagamento"
                                   class="form-control"
                                   value="{{ today()->format('Y-m-d') }}">
                            <div class="form-text">Se deixar em branco, usaremos a data de hoje.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 border-top mt-4 pt-3">
                        <button type="button" class="btn btn-light-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-brand">
                            <i class="bi bi-check2-circle me-1"></i> Registrar recebimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
