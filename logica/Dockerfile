# ======================================================
# Etapa 1 — Build do Laravel com Composer
# ======================================================
FROM php:8.3-fpm AS build

WORKDIR /var/www/html

# Instalar dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copiar Composer do container oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar o código Laravel
COPY . /var/www/html

# Instalar dependências Laravel (modo produção)
RUN composer install --no-dev --optimize-autoloader

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

    RUN mkdir -p bootstrap/cache storage \
    && chmod -R 775 bootstrap/cache storage


# ======================================================
# Etapa 2 — Imagem final de produção
# ======================================================
FROM php:8.3-fpm

WORKDIR /var/www/html

# Instalar Nginx, Supervisor e dependências PHP necessárias
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Copiar a aplicação e configurações do build
COPY --from=build /var/www/html /var/www/html
COPY ca.pem /etc/ssl/certs/ca.pem
COPY nginx.conf /etc/nginx/sites-available/default
COPY supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Expor a porta HTTP
EXPOSE 80

# ======================================================
# ENTRYPOINT — Inicialização automática no Render
# ======================================================
CMD bash -c "\
echo '🧩 Iniciando container Laravel...' && \
if [ ! -f /var/www/html/.env ]; then \
    echo '⚙️ Nenhum .env detectado — criando a partir do exemplo'; \
    cp /var/www/html/.env.example /var/www/html/.env || true; \
fi && \
php artisan config:cache || true && \
php artisan route:cache || true && \
echo '✅ Laravel pronto (com APP_KEY do Render)' && \
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"
