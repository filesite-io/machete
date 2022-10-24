FROM php:8.1.12RC1-fpm-alpine3.16
RUN apk add \
    nginx zlib-dev libpng-dev freetype-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && mkdir -p /var/www/downloads && cd /var/www/downloads/ && \
    wget "https://git.filesite.io/filesite/machete/archive/master.tar.gz" && \
    tar -zxvf master.tar.gz && \
    mv machete/ /var/www/ && \
    cd /var/www/ && rm -rf downloads/ && \
    mkdir machete/www/navs/ && mkdir machete/www/girls/ && mkdir machete/www/videos/ && \
    rm -f /etc/nginx/http.d/default.conf && \
    cp /var/www/machete/conf/nginx_machete.conf /etc/nginx/http.d/machete.conf

EXPOSE 80/tcp
ENTRYPOINT ["/var/www/machete/docker-entrypoint.sh"]
# 默认使用导航站皮肤：manual
CMD ["manual"]


# build command
# docker build --no-cache -t filesite/machete .
