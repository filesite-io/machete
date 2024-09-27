#!/bin/sh
echo "Upgrade machete ..."
echo ""

#增加手动下载更新包后更新操作
manual=$1

## 默认从git.filesite.io升级最新版
download_link_filesite="https://git.filesite.io/filesite/machete/archive/master.tar.gz"


# 检测域名是否能连接
# 返回值：0 - 不能连接，1 - 可连接
detectDomainCanConnect () {
    domain=$1
    if [ -z "${domain}" ]; then
        echo "Usage: detectDomainCanConnect domain"
        echo ""
        exit 1
    fi

    ping_res=`ping -c 3 "${domain}"`
    if [[ $ping_res == *"100.0% packet loss"* ]]; then
        return 0
    fi

    return 1
}


if [ ! -d /var/www/downloads ]; then
    mkdir -p /var/www/downloads
fi

cd /var/www/downloads/

# 下载压缩包
if [ -z "${manual}" ]; then
    if [ -f master.tar.gz ]; then
        rm -f master.tar.gz
    fi

    if [ -d machete/ ]; then
        rm -rf machete/
    fi

    echo "尝试从filesite.io下载最新版源码..."
    echo ""

    detect_domain="git.filesite.io"
    echo "检测${detect_domain}是否可连接..."
    detectDomainCanConnect "${detect_domain}"
    connect_res=$?
    if [ $connect_res -eq 0 ]; then
        echo "⚠️⚠️"
        echo "当前网络无法连接[${detect_domain}]"
        echo "请手动下载：${download_link_filesite}，并保存到：/var/www/downloads/"
        echo "再执行：/var/www/machete/upgrade.sh manual"
        echo ""
        exit
    else
        curl --connect-timeout 15 "${download_link_filesite}" -o "master.tar.gz"
    fi
fi

if [ -f "master.tar.gz" ]; then

    tar -zxvf master.tar.gz
    rm -f master.tar.gz

    rsync -vrL machete/* /var/www/machete/ \
        --exclude=www/content/ \
        --exclude=www/navs/ \
        --exclude=www/girls/ \
        --exclude=www/videos/ \
        --exclude=www/tajian/ \
        --exclude=runtime/

    echo "Machete front-end upgraded."
    echo ""


    # upgrade nginx config
    if [ -d /etc/nginx/http.d/ ]; then
        cp /var/www/machete/conf/nginx_machete.conf /etc/nginx/http.d/machete.conf
    fi

    if [ -d /etc/nginx/conf.d/ ]; then
        cp /var/www/machete/conf/nginx_machete.conf /etc/nginx/conf.d/machete.conf
    fi

    /usr/sbin/nginx -s reload
    echo "Nginx config upgraded and reloaded."
    echo ""

else
    echo "没有找到源码压缩包：/var/www/downloads/master.tar.gz"
    echo ""
fi



echo ""
echo "==Machete upgrade done.=="
echo ""
