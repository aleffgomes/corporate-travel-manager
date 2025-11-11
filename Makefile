.PHONY: help start stop restart build logs clean test setup

help:
	@echo "  make start      		- Inicia containers (gera chaves automaticamente)"
	@echo "  make stop       		- Para containers"
	@echo "  make restart   		- Reinicia containers"
	@echo "  make build      		- Rebuild images"
	@echo "  make logs       		- Mostra logs"
	@echo "  make test       		- Roda testes"
	@echo "  make test-unit  		- Roda testes unitários"
	@echo "  make test-services		- Roda testes dos services"
	@echo "  make clean      		- Remove tudo"
	@echo "  make shell-backend     	- Acessa o shell do backend"
	@echo "  make shell-frontend    	- Acessa o shell do frontend"
	@echo ""


start:
	@echo "Iniciando containers..."
	@docker compose up -d --build
	@echo ""
	@$(MAKE) status

stop:
	@docker compose down
	@echo "Containers parados"
	@docker ps

restart:
	@docker compose restart
	@echo "Containers reiniciados"
	@docker ps

build:
	@echo "Rebuilding images..."
	@docker compose build --no-cache
	@echo "Build concluído!"

logs:
	@docker compose logs -f

logs-backend:
	@docker compose logs -f backend

logs-frontend:
	@docker compose logs -f frontend

logs-queue:
	@docker compose logs -f queue

test:
	@docker compose exec backend php artisan test

test-unit:
	@echo "Rodando testes unitários..."
	@docker compose exec backend php artisan test --testsuite=Unit

test-services:
	@echo "Rodando testes dos services..."
	@docker compose exec backend php artisan test tests/Unit/Services

shell-backend:
	@docker compose exec backend bash

shell-frontend:
	@docker compose exec frontend sh

db-fresh:
	@docker compose exec backend php artisan migrate:fresh --seed

status:
	@echo "Frontend:  http://localhost:$$(grep VITE_PORT= .env | cut -d '=' -f2)"
	@echo "Backend:   http://localhost:$$(grep APP_PORT= .env | cut -d '=' -f2)"
	@echo "MariaDB:   localhost:$$(grep DB_PORT .env | cut -d '=' -f2)"
	@echo "Redis:     localhost:$$(grep REDIS_PORT .env | cut -d '=' -f2)"
	@echo ""

clean:
	@echo "Isso vai limpar o remover as imagens e volumes docker. Continuar? [y/N]" && read ans && [ $${ans:-N} = y ]
	@docker compose down -v
	@docker rmi -f $$(docker images -q ctm-*) 2>/dev/null || true
	@echo "Limpeza finalizada!"