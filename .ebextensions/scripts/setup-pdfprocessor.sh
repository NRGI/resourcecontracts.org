#!/bin/bash
yum -y install poppler-utils
mkdir -p /var/app/lib
cd /var/app/lib
git clone https://github.com/anjesh/pdf-processor.git
cd /var/app/lib/pdf-processor
cat > /var/app/lib/pdf-processor/settings.config <<EOL
[abbyy]
appid       = xxxxx
password    = xxxxx
EOL
chown -R webapp.webapp /var/app/lib/pdf-processor