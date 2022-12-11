#!/bin/sh

# 查看正在使用的循环分区
losetup -a

# 修改系统配置，增加循环分区数量
/etc/modprobe.conf
# 添加：options loop max_loop=20
# 让它生效
modprobe -v loop

# 查看所有循环分区
ls -l /dev | grep loop

# 创建新的循环分区
mknod -m 0660 /dev/loopX b 7 X

# 修改所有权
chown root:disk /dev/loopX

# 创建指定大小的镜像文件
dd if=/dev/zero of=/user_disks/username.img bs=1MB count=1024

# 格式化镜像文件
mkfs.ext4 /user_disks/username.img

# 初始化镜像
losetup /dev/loopX /user_disks/username.img

# 创建挂载目录
mkdir /mnt/disk_username

# 挂载用户镜像
mount /dev/loopX /mnt/disk_username

# 卸载循环分区
#umount /dev/loopX

# 删除循环分区
#losetup -d /dev/loopX


# 为用户启动docker容器
#```
#docker run --name machete_username -p 1083:80 -p 8445:445 \
#    -v /mnt/disk_username/content/:/var/www/sambashare/filesite/content/ \
#    -v /mnt/disk_username/navs/:/var/www/sambashare/filesite/navs/ \
#    -v /mnt/disk_username/girls/:/var/www/sambashare/filesite/girls/ \
#    -v /mnt/disk_username/videos/:/var/www/sambashare/filesite/videos/ \
#    -itd filesite/machete:samba \
#    webdirectory
#```