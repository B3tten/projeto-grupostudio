<div class="modal fade"
     id="contaModal"
     tabindex="-1"
     aria-labelledby="contaModalLabel"
     aria-hidden="true"
     @if ($abrirConta) data-autoshow @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="contaModalLabel">Nova conta</h2>
                    <p class="section-subtitle">Cadastre um novo recebível e acompanhe o vencimento.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                @include('contas-receber._form', [
                    'conta' => null,
                    'action' => route('contas-receber.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Salvar conta',
                    'cancelDismiss' => true,
                    'prefix' => 'nova',
                    'filtros' => $filtros ?? [],
                    'paginaAtual' => $paginaAtual ?? 1,
                ])
            </div>
        </div>
    </div>
</div>
