# Instalação do zero

Guia passo a passo para rodar o projeto em um computador **sem nada instalado** (Windows 10/11). No fim há uma seção para Linux e macOS.

O projeto é uma aplicação **Laravel 9 + MySQL** e usa Bootstrap, ícones e fontes via CDN. Por isso **não é necessário instalar Node.js/npm** para executá-lo.

> Tempo estimado: 20 a 30 minutos, incluindo downloads.

---

## O que será instalado

| Ferramenta | Para que serve | Link oficial |
| --- | --- | --- |
| **XAMPP** (PHP 8.2 + MySQL/MariaDB + phpMyAdmin) | Servidor PHP e banco de dados | https://www.apachefriends.org/download.html |
| **Composer** | Instala as dependências PHP do Laravel | https://getcomposer.org/download/ |
| **Git para Windows** | Baixar o projeto do GitHub (opcional se usar o ZIP) | https://git-scm.com/download/win |
| **Visual Studio Code** (opcional) | Editar o código | https://code.visualstudio.com/ |

Repositório do projeto:

```text
https://github.com/B3tten/projeto-grupostudio
```

---

## Resumo rápido (para quem já tem tudo)

Se XAMPP, PHP e Composer já estão instalados e configurados no PATH:

```powershell
cd C:\xampp\htdocs
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
Copy-Item .env.example .env
php artisan key:generate
# Crie o banco "app-crud" no phpMyAdmin antes do próximo comando
php artisan migrate --seed
php artisan serve
```

Acesse `http://127.0.0.1:8000`.

---

## 1. Instalar o XAMPP

1. Acesse https://www.apachefriends.org/download.html.
2. Baixe a versão com **PHP 8.2** (o projeto foi testado com PHP 8.2.12). Se a página não oferecer mais o PHP 8.2, baixe diretamente em https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/.
3. Execute o instalador e instale em `C:\xampp` (evite caminhos com espaços ou acentos).
4. Abra o **XAMPP Control Panel**.
5. Clique em **Start** em **Apache** e em **MySQL**.

Para confirmar, abra o navegador em:

```text
http://localhost
```

Deve aparecer o painel do XAMPP.

> Se o Apache não iniciar, provavelmente a porta 80 está ocupada (IIS, Skype ou outro Apache). O projeto pode rodar com `php artisan serve` sem o Apache, mas o **MySQL precisa estar iniciado**. Para liberar a porta, pare o serviço conflitante ou altere a porta do Apache no XAMPP.

---

## 2. Adicionar o PHP ao PATH

Isso permite digitar `php` e `composer` em qualquer terminal.

1. Abra o menu Iniciar e procure por **"Editar as variáveis de ambiente do sistema"**.
2. Clique em **Variáveis de Ambiente**.
3. Em **Variáveis do sistema**, selecione **Path** e clique em **Editar**.
4. Clique em **Novo** e adicione:

```text
C:\xampp\php
```

5. Confirme todas as janelas com **OK**.
6. **Feche e abra novamente** o PowerShell ou o Prompt de Comando.
7. Verifique:

```powershell
php -v
```

O resultado deve ser algo como `PHP 8.2.x`.

Confirme também as extensões necessárias:

```powershell
php -m | Select-String 'pdo_mysql|mbstring|openssl|fileinfo|tokenizer'
```

Todas devem aparecer na lista.

> Sempre que instalar algo novo, feche e reabra o terminal para ele reconhecer o PATH.

---

## 3. Instalar o Git (opcional)

Você só precisa do Git se for clonar o repositório. Se preferir, pule para a opção ZIP no passo 5.

1. Acesse https://git-scm.com/download/win.
2. Baixe o instalador **64-bit Git for Windows Setup**.
3. Avance com as opções padrão (**Next** em todas as telas).
4. Feche e reabra o terminal.
5. Verifique:

```powershell
git --version
```

---

## 4. Instalar o Composer

1. Acesse https://getcomposer.org/download/.
2. Baixe o **Composer-Setup.exe** (Windows Installer), link direto: https://getcomposer.org/Composer-Setup.exe.
3. Execute o instalador.
4. Quando ele pedir o caminho do PHP, informe:

```text
C:\xampp\php\php.exe
```

5. Continue com as opções padrão.
6. Feche e reabra o terminal.
7. Verifique:

```powershell
composer -V
```

O resultado deve ser `Composer version 2.x`.

> Se aparecer `composer não é reconhecido`, feche e reabra o terminal. Se persistir, reinstale o Composer selecionando o `php.exe` do XAMPP.

---

## 5. Obter o projeto

### Opção A — com Git (recomendado)

```powershell
cd C:\xampp\htdocs
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
```

### Opção B — sem Git (ZIP)

1. Abra https://github.com/B3tten/projeto-grupostudio.
2. Clique em **Code** → **Download ZIP**.
3. Extraia o conteúdo em `C:\xampp\htdocs`.
4. Renomeie a pasta extraída para `projeto-grupostudio`.
5. Entre na pasta pelo terminal:

```powershell
cd C:\xampp\htdocs\projeto-grupostudio
```

> Se o XAMPP estiver instalado em outro lugar (por exemplo `D:\xampp`), use `D:\xampp\htdocs` nos comandos.

### Se o repositório for privado

O Git vai pedir autenticação. Use uma conta com acesso ao repositório ou um **Personal Access Token** do GitHub no lugar da senha. Alternativamente, baixe o ZIP estando logado no GitHub.

---

## 6. Instalar as dependências PHP

Dentro da pasta do projeto:

```powershell
composer install
```

Aguarde o download das bibliotecas. Ao final, a pasta `vendor/` será criada.

> `vendor/` não vem no repositório porque é recriada por este comando.

---

## 7. Configurar o arquivo `.env`

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

Isso preenche `APP_KEY` no `.env`. Sem essa chave a aplicação não funciona.

> O `.env` é pessoal e **não deve ir para o Git**. O repositório contém apenas o `.env.example`, sem segredos.

---

## 8. Criar o banco de dados

O projeto usa MySQL e espera um banco chamado **`app-crud`**.

1. Confirme que o **MySQL está iniciado** no XAMPP Control Panel.
2. Abra http://localhost/phpmyadmin.
3. Clique em **Novo** (ou **New**) no menu lateral.
4. Nome do banco:

```text
app-crud
```

5. Codificação/collation:

```text
utf8mb4_unicode_ci
```

6. Clique em **Criar**.

Alternativa pela aba **SQL** do phpMyAdmin:

```sql
CREATE DATABASE `app-crud`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

> O nome tem hífen, por isso precisa de crases no SQL.

Confira se o `.env` está assim (padrão do XAMPP):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app-crud
DB_USERNAME=root
DB_PASSWORD=
```

- Se o usuário `root` do seu MySQL tiver senha, preencha `DB_PASSWORD`.
- Se você criou outro usuário, ajuste `DB_USERNAME` e `DB_PASSWORD`.

---

## 9. Criar as tabelas e os dados de demonstração

```powershell
php artisan migrate --seed
```

Isso cria as tabelas e insere **22 contas a receber fictícias**.

Se o banco já tiver tabelas antigas ou a migração falhar no meio, recrie tudo do zero:

```powershell
php artisan migrate:fresh --seed
```

> Atenção: `migrate:fresh` **apaga todas as tabelas e dados** do banco configurado no `.env`. Use apenas em ambiente local.

Se as tabelas já existirem mas os dados não tiverem sido inseridos:

```powershell
php artisan db:seed
```

---

## 10. Subir a aplicação

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

## 11. Rodar os testes (opcional)

```powershell
php artisan test
```

Resultado esperado: **5 testes passando**. Os testes usam SQLite em memória e não alteram o banco MySQL.

---

## 12. Usar com Apache em vez de `artisan serve` (opcional)

Com o Apache iniciado no XAMPP, o projeto também pode ser acessado por:

```text
http://localhost/projeto-grupostudio/public
```

Isso funciona porque o Laravel é servido a partir da pasta `public/`.

Para usar um domínio local, configure um VirtualHost no Apache apontando o **DocumentRoot** para a pasta `public/`:

```apache
<VirtualHost *:80>
    ServerName financeiro.local
    DocumentRoot "C:/xampp/htdocs/projeto-grupostudio/public"

    <Directory "C:/xampp/htdocs/projeto-grupostudio/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Depois adicione `127.0.0.1 financeiro.local` ao arquivo `C:\Windows\System32\drivers\etc\hosts` e reinicie o Apache.

> Em produção, **nunca** aponte o DocumentRoot para a raiz do projeto. Use sempre `public/`. Veja [`SEGURANCA.md`](SEGURANCA.md).

---

## Problemas comuns

| Mensagem / sintoma | Causa provável | Solução |
| --- | --- | --- |
| `php não é reconhecido...` | PHP fora do PATH | Refaça o passo 2 e reabra o terminal. |
| `composer não é reconhecido...` | Composer fora do PATH | Reinstale o Composer apontando para `C:\xampp\php\php.exe` e reabra o terminal. |
| `could not find driver` | Extensão `pdo_mysql` desativada | Abra `C:\xampp\php\php.ini`, remova o `;` de `extension=pdo_mysql` e reinicie o Apache/terminal. |
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL parado | Inicie o MySQL no XAMPP Control Panel. |
| `SQLSTATE[HY000] [1045] Access denied` | Usuário/senha do banco errados | Ajuste `DB_USERNAME` e `DB_PASSWORD` no `.env`. |
| `Unknown database 'app-crud'` | Banco não criado | Execute o passo 8. |
| `No application encryption key has been specified` | `APP_KEY` vazia | Rode `php artisan key:generate`. |
| Página em branco ou erro 500 | Cache/config antigos ou permissões | Rode `php artisan config:clear`, `php artisan view:clear` e consulte `storage/logs/laravel.log`. |
| `Class not found` após copiar o projeto | Dependências/autoload desatualizados | Rode `composer install` e `composer dump-autoload`. |
| `Failed to listen on 127.0.0.1:8000` | Porta ocupada | Use `php artisan serve --port=8001`. |
| Estilos/ícones não aparecem | Sem internet ou CDN bloqueada | Bootstrap, ícones e fontes são carregados por CDN; libere o acesso ou use uma rede com internet. |
| Datas/valores estranhos | Fuso horário diferente | Verifique `APP_TIMEZONE`/`config/app.php`; o projeto usa o fuso padrão da aplicação. |

### Verificações úteis

```powershell
php -v
composer -V
php -m | findstr pdo_mysql
php artisan about
php artisan migrate:status
```

---

## Linux (Ubuntu/Debian)

Instale PHP, extensões, MySQL, Git e Composer:

```bash
sudo apt update
sudo apt install -y php php-cli php-mysql php-mbstring php-xml php-curl php-zip unzip git mysql-server
sudo apt install -y composer
```

Inicie o MySQL:

```bash
sudo systemctl start mysql
```

Crie o banco:

```bash
sudo mysql -e "CREATE DATABASE \`app-crud\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Se o usuário do banco não for `root`, crie um usuário dedicado e ajuste o `.env`.

Depois, na pasta do projeto:

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Permissões de escrita (se aparecer erro em `storage/` ou `bootstrap/cache/`):

```bash
chmod -R ug+rwx storage bootstrap/cache
```

## macOS (Homebrew)

Instale as ferramentas:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
brew install php composer mysql git
brew services start mysql
```

Crie o banco:

```bash
mysql -u root -e "CREATE DATABASE \`app-crud\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Depois:

```bash
git clone https://github.com/B3tten/projeto-grupostudio.git
cd projeto-grupostudio
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---

## Checklist final

- [ ] XAMPP instalado e MySQL iniciado.
- [ ] PHP 8.0+ e Composer funcionando no terminal (`php -v`, `composer -V`).
- [ ] Projeto clonado/extraído em `htdocs`.
- [ ] `composer install` concluído.
- [ ] `.env` criado e `APP_KEY` gerada.
- [ ] Banco `app-crud` criado no phpMyAdmin.
- [ ] `php artisan migrate --seed` executado (22 registros).
- [ ] `php artisan serve` rodando.
- [ ] Dashboard abrindo em `http://127.0.0.1:8000`.
- [ ] (Opcional) `php artisan test` com 5 testes passando.

Pronto. Se algo falhar, consulte a tabela de problemas comuns e o log em `storage/logs/laravel.log`.
