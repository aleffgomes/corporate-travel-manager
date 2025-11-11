# Corporate Travel Manager - Frontend

Sistema de gerenciamento de viagens corporativas.

## Tecnologias

- **Vue 3** - Framework progressivo com Composition API
- **Vue Router 4** - Navegação SPA com lazy loading e guards
- **PrimeVue** - Biblioteca de componentes UI rica e acessível
- **Tailwind CSS** - Framework CSS utilitário para estilização
- **Vite** - Build tool
- **TypeScript** - Tipagem

## Design

O projeto utiliza **PrimeVue** como biblioteca principal de componentes, inspirado no template [Sakai](https://sakai.primevue.org/).

## Estrutura

```bash
src/
├── assets/          # CSS global e recursos estáticos
├── components/      # Componentes Vue reutilizáveis
├── composables/     # Composables (useTheme, etc.)
├── config/          # Configurações (PrimeVue, cores)
├── layouts/         # Layouts da aplicação (AppLayout com menu dinâmico)
├── pages/           # Páginas/Views principais
├── router/          # Configuração de rotas com guards
├── services/        # Serviços de API (auth, travel)
├── stores/          # Pinia stores para gerenciamento de estado
├── types/           # Definições TypeScript
└── utils/           # Utilitários e helpers (formatters)
```

## Funcionalidades

### Autenticação

- Login com email e senha
- Registro com validação de senha
- Persistência de sessão com JWT
- Validação de token

### Dashboard

- Listagem de solicitações em DataTable
- Filtro por status (Pendente, Aprovado, Rejeitado, Cancelado)
- Paginação e ordenação
- Visualização de detalhes
- Criação de novas solicitações
- Edição de solicitações pendentes
- Cancelamento de solicitações não aprovadas
- Exclusão de solicitações

### Administração (Admin Only)

- Página "Todas as Solicitações" com visão completa
- Filtros avançados por coluna (destino, solicitante, status)
- Busca global em múltiplos campos
- Aprovação/Rejeição de solicitações
- Campo obrigatório de motivo ao rejeitar
- Exclusão administrativa

### Componentes Reutilizáveis

- **TravelRequestFormDialog**: Modal de criação/edição
- **TravelRequestDetailsDialog**: Modal de visualização completa
- **TravelRequestStatusDialog**: Modal de aprovação/rejeição
- **TravelRequestDeleteDialog**: Modal de confirmação de exclusão

### Permissões e Segurança

- Menu dinâmico baseado em role do usuário
- Guard de rota para páginas administrativas
- Validação de permissões no backend
- Persistência segura de token JWT
