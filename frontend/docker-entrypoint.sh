#!/bin/sh
set -e

echo "Instalando dependências do NPM..."
npm install
echo "Dependências instaladas"

echo "Servidor Vite iniciando..."
exec npm run dev -- --host 0.0.0.0
