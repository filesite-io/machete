FROM alpine
RUN apk add php8-fpm nginx && \
    mkdir -p /var/www/downloads && cd /var/www/downloads/ && \
    wget "https://git.filesite.io/filesite/machete/archive/master.tar.gz" && \
    tar -zxvf master.tar.gz && \
    mv machete/ /var/www/ && \
    cd /var/www/ && rm -rf downloads/ && \
    rm -f /etc/nginx/http.d/default.conf && \
    cp /var/www/machete/conf/nginx_machete.conf /etc/nginx/http.d/machete.conf

EXPOSE 80/tcp
ENTRYPOINT ["/var/www/machete/docker-entrypoint.sh"]
## 默认使用导航站皮肤：webdirectory
CMD ["webdirectory"]
