# Imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar herramientas y dependencias del sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    mysqli \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar archivos de dependencias
COPY composer.json composer.lock ./

# Instalar dependencias PHP
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Copiar archivos del proyecto
COPY . .

# Puerto de Apache
EXPOSE 80