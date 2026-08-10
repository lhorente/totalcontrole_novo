# Total Controle

Sistema de controle financeiro, composto por duas aplicações que compartilham o mesmo banco de dados MySQL:

- **v1** — versão legada, construída em CakePHP 2.x (PHP 7.4).
- **v2** — versão atual, construída em Laravel 8 + Jetstream/Inertia + Vue 2 (PHP 8.3), com autenticação de dois fatores (2FA) obrigatória no login.

## Estrutura do projeto

```
.
├── docker-compose.yml   # orquestra banco de dados, v1 e v2
├── .env.example         # variáveis de ambiente do docker-compose
├── v1/                  # aplicação legada (CakePHP)
│   └── src/
└── v2/                  # aplicação atual (Laravel)
    └── src/
```

## Pré-requisitos

- Docker e Docker Compose
- Git
- (opcional, apenas se for alterar dependências) PHP 8.3 + Composer e Node.js 18

## 1. Clonar o repositório

```bash
git clone git@github.com:lhorente/totalcontrole_novo.git
cd totalcontrole_novo
```

## 2. Configurar as variáveis de ambiente

### 2.1. Variáveis do Docker Compose (banco de dados)

Copie o arquivo de exemplo e ajuste os valores:

```bash
cp .env.example .env
```

```env
MYSQL_ROOT_PASSWORD=defina_uma_senha
MYSQL_DATABASE=totalcontrole
MYSQL_USER=totalcontrole
MYSQL_PASSWORD=defina_uma_senha

DB_HOST=totalcontrole_db
DB_DATABASE=totalcontrole
DB_USERNAME=totalcontrole
DB_PASSWORD=defina_uma_senha
```

> `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` devem ser iguais a `MYSQL_DATABASE`, `MYSQL_USER` e `MYSQL_PASSWORD`. `DB_HOST` deve permanecer `totalcontrole_db`, que é o nome do serviço do banco na rede do Docker Compose.

### 2.2. Configuração do v1 (CakePHP)

O arquivo `v1/src/app/Config/database.php` não é versionado. Crie-o a partir do modelo existente:

```bash
cp v1/src/app/Config/database_sample.php v1/src/app/Config/database.php
```

Edite o array `$default` com os mesmos dados usados no passo 2.1:

```php
public $default = array(
    'datasource' => 'Database/Mysql',
    'persistent' => false,
    'host' => 'totalcontrole_db',
    'login' => 'totalcontrole',
    'password' => 'defina_uma_senha',
    'database' => 'totalcontrole',
    'prefix' => '',
    'encoding' => 'utf8',
);
```

### 2.3. Configuração do v2 (Laravel)

Crie o arquivo `v2/src/.env` (também não versionado):

```env
APP_NAME="Total Controle"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8092

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=totalcontrole_db
DB_PORT=3306
DB_DATABASE=totalcontrole
DB_USERNAME=totalcontrole
DB_PASSWORD=defina_uma_senha

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"
```

Deixe `APP_KEY` em branco — ela será gerada no passo 4.

## 3. Subir os containers

```bash
docker compose up -d --build
```

Isso cria três serviços:

| Serviço              | Descrição            | Porta                  |
|----------------------|-----------------------|-------------------------|
| `totalcontrole_db`    | MySQL 5.7              | `3310` (host) → `3306` |
| `total_controle_v1`   | App legado (CakePHP)   | `8091` → `80`          |
| `total_controle_v2`   | App atual (Laravel)    | `8092` → `80`          |

## 4. Preparar a aplicação v2 (Laravel)

```bash
# gerar a APP_KEY
docker compose exec total_controle_v2 php artisan key:generate

# rodar as migrations (cria as tabelas usadas por v1 e v2)
docker compose exec total_controle_v2 php artisan migrate

# (opcional) popular dados iniciais
docker compose exec total_controle_v2 php artisan db:seed
```

> As dependências PHP (`vendor/`) e os assets de front-end já compilados (`public/js`, `public/css`) estão versionados no repositório, então **não é necessário** rodar `composer install` ou `npm install` para subir a aplicação. Só rode esses comandos se for alterar dependências ou recompilar os assets:
>
> ```bash
> docker compose exec total_controle_v2 composer install
> docker run --rm -v "$(pwd)/v2/src:/app" -w /app node:18-alpine sh -c "npm ci && npm run production"
> ```

## 5. Acessar a aplicação

- v2 (atual): http://localhost:8092
- v1 (legado): http://localhost:8091

No primeiro acesso ao v2, será necessário configurar a autenticação de dois fatores (2FA), obrigatória para todos os usuários.

## Comandos úteis

```bash
docker compose logs -f total_controle_v2   # logs da aplicação v2
docker compose exec total_controle_v2 bash # shell dentro do container v2
docker compose down                        # parar os containers
docker compose down -v                     # parar e apagar os dados do banco
```
