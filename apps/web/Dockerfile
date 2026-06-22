FROM php:8.2-apache

# Enable Apache mod_rewrite for Yii2 URLs
RUN a2enmod rewrite

# Install required PHP extensions for Yii2
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip

# Set DocumentRoot to Yii2 frontend web directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/frontend/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Initialize Yii2 application (Production environment)
RUN php init --env=Production --overwrite=All

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
