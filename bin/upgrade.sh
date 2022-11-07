#!/bin/sh
echo "Upgrade machete ..."
echo ""


mkdir -p /var/www/downloads
cd /var/www/downloads/
rm -f master.tar.gz
rm -rf machete/
wget "https://git.filesite.io/filesite/machete/archive/master.tar.gz"
tar -zxvf master.tar.gz
rm -f master.tar.gz
rsync -vruL machete/* /var/www/machete/ \
    --exclude=www/content/ \
    --exclude=www/navs/ \
    --exclude=www/girls/ \
    --exclude=www/videos/ \
    --exclude=runtime/

# upgrade admin
cd /var/www/downloads/
wget "https://git.filesite.io/wen/jialuomaadmin/archive/master.tar.gz"
tar -zxvf master.tar.gz
rm -f master.tar.gz
rm -rf /var/www/machete/www/admin/
mv jialuomaadmin/dist/ /var/www/machete/www/admin

echo "Machete upgrade done."
echo ""