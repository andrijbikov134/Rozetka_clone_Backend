FROM php:8.2-apache
WORKDIR /var/www/html
COPY . .

# Встановлюємо необхідні залежності
RUN apt-get update && apt-get install -y default-mysql-client \
    && docker-php-ext-install mysqli pdo_mysql
RUN apt-get install -y zip unzip git

# Копіюємо файл конфігурації VirtualHost
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN echo "Listen 8888" >> /etc/apache2/ports.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

RUN composer install --no-dev --optimize-autoloader

# Активуємо конфігурацію
RUN a2ensite 000-default.conf && a2enmod rewrite

# Експортуємо порт
EXPOSE 8888

# Запускаємо Apache
CMD ["apache2-foreground"]