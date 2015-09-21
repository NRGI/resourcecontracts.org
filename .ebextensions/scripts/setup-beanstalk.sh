# References: http://blog.rudylee.com/2014/05/22/configuring-elastic-beanstalk-environment-with-ebextensions/

export PATH=$PATH:/usr/local/bin:/usr/local/sbin
mkdir -p /home/ec2-user/beanstalkd

#beanstalkd user needed to run beanstalkd server
adduser beanstalkd

#beanstalkd for queue
git clone https://github.com/kr/beanstalkd.git /home/ec2-user/beanstalkd
cd /home/ec2-user/beanstalkd
make

cd /home/ec2-user
cp /home/ec2-user/beanstalkd/beanstalkd /usr/bin/
ln -s /usr/bin/beanstalkd /usr/sbin/beanstalkd
rm -rf /home/ec2-user/beanstalkd


#daemonize needed for beanstalkd.sh
mkdir -p /home/ec2-user/daemonize
git clone https://github.com/bmc/daemonize.git /home/ec2-user/daemonize
cd /home/ec2-user/daemonize
sh configure
make

cd /home/ec2-user
cp -rf /home/ec2-user/daemonize/daemonize /usr/bin/
ln -s /usr/bin/daemonize /usr/sbin/daemonize
rm -rf /home/ec2-user/daemonize

#copy beanstalk config and sh to init and run
cp /home/ec2-user/scripts/conf/beanstalkd.conf /etc/default/beanstalkd
cp /home/ec2-user/scripts/conf/beanstalkd.sh /etc/init.d/beanstalkd
chmod 777 /etc/init.d/beanstalkd
/etc/init.d/beanstalkd start
