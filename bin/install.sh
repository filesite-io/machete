#!/bin/sh
echo "Install php82-fpm and nginx in centos 7.9"

yum -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
yum -y install https://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum -y install yum-utils
yum-config-manager --disable 'remi-php*'
yum-config-manager --enable remi-safe
yum -y install php82-php-fpm php82-php-gd php82-php-mbstring
yum -y install nginx
yum -y install wget
yum -y install samba

ln -s /opt/remi/php82/root/usr/sbin/php-fpm /usr/sbin/php-fpm
php-fpm -v

## show php82-fpm's path
echo "php82-php-fpm installed in"
rpm -ql php82-php-fpm-8.2.0~rc5-18.el7.remi

## replace configs in php.ini
cd /etc/opt/remi/php82/
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 10M/g' php.ini

## zh_CN support
yum -y install kde-l10n-Chinese
yum -y reinstall glibc-common
localedef -c -f UTF-8 -i zh_CN zh_CN.utf8
echo 'LANG="zh_CN.UTF-8"' > /etc/locale.conf
echo 'export LC_ALL="zh_CN.UTF-8"' >> ~/.bashrc

## start php-fpm in background
#php-fpm -D

## start php-fpm in foreground
#php-fpm -F

## start nginx
#/usr/sbin/nginx


## samba user and directory config
#mkdir -p /var/www/sambashare/filesite/

#cd /var/www/sambashare/filesite/
#mv /var/www/machete/www/content/ ./
#mv /var/www/machete/www/navs/ ./
#mv /var/www/machete/www/girls/ ./
#mv /var/www/machete/www/videos/ ./
#ln -s /var/www/sambashare/filesite/content/ /var/www/machete/www/content
#ln -s /var/www/sambashare/filesite/navs/ /var/www/machete/www/navs
#ln -s /var/www/sambashare/filesite/girls/ /var/www/machete/www/girls
#ln -s /var/www/sambashare/filesite/videos/ /var/www/machete/www/videos

#chgrp apache /var/www/sambashare/
#useradd -M -d /var/www/sambashare/filesite -s /usr/sbin/nologin -G apache filesite
#chown -R filesite:apache /var/www/sambashare/filesite
#chmod -R 775 /var/www/sambashare/filesite
#smbpasswd -a filesite
#smbpasswd -e filesite


## start samba
#/usr/sbin/smbd -D

## open port for http and https
#firewall-cmd –-permanent –-add-service=http
#firewall-cmd –-permanent –-add-service=https


## open port for samba
#firewall-cmd –-permanent –-add-service=samba
#firewall-cmd –-reload




## php82-fpm's path
##/etc/logrotate.d/php82-php-fpm
##/etc/opt/remi/php82/php-fpm.conf
##/etc/opt/remi/php82/php-fpm.d
##/etc/opt/remi/php82/php-fpm.d/www.conf
##/etc/opt/remi/php82/sysconfig/php-fpm
##/etc/systemd/system/php82-php-fpm.service.d
##/opt/remi/php82/root/usr/sbin/php-fpm
##/opt/remi/php82/root/usr/share/doc/php82-php-fpm-8.2.0~rc5
##/opt/remi/php82/root/usr/share/doc/php82-php-fpm-8.2.0~rc5/php-fpm.conf.default
##/opt/remi/php82/root/usr/share/doc/php82-php-fpm-8.2.0~rc5/www.conf.default
##/opt/remi/php82/root/usr/share/fpm
##/opt/remi/php82/root/usr/share/fpm/status.html
##/opt/remi/php82/root/usr/share/licenses/php82-php-fpm-8.2.0~rc5
##/opt/remi/php82/root/usr/share/licenses/php82-php-fpm-8.2.0~rc5/fpm_LICENSE
##/opt/remi/php82/root/usr/share/man/man8/php-fpm.8.gz
##/usr/lib/systemd/system/php82-php-fpm.service
##/var/opt/remi/php82/lib/php/opcache
##/var/opt/remi/php82/lib/php/session
##/var/opt/remi/php82/lib/php/wsdlcache
##/var/opt/remi/php82/log/php-fpm
##/var/opt/remi/php82/run/php-fpm