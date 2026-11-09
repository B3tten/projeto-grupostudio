# Processo de desenvolvimento

Resumo das etapas seguidas para construir o projeto **Gestão Financeira — Contas a Receber**.

## 1. Entendimento do escopo

O ponto de partida foi transformar o texto do teste em uma lista verificável de requisitos:

- Dashboard com quatro indicadores financeiros.
- CRUD de contas a receber.
- Status Pendente, Recebido e Vencido.
- Ação de registrar recebimento.
- Pesquisa e filtros por cliente e status.
- Interface responsiva e indicadores sempre atualizados.
- README, dados fictícios e relato de uso de IA.

Também foram definidos os itens fora do escopo obrigatório, mas úteis para a demonstração: importação CSV/OFX e log das requisições.

## 2. Modelagem dos dados

A tabela `contas_receber` foi criada com os campos necessários para o CRUD e para a demonstração:

- `cliente`, `descricao`, `documento`, `valor`, `data_vencimento`.
- `status`, `data_pagamento`, `forma_pagamento`, `observacao`.

Uma decisão importante foi **não persistir o status Vencido**. Ele é derivado de `status = pendente` + `data_vencimento < hoje`. Isso evita uma rotina agendada para atualizar registros todos os dias e mantém o status sempre coerente.

## 3. Backend

- Rotas REST para o CRUD e uma rota dedicada para registrar recebimento.
- Validação centralizada no controller, com mensagens em português.
- Regra de coerência entre status e data de pagamento:
  - Conta recebida sem data informada recebe a data atual.
  - Conta que deixa de ser recebida tem a data de pagamento limpa.
- Filtros implementados como scope no model, reutilizados pela listagem.
- Dashboard com agregações calculadas a cada requisição.
- Seeder com 22 registros fictícios distribuídos entre recebidos, vencidos, a vencer e cancelados.

## 4. Frontend

- Layout em Blade com Bootstrap 5 e JavaScript puro.
- Dashboard compacto, pensado para 1920×1080 sem rolagem de página.
- Navegação principal entre Dashboard e Contas a Receber.
- Listagem com busca no cabeçalho, filtro por status/período e total filtrado.
- Cadastro, edição, recebimento e exclusão em modais, preservando o contexto da listagem.
- Um único modal de edição e um único de exclusão, preenchidos a partir dos dados da linha, para evitar IDs duplicados e duplicação de formulário.
- Exclusão com todos os campos somente leitura e botão vermelho, sem `confirm()` do navegador.
- Ajustes responsivos para telas menores.

## 5. Validação

Cada entrega foi testada no navegador:

- Cadastro, edição e exclusão de contas.
- Registro de recebimento e atualização imediata do dashboard.
- Filtros por status, incluindo Vencido.
- Busca por cliente.
- Reabertura do modal correto após erro de validação.
- Importação CSV e OFX com arquivos de exemplo.
- Verificação dos totais do dashboard contra o banco.
- Conferência de logs gerados.

Também foram adicionados testes automatizados com PHPUnit e SQLite em memória para os cálculos do dashboard, filtro de vencidos, registro de recebimento e CRUD.

## 6. Ajustes e limpeza

- Remoção de um CRUD de produtos que não fazia parte do escopo.
- Correção de vazamento de variável de loop do Blade que preenchia o modal de cadastro.
- Padronização do termo **Vencido** na interface.
- Ajuste do `.env.example` para o banco real do projeto.
- Endurecimento do `.gitignore` para bloquear `.env`, `/logs/*`, logs do Laravel, caches, bancos SQLite, dumps e chaves.
- Remoção de detalhes internos das mensagens de erro da importação e registro do erro no log da aplicação.
- Comentários pontuais apenas em regras de negócio e decisões não óbvias.
- Revisão da documentação e dos dados de demonstração.
- Criação do checklist de segurança e publicação em `docs/SEGURANCA.md`.

## 7. Uso de IA

A IA foi usada como par de programação em todas as etapas, com revisão e validação humana. O relato completo está em [`USO_DE_IA.md`](USO_DE_IA.md).
