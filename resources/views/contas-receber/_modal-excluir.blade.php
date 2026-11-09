<div class="modal fade"
     id="excluirModal"
     tabindex="-1"
     aria-labelledby="excluirModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="excluirModalLabel">Excluir conta</h2>
                    <p class="section-subtitle">Confira os dados abaixo. Esta ação não pode ser desfeita.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <form action="#" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="row g-3 g-lg-4">
                        <div class="col-12 col-md-8">
                            <label for="excluir_cliente" class="form-label">Cliente</label>
                            <input type="text" id="excluir_cliente" class="form-control" data-campo="cliente" readonly>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="excluir_documento" class="form-label">Documento</label>
                            <input type="text" id="excluir_documento" class="form-control" data-campo="documento" readonly>
                        </div>

                        <div class="col-12">
                            <label for="excluir_descricao" class="form-label">Descrição</label>
                            <input type="text" id="excluir_descricao" class="form-control" data-campo="descricao" readonly>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="excluir_valor" class="form-label">Valor</label>
                            <input type="text" id="excluir_valor" class="form-control" data-campo="valor" readonly>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="excluir_vencimento" class="form-label">Vencimento</label>
                            <input type="text" id="excluir_vencimento" class="form-control" data-campo="vencimento" readonly>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="excluir_status" class="form-label">Status</label>
                            <input type="text" id="excluir_status" class="form-control" data-campo="status" readonly>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="excluir_forma" class="form-label">Forma de pagamento</label>
                            <input type="text" id="excluir_forma" class="form-control" data-campo="forma" readonly>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="excluir_pagamento" class="form-label">Data de pagamento</label>
                            <input type="text" id="excluir_pagamento" class="form-control" data-campo="pagamento" readonly>
                        </div>

                        <div class="col-12">
                            <label for="excluir_observacao" class="form-label">Observação</label>
                            <textarea id="excluir_observacao" rows="3" class="form-control" data-campo="observacao" readonly></textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 border-top mt-4 pt-3">
                        <button type="button" class="btn btn-light-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash3 me-1"></i> Deletar conta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
