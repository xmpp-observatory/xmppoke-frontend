FROM php:7.2-apache

MAINTAINER Jonas Wielicki <jonas@wielicki.name>

RUN apt-get update && apt-get install -y \
        mercurial \
        libpq-dev \
        libicu-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*


COPY patches/ /tmp/patches/
RUN hg clone https://bitbucket.org/xnyhps/xmppoke-frontend /var/www/html/ && cd /var/www/html && patch -p1 < /tmp/patches/docker.patch
COPY secrets.php /var/www/html/
#COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

#RUN chmod ugo+rx /usr/local/bin/entrypoint.sh

#EXPOSE 8000

#ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
