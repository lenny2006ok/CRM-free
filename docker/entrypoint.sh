#!/bin/sh
set -e

# Configura a porta dinâmica do Render.com (variável PORT, default 10000)
PORT="${PORT:-10000}"
echo "==> Configurando Nginx para escutar na porta ${PORT}..."
sed -i "s/__PORT__/${PORT}/g" /etc/nginx/nginx.conf

# Cria estrutura de diretórios e ajusta permissões
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/log/supervisor \
         /var/log/nginx

# Se estiver usando SQLite e o arquivo não existir, cria-o
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "==> Inicializando banco de dados SQLite..."
        touch /var/www/html/database/database.sqlite
    fi
    chown -R www-data:www-data /var/www/html/database
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Se APP_KEY não estiver definido no ambiente, gera uma nova chave
if [ -z "$APP_KEY" ]; then
    echo "==> APP_KEY não fornecida. Gerando nova chave da aplicação..."
    php artisan key:generate --force
fi

# Executa migrações do banco de dados automaticamente se habilitado (padrão: true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Executando migrações do banco de dados..."
    php artisan migrate --force || echo "==> Aviso: As migrações falharam ou a conexão com o banco não está disponível no momento."
fi

# Cria o link simbólico do storage público
php artisan storage:link || true

# Otimizações em ambiente de produção
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> Aplicando caches de otimização de produção do Laravel..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "==> Iniciando Nginx e PHP-FPM via Supervisord na porta ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
