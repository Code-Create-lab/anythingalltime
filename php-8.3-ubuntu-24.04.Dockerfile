FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y apache2 libapache2-mod-php php-xml php-mbstring php-mysql php-gd php-zip php-curl unzip

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

COPY ./docker_config/apache2/000-default.conf /etc/apache2/sites-available/000-default.conf

#COPY . /var/www/html

RUN a2enmod rewrite

EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]
