FROM php:8.4.0RC2-fpm-alpine3.20
RUN apk add rsync nginx zlib-dev libpng-dev freetype-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && cd /usr/local/etc/php/ \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/g' php.ini \
    && sed -i 's/post_max_size = 8M/post_max_size = 100M/g' php.ini \
    && sed -i 's/memory_limit = 128M/memory_limit = 1024M/g' php.ini \
    && mkdir -p /var/www/downloads \
    && cd /var/www/downloads/ \
    && wget "https://git.filesite.io/filesite/machete/archive/master.tar.gz" \
    && tar -zxvf master.tar.gz \
    && rm -f master.tar.gz \
    && mv machete/ /var/www/ \
    && cd /var/www/machete/ \
    && chown www-data:www-data runtime/ \
    && chown -R www-data:www-data www/content/ \
    && chown www-data:www-data www/navs/ \
    && chown www-data:www-data www/girls/ \
    && chown www-data:www-data www/videos/ \
    && rm -f /etc/nginx/http.d/default.conf \
    && cp /var/www/machete/conf/nginx_machete.conf /etc/nginx/http.d/machete.conf \
    && ln -s /var/www/machete/bin/upgrade.sh /usr/bin/upgrade_machete

EXPOSE 80/tcp
ENTRYPOINT ["/var/www/machete/docker-entrypoint.sh"]
# 默认使用图片展皮肤：beauty
CMD ["beauty"]


# 去掉admin后台
#&& cd /var/www/downloads/ \
#&& wget "https://git.filesite.io/wen/jialuomaadmin/archive/master.tar.gz" \
#    && tar -zxvf master.tar.gz \
#    && rm -f master.tar.gz \
#    && rm -rf /var/www/machete/www/admin/ \
#    && mv jialuomaadmin/dist/ /var/www/machete/www/admin \