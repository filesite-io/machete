#!/bin/sh
theme=$1
if [ -d "/var/www/machete/themes/${theme}/" ]; then
    echo "Theme chosed [${theme}]."

    ## 权限重新设置
    chown -R www-data:www-data /var/www/machete/runtime/

    ## 复制对应皮肤的配置文件
    cd /var/www/machete/runtime/
    if [ ! -d cache ]; then
        mkdir cache/
        chown -R www-data:www-data cache/
    fi


    ## 避免容器重启后重新生成配置文件
    if [ ! -f custom_config.json ]; then
        cp "../conf/custom_config_${theme}.json" ./custom_config.json
        chmod 777 custom_config.json
    fi
fi


## 启动nginx和php-fpm
if [ -e /usr/sbin/nginx ]; then
    echo "Start nginx."
    /usr/sbin/nginx
fi


## 启动bot主程序
if [ -e /usr/local/bin/php ]; then
    echo "Start main bot."
    cd /var/www/machete/
    if [ -f runtime/cache/stats_scan.json ]; then
        rm -f runtime/cache/stats_scan.json
    fi
    /usr/local/bin/php bin/command.php mainBot &
fi


## 启动php-fpm
if [ -e /usr/local/sbin/php-fpm ]; then
    echo "Start php-fpm."
    php-fpm -F
fi