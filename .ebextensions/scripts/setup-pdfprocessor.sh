#!/bin/bash
#install poppler-utils
yum -y install poppler-utils

#install pdftk
git clone https://github.com/anjesh/lambda-pdftk-example.git pdftk-bin
cp pdftk-bin/bin/libgcj.so.10 /usr/lib64/
cp pdftk-bin/bin/pdftk /usr/bin/
rm -rf pdftk-bin

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