#!/bin/sh
theme=$1
if [ -d "/var/www/machete/themes/${theme}/" ]; then
    echo "Theme chosed [${theme}]."

    ## 复制对应皮肤的配置文件
    cd /var/www/machete/runtime/

    ## 避免容器重启后重新生成配置文件
    if [ ! -f custom_config.json ]; then
        cp "../conf/custom_config_${theme}.json" ./custom_config.json
        chmod 777 custom_config.json
    fi
fi

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
