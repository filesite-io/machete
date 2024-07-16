#!/bin/sh
echo "Upgrade machete ..."
echo ""

download_link_filesite="https://git.filesite.io/filesite/machete/archive/master.tar.gz"
download_link_gitcode="https://gitcode.net/filesite/machete/-/archive/master/machete-master.tar.gz"


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

if [ -f master.tar.gz ]; then
    rm -f master.tar.gz
fi

if [ -d machete/ ]; then
    rm -rf machete/
fi

# 升级machete
detect_domain="git.filesite.io"
echo "检测${detect_domain}是否可连接..."
detectDomainCanConnect "${detect_domain}"
connect_res=$?
if [ $connect_res -eq 0 ]; then
    echo "⚠️⚠️"
    echo "当前网络无法连接[${detect_domain}]，即将从备用网址下载machete"
    curl --connect-timeout 15 "${download_link_gitcode}" -o "master.tar.gz"
else
    curl --connect-timeout 15 "${download_link_filesite}" -o "master.tar.gz"
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

fi

echo "Machete front-end upgraded."
echo ""


# upgrade admin
cd /var/www/downloads/
curl --connect-timeout 15 "https://git.filesite.io/wen/jialuomaadmin/archive/master.tar.gz" -o "master.tar.gz"

if [ -f "master.tar.gz" ]; then

    tar -zxvf master.tar.gz
    rm -f master.tar.gz
    rm -rf /var/www/machete/www/admin/
    mv jialuomaadmin/dist/ /var/www/machete/www/admin

fi
echo "Admin system upgraded."
echo ""


# upgrade nginx config
cp /var/www/machete/conf/nginx_machete.conf /etc/nginx/http.d/machete.conf
/usr/sbin/nginx -s reload
echo "Nginx config upgraded and reloaded."
echo ""

echo ""
echo "==Machete upgrade done.=="
echo ""
