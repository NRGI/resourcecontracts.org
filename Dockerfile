FROM ubuntu:14.04
MAINTAINER Anjesh Tuladhar <anjesh@yipl.com.np>

RUN apt-get update && apt-get install -y \
                    curl \
                    git \
                    wget \
 && echo "deb http://ppa.launchpad.net/ondrej/php5-5.6/ubuntu trusty main" > /etc/apt/sources.list.d/ondrej-php5-5_6-trusty.list \
 && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C \
 && apt-get install -y \
                    apache2 \
                    php5 \
                    php5-cli \
                    php5-curl \
                    php5-mcrypt \
                    php5-pgsql \
                    php5-readline \
                    beanstalkd \
                    pdftk \
                    poppler-utils \
                    supervisor \
 && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
 && a2enmod php5 \
 && ln -s /etc/php5/mods-available/mcrypt.ini /etc/php5/apache2/conf.d/20-mcrypt.ini \
 && ln -s /etc/php5/mods-available/mcrypt.ini /etc/php5/cli/conf.d/20-mcrypt.ini

COPY conf/rc-admin.conf /etc/apache2/sites-available/rc-admin.conf
RUN ln -s /etc/apache2/sites-available/rc-admin.conf /etc/apache2/sites-enabled/rc-admin.conf \
 && rm -f /etc/apache2/sites-enabled/000-default.conf

COPY conf/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/

RUN git clone https://github.com/anjesh/pdf-processor.git

COPY . rc-admin

RUN mkdir /shared_path \
 && mkdir -p /shared_path/rc-admin/data \
 && mkdir -p /shared_path/rc-admin/storage/logs \
 && mkdir -p /shared_path/rc-admin/storage/app \
 && mkdir -p /shared_path/rc-admin/storage/framework/cache \
 && mkdir -p /shared_path/rc-admin/storage/framework/sessions \
 && mkdir -p /shared_path/rc-admin/storage/framework/views \
 && mkdir -p /shared_path/pdf-processor/logs \
 && chmod -R 777 /shared_path \
 && rm -rf /var/www/html \
 && rm -rf /var/www/rc-admin/storage \
 && ln -s /shared_path/rc-admin/storage/ /var/www/rc-admin/storage \
 && ln -s /shared_path/rc-admin/data/ /var/www/rc-admin/public/data \
 && rm -rf /var/www/pdfprocessor/logs \
 && ln -s /shared_path/pdfprocessor/logs/ /var/www/pdf-processor/logs

WORKDIR /var/www/rc-admin

RUN curl -s http://getcomposer.org/installer | php \
 && php composer.phar install --prefer-dist \
 && php composer.phar dump-autoload --optimize \
 && php artisan clear-compiled

EXPOSE 80
CMD /etc/init.d/beanstalkd start && supervisord -c /etc/supervisord.conf && /usr/sbin/apache2ctl -D FOREGROUND
