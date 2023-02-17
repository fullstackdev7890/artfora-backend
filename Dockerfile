FROM artelworkshop/default-php8.1:1.0

WORKDIR /app
COPY ./ /app
RUN composer install
RUN chown -R www-data:www-data /app

EXPOSE 9000
