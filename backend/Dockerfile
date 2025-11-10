FROM bitnami/laravel:latest

WORKDIR /app

COPY composer.json ./
COPY composer.lock* ./

COPY . .

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
