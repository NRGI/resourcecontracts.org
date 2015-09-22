#!/bin/bash
pip install supervisor
cp /home/ec2-user/scripts/conf/supervisord.conf /etc/supervisord.conf
cp /home/ec2-user/scripts/conf/supervisord.sh /etc/init.d/supervisord
ln -s /usr/local/bin/supervisord /usr/sbin/supervisord
chmod 777 /etc/init.d/supervisord
/etc/init.d/supervisord restart