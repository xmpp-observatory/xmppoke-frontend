FROM debian:stretch
MAINTAINER Jonas Wielicki <jonas@wielicki.name>

ENV DEBIAN_FRONTEND noninteractive

RUN \
  apt-get -qq update && \
  apt-get install -y \
  curl \
  wget \
  git \
  supervisor \
  gnupg2 \
  apt-transport-https \
  lsb-release \
  ca-certificates


RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

RUN \
  apt-get -qq update && \
  apt-get -qq --no-install-recommends install -y \
    php7.1 \
    php7.1-fpm \
    nginx \
    vim-nox \
    locales \
    iptables \
    php7.1-fpm \
    php7.1-gd \
    php7.1-imagick \
    php7.1-dev \
    php7.1-curl \
    php7.1-opcache \
    php7.1-cli \
    php7.1-sqlite \
    php7.1-intl \
    php7.1-tidy \
    php7.1-json \
    php7.1-pspell \
    php7.1-recode \
    php7.1-common \
    php7.1-sqlite3 \
    php7.1-bz2 \
    php7.1-mcrypt \
    php7.1-common \
    php7.1-apcu-bc \
    php7.1-xml \
    php7.1-shmop \
    php7.1-mbstring \
    php7.1-zip \
    php7.1-soap \
    php7.1-pgsql \
    netcat \
    supervisor \
    rsyslog \
    cron \
    mercurial \
  && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copying the composer files manually here means that it'll force the composer install to re-run if they change
# COPY composer.json /opt/installtree/composer.json
# COPY composer.lock /opt/installtree/composer.lock
# COPY app/config/parameters.yml.dist /opt/installtree/app/config/parameters.yml.dist

# Run this during the template creation, as the dependencies change infrequently,
#  and it makes running up a fresh container much faster
# RUN cd /opt/installtree && composer install -n --no-scripts --no-autoloader

RUN hg clone https://bitbucket.org/xnyhps/xmppoke-frontend /opt/installtree

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# COPY ${relative_rff_directory} /opt/installtree/
# Run it again now the files are in place, to install the autoloaders
# RUN cd /opt/installtree && composer install -n --no-scripts
RUN chmod ugo+rx /usr/local/bin/entrypoint.sh
WORKDIR /opt/installtree

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
