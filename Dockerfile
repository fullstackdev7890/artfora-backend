FROM artelworkshop/default-php8.1:1.0

RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN apt-get install -y libmagickwand-dev --no-install-recommends && pecl install imagick
RUN docker-php-ext-enable imagick

RUN rm -rf /app/storage/logs
RUN mkdir /app /home/${short_branch}
WORKDIR /app
COPY . /app
RUN composer install
USER root

RUN ln -s /app/ /home/${short_branch}
# RUN ln -s /home/storage /app/public/storage
# RUN ln -s /home/logs  /app/storage/logs
RUN chown -R www-data:www-data /app
EXPOSE 9000
