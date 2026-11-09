# Instalação do zero (SQLite)

Guia passo a passo para rodar o projeto em um computador **sem PHP e sem Composer instalados**. O banco usado é o **SQLite**, que não precisa de servidor, usuário nem senha.

Este guia instala apenas **PHP** e **Composer**. O projeto usa **Laravel 9 + SQLite**; Bootstrap, ícones e fontes vêm por CDN, então **não é necessário instalar Node.js/npm**.

---

## Resumo rápido

Para quem já tem PHP e Composer configurados:

```powershell
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database\database.sqlite -ItemType File
php artisan migrate --seed
php artisan serve
```

Acesse `http://127.0.0.1:8000`.

---

## 1. Instalar o PHP 8.2 (Windows)

1. Acesse https://windows.php.net/download/.
2. Baixe o **PHP 8.2**, versão **x64 Thread Safe**, no formato **ZIP**.
   - Se o 8.2 não estiver na página principal, use o arquivo de versões anteriores: https://windows.php.net/downloads/releases/archives/.
3. Extraia o conteúdo do ZIP para uma pasta simples, sem espaços ou acentos:

```text
C:\php
```

4. Dentro de `C:\php`, copie o arquivo `php.ini-development` e renomeie a cópia para `php.ini`.
5. Abra `C:\php\php.ini` em um editor de texto e configure as extensões necessárias:
   - Localize `extension_dir` e deixe assim:

```ini
extension_dir = "C:\php\ext"
```

   - Remova o `;` do início das linhas abaixo (descomente cada uma):

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

   > O `tokenizer` já vem habilitado no PHP para Windows e não precisa de linha extra.

6. Adicione `C:\php` ao PATH do Windows:
   1. Abra o menu Iniciar e procure por **"Editar as variáveis de ambiente do sistema"**.
   2. Clique em **Variáveis de Ambiente**.
   3. Em **Variáveis do sistema**, selecione **Path** e clique em **Editar**.
   4. Clique em **Novo** e adicione:

```text
C:\php
```

   5. Confirme todas as janelas com **OK**.
   6. **Feche e abra novamente** o terminal.

7. Verifique a instalação:

```powershell
php -v
```

O resultado deve ser algo como `PHP 8.2.x`.

8. Confirme as extensões necessárias:

```powershell
php -m | Select-String 'pdo_sqlite|sqlite3|mbstring|openssl|fileinfo|tokenizer'
```

Todas devem aparecer na lista. Se alguma não aparecer, confira o `php.ini` e reabra o terminal.

> Se o Windows bloquear o arquivo ZIP, clique com o botão direito no ZIP → **Propriedades** → marque **Desbloquear** → **Aplicar**, e extraia novamente.

---

## 2. Instalar o Composer

1. Acesse https://getcomposer.org/download/.
2. Baixe o **Composer-Setup.exe** (link direto: https://getcomposer.org/Composer-Setup.exe).
3. Execute o instalador.
4. Quando ele pedir o caminho do PHP, informe:

```text
C:\php\php.exe
```

5. Continue com as opções padrão.
6. Feche e reabra o terminal.
7. Verifique:

```powershell
composer -V
```

O resultado deve ser `Composer version 2.x`.

> Se aparecer `composer não é reconhecido`, feche e reabra o terminal. Se persistir, reinstale o Composer apontando para `C:\php\php.exe`.

---

## 3. Clonar o projeto

Escolha uma pasta para os projetos, por exemplo `C:\projetos`:

```powershell
cd C:\projetos
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
```

Se `C:\projetos` não existir:

```powershell
New-Item -ItemType Directory C:\projetos
```

> A aplicação sobe com `php artisan serve`; não é necessário configurar um servidor web.

### Se o repositório for privado

O Git vai pedir autenticação. Use uma conta com acesso ao repositório ou um **Personal Access Token** do GitHub no lugar da senha.

---

## 4. Instalar as dependências PHP

Dentro da pasta do projeto:

```powershell
composer install
```

Aguarde o download das bibliotecas. Ao final, a pasta `vendor/` será criada (ela não vem no repositório).

---

## 5. Configurar o arquivo `.env`

Crie o arquivo de ambiente a partir do modelo:

**PowerShell:**

```powershell
Copy-Item .env.example .env
```

**Prompt de Comando (CMD):**

```bat
copy .env.example .env
```

Gere a chave da aplicação:

```powershell
php artisan key:generate
```

O `.env.example` já está configurado para SQLite:

```env
DB_CONNECTION=sqlite
```

Não existem `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` nem banco para criar. Se quiser usar outro arquivo SQLite, descomente e ajuste a linha opcional do `.env.example`:

```env
# DB_DATABASE=C:\caminho\completo\database.sqlite
```

> O `.env` é pessoal e **não deve ir para o Git**. O repositório contém apenas o `.env.example`, sem segredos.

---

## 6. Criar o banco SQLite

O Laravel precisa que o arquivo do banco exista antes de rodar as migrações. Crie-o dentro de `database/`:

**PowerShell:**

```powershell
New-Item database\database.sqlite -ItemType File
```

**Prompt de Comando (CMD):**

```bat
type nul > database\database.sqlite
```

O arquivo `database/database.sqlite` é ignorado pelo Git (`database/.gitignore`), então cada computador cria o seu. Não é preciso versioná-lo.

---

## 7. Criar as tabelas e os dados de demonstração

```powershell
php artisan migrate --seed
```

Isso cria as tabelas e insere **22 contas a receber fictícias**.

Para conferir:

```powershell
php artisan tinker --execute="echo App\Models\ContaReceber::count();"
```

O resultado esperado é `22`.

Se precisar recriar o banco do zero:

```powershell
php artisan migrate:fresh --seed
```

> Atenção: `migrate:fresh` apaga todas as tabelas e dados do arquivo SQLite configurado no `.env`. Use apenas em ambiente local.

Se as tabelas já existirem mas os dados não tiverem sido inseridos:

```powershell
php artisan db:seed
```

---

## 8. Subir a aplicação

```powershell
php artisan serve
```

Abra no navegador:

```text
http://127.0.0.1:8000
```

Você deve ver o dashboard com os indicadores **Total a receber**, **Total recebido**, **Total vencido** e **Total a vencer**.

- Mantenha o terminal aberto enquanto usa o site.
- Para parar o servidor, pressione `Ctrl + C`.
- Se a porta 8000 estiver ocupada:

```powershell
php artisan serve --port=8001
```

E acesse `http://127.0.0.1:8001`.

---

## 9. Rodar os testes (opcional)

```powershell
php artisan test
```

Resultado esperado: **7 testes passando**. Os testes usam SQLite em memória e não alteram o arquivo `database/database.sqlite`.

---

## Problemas comuns

| Mensagem / sintoma | Causa provável | Solução |
| --- | --- | --- |
| `php não é reconhecido...` | PHP fora do PATH | Refaça o passo 1, adicionando `C:\php` ao PATH, e reabra o terminal. |
| `composer não é reconhecido...` | Composer fora do PATH | Reinstale o Composer apontando para `C:\php\php.exe` e reabra o terminal. |
| `could not find driver` | Extensão `pdo_sqlite` desativada | Descomente `extension=pdo_sqlite` no `php.ini` e reabra o terminal. |
| `Database file at path [...] does not exist` | Arquivo SQLite não criado | Execute o passo 6 (`New-Item database\database.sqlite -ItemType File`). |
| `No application encryption key has been specified` | `APP_KEY` vazia | Rode `php artisan key:generate`. |
| Página em branco ou erro 500 | Cache/config antigos ou permissões | Rode `php artisan config:clear` e `php artisan view:clear`; consulte `storage/logs/laravel.log`. |
| `Class not found` após copiar o projeto | Dependências/autoload desatualizados | Rode `composer install` e `composer dump-autoload`. |
| `Failed to listen on 127.0.0.1:8000` | Porta ocupada | Use `php artisan serve --port=8001`. |
| Estilos/ícones não aparecem | Sem internet ou CDN bloqueada | Bootstrap, ícones e fontes são carregados por CDN; use uma rede com internet. |
| `database is locked` | Outro processo usando o SQLite | Feche outros terminais/servidores que estejam usando o mesmo arquivo e tente novamente. |

### Verificações úteis

```powershell
php -v
composer -V
php -m | Select-String 'pdo_sqlite|sqlite3|mbstring|openssl|fileinfo|tokenizer'
php artisan about
php artisan migrate:status
```

---

## Linux (Ubuntu/Debian)

Instale o Git e o PHP com a extensão SQLite, além do Composer. Em uma VM/instalação nova, o Git também é necessário para clonar o repositório:

```bash
sudo apt update
sudo apt install -y git php-cli php-sqlite3 php-mbstring php-xml php-curl php-zip unzip composer
```

Depois, na pasta do projeto:

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Permissões de escrita (se aparecer erro em `storage/`, `bootstrap/cache/` ou `database/`):

```bash
chmod -R ug+rwx storage bootstrap/cache database
```

## macOS (Homebrew)

Instale PHP e Composer:

```bash
brew install php composer
```

Depois:

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

---

## Checklist final

- [ ] PHP 8.0+ instalado e funcionando (`php -v`).
- [ ] Extensões `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `fileinfo` e `tokenizer` habilitadas.
- [ ] Composer instalado e funcionando (`composer -V`).
- [ ] Projeto clonado do GitHub.
- [ ] `composer install` concluído.
- [ ] `.env` criado a partir do `.env.example`.
- [ ] `APP_KEY` gerada.
- [ ] Arquivo `database/database.sqlite` criado.
- [ ] `php artisan migrate --seed` executado (22 registros).
- [ ] `php artisan serve` rodando.
- [ ] Dashboard abrindo em `http://127.0.0.1:8000`.
- [ ] (Opcional) `php artisan test` com 7 testes passando.

Pronto. Se algo falhar, consulte a tabela de problemas comuns e o log em `storage/logs/laravel.log`.
