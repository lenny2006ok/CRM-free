#!/bin/sh
set -e

# Configura a porta dinâmica do Render.com (variável PORT, default 10000)
PORT="${PORT:-10000}"
echo "==> Configurando Nginx para escutar na porta ${PORT}..."
sed -i "s/__PORT__/${PORT}/g" /etc/nginx/nginx.conf

# Garante que o arquivo .env exista antes de qualquer comando do Artisan
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        echo "==> Copiando .env.example para .env..."
        cp /var/www/html/.env.example /var/www/html/.env
    else
        echo "==> Criando arquivo .env vazio..."
        touch /var/www/html/.env
    fi
fi
chown www-data:www-data /var/www/html/.env
chmod 664 /var/www/html/.env

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
    chmod 775 /var/www/html/database
    chmod 664 /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Gerenciamento de APP_KEY
if [ -n "$APP_KEY" ]; then
    echo "==> Sincronizando APP_KEY fornecida pelas variáveis de ambiente no .env..."
    if grep -q "^APP_KEY=" /var/www/html/.env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
    else
        echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
    fi
else
    # Se não foi passada por ambiente, verifica se já existe uma chave válida no .env
    EXISTING_KEY=$(grep "^APP_KEY=" /var/www/html/.env | cut -d '=' -f2)
    if [ -z "$EXISTING_KEY" ]; then
        echo "==> APP_KEY não fornecida. Gerando nova chave da aplicação..."
        php artisan key:generate --force
    fi
fi

# Executa migrações do banco de dados automaticamente se habilitado (padrão: true)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Executando migrações do banco de dados..."
    php artisan migrate --force || echo "==> Aviso: As migrações falharam ou o banco ainda não está acessível."
fi

# Reajusta permissões de todos os diretórios graváveis para o usuário www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/.env
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Cria o link simbólico do storage público
php artisan storage:link || true

# Otimizações em ambiente de produção
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> Aplicando caches de otimização de produção do Laravel..."
    php artisan config:clear || true
    php artisan config:cache || echo "==> Aviso: Falha ao cachear config."
    php artisan route:cache || echo "==> Aviso: Falha ao cachear rotas."
    php artisan view:cache || echo "==> Aviso: Falha ao cachear views."
fi

echo "==> Iniciando Nginx e PHP-FPM via Supervisord na porta ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
