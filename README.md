# Corporate Travel Manager

## Descrição

O Corporate Travel Manager é um aplicativo full-stack que gerencia solicitações de viagens corporativas por meio de uma API REST baseada em Laravel e uma interface de front-end em Vue.js.

## Observações importantes

Este projeto foi desenvolvido como um teste técnico e sua infraestrutura foi criada para ambiente de desenvolvimento local.

A estrutura atual utiliza volumes Docker para hot-reload, servidor de desenvolvimento PHP Artisan, Vite dev server, banco de dados interno e configurações simplificadas, além de ser um monorepo. Não foram implementados Nginx, builds de produção, múltiplas réplicas, load balancer ou separação de serviços.

Esta abordagem permite desenvolvimento ágil e testes rápidos. Para ambientes de produção, o projeto pode ser facilmente escalado com as seguintes melhorias:

- Nginx como reverse proxy e servidor de arquivos estáticos
- Builds de produção sem uso de volumes
- PHP-FPM ao invés de php artisan serve
- Load balancer para distribuição de carga
- Banco de dados em serviço externo
- Assets compilados

Como o foco não inclui demonstrar essas habilidades, a infraestrutura para produção não foi priorizada.

## Iniciar projeto

```bash
git clone https://github.com/aleffgomes/corporate-travel-manager.git
cd corporate-travel-manager
```

Este projeto utiliza Make para simplificar os comandos. Se não estiver instalado (verificar em `make --version`) instale-o antes de prosseguir:

```bash
sudo apt update
sudo apt install make -y
```

Após instalar o Make, execute:

```bash
cp .env-example .env
make start
```

## Comandos

```bash
make start          # Inicia containers
make stop           # Para containers
make restart        # Reinicia containers
make build          # Rebuild imagens
make logs           # Ver todos os logs
make test           # Rodar todos os testes
make test-unit      # Rodar testes unitários
make test-services  # Rodar testes dos services
make clean          # Apagar tudo
```

## Testes

O projeto utiliza **testes unitários** para validar a camada de services, garantindo que a lógica de negócio funcione corretamente sem dependências externas.

### Executar Testes

```bash
make test
```

### Estrutura de Testes

```plaintext
tests/
└── Unit/
    └── Services/
```

## Arquitetura

```plaintext
├── backend/
│   └── Dockerfile
│   └── docker-entrypoint.sh
├── frontend/
│   └── Dockerfile
│   └── docker-entrypoint.sh
├── .env
├── .env.example
├── docker-compose.yml
└── Makefile
```

## Variáveis de Ambiente

Todas as configurações estão no arquivo `.env`

## Dockerfiles

### Backend (Laravel)

- Base: `bitnami/laravel:latest`
- JWT pré-instalado
- Hot reload com volumes

### Frontend (Vue.js)

- Base: `node:20-alpine`
- Vite + HMR
- ESLint + Prettier

## Documentação Detalhada

Para mais informações sobre cada parte do projeto, consulte:

- **[Backend README](./backend/README.md)** - Arquitetura, rotas da API, seeders e padrões de design
- **[Frontend README](./frontend/README.md)** - Estrutura, componentes e funcionalidades da interface
