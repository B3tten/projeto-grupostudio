# Instalação (guia rápido)

Este projeto usa **Laravel 9 + SQLite**. O fluxo abaixo assume uma máquina de desenvolvimento com **PHP 8.0+**, **Composer**, **Git** e a extensão **SQLite** já instalados. Não há MySQL, nem servidor web adicional: a aplicação sobe com `php artisan serve`.

> Bootstrap e ícones vêm por CDN — **não** é necessário Node.js/npm.

## 1. Clonar o projeto

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
```

## 2. Instalar as dependências

```bash
composer install
```

## 3. Criar o `.env`

O repositório **não versiona o `.env`** — ele precisa ser copiado manualmente a partir do `.env.example`:

```bash
cp .env.example .env
```

No PowerShell:

```powershell
Copy-Item .env.example .env
```

## 4. Gerar a `APP_KEY`

```bash
php artisan key:generate
```

## 5. Criar o banco SQLite

O Laravel precisa que o arquivo do banco exista antes das migrações:

```bash
touch database/database.sqlite
```

No PowerShell:

```powershell
New-Item database\database.sqlite -ItemType File
```

> `database/database.sqlite` é ignorado pelo Git — cada máquina cria o seu, o repositório **não** traz o banco pronto.

## 6. Criar tabelas e dados de demonstração

```bash
php artisan migrate --seed
```

Isso cria as tabelas e insere as **22 contas a receber fictícias**.

## 7. Subir a aplicação

```bash
php artisan serve
```

Abra **http://127.0.0.1:8000**. Você deve ver o dashboard com os indicadores **Total a receber, Total recebido, Total vencido e Total a vencer**.

---

## Passos que não podem ser pulados

1. `cp .env.example .env` — sem isso a aplicação não sobe (o `.env` não está no repositório).
2. `php artisan key:generate` — sem isso aparece "No application encryption key has been specified".
3. `touch database/database.sqlite` — sem isso o `migrate` falha com "Database file ... does not exist".
4. `php artisan migrate --seed` — cria o banco e os dados de demonstração.

## Recriar o banco do zero

```bash
php artisan migrate:fresh --seed
```

> Atenção: `migrate:fresh` apaga todas as tabelas e dados do arquivo SQLite. Use apenas em ambiente local.

## Conferir o ambiente (opcional)

```bash
php -v                       # PHP 8.0+
composer -V                  # Composer 2.x
php -m | grep -E 'pdo_sqlite|sqlite3|mbstring|openssl|fileinfo|tokenizer'
php artisan test             # 11 testes passando (SQLite em memória)
```

## Problemas comuns

| Mensagem / sintoma | Solução |
| --- | --- |
| `No application encryption key has been specified` | Rode `php artisan key:generate`. |
| `could not find driver` ou `pdo_sqlite` ausente | Habilite `extension=pdo_sqlite` e `extension=sqlite3` no `php.ini` e reabra o terminal. |
| `Database file at path [...] does not exist` | Faça o passo 5 e depois `php artisan migrate --seed`. |
| Página em branco ou erro 500 | Rode `php artisan config:clear` e `php artisan view:clear`; consulte `storage/logs/laravel.log`. |
| Porta 8000 ocupada | Suba em outra porta: `php artisan serve --port=8001`. |

