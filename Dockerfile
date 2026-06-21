FROM php-apache-custom

RUN apt-get update && apt-get install -y --no-install-recommends \
    openssh-client \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

EXPOSE 80

CMD [ apache2-foreground]
