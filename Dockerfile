FROM ubuntu:14.04
MAINTAINER Anjesh Tuladhar <anjesh@yipl.com.np>

RUN apt-get update
RUN apt-get install -y \
                    curl \
                    git \
                    wget
RUN echo "deb http://ppa.launchpad.net/ondrej/php5-5.6/ubuntu trusty main" > /etc/apt/sources.list.d/ondrej-php5-5_6-trusty.list
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
RUN apt-get install -y \
                    apache2 \
                    php5 \
                    php5-cli \
                    php5-curl \
                    php5-mcrypt \
                    php5-pgsql \
                    php5-readline 
RUN apt-get install -y \
                    beanstalkd \
                    pdftk \
                    poppler-utils \
                    supervisor

RUN a2enmod rewrite
RUN a2enmod php5
RUN ln -s /etc/php5/mods-available/mcrypt.ini /etc/php5/apache2/conf.d/20-mcrypt.ini
RUN ln -s /etc/php5/mods-available/mcrypt.ini /etc/php5/cli/conf.d/20-mcrypt.ini

WORKDIR /var/www/html/
RUN git clone https://github.com/NRGI/resourcecontracts.org.git rc

RUN git clone https://github.com/anjesh/pdf-processor.git

RUN mkdir /shared_path
RUN mkdir -p /shared_path/rc
RUN mkdir -p /shared_path/rc/data
RUN mkdir -p /shared_path/rc/storage
RUN mkdir -p /shared_path/rc/storage/logs
RUN mkdir -p /shared_path/rc/storage/app
RUN mkdir -p /shared_path/rc/storage/framework
RUN mkdir -p /shared_path/rc/storage/framework/cache
RUN mkdir -p /shared_path/rc/storage/framework/sessions
RUN mkdir -p /shared_path/rc/storage/framework/views
RUN mkdir -p /shared_path/pdf-processor/logs
RUN chmod -R 777 /shared_path

RUN rm -rf /var/www/html/rc/storage
RUN ln -s /shared_path/rc/storage/ /var/www/html/rc/storage
RUN ln -s /shared_path/rc/data/ /var/www/html/rc/public/data
RUN rm -rf /var/www/html/pdfprocessor/logs
RUN ln -s /shared_path/pdfprocessor/logs/ /var/www/html/pdf-processor/logs

WORKDIR /var/www/html/rc
RUN curl -s http://getcomposer.org/installer | php
RUN php composer.phar install --prefer-source
RUN php composer.phar dump-autoload --optimize
RUN php artisan clear-compiled

ADD conf/supervisord.conf /etc/supervisord.conf

ADD conf/.env /var/www/html/rc/.env

EXPOSE 80
CMD /etc/init.d/beanstalkd start && supervisord -c /etc/supervisord.conf && /usr/sbin/apache2ctl -D FOREGROUND