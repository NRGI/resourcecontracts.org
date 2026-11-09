FROM public.ecr.aws/docker/library/php:8.2-apache

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Kiev

RUN apt-get update && apt-get install -y \
    curl git unzip zip wget apt-utils \
    apache2 libpq-dev libzip-dev libgd-dev libicu-dev libxml2-dev libxslt1-dev \
    beanstalkd poppler-utils supervisor gettext python3 python3-pip cron \
    pdftk-java \
 && docker-php-ext-install pdo pdo_pgsql zip gd intl xsl \
 && rm -rf /var/lib/apt/lists/*

RUN curl -O -L https://github.com/papertrail/remote_syslog2/releases/download/v0.20/remote_syslog_linux_amd64.tar.gz \
 && tar -zxf remote_syslog_linux_amd64.tar.gz \
 && cp remote_syslog/remote_syslog /usr/local/bin \
 && rm -r remote_syslog_linux_amd64.tar.gz \
 && rm -r remote_syslog

RUN a2enmod rewrite

RUN mkdir /var/www/rc-admin
COPY composer.json /var/www/rc-admin
COPY composer.lock /var/www/rc-admin

WORKDIR /var/www/rc-admin
RUN curl -s http://getcomposer.org/installer | php \
 && php composer.phar update --prefer-dist --no-scripts --no-autoloader

COPY conf/rc-admin.conf /etc/apache2/sites-available/rc-admin.conf
RUN ln -s /etc/apache2/sites-available/rc-admin.conf /etc/apache2/sites-enabled/rc-admin.conf \
 && rm -f /etc/apache2/sites-enabled/000-default.conf

RUN ln -s /usr/bin/python3 /usr/bin/python

COPY conf/supervisord.conf /etc/supervisord.conf

RUN mkdir -p /var/container_init
COPY conf/init.sh /var/container_init/init.sh
COPY conf/env.template /var/container_init/env.template
COPY conf/log_files.yml.template /var/container_init/log_files.yml.template
COPY conf/logrotate.conf /etc/logrotate.d/rc-admin
COPY conf/settings.config.template /var/container_init/settings.config.template

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
 && printf "post_max_size = 5120M\nupload_max_filesize = 5120M\nmemory_limit = 512M\nmax_execution_time = 180\n" > \
 /usr/local/etc/php/conf.d/rc-admin.ini

COPY . /var/www/rc-admin

WORKDIR /var/www/
RUN git clone -b feature/textract https://github.com/NRGI/pdf-processor.git
WORKDIR /var/www/pdf-processor
RUN python3 -m pip install --break-system-packages -r requirements.txt
WORKDIR /var/www/

RUN mkdir /shared_path \
 && mkdir -p /shared_path/rc-admin/data \
 && mkdir -p /shared_path/rc-admin/storage/logs \
 && touch /shared_path/rc-admin/storage/logs/laravel.log \
 && mkdir -p /shared_path/rc-admin/storage/app \
 && mkdir -p /shared_path/rc-admin/storage/framework/cache \
 && mkdir -p /shared_path/rc-admin/storage/framework/sessions \
 && mkdir -p /shared_path/rc-admin/storage/framework/views \
 && mkdir -p /var/log/supervisor \
 && chmod -R 777 /shared_path \
 && rm -rf /var/www/html \
 && rm -rf /var/www/rc-admin/storage \
 && ln -s /shared_path/rc-admin/storage/ /var/www/rc-admin/storage \
 && ln -s /shared_path/rc-admin/data/ /var/www/rc-admin/public/data \
 && chown -R www-data: /var/www/rc-admin \
 && chown -R www-data: /shared_path

WORKDIR /var/www/rc-admin
RUN php composer.phar dump-autoload --optimize

EXPOSE 80
CMD cd /var/container_init && ./init.sh && /etc/init.d/beanstalkd start && supervisord -c /etc/supervisord.conf && \
 /usr/sbin/apache2ctl -D FOREGROUND