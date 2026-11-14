# ==============================================================================
# Multi-Stage Dockerfile Otimizado para Produção (PHP 8.3-FPM + Nginx) - Render.com
# ==============================================================================

# ------------------------------------------------------------------------------
# Estágio 1: Build de Assets Frontend (Vite / Tailwind CSS)
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci --ignore-scripts || npm install --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ------------------------------------------------------------------------------
# Estágio 2: Ambiente de Execução PHP 8.3-FPM + Nginx
# ------------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

LABEL maintainer="CRM Gestor IPTV"
LABEL description="Produção Laravel 11/12/13 com PHP-FPM, Nginx e Supervisord para Render.com"

# Instala dependências de sistema necessárias
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    sed \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    linux-headers \
    $PHPIZE_DEPS

# Configura e instala extensões PHP essenciais
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        zip \
        intl \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        bcmath \
        mbstring \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Instala Composer oficial a partir da imagem do Composer v2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia configurações personalizadas de PHP, Nginx, Supervisor e Entrypoint
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copia dependências do Composer para cache eficiente de camadas
COPY composer.json composer.lock ./

# Instala pacotes do Composer sem dependências de desenvolvimento
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copia o restante do código-fonte da aplicação
COPY . .

# Copia os assets compilados pelo Vite do primeiro estágio
COPY --from=frontend /app/public/build ./public/build

# Gera o autoloader otimizado de produção
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Ajusta permissões dos diretórios de armazenamento e cache
RUN mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Porta padrão de escuta (sobrescrita em runtime pela variável PORT do Render)
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
