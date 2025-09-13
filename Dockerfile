# rendezvous-api/Dockerfile pour rendezvous-api Laravel
FROM php:8.2-fpm-alpine

# Installation des dépendances système
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Installation des extensions PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Création de l'utilisateur www
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers composer du rendezvous-api
COPY rendezvous-api/composer*.json ./

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copier le code source du rendezvous-api
COPY rendezvous-api/ .

# Fixer les permissions
RUN chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache

# Configuration Nginx pour Laravel
COPY rendezvous-api/docker/nginx.conf /etc/nginx/nginx.conf
COPY rendezvous-api/docker/laravel.conf /etc/nginx/http.d/default.conf

# Configuration Supervisor
COPY rendezvous-api/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de démarrage
COPY rendezvous-api/docker/start.sh /start.sh
RUN chmod +x /start.sh

# Exposer le port 80
EXPOSE 80

# Utiliser le script de démarrage
CMD ["/start.sh"]