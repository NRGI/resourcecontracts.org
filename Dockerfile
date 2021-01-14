FROM ubuntu:16.04
MAINTAINER Anjesh Tuladhar <anjesh@yipl.com.np>

RUN apt-get update && apt-get install -y \
                    curl \
                    git \
                    software-properties-common \
                    unzip \
                    wget \
 && LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php \
 && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C \
 && apt-get update && apt-get upgrade -y && apt-get install -y \
                    apache2 \
                    php5.6 \
                    php5.6-cli \
                    php5.6-curl \
                    php5.6-mbstring \
                    php5.6-mcrypt \
                    php5.6-pgsql \
                    php5.6-readline \
                    php5.6-xml \
                    beanstalkd \
                    pdftk \
                    poppler-utils \
                    supervisor \
		            gettext \
 && rm -rf /var/lib/apt/lists/* \
 && curl -O -L https://github.com/papertrail/remote_syslog2/releases/download/v0.20/remote_syslog_linux_amd64.tar.gz \
 && tar -zxf remote_syslog_linux_amd64.tar.gz \
 && cp remote_syslog/remote_syslog /usr/local/bin \
 && rm -r remote_syslog_linux_amd64.tar.gz \
 && rm -r remote_syslog 

RUN a2enmod rewrite \
 && a2enmod php5.6

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

COPY conf/supervisord.conf /etc/supervisord.conf

RUN mkdir -p /var/container_init
COPY conf/init.sh /var/container_init/init.sh
COPY conf/env.template /var/container_init/env.template
COPY conf/log_files.yml.template /var/container_init/log_files.yml.template
COPY conf/logrotate.conf /etc/logrotate.d/rc-admin
COPY conf/settings.config.template /var/container_init/settings.config.template

# Configure PHP
RUN sed -i "s/^post_max_size =.*/post_max_size = 5120M/" /etc/php/5.6/apache2/php.ini \
 && sed -i "s/^upload_max_filesize =.*/upload_max_filesize = 5120M/" /etc/php/5.6/apache2/php.ini \
 && sed -i "s/^memory_limit =.*/memory_limit = 256M/" /etc/php/5.6/apache2/php.ini

COPY . /var/www/rc-admin

WORKDIR /var/www/
# Clone pdf-processor after copying project files to make sure we defeat the cache to get latest code
RUN git clone https://github.com/anjesh/pdf-processor.git

RUN mkdir /shared_path \
 && mkdir -p /shared_path/rc-admin/data \
 && mkdir -p /shared_path/rc-admin/storage/logs \
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
 && chown -R www-data: /var/www/rc-admin

WORKDIR /var/www/rc-admin
RUN php composer.phar dump-autoload --optimize \
 && php artisan clear-compiled

EXPOSE 80
CMD cd /var/container_init && ./init.sh && /etc/init.d/beanstalkd start && supervisord -c /etc/supervisord.conf && /usr/sbin/apache2ctl -D FOREGROUND
