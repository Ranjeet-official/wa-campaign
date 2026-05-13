FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev

RUN docker-php-ext-install zip gd pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --optimize-autoloader --no-interaction

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080
