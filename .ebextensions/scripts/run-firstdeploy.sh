# References: http://blog.rudylee.com/2014/05/22/configuring-elastic-beanstalk-environment-with-ebextensions/
appName="setupbeanstalk"
if ([ ! -f /root/.not-a-new-instance.txt ]) then
  newEC2Instance=true
fi

if ([ $newEC2Instance ]) then
    setup-beanstalk.sh
    setup-supervisor.sh
    setup-pdfprocessor.sh
else
    /etc/init.d/supervisord restart
    /etc/init.d/beanstalk restart
fi

if ([ $newEC2Instance ]) then
    echo -n "" > /root/.not-a-new-instance.txt
fi    