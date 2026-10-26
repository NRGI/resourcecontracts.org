# ResourceContracts.org admin (rc-admin)
# PHP 8.4 / Laravel 13 on Ubuntu 24.04 LTS (Apache mod_php, beanstalkd, supervisor, cron)
FROM ubuntu:24.04
LABEL org.opencontainers.image.source="https://github.com/NRGI/resourcecontracts.org"

ARG DEBIAN_FRONTEND=noninteractive
ARG PHP_VERSION=8.4
ENV TZ=Europe/Kiev \
    PHP_VERSION=${PHP_VERSION} \
    COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates curl git gnupg software-properties-common unzip zip wget apt-utils tzdata \
 && LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php \
 && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
 && apt-get update && apt-get install -y --no-install-recommends \
        apache2 \
        libapache2-mod-php${PHP_VERSION} \
        php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-pgsql \
        php${PHP_VERSION}-readline \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-xsl \
        php${PHP_VERSION}-zip \
        beanstalkd \
        poppler-utils \
        pdftk-java \
        supervisor \
        gettext-base \
        python3 \
        python3-pip \
        python3-venv \
        cron \
 && rm -rf /var/lib/apt/lists/*

# Papertrail remote_syslog2
RUN curl -sSL -o /tmp/remote_syslog.tar.gz https://github.com/papertrail/remote_syslog2/releases/download/v0.21/remote_syslog_linux_amd64.tar.gz \
 && tar -zxf /tmp/remote_syslog.tar.gz -C /tmp \
 && cp /tmp/remote_syslog/remote_syslog /usr/local/bin/ \
 && rm -rf /tmp/remote_syslog /tmp/remote_syslog.tar.gz

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite && a2enmod php${PHP_VERSION}

# Install PHP dependencies before copying the code so the layer is cached.
WORKDIR /var/www/rc-admin
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader --no-interaction --no-progress

COPY conf/rc-admin.conf /etc/apache2/sites-available/rc-admin.conf
RUN ln -s /etc/apache2/sites-available/rc-admin.conf /etc/apache2/sites-enabled/rc-admin.conf \
 && rm -f /etc/apache2/sites-enabled/000-default.conf

COPY conf/supervisord.conf /etc/supervisord.conf
COPY conf/logrotate.conf /etc/logrotate.d/rc-admin
RUN mkdir -p /var/container_init
COPY conf/init.sh conf/env.template conf/log_files.yml.template conf/settings.config.template /var/container_init/
RUN chmod +x /var/container_init/init.sh

# PHP settings (web + cli so queue workers get the same limits)
RUN for sapi in apache2 cli; do \
      ini=/etc/php/${PHP_VERSION}/${sapi}/php.ini; \
      sed -i "s/^post_max_size =.*/post_max_size = 5120M/" $ini \
   && sed -i "s/^upload_max_filesize =.*/upload_max_filesize = 5120M/" $ini \
   && sed -i "s/^memory_limit =.*/memory_limit = 512M/" $ini \
   && sed -i "s/^max_execution_time =.*/max_execution_time = 180/" $ini; \
    done

COPY . /var/www/rc-admin

# pdf-processor (Python, Textract) in its own virtualenv; `python` on PATH is used by ProcessService.
WORKDIR /var/www
RUN git clone --depth 1 -b feature/textract https://github.com/NRGI/pdf-processor.git \
 && python3 -m venv /opt/pdf-processor-venv \
 && /opt/pdf-processor-venv/bin/pip install --no-cache-dir -r /var/www/pdf-processor/requirements.txt \
 && ln -sf /opt/pdf-processor-venv/bin/python /usr/local/bin/python

# Shared, writable runtime state
RUN mkdir -p /shared_path/rc-admin/data \
             /shared_path/rc-admin/storage/logs \
             /shared_path/rc-admin/storage/app \
             /shared_path/rc-admin/storage/framework/cache \
             /shared_path/rc-admin/storage/framework/sessions \
             /shared_path/rc-admin/storage/framework/views \
             /var/log/supervisor \
 && touch /shared_path/rc-admin/storage/logs/laravel.log \
 && rm -rf /var/www/html /var/www/rc-admin/storage \
 && ln -s /shared_path/rc-admin/storage/ /var/www/rc-admin/storage \
 && ln -s /shared_path/rc-admin/data/ /var/www/rc-admin/public/data \
 && rm -rf /var/www/pdf-processor/logs \
 && chown -R www-data: /var/www/rc-admin /var/www/pdf-processor /shared_path \
 && chmod -R 775 /shared_path

WORKDIR /var/www/rc-admin
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
 && php artisan package:discover --ansi

EXPOSE 80
CMD ["/var/container_init/init.sh"]
