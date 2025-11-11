#!/bin/bash
set -e

echo "Instalando dependências do Composer..."
composer install --optimize-autoloader --no-interaction
echo " Dependências instaladas"

echo "Aguardando banco de dados..."
for i in {1..30}; do
  if php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; then
    echo " Banco de dados está pronto!"
    break
  fi
  echo " Aguardando MariaDB... (tentativa $i/30)"
  sleep 2
done

if grep -q "APP_KEY=$" .env || ! grep -q "APP_KEY=" .env; then
  echo "Gerando APP_KEY..."
  php artisan key:generate --force --no-interaction
  echo " APP_KEY gerado"
else
  echo " APP_KEY já configurado"
fi

if grep -q "JWT_SECRET=$" .env || ! grep -q "JWT_SECRET=" .env; then
  echo "Gerando JWT_SECRET..."
  php artisan jwt:secret --force --no-interaction
  echo " JWT_SECRET gerado"
else
  echo " JWT_SECRET já configurado"
fi

echo "Executando migrations..."
php artisan migrate --force --no-interaction
echo " Migrations executadas"

echo "Servidor Laravel iniciando..."

exec php artisan serve --host=0.0.0.0 --port=8000
