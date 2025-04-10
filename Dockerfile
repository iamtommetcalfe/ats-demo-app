# Dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl zip unzip git libonig-dev libxml2-dev libzip-dev \
    nodejs npm nginx supervisor

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PNPM + latest Node if you want
RUN npm install -g pnpm && npm install -g vite

# Set working directory
WORKDIR /var/www

# Copy Laravel app
COPY . .

# Install PHP & Node dependencies
RUN composer install --no-scripts --no-interaction && \
    npm install && npm run build

# Permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

CMD ["php-fpm"]
