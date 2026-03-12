# Multi-Gateway Payment System

API para gerenciamento de pagamentos multi-gateway com fallback automatico, autenticacao JWT e controle de acesso baseado em roles.

## Requisitos

- Docker e Docker Compose
- (Opcional) PHP 8.3+, Composer 2 (para desenvolvimento local sem Docker)

## Como Instalar e Rodar

### Com Docker (recomendado)

```bash
git clone <repo-url>
cd laravel

# Copiar arquivo de ambiente
cp .env.example .env

# Subir todos os servicos
docker compose up --build -d

# A aplicacao estara disponivel em http://localhost:8000
# O entrypoint executa migrations e seeders automaticamente
```

### Sem Docker (desenvolvimento local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Configurar banco MySQL no .env
php artisan migrate
php artisan db:seed

php artisan serve
```

### Rodando os Mocks dos Gateways (sem Docker)

```bash
docker run -p 3001:3001 -p 3002:3002 matheusprotzen/gateways-mock
```

## Como Rodar os Testes

```bash
php artisan test

# Ou com verbose
php artisan test --verbose

# Rodar grupo especifico
php artisan test tests/Unit/
php artisan test tests/Feature/
```

## Arquitetura

```
Controller -> Action (Use Case) -> Service/Repository -> Model
```

- **Controllers**: magros, recebem request, delegam para Actions
- **Actions**: encapsulam a logica de cada use case
- **Services**: orquestram operacoes cross-cutting (PaymentService)
- **DTOs**: transportam dados tipados entre camadas
- **Contracts**: interfaces para inversao de dependencia (SOLID)
- **Gateways**: implementacoes do GatewayContract (Strategy Pattern)

### Adicionando um Novo Gateway

1. Criar classe implementando `App\Contracts\GatewayContract`
2. Registrar no `App\Providers\GatewayServiceProvider`
3. Inserir registro na tabela `gateways`

Nenhum codigo existente precisa ser modificado (Open/Closed Principle).

## Usuarios Padrao (Seed)

| Email | Senha | Role |
|-------|-------|------|
| admin@payment.com | password | ADMIN |

## Rotas da API

### Rotas Publicas

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/api/auth/login` | Login (retorna JWT token) |
| POST | `/api/purchases` | Realizar uma compra |

### Rotas Privadas (requerem Bearer token)

#### Gateway Management (ADMIN)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| PATCH | `/api/gateways/{id}/toggle` | Ativar/desativar gateway |
| PATCH | `/api/gateways/{id}/priority` | Alterar prioridade |

#### User Management (ADMIN, MANAGER)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/api/users` | Listar usuarios |
| POST | `/api/users` | Criar usuario |
| GET | `/api/users/{id}` | Detalhe do usuario |
| PUT | `/api/users/{id}` | Atualizar usuario |
| DELETE | `/api/users/{id}` | Deletar usuario (soft delete) |

#### Product Management (ADMIN, MANAGER, FINANCE)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/api/products` | Listar produtos |
| POST | `/api/products` | Criar produto |
| GET | `/api/products/{id}` | Detalhe do produto |
| PUT | `/api/products/{id}` | Atualizar produto |
| DELETE | `/api/products/{id}` | Deletar produto (soft delete) |

#### Refund (ADMIN, FINANCE)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| POST | `/api/transactions/{id}/refund` | Reembolsar compra |

#### Clients & Transactions (todos autenticados)

| Metodo | Rota | Descricao |
|--------|------|-----------|
| GET | `/api/clients` | Listar clientes |
| GET | `/api/clients/{id}` | Detalhe do cliente com compras |
| GET | `/api/transactions` | Listar transacoes |
| GET | `/api/transactions/{id}` | Detalhe da transacao |

## Payloads

### Login

```json
POST /api/auth/login
{
    "email": "admin@payment.com",
    "password": "password"
}
```

### Compra

```json
POST /api/purchases
{
    "client_name": "John Doe",
    "client_email": "john@email.com",
    "card_number": "5569000000006063",
    "cvv": "010",
    "products": [
        { "product_id": 1, "quantity": 2 },
        { "product_id": 3, "quantity": 1 }
    ]
}
```

### Criar Usuario

```json
POST /api/users
{
    "name": "New User",
    "email": "user@email.com",
    "password": "password123",
    "password_confirmation": "password123",
    "roles": ["USER"]
}
```

### Alterar Prioridade

```json
PATCH /api/gateways/{id}/priority
{
    "priority": 2
}
```

## Roles

| Role | Permissoes |
|------|-----------|
| ADMIN | Acesso total |
| MANAGER | Gerenciar produtos e usuarios |
| FINANCE | Gerenciar produtos e realizar reembolsos |
| USER | Listar clientes e transacoes |

Um usuario pode ter multiplas roles.

## Sistema Multi-Gateway

O sistema tenta processar pagamentos seguindo a ordem de prioridade dos gateways ativos. Se o primeiro falhar, tenta o proximo automaticamente. Somente se todos falharem, retorna erro.

### Gateway 1 (porta 3001)
- Autenticacao via Bearer token (login com email/token)
- Campos em ingles (amount, name, cardNumber)
- Reembolso via POST /transactions/:id/charge_back

### Gateway 2 (porta 3002)
- Autenticacao via headers estaticos
- Campos em portugues (valor, nome, numeroCartao)
- Reembolso via POST /transacoes/reembolso com body { id }

## Variaveis de Ambiente

| Variavel | Descricao | Default |
|----------|-----------|---------|
| DB_CONNECTION | Driver do banco | mysql |
| DB_HOST | Host do MySQL | mysql |
| DB_DATABASE | Nome do banco | payment_system |
| GATEWAY_ONE_URL | URL do Gateway 1 | http://gateways-mock:3001 |
| GATEWAY_TWO_URL | URL do Gateway 2 | http://gateways-mock:3002 |
| JWT_SECRET | Chave secreta do JWT | (gerado via artisan) |

## Docker Compose - Servicos

| Servico | Imagem | Porta | Descricao |
|---------|--------|-------|-----------|
| app | Dockerfile (PHP 8.3) | 8000 | Aplicacao Laravel |
| mysql | mysql:8.0 | 3307 (host) / 3306 (container) | Banco de dados |
| gateways-mock | matheusprotzen/gateways-mock | 3001, 3002 | Mock dos gateways |

## Stack Tecnologica

- PHP 8.3
- Laravel 12
- MySQL 8
- JWT Auth (php-open-source-saver/jwt-auth)
- PHPUnit (TDD)
- Docker Compose
