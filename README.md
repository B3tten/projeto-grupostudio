# Gestão Financeira — Contas a Receber

Aplicação web de gestão financeira focada em **contas a receber**, desenvolvida como teste técnico para a vaga de Desenvolvedor PHP/Laravel (Grupo Estúdio · Plataforma PJ360).

O sistema permite acompanhar os principais indicadores financeiros, cadastrar e manter recebíveis, pesquisar/filtrar registros e registrar recebimentos. O layout é responsivo e usa apenas **Blade, Bootstrap 5 e JavaScript puro** (sem framework de frontend).

> **Vai rodar em outro computador, sem nada instalado?** Siga o guia [Instalação do zero](docs/INSTALACAO.md), com links de download, comandos e solução de problemas para Windows, Linux e macOS.

> **O projeto usa SQLite.** Basta PHP + Composer; a aplicação roda com `php artisan serve`, sem servidor de banco nem servidor web adicional.

---

## Sumário

- [Stack](#stack)
- [Funcionalidades](#funcionalidades)
- [Regras de negócio](#regras-de-negócio)
- [Como executar](#como-executar)
- [Instalação do zero](docs/INSTALACAO.md)
- [Dados de demonstração](#dados-de-demonstração)
- [Carga de arquivos CSV/OFX](#carga-de-arquivos-csvofx)
- [Logs de requisições](#logs-de-requisições)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Segurança](#segurança)
- [Decisões técnicas](#decisões-técnicas)
- [Processo de desenvolvimento](#processo-de-desenvolvimento)
- [Uso de Inteligência Artificial](#uso-de-inteligência-artificial)
- [Checklist do teste](#checklist-do-teste)
- [Publicação no GitHub](#publicação-no-github)

---

## Stack

| Camada | Tecnologia |
| --- | --- |
| Backend | PHP 8+ / Laravel 9 |
| Banco | SQLite (`database/database.sqlite`) |
| Frontend | Blade + Bootstrap 5.3 (CDN) + Bootstrap Icons + JavaScript puro |
| Build | Nenhum passo de build obrigatório (`npm` não é necessário para rodar) |
| Testes | PHPUnit com SQLite em memória (`php artisan test`) |

---

## Funcionalidades

### Dashboard
- **Total a receber**, **Total recebido**, **Total vencido** e **Total a vencer**.
- Quantidade de contas em cada situação.
- Gráfico de recebimentos dos últimos 6 meses.
- Composição da carteira (recebido, vencido e a vencer).
- Listas de próximos vencimentos e últimos recebimentos.
- Indicadores recalculados a cada requisição, sempre a partir do banco.

### Contas a receber
- Listagem paginada com cliente, documento, vencimento, valor, status e ações.
- Cadastro, edição e exclusão em **modais** na própria listagem.
- Campos: cliente, descrição, documento, valor, vencimento, status, forma de pagamento, data de pagamento e observação.
- Status exibidos: **Pendente**, **Recebido** e **Vencido** (além de **Cancelado**, usado como situação administrativa).
- Ação dedicada **Registrar recebimento**, com data de recebimento (padrão: hoje).
- Pesquisa por cliente, descrição ou documento.
- Filtros por status e período de vencimento.
- Contador de filtros ativos e **Total filtrado** atualizado conforme o filtro.
- Paginação preservando os filtros aplicados.

### Extras
- Importação de contas via CSV ou OFX.
- Download de arquivos-modelo CSV e OFX.
- Log de entrada, SQL executado e saída de cada requisição em `logs/`.
- Interface responsiva (desktop, tablet e celular).

---

## Regras de negócio

### Situação das contas

O banco armazena o status principal da conta como `pendente`, `pago` ou `cancelado`.

O status **Vencido** é **derivado**, e não persistido:

> Uma conta é considerada **Vencida** quando está `pendente` e sua `data_vencimento` é anterior à data atual.

Essa decisão evita a necessidade de rotinas agendadas para atualizar o status todo dia. Assim que a data passa, a conta aparece automaticamente como vencida na listagem, nos filtros e no dashboard.

### Indicadores do dashboard

| Indicador | Cálculo |
| --- | --- |
| Total a receber | Soma de todas as contas `pendente` (vencidas + a vencer) |
| Total recebido | Soma de todas as contas `pago` |
| Total vencido | Soma das contas `pendente` com vencimento anterior a hoje |
| Total a vencer | Soma das contas `pendente` com vencimento hoje ou no futuro |

Contas `cancelado` não entram na composição da carteira.

### Registrar recebimento

Ao registrar o recebimento:

1. A conta passa para `pago`.
2. A `data_pagamento` é gravada com a data informada ou com a data atual, se o campo ficar em branco.
3. O dashboard e a listagem refletem a mudança imediatamente.

Contas já recebidas ou canceladas não podem ser recebidas novamente; o sistema exibe um aviso.

---

## Como executar

> Para um computador sem PHP e Composer instalados, use o passo a passo completo em **[`docs/INSTALACAO.md`](docs/INSTALACAO.md)**. O resumo abaixo assume que PHP e Composer já estão configurados.

### Pré-requisitos

- PHP **8.0+** com as extensões `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo` e `tokenizer`.
- [Composer](https://getcomposer.org/).

### 1. Obter o código

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
```

Se estiver usando o projeto já extraído, basta entrar na pasta.

### 2. Instalar as dependências

```bash
composer install
```

### 3. Configurar o ambiente

No Windows:

```powershell
copy .env.example .env
```

No Linux/macOS:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

O `.env.example` já vem configurado para SQLite:

```env
APP_NAME="Financeiro"
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
```

Não há usuário, senha nem servidor de banco para configurar. Se quiser usar outro arquivo SQLite, descomente `DB_DATABASE` no `.env` e informe o caminho absoluto.

> O `.env` é pessoal e **não deve ser versionado**. O único arquivo de ambiente que vai para o Git é o `.env.example`, que contém apenas valores de exemplo.

Para produção, use `APP_ENV=production`, `APP_DEBUG=false` e uma `APP_KEY` gerada no próprio servidor. A configuração completa está em [`docs/SEGURANCA.md`](docs/SEGURANCA.md).

### 4. Criar o banco SQLite

O Laravel precisa que o arquivo do banco exista antes das migrações. Crie-o em `database/`:

```powershell
New-Item database\database.sqlite -ItemType File
```

No Linux/macOS:

```bash
touch database/database.sqlite
```

> O arquivo `database/database.sqlite` é ignorado pelo Git. Cada computador cria o seu.

### 5. Criar as tabelas e popular os dados fictícios

```bash
php artisan migrate --seed
```

Para recriar o banco do zero com os dados de demonstração:

```bash
php artisan migrate:fresh --seed
```

### 6. Subir a aplicação

```bash
php artisan serve
```

Acesse: **http://127.0.0.1:8000**

> O projeto não possui autenticação; o acesso é direto ao dashboard.

### 7. Rodar os testes automatizados (opcional)

```bash
php artisan test
```

A suíte usa **SQLite em memória** (configurado em `phpunit.xml`), então não altera o arquivo `database/database.sqlite`. Os testes cobrem:

- Cálculo dos quatro indicadores do dashboard.
- Filtro de contas vencidas.
- Registro de recebimento.
- Bloqueio de recebimento de conta cancelada.
- Fluxo de cadastro, edição e exclusão.

---

## Dados de demonstração

O seeder `Database\Seeders\ContaReceberSeeder` cria **22 contas fictícias**:

- 9 contas recebidas, distribuídas nos últimos meses (para alimentar o gráfico).
- 5 contas pendentes já vencidas.
- 7 contas pendentes a vencer (incluindo uma com vencimento hoje).
- 1 conta cancelada.

Os valores, clientes, documentos e formas de pagamento são fictícios e servem apenas para demonstração.

Para rodar o seeder novamente em um banco vazio:

```bash
php artisan db:seed
```

O seeder é idempotente: se já existir alguma conta, ele não duplica os registros.

---

## Carga de arquivos CSV/OFX

Na listagem, o botão **Carga** abre o modal de importação. Também é possível baixar modelos pelo ícone de download.

### CSV

- Separador `;` ou `,` detectado automaticamente.
- Cabeçalho obrigatório: `cliente`, `valor`, `data_vencimento`.
- Colunas opcionais: `descricao`, `documento`, `status`, `data_pagamento`, `forma_pagamento`, `observacao`.
- Datas aceitas: `d/m/Y`, `Y-m-d` e `d-m-Y`.
- Valores aceitos: `1234.56` ou `1.234,56`.
- Status aceitos: `pendente`, `pago`/`recebido`, `cancelado` (também em inglês).

### OFX

- Lê transações no formato SGML/OFX (`<STMTTRN>`).
- Usa `NAME`/`PAYEE`, `MEMO`, `FITID`, `TRNAMT` e `DTPOSTED`.
- Registros importados entram como `pendente`.

Linhas inválidas são ignoradas e reportadas ao final da importação, sem interromper as linhas válidas.

---

## Logs de requisições

O middleware `App\Http\Middleware\LogBackendCalls` grava um arquivo por API por dia em `logs/`:

```text
logs/contasreceber_08112026.txt
```

Cada requisição gera três linhas com o prefixo `api_metodo_data_`:

```text
ENTRADA-> { método, URL e dados recebidos }
SQL->     { consultas executadas }
SAIDA->   { status, redirecionamento/mensagem ou view renderizada }
```

O diretório `logs/` possui `.htaccess` para bloquear acesso via navegador e `.gitignore` para não versionar os arquivos gerados. Na raiz, o `.gitignore` também bloqueia `/logs/*` explicitamente.

> Os logs podem conter nomes de clientes, valores e SQL executado. Eles ficam fora de `public/`, não são versionados e devem ter permissões restritas em produção. Veja [`docs/SEGURANCA.md`](docs/SEGURANCA.md).

---

## Estrutura do projeto

```text
app/
  Http/Controllers/
    DashboardController.php          # indicadores e gráficos
    ContaReceberController.php       # listagem, CRUD e registrar recebimento
    ContaReceberCargaController.php  # upload e download de modelos
  Http/Middleware/
    LogBackendCalls.php              # log de entrada, SQL e saída
  Models/
    ContaReceber.php                 # regras de status, filtros e formatação
  Services/
    ContaReceberImportService.php    # parsers e normalização CSV/OFX

database/
  migrations/                        # contas_receber e estrutura padrão do Laravel
  seeders/ContaReceberSeeder.php     # 22 registros fictícios

resources/views/
  dashboard/index.blade.php          # dashboard
  contas-receber/
    index.blade.php                  # listagem, filtros e ações
    _modal-conta.blade.php           # cadastro
    _modal-editar.blade.php          # edição
    _modal-receber.blade.php         # registrar recebimento
    _modal-excluir.blade.php         # exclusão com campos somente leitura
    _modal-carga.blade.php           # importação
    _form.blade.php                  # formulário compartilhado
  layouts/app.blade.php              # layout, navegação e estilos globais

routes/web.php                       # rotas do dashboard, CRUD, carga e recebimento
logs/                                # logs diários por API (não versionados)
  .htaccess                          # proteção extra contra acesso web aos logs
  .gitignore                         # ignora o conteúdo dos logs
docs/
  INSTALACAO.md                      # passo a passo para computador sem nada instalado
  PROCESSO.md                        # processo de desenvolvimento
  USO_DE_IA.md                       # relato de uso de IA
  SEGURANCA.md                       # arquivos sensíveis e checklist de segurança
.gitignore                           # proteção contra versionamento de segredos
.env.example                         # modelo de ambiente, sem segredos reais
```

---

## Segurança

O projeto foi configurado para não versionar arquivos sensíveis. O `.gitignore` da raiz bloqueia:

- `.env` e variações (`.env.local`, `.env.production` etc.), mantendo apenas `.env.example`.
- `/logs/*` — logs do middleware com URL, entrada e SQL.
- `/storage/logs/*`, sessões, cache e `bootstrap/cache/*.php`.
- `vendor/`, `node_modules/` e `public/storage`.
- Bancos SQLite, dumps `.sql`/`.dump`, chaves `.key`/`.pem` e `auth.json`.
- Caches de teste e arquivos de IDE/sistema.

### Regras de segurança mais importantes

| Regra | Motivo |
| --- | --- |
| Nunca versionar `.env` | Contém a `APP_KEY` e a configuração do ambiente. |
| Nunca versionar `logs/` ou `storage/logs/` | Podem conter dados de clientes, valores e SQL executado. |
| Em produção, document root em `public/` | Mantém `.env`, `logs/`, `storage/`, `database/` e `vendor/` fora da web. |
| `APP_DEBUG=false` em produção | Evita expor stack traces, caminhos e SQL em erros. |
| Autenticação antes de expor dados reais | O projeto é uma demonstração e não possui login. |
| HTTPS em produção | Protege sessões, formulários e dados financeiros em trânsito. |

O passo a passo completo de verificação, publicação, configuração de servidor web, permissões, rotação de segredos e checklist de produção está em [`docs/SEGURANCA.md`](docs/SEGURANCA.md).

> **Atenção:** se o `.env` ou os logs já foram commitados, remova-os do versionamento e **rotacione as credenciais**. O histórico do Git continua contendo o conteúdo antigo.

---

## Decisões técnicas

- **Vencido derivado**: mantém o banco simples e o status sempre correto, sem job diário.
- **Status interno `pago` exibido como “Recebido”**: o valor armazenado é técnico; o rótulo de negócio é “Recebido”.
- **Modais em vez de páginas separadas**: cadastro, edição, recebimento e exclusão acontecem sem sair da listagem, preservando o contexto e os filtros.
- **Um único modal de edição e um único de exclusão**: os dados vêm de `data-*` da linha, evitando IDs duplicados e duplicação de formulários.
- **Exclusão sem `confirm()`**: o próprio modal, com campos somente leitura, é a confirmação.
- **Filtros no servidor**: a query string é preservada na paginação e nos formulários.
- **Importação separada em serviço**: parsing, normalização e persistência ficam isolados da controller.
- **Sem framework de frontend**: Bootstrap e JavaScript puro atendem ao escopo e reduzem complexidade.

---

## Processo de desenvolvimento

O resumo das etapas de levantamento, modelagem, backend, frontend, validação e limpeza está em [`docs/PROCESSO.md`](docs/PROCESSO.md).

---

## Uso de Inteligência Artificial

A IA foi utilizada como **par de programação** durante todo o desenvolvimento, principalmente para acelerar a implementação e revisar decisões — não para substituir a validação humana.

### Como a IA ajudou

- Estruturação inicial do projeto Laravel e do modelo de dados.
- Criação e evolução das telas Blade/Bootstrap (dashboard, listagem, modais e formulário compartilhado).
- Implementação de filtros, paginação, máscaras/formatação de valores e datas.
- Construção da ação de registrar recebimento e das regras de status derivado.
- Parsers de importação CSV/OFX e middleware de log.
- Depuração de problemas reais: IDs duplicados entre modais, vazamento de variável de loop do Blade preenchendo o modal de cadastro e reabertura correta do modal após erro de validação.
- Revisão de responsividade e ajuste do dashboard para 1920×1080 sem rolagem de página.
- Escrita e revisão desta documentação.

### O que foi validado manualmente

- Regras de negócio (o que é “a receber”, “recebido”, “vencido” e “a vencer”).
- Todos os fluxos de CRUD, recebimento, filtros e importação no navegador.
- Validação de formulários e mensagens de erro.
- Consistência dos totais do dashboard com os registros do banco.
- Revisão do código gerado, nomes, organização e remoção de sobras.

O detalhamento do processo está em [`docs/USO_DE_IA.md`](docs/USO_DE_IA.md).

---

## Checklist do teste

| Requisito | Implementação |
| --- | --- |
| Dashboard com total a receber | `DashboardController@index` + card no dashboard |
| Dashboard com total recebido | Soma das contas `pago` |
| Dashboard com total vencido | Pendentes com vencimento anterior a hoje |
| Dashboard com total a vencer | Pendentes com vencimento hoje ou no futuro |
| Listagem de contas | `ContaReceberController@index` + `index.blade.php` |
| Cadastro de conta | Modal `_modal-conta.blade.php` + `store` |
| Edição de conta | Modal `_modal-editar.blade.php` + `update` |
| Exclusão de conta | Modal `_modal-excluir.blade.php` + `destroy` |
| Cliente, descrição, valor e vencimento | Campos do formulário e colunas da listagem |
| Status Pendente / Recebido / Vencido | `ContaReceber::STATUS` + `situacao` derivada |
| Registrar recebimento | Rota `contas-receber.receber` + `_modal-receber.blade.php` |
| Pesquisa por cliente | Busca por cliente, descrição e documento |
| Filtros por cliente e status | Busca + filtro de status e período de vencimento |
| Interface moderna, simples e responsiva | Bootstrap 5 + layout próprio + breakpoints |
| Navegação intuitiva | Menu Dashboard/Contas a Receber e ações na listagem |
| Indicadores atualizados | Todos os totais calculados a cada requisição |
| Banco com registros fictícios | `ContaReceberSeeder` com 22 registros |
| Instruções para executar | Este README |
| Descrição do uso de IA | Esta seção + `docs/USO_DE_IA.md` |
| Documentação do processo | `docs/PROCESSO.md` |
| Segurança e arquivos sensíveis | `.gitignore` + `docs/SEGURANCA.md` |
| Qualidade verificável | `tests/Feature/ContaReceberTest.php` (5 testes) |

---

## Publicação no GitHub

### 1. Antes de commitar

Confirme que o `.env` e os arquivos gerados estão fora do versionamento:

```bash
git status --ignored
git check-ignore -v .env
git check-ignore -v logs/contasreceber_08112026.txt
git check-ignore -v storage/logs/laravel.log
git ls-files
```

O `git ls-files` **não deve listar**:

- `.env`
- arquivos dentro de `logs/` (exceto `.gitignore` e `.htaccess`)
- arquivos dentro de `storage/` (exceto os `.gitignore`)
- `vendor/`, `node_modules/`
- `.sql`, `.sqlite`, `.key`, `.pem`, `auth.json`

### 2. Publicar

```bash
git init
git add .
git status
git commit -m "Teste técnico: gestão financeira - contas a receber"
git branch -M main
git remote add origin https://github.com/B3tten/projeto-grupostudio.git
git push -u origin main
```

> O `.gitignore` já exclui `vendor/`, `node_modules/`, `.env`, `/logs/*`, `storage/logs/*`, caches, bancos SQLite, dumps e chaves. O banco de demonstração é recriado com `php artisan migrate --seed`, então não é necessário versionar o arquivo do banco.

### 3. Se o `.env` ou logs já foram commitados

Remover do próximo commit não apaga o histórico. Faça:

```bash
git rm --cached .env
git rm -r --cached logs
git add logs/.gitignore logs/.htaccess
git commit -m "Remove arquivos sensíveis do versionamento"
git push
```

Depois:

1. Rotacione a `APP_KEY` com `php artisan key:generate` se ela foi exposta.
2. Se o repositório era público, limpe o histórico com `git filter-repo` ou BFG.
3. Confira no GitHub se `.env` e logs sumiram da árvore atual.

O passo a passo completo de segurança, incluindo servidor web, permissões e checklist de produção, está em [`docs/SEGURANCA.md`](docs/SEGURANCA.md).

Repositório: https://github.com/B3tten/projeto-grupostudio
