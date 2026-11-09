@php($contaEdicaoId = $contaEdicao?->id)

<div class="modal fade"
     id="editarModal"
     tabindex="-1"
     aria-labelledby="editarModalLabel"
     aria-hidden="true"
     @if ($abrirEdicao) data-autoshow @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="editarModalLabel">Editar conta</h2>
                    <p class="section-subtitle">Atualize os dados do recebível selecionado.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                @include('contas-receber._form', [
                    'conta' => $contaEdicao,
                    'action' => $contaEdicaoId ? route('contas-receber.update', ['conta' => $contaEdicaoId]) : '#',
                    'method' => 'PUT',
                    'submitLabel' => 'Atualizar conta',
                    'cancelDismiss' => true,
                    'prefix' => 'editar',
                ])
            </div>
        </div>
    </div>
</div>
