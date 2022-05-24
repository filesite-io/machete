#!/bin/sh
theme=$1
if [ ! -d "/var/www/machete/themes/${theme}/" ]; then
    theme=webdirectory
fi

echo "Theme chosed [${theme}]."

## 复制对应皮肤的配置文件
cd /var/www/machete/conf/
rm -f app.php
cp "template_${theme}.php" app.php


## 启动nginx和php-fpm
if [ -e /usr/sbin/nginx ]; then
    /usr/sbin/nginx
fi
if [ -e /usr/sbin/php-fpm8 ]; then
    /usr/sbin/php-fpm8 -F
fi
