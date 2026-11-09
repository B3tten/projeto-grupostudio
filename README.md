# Gestão Financeira — Contas a Receber

Olá! Este repositório é o meu projeto teste para a vaga de **Desenvolvedor Web (Grupo Estúdio)**. A ideia era construir uma aplicação de gestão financeira focada em **contas a receber**, com layout moderno e responsivo.

O sistema mostra os indicadores financeiros, lista os recebíveis, permite pesquisar e filtrar, e deixa criar, editar, excluir e registrar recebimentos sem sair da listagem. Ele roda com **Laravel 9 + SQLite** no backend e **Bootstrap 5 + JavaScript puro** no frontend.

> **Roda em outra máquina?** Clone, `composer install`, copie o `.env`, crie o arquivo `database/database.sqlite`, rode `php artisan migrate --seed` e suba com `php artisan serve`. O passo a passo resumido está em [`docs/INSTALACAO.md`](docs/INSTALACAO.md).

---

## O que você encontra aqui

- **Dashboard** com os quatro indicadores pedidos: **Total a receber**, **Total recebido**, **Total vencido** e **Total a vencer**, além do gráfico de recebimentos dos últimos 6 meses e da composição da carteira.
- **Listagem de contas** com busca por cliente/descrição/documento, filtro por status e período de vencimento, contador de filtros ativos e "Total filtrado".
- **CRUD completo em modais** na própria listagem: cadastrar, editar, excluir (com a exclusão feita pelo próprio modal de revisão, sem `confirm()`) e **Registrar recebimento** com uma data (hoje, se o campo ficar em branco).
- **Filtros preservados**: criar, editar, excluir ou registrar recebimento atualiza **apenas a tabela**, sem recarregar a página e sem perder o filtro aplicado. Se a página for recarregada, o filtro usado por último se mantém.
- **Máscara de moeda** que preenche da direita para a esquerda e sempre mantém duas casas decimais (digite `123456` e veja `1.234,56`).
- **22 contas fictícias** no seeder para o dashboard não nascer vazio.
- Extras: importação de contas via **CSV/OFX** com modelos para download, e um log de requisições em `logs/` (não versionado).

## Rodando em 3 minutos

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
cp .env.example .env      # PowerShell: Copy-Item .env.example .env
php artisan key:generate
touch database/database.sqlite   # PowerShell: New-Item database\database.sqlite -ItemType File
php artisan migrate --seed
php artisan serve
```

Abra **http://127.0.0.1:8000** e entre no dashboard. Se algo falhar, o passo a passo completo (com a tabela de problemas comuns) está em [`docs/INSTALACAO.md`](docs/INSTALACAO.md).

Os testes automatizados ficam num banco SQLite em memória, então não tocam no banco de demonstração:

```bash
php artisan test    # 11 testes passando
```

## Algumas escolhas que valem mencionar

- **"Vencido" não é uma situação guardada no banco.** É derivado na hora: pendente + `data_vencimento` anterior a hoje. Isso evita job agendado e mantém as telas corretas sem esforço.
- O status armazenado é `pago`; na tela ele aparece como **Recebido**. `cancelado` existe como situação administrativa, mas contas canceladas não entram nos totais nem podem ser recebidas de novo.
- Os formulários usam **AJAX (fetch + JSON)**, com o controller devolvendo apenas o HTML re-renderizado da tabela. Sem o JavaScript, o formulário funciona normalmente como fallback e redireciona para a URL filtrada.
- Usei **SQLite** exatamente para que você não precise subir nada além de `php artisan serve`.
- O **filtro Pendente exclui as vencidas** (elas ficam em Vencido); "A receber" é o filtro que mostra todas as pendentes, como no card clicável do dashboard.
- Os **cards do dashboard levam para a listagem já filtrada**, e os totais e a composição se atualizam sozinhos na página (a cada 30 s e ao focar a janela).

## Segurança (o mínimo necessário)

Pontos que valem um olhar, porque são os que manteriam o projeto seguro:

- **`.env` nunca entra no Git** — apenas o `.env.example`, sem segredos. `.gitignore` bloqueia `.env`, `/logs/*`, `storage/logs/*`, caches, bancos `.sqlite`, dumps, chaves e `auth.json`.
- Os logs do middleware ficam em `logs/`, fora do versionamento e fora da web; eles podem conter nomes de clientes, valores e SQL.
- O banco de demonstração é recriado com `php artisan migrate --seed`; o arquivo SQLite não é versionado.
- O projeto **não tem login** (foi pedido como demonstração). Para expor com dados reais, é preciso autenticação, HTTPS e `APP_DEBUG=false` em produção.

O detalhamento — inclusive o que existe em produção, document root em `public/`, rotação de segredos e checklist — está em [`docs/SEGURANCA.md`](docs/SEGURANCA.md).

## Sobre o uso de IA

Sim, usei IA como **par de programação** durante todo o desenvolvimento: para acelerar a implementação, revisar decisões, achar bugs reais (IDs duplicados nos modais, vazamento de variável de loop do Blade, reabertura correta do modal após erro de validação) e escrever boa parte desta documentação. Tudo foi validado manualmente — as regras de negócio, os fluxos de CRUD/recebimento/filtros/importação e a consistência dos totais no dashboard. O relato completo, com o que foi feito manualmente e o que a IA sugeriu, está em [`docs/USO_DE_IA.md`](docs/USO_DE_IA.md).

## O que eu melhoraria numa próxima versão

Sou honesto do que falta: **login e permissões**, atualização do **Laravel 9** (as vulnerabilidades conhecidas estão documentadas em vez de resolvidas), HTTPS para produção e um pipeline mínimo de CI. Numa aplicação real eu faria esses itens antes de qualquer evolução de funcionalidade.

## Checklist do teste (mapa rápido)

| Requisito | Onde está |
| --- | --- |
| Dashboard com total a receber / recebido / vencido / a vencer | `DashboardController@index` |
| Listagem, cadastro, edição, exclusão | `ContaReceberController` + `views/contas-receber/` |
| Cliente, descrição, valor, vencimento | Formulário em `_form.blade.php` |
| Status Pendente / Recebido / Vencido | `ContaReceber::STATUS` + situação derivada |
| Registrar recebimento | `POST contas-a-receber/{conta}/receber` |
| Busca por cliente e filtro por status | `ContaReceber::scopeFiltros` |
| Layout moderno e responsivo | Bootstrap 5 + CSS próprio |
| Dados fictícios no banco | `ContaReceberSeeder` (22 registros) |
| Instruções para executar | Este README + `docs/INSTALACAO.md` |
| Descrição do uso de IA e do processo | `docs/USO_DE_IA.md` e `docs/PROCESSO.md` |
| Segurança / arquivos sensíveis | `.gitignore` + `docs/SEGURANCA.md` |
| Testes automatizados | `tests/Feature/ContaReceberTest.php` (11 testes) |

## Estrutura, de um jeito resumido

```text
app/
  Http/Controllers/        DashboardController, ContaReceberController, ContaReceberCargaController
  Http/Controllers/Concerns/ListaContasReceber.php   # filtros e resposta AJAX/JSON
  Http/Requests/           ContaReceberRequest (base) + Store/Update (validação/máscara de moeda)
  Http/Middleware/         LogBackendCalls.php        # logs de entrada, SQL e saída
  Models/ContaReceber.php                             # regras de status, filtros, formatação
  Services/ContaReceberImportService.php              # parsers CSV/OFX
database/
  factories/ContaReceberFactory.php                   # fabrica contas fictícias (testes)
  migrations/ seeders/     contas_receber + 22 contas fictícias
resources/views/
  dashboard/index.blade.php
  contas-receber/          index (listing) + partials de tabela/modais/formulário
  layouts/app.blade.php
routes/web.php             dashboard, CRUD, carga e recebimento
docs/                      INSTALACAO · PROCESSO · USO_DE_IA · SEGURANCA
```

Obrigado por chegar até aqui, espero que o projeto tenha sido satisfatório. O repositório está em **https://github.com/B3tten/projeto-grupostudio**.

— Gabriel Bettencourt Vieira

