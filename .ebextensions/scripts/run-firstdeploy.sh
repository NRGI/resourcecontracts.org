# References: http://blog.rudylee.com/2014/05/22/configuring-elastic-beanstalk-environment-with-ebextensions/
appName="setupbeanstalk"
if ([ ! -f /root/.not-a-new-instance.txt ]) then
  newEC2Instance=true
fi

if ([ $newEC2Instance ]) then


fi

if ([ $newEC2Instance ]) then
    echo -n "" > /root/.not-a-new-instance.txt
fi    