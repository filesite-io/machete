#!/bin/sh
theme=$1
if [ ! -d "/var/www/machete/themes/${theme}/" ]; then
    theme=webdirectory
fi

echo "Theme chosed [${theme}]."

## 复制对应皮肤的配置文件
cd /var/www/machete/runtime/
rm -f custom_config.json
cp "../conf/custom_config_${theme}.json" ./custom_config.json
chown apache:apache custom_config.json

## 启动samba
if [ -e /usr/sbin/smbd ]; then
    echo "Start smbd."
    /usr/sbin/smbd -D
fi

## 启动nginx和php-fpm
if [ -e /usr/sbin/nginx ]; then
    echo "Start nginx."
    /usr/sbin/nginx
fi

echo "Start php-fpm."
php-fpm -F
