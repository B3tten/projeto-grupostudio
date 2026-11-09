@extends('layouts.app')

@section('title', 'Contas a Receber')

@section('content')
    @php
        $abrirCarga = request()->boolean('carga') || $errors->has('arquivo');
        $abrirEdicao = ! $abrirCarga && $contaEdicao !== null;
        $abrirConta = ! $abrirCarga && ! $abrirEdicao && (request()->boolean('nova') || $errors->any());
        $paginaAtual = $contas->currentPage();
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

    <div id="alertas-ajax" aria-live="polite"></div>

    <div id="contas-tabela">
        @include('contas-receber._tabela')
    </div>

    @include('contas-receber._modal-conta', ['filtros' => $filtros, 'paginaAtual' => $paginaAtual])
    @include('contas-receber._modal-editar', ['filtros' => $filtros, 'paginaAtual' => $paginaAtual])
    @include('contas-receber._modal-receber', ['filtros' => $filtros, 'paginaAtual' => $paginaAtual])
    @include('contas-receber._modal-excluir', ['filtros' => $filtros, 'paginaAtual' => $paginaAtual])
    @include('contas-receber._modal-carga', ['filtros' => $filtros, 'paginaAtual' => $paginaAtual])
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
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const tabela = document.getElementById('contas-tabela');
            const alertas = document.getElementById('alertas-ajax');

            /* Currency mask: digits fill right to left and always keep two decimals. */
            const formatarMoeda = function (valor) {
                const digitos = String(valor ?? '').replace(/\D/g, '').slice(0, 15);
                const numero = (digitos === '' ? 0 : parseInt(digitos, 10)) / 100;

                return numero.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            };

            const aplicarMascara = function (input) {
                if (input && input.matches && input.matches('[data-money]')) {
                    input.value = formatarMoeda(input.value);
                }
            };

            document.querySelectorAll('[data-money]').forEach(aplicarMascara);

            document.addEventListener('input', function (event) {
                if (!event.target.matches('[data-money]')) {
                    return;
                }

                event.target.value = formatarMoeda(event.target.value);

                const fim = event.target.value.length;

                try {
                    event.target.setSelectionRange(fim, fim);
                } catch (erro) {
                    // Some date-like inputs do not support selection ranges.
                }
            });

            document.addEventListener('focusin', function (event) {
                if (event.target.matches('[data-money]') && event.target.value.trim() === '') {
                    event.target.value = '0,00';
                }
            });

            /* Modals opened by the server after a validation error. */
            document.querySelectorAll('[data-autoshow]').forEach(function (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            });

            /* Remove empty filter fields so the URL keeps only the active filters. */
            document.addEventListener('submit', function (event) {
                const form = event.target.closest('.header-search, #filtrosPanel form');

                if (!form) {
                    return;
                }

                form.querySelectorAll('input[name], select[name]').forEach(function (campo) {
                    if (campo.value === '') {
                        campo.disabled = true;
                    }
                });
            });

            /* Clear server-side error state whenever a modal is opened from a button. */
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
                    modal.querySelectorAll('[data-money]').forEach(aplicarMascara);
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

                    aplicarMascara(campos.valor);
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

            /* ----------------- AJAX submit: keep filters, refresh only the table ----------------- */

            const mostrarAlerta = function (mensagem, tipo) {
                if (!alertas) {
                    return;
                }

                const aviso = tipo === 'warning';
                const wrapper = document.createElement('div');
                wrapper.className = 'alert alert-' + (aviso ? 'warning' : 'success') +
                    ' alert-dismissible fade show d-flex align-items-center gap-2 border-0 rounded-3 shadow-sm';
                wrapper.setAttribute('role', 'alert');
                wrapper.innerHTML = '<i class="bi bi-' + (aviso ? 'exclamation-triangle-fill' : 'check-circle-fill') +
                    '"></i><div></div><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fechar"></button>';
                wrapper.querySelector('div').textContent = mensagem;
                alertas.prepend(wrapper);

                window.setTimeout(function () {
                    bootstrap.Alert.getOrCreateInstance(wrapper).close();
                }, 5000);
            };

            const limparErros = function (form) {
                form.querySelectorAll('.ajax-alert').forEach(function (alerta) {
                    alerta.remove();
                });
                form.querySelectorAll('.is-invalid').forEach(function (campo) {
                    campo.classList.remove('is-invalid');
                });
            };

            const mostrarErros = function (form, erros, mensagem) {
                limparErros(form);

                const lista = [];

                if (erros && typeof erros === 'object') {
                    Object.keys(erros).forEach(function (campo) {
                        (erros[campo] || []).forEach(function (texto) {
                            lista.push(texto);
                        });

                        const input = form.querySelector('[name="' + campo + '"]');

                        if (input) {
                            input.classList.add('is-invalid');
                        }
                    });
                }

                if (!lista.length && mensagem) {
                    lista.push(mensagem);
                }

                if (!lista.length) {
                    lista.push('Não foi possível concluir a operação.');
                }

                const alerta = document.createElement('div');
                alerta.className = 'alert alert-danger d-flex align-items-start gap-2 border-0 rounded-3 ajax-alert';
                alerta.setAttribute('role', 'alert');
                alerta.innerHTML = '<i class="bi bi-exclamation-triangle-fill mt-1"></i>' +
                    '<div><div class="fw-semibold mb-1">Verifique os campos abaixo.</div><ul class="mb-0 ps-3 small"></ul></div>';

                const ul = alerta.querySelector('ul');

                lista.forEach(function (texto) {
                    const li = document.createElement('li');
                    li.textContent = texto;
                    ul.appendChild(li);
                });

                form.prepend(alerta);
            };

            const fecharModaisAbertos = function () {
                document.querySelectorAll('.modal.show').forEach(function (modal) {
                    const instancia = bootstrap.Modal.getInstance(modal);

                    if (instancia) {
                        instancia.hide();
                    }
                });
            };

            document.addEventListener('submit', function (event) {
                const form = event.target.closest('form[data-ajax-form]');

                if (!form) {
                    return;
                }

                event.preventDefault();

                const botao = form.querySelector('[type="submit"]');
                const formData = new FormData(form);

                limparErros(form);

                if (botao) {
                    botao.disabled = true;
                    botao.dataset.originalHtml = botao.innerHTML;
                    botao.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
                }

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                })
                    .then(function (resposta) {
                        return resposta.json().catch(function () {
                            return null;
                        }).then(function (dados) {
                            return { ok: resposta.ok, status: resposta.status, dados: dados };
                        });
                    })
                    .then(function (resultado) {
                        const dados = resultado.dados || {};

                        if (!resultado.ok) {
                            mostrarErros(form, dados.errors, dados.message);
                            return;
                        }

                        if (tabela && typeof dados.html === 'string') {
                            tabela.innerHTML = dados.html;
                        }

                        fecharModaisAbertos();

                        if (dados.url) {
                            history.replaceState({}, '', dados.url);
                        }

                        mostrarAlerta(dados.message || 'Operação concluída.', dados.type || 'success');

                        if (form.dataset.formMode === 'create' || form.dataset.formMode === 'import') {
                            form.reset();
                            form.querySelectorAll('[data-money]').forEach(aplicarMascara);
                            form.querySelectorAll('.is-invalid').forEach(function (campo) {
                                campo.classList.remove('is-invalid');
                            });
                        }
                    })
                    .catch(function () {
                        mostrarErros(form, null, 'Falha de conexão. Verifique sua internet e tente novamente.');
                    })
                    .finally(function () {
                        if (botao) {
                            botao.disabled = false;
                            if (botao.dataset.originalHtml) {
                                botao.innerHTML = botao.dataset.originalHtml;
                            }
                        }
                    });
            });

            /* Drop params that only control modal state; keep the filters themselves. */
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
