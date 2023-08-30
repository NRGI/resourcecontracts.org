FROM ubuntu:20.04
MAINTAINER Anjesh Tuladhar <anjesh@yipl.com.np>

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Europe/Kiev
RUN apt-get update && apt-get install -y \
                    curl \
                    git \
                    software-properties-common \
                    unzip \
                    zip \
                    wget \
                    apt-utils \
 && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
 && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
                    apache2 \
                    php7.4 \
                    php7.4-cli \
                    php7.4-curl \
                    php7.4-mbstring \
                    php7.4-mcrypt \
                    php7.4-pgsql \
                    php7.4-readline \
                    php7.4-xml \
                    php7.4-zip \
                    php7.4-gd \
                    php7.4-intl \
                    php7.4-xsl \
                    beanstalkd \
                    poppler-utils \
                    supervisor \
                    gettext \
                    postgresql 

RUN wget http://launchpadlibrarian.net/383018194/pdftk-java_0.0.0+20180723.1-1_all.deb
RUN apt install default-jre-headless libcommons-lang3-java libbcprov-java -y
RUN dpkg -i pdftk-java_0.0.0+20180723.1-1_all.deb
RUN which pdftk

RUN rm -rf /var/lib/apt/lists/* \
 && curl -O -L https://github.com/papertrail/remote_syslog2/releases/download/v0.20/remote_syslog_linux_amd64.tar.gz \
 && tar -zxf remote_syslog_linux_amd64.tar.gz \
 && cp remote_syslog/remote_syslog /usr/local/bin \
 && rm -r remote_syslog_linux_amd64.tar.gz \
 && rm -r remote_syslog 

RUN a2enmod rewrite \
 && a2enmod php7.4

# Fetch composer packages before copying project code to leverage Docker caching
RUN mkdir /var/www/rc-admin
COPY composer.json /var/www/rc-admin
COPY composer.lock /var/www/rc-admin

WORKDIR /var/www/rc-admin
RUN curl -s http://getcomposer.org/installer | php \
 && php composer.phar install --prefer-dist --no-scripts --no-autoloader

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

# Configure PHP
RUN sed -i "s/^post_max_size =.*/post_max_size = 5120M/" /etc/php/7.4/apache2/php.ini \
 && sed -i "s/^upload_max_filesize =.*/upload_max_filesize = 5120M/" /etc/php/7.4/apache2/php.ini \
 && sed -i "s/^memory_limit =.*/memory_limit = 512M/" /etc/php/7.4/apache2/php.ini \
 && sed -i "s/^max_execution_time =.*/max_execution_time = 180/" /etc/php/7.4/apache2/php.ini

COPY . /var/www/rc-admin

WORKDIR /var/www/
# Clone pdf-processor after copying project files to make sure we defeat the cache to get latest code
RUN git clone --branch feature/abby-ocr-container https://github.com/NRGI/pdf-processor.git

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
 && rm -rf /var/www/pdfprocessor/logs \
 && chown -R www-data: /var/www/rc-admin \
 && chown -R www-data: /shared_path

WORKDIR /var/www/rc-admin
RUN php composer.phar dump-autoload --optimize \
 && php artisan clear-compiled

EXPOSE 80
CMD cd /var/container_init && ./init.sh && /etc/init.d/beanstalkd start && supervisord -c /etc/supervisord.conf && /usr/sbin/apache2ctl -D FOREGROUND
