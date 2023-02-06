FROM artelworkshop/default-php8.1:1.0
RUN rm -rf /app/storage/logs
RUN mkdir /app /home/${short_branch}-esc
WORKDIR /app
COPY . /app
RUN composer install
USER root

RUN ln -s /app/ /home/${short_branch}-esc/backend
RUN ln -s /home/storage /app/public/storage
RUN ln -s /home/logs  /app/storage/logs
RUN chown -R www-data:www-data /app

EXPOSE 9000
