# Corporate Travel Manager - Backend

API REST para gerenciamento de viagens corporativas.

## Tecnologias

- **Laravel**
- **JWT Authentication**
- **MySQL/MariaDB**
- **Redis**
- **PHPUnit**

## Arquitetura

O projeto segue uma arquitetura em camadas com separação de responsabilidades:

```bash
app/
├── Contracts/           # Interfaces (Repository Pattern)
├── Domain/              # Objetos de domínio e transformação de dados
├── Http/
│   ├── Controllers/     # Endpoints da API
│   └── Middleware/      # Middlewares
├── Models/              # Eloquent Models com relacionamentos
├── Providers/           # Service Providers (DI)
├── Repositories/        # Implementação dos repositórios
└── Services/            # Lógica de negócio
```

### Padrões de Design

- **Repository Pattern**: Abstração da camada de dados
- **Domain Layer**: Transformação e encapsulamento de dados
- **Service Layer**: Lógica de negócio centralizada
- **Dependency Injection**: Inversão de controle via Laravel Container

## Funcionalidades

### Autenticação

- Registro de usuários com validação
- Login com JWT
- Refresh token
- Logout
- Perfis: Admin e User

### Gerenciamento de Viagens

- **CRUD completo** de solicitações de viagem
- **Controle de permissões** baseado em roles
- **Workflow de status**: Pendente → Aprovado/Rejeitado/Cancelado
- **Regras de negócio**:
  - Apenas admins aprovam/rejeitam
  - Usuários só veem suas próprias solicitações
  - Cancelamento apenas se não aprovado
  - Edição apenas se status pendente

## Database

### Tabelas

- `roles` - Perfis de usuário (Admin, User)
- `users` - Usuários do sistema
- `travel_request_statuses` - Status das solicitações
- `travel_requests` - Solicitações de viagem

### Relacionamentos

- `users` → `roles` (belongsTo)
- `travel_requests` → `users` (belongsTo)
- `travel_requests` → `travel_request_statuses` (belongsTo)

### Seeders

O projeto inclui seeders para popular o banco com dados de exemplo:

```bash
# Dados criados automaticamente ao iniciar o container:
# - 2 roles (admin, user)
# - 4 status (pending, approved, rejected, cancelled)

# Para popular banco com dados fictícios:
docker exec -it corporate-travel-manager-backend-1 php artisan db:seed DatabaseSeeder

```

**Credenciais fictícios:**

```text
Admin:
- Email: admin@corporatetravel.com
- Senha: admin123

Usuário:
- Email: john@example.com
- Senha: password123
```

## Rotas da API

### Autenticação (Public)

```http
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/refresh
POST   /api/auth/logout
GET    /api/auth/me
```

### Viagens (Protected - JWT)

```http
GET    /api/travel-requests           # Listar (admin vê todas, user vê suas)
POST   /api/travel-requests           # Criar nova solicitação
GET    /api/travel-requests/{id}      # Ver detalhes
PUT    /api/travel-requests/{id}      # Editar (apenas se pending)
DELETE /api/travel-requests/{id}      # Deletar (admin ou próprio user)
PATCH  /api/travel-requests/{id}/status      # Aprovar/Rejeitar (admin only)
PATCH  /api/travel-requests/{id}/cancel      # Cancelar (se não aprovado)
```

## Testes

```bash
# Rodar todos os testes
make test
