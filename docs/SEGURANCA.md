# Segurança e arquivos que não devem ir para o repositório

Este documento resume o que é sensível no projeto, o que já está protegido e o passo a passo para publicar e implantar com segurança.

> **Importante:** este é um projeto de demonstração para teste técnico. Ele **não possui autenticação**. Não exponha a aplicação publicamente com dados reais sem adicionar login, autorização e HTTPS.

---

## 1. Arquivos e diretórios que não devem ser versionados

O `.gitignore` da raiz já bloqueia os itens abaixo. O `logs/.gitignore` e os `.gitignore` internos de `storage/` e `bootstrap/cache/` funcionam como segunda camada de proteção.

| Caminho | Por que não versionar |
| --- | --- |
| `.env` | Contém a `APP_KEY` e as configurações do ambiente. |
| `.env.*` (exceto `.env.example`) | Podem conter credenciais de staging/produção. |
| `/logs/*` | Logs do middleware com URLs, dados de entrada e SQL executado. |
| `/storage/logs/*` | Log padrão do Laravel, com stack traces e dados internos. |
| `/storage/framework/sessions/*` | Dados de sessão dos usuários. |
| `/storage/framework/cache/*` | Cache interno da aplicação. |
| `/bootstrap/cache/*.php` | Configuração compilada, que pode conter segredos do `.env`. |
| `/vendor/` | Dependências instaladas; são recriadas com `composer install`. |
| `/node_modules/` | Dependências de frontend, se existirem; são recriadas com `npm install`. |
| `/public/storage` | Link simbólico local para arquivos públicos. |
| `/database/*.sqlite`, `*.sqlite3` | Banco local com dados reais ou de teste. |
| `*.sql`, `*.sql.gz`, `*.dump` | Dumps de banco com dados sensíveis. |
| `/auth.json` | Credenciais do Composer/GitHub, quando existirem. |
| `*.key`, `*.pem`, `*.p12`, `*.pfx` | Chaves e certificados privados. |
| `.phpunit.result.cache`, `.phpunit.cache` | Artefatos locais de teste. |
| `.idea/`, `.vscode/`, `.DS_Store`, `Thumbs.db` | Configurações locais de IDE e sistema. |

### O que deve ser versionado

- `.env.example` — apenas com valores de exemplo, sem segredos reais.
- `composer.json` e `composer.lock` — reprodutibilidade das dependências.
- `package.json` — dependências de frontend, se usadas.
- Migrations, seeders, testes e código-fonte.
- `logs/.gitignore` e `logs/.htaccess` — protegem o diretório de logs.
- `.gitignore` da raiz.

---

## 2. Verificações antes de commitar

Rode na raiz do projeto:

```bash
git status --ignored
git check-ignore -v .env
git check-ignore -v logs/contasreceber_08112026.txt
git check-ignore -v storage/logs/laravel.log
git ls-files
```

O `git ls-files` não deve listar:

- `.env`
- arquivos dentro de `logs/` (exceto `.gitignore` e `.htaccess`)
- arquivos dentro de `storage/` (exceto os `.gitignore`)
- `vendor/`, `node_modules/`
- arquivos `.sql`, `.sqlite`, `.key`, `.pem`
- `auth.json`

No Windows, uma verificação rápida:

```powershell
git ls-files | Select-String -Pattern '(^|/)(\.env|logs/|storage/|vendor/|node_modules/|auth\.json|.*\.sql|.*\.sqlite|.*\.key|.*\.pem)'
```

O ideal é que esse comando não retorne nada além de `.gitignore`, `.htaccess` e arquivos deliberadamente públicos.

---

## 3. Se algum arquivo sensível já foi commitado

Remover apenas do próximo commit não apaga o histórico. Se o repositório já foi enviado ao GitHub, faça:

```bash
git rm --cached .env
git rm -r --cached logs
git add logs/.gitignore logs/.htaccess
git commit -m "Remove arquivos sensíveis do versionamento"
git push
```

Depois disso:

1. **Rotacione o `APP_KEY`** (`php artisan key:generate`) se ela foi exposta.
2. Se o arquivo `database/database.sqlite` foi commitado, remova-o do histórico: ele contém os dados financeiros.
3. Se o repositório era público, considere limpar o histórico com `git filter-repo` ou BFG Repo-Cleaner.
4. Verifique no GitHub se `.env`, logs e o arquivo SQLite desapareceram da árvore atual.

> `git rm --cached` mantém o arquivo no disco local; ele apenas para de ser rastreado.

---

## 4. Configuração segura de produção

### 4.1 Variáveis de ambiente

No `.env` de produção:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

DB_CONNECTION=sqlite
# DB_DATABASE=/caminho/absoluto/para/database.sqlite
```

- Nunca use `APP_DEBUG=true` em produção: stack traces podem expor caminhos, SQL e dados.
- Gere uma `APP_KEY` única por ambiente com `php artisan key:generate`.
- Mantenha o arquivo `database/database.sqlite` fora de `public/` e com permissão de leitura/escrita apenas para o usuário da aplicação.
- Em HTTPS, configure `SESSION_SECURE_COOKIE=true`.

### 4.2 Document root do servidor

O servidor web deve apontar **somente para a pasta `public/`**:

```text
DocumentRoot "/var/www/projeto/public"
```

No Windows, use o caminho onde o projeto foi clonado, por exemplo:

```text
DocumentRoot "C:/projetos/projeto-grupostudio/public"
```

Nunca aponte para a raiz do projeto. Se a raiz for exposta, `.env`, `logs/`, `storage/`, `database/` e `vendor/` podem ficar acessíveis.

Com `php artisan serve`, o Laravel já usa `public/` como raiz.

### 4.3 Apache

Exemplo de VirtualHost:

```apache
<VirtualHost *:80>
    ServerName financeiro.local
    DocumentRoot "/var/www/projeto/public"

    <Directory "/var/www/projeto/public">
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/financeiro-error.log"
    CustomLog "logs/financeiro-access.log" combined
</VirtualHost>
```

Adicione também headers básicos de segurança, se possível:

```apache
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### 4.4 Nginx

```nginx
server {
    listen 80;
    server_name financeiro.local;
    root /var/www/projeto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

O `logs/.htaccess` só funciona no Apache. No Nginx, mantenha `logs/`, `storage/`, `database/` e `.env` fora do `root` (o que já acontece quando o root é `public/`).

### 4.5 Permissões

- `.env`: leitura apenas pelo usuário da aplicação (`chmod 600` em Linux).
- `database/database.sqlite` e seu diretório: leitura/escrita apenas pelo usuário da aplicação.
- `storage/` e `bootstrap/cache/`: escrita pelo usuário do servidor web.
- `logs/`: escrita pelo usuário da aplicação, leitura restrita.
- Evite permissões `777`.

### 4.6 Logs e dados financeiros

Os logs do middleware registram dados de entrada e SQL. Eles podem conter nomes de clientes, valores e descrições. Portanto:

- Não versionar.
- Não servir pela web.
- Restringir permissões.
- Fazer rotação e descarte periódico.
- Evitar registrar dados pessoais desnecessários em produção (princípio da minimização/LGPD).

O `logs/.htaccess` bloqueia o acesso em servidores compatíveis, mas a proteção principal é manter o document root em `public/`.

### 4.7 Autenticação e autorização

O projeto **não tem login**. Para uso real:

- Adicionar autenticação (Laravel Breeze/Jetstream ou solução própria).
- Proteger todas as rotas de contas a receber.
- Adicionar autorização por perfil/empresa, se houver mais de um usuário.
- Adicionar rate limiting em login, importação e registro de recebimento.

### 4.8 Dependências

Antes de produção, rode:

```bash
composer audit
composer outdated
```

O projeto usa Laravel 9 por compatibilidade com o ambiente do teste. O Laravel 9 está fora do ciclo de suporte e o `composer audit` pode reportar advisories conhecidos. Para produção, planeje a atualização para uma versão suportada do Laravel (10, 11 ou 12) e reexecute os testes.

Observações sobre os advisories atuais:

- A maioria depende de recursos não usados neste projeto (autenticação por token, URLs assinadas, validação de e-mail, parser YAML).
- A validação de upload já limita tamanho e extensão e não armazena o arquivo em diretório público.
- Ainda assim, a atualização é recomendada antes de expor o sistema publicamente.

---

## 5. Checklist rápido de publicação

- [ ] `.env` não está no `git ls-files`.
- [ ] `/logs/*` está no `.gitignore` e os logs não estão versionados.
- [ ] `storage/logs`, sessões, cache e `bootstrap/cache` não estão versionados.
- [ ] `vendor/` e `node_modules/` não estão versionados.
- [ ] Nenhum `.sql`, `.sqlite`, `.key`, `.pem` ou `auth.json` versionado.
- [ ] `.env.example` contém apenas valores de exemplo.
- [ ] `APP_ENV=production` e `APP_DEBUG=false` no servidor.
- [ ] `APP_KEY` gerada no servidor e nunca reutilizada do exemplo.
- [ ] Document root apontando para `public/`.
- [ ] HTTPS configurado.
- [ ] Usuário de banco dedicado, com senha forte e permissões mínimas.
- [ ] Autenticação adicionada antes de expor dados reais.
- [ ] `composer audit` executado e plano de atualização definido.
- [ ] Logs com permissões restritas e rotação configurada.
