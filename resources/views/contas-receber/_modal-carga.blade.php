<div class="modal fade"
     id="cargaModal"
     tabindex="-1"
     aria-labelledby="cargaModalLabel"
     aria-hidden="true"
     @if ($abrirCarga) data-autoshow @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="cargaModalLabel">Carga de contas</h2>
                    <p class="section-subtitle">Importe vários recebíveis de uma vez usando CSV ou OFX.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                @if ($abrirCarga && $errors->any())
                    <div class="alert alert-danger d-flex align-items-start gap-2 border-0 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('contas-receber.importar') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      data-ajax-form
                      data-form-mode="import">
                    @csrf
                    @include('contas-receber._filtros-ocultos', ['filtros' => $filtros ?? [], 'pagina' => $paginaAtual ?? 1])

                    <div class="upload-zone">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <p class="fw-semibold mb-1">Selecione o arquivo</p>
                        <p class="text-muted small mb-3">CSV com separador <strong>;</strong> ou OFX bancário · até 10 MB</p>

                        <input type="file"
                               id="arquivo"
                               name="arquivo"
                               accept=".csv,.ofx"
                               class="form-control @error('arquivo') is-invalid @enderror"
                               aria-describedby="arquivoAjuda">
                        @error('arquivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="arquivoAjuda" class="form-text mt-2">O arquivo é processado linha a linha.</div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2 border-top mt-4 pt-3">
                        <button type="button" class="btn btn-light-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-brand">
                            <i class="bi bi-upload me-1"></i> Importar contas
                        </button>
                    </div>
                </form>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top">
                    <span class="text-muted small me-1">Modelos com 3 linhas de exemplo:</span>
                    <a href="{{ route('contas-receber.modelo', 'csv') }}" class="model-link">
                        <i class="bi bi-filetype-csv me-1"></i> CSV
                        <i class="bi bi-download ms-1"></i>
                    </a>
                    <a href="{{ route('contas-receber.modelo', 'ofx') }}" class="model-link">
                        <i class="bi bi-file-earmark-text me-1"></i> OFX
                        <i class="bi bi-download ms-1"></i>
                    </a>
                </div>

                <p class="text-muted small mb-0 mt-3">
                    Colunas obrigatórias no CSV: <strong>cliente</strong>, <strong>valor</strong> e
                    <strong>data_vencimento</strong>. Contas importadas entram como <strong>Pendente</strong>,
                    exceto quando o CSV informar o status.
                </p>
            </div>
        </div>
    </div>
</div>
