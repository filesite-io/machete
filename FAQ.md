# Machete家庭相册系统使用FAQ常见问题

项目完整名称为：Filesite/Machete，为求简单下文都以machete代替。

本文目录如下：

* [Filesite/Machete家庭相册系统有哪些特点？](#filesitemachete家庭相册系统有哪些特点)
* [用machete家庭相册系统管理我的照片安全吗？](#用machete家庭相册系统管理我的照片安全吗)
* [怎么升级machete家庭相册代码？](#怎么升级machete家庭相册代码)
* [系统配置文件在哪里？](#系统配置文件在哪里)
* [怎么开启密码授权访问？](#怎么开启密码授权访问)
* [怎么关闭网页底部的二维码？](#怎么关闭网页底部的二维码)
* [怎么修改网站简介信息？](#怎么修改网站简介信息)
* [怎么修改网站标题？](#怎么修改网站标题)
* [怎么修改底部的版权信息？](#怎么修改底部的版权信息)
* [怎么修改浏览器地址栏左侧的小图标？](#怎么修改浏览器地址栏左侧的小图标)
* [相册部署到外网了，点击图片打开有点慢怎么办？](#相册部署到外网了点击图片打开有点慢怎么办)
* [在局域网内使用，能否所有图片使用原图而不是缩略图？](#在局域网内使用能否所有图片使用原图而不是缩略图)
* [怎么对照片、视频、音乐文件进行排序？](#怎么对照片、视频、音乐文件进行排序)
* [怎么设置每页照片、视频、音乐文件数量？](#怎么设置每页照片、视频、音乐文件数量)
* [怎么设置自动播放时每张照片停留时间？](#怎么设置自动播放时每张照片停留时间)
* [怎么设置局域网内访问拥有刷新、设置封面等管理权限？](#怎么设置局域网内访问拥有刷新、设置封面等管理权限)
* [外网访问怎么设置允许当前IP访问拥有刷新、设置封面等管理权限？](#外网访问怎么设置允许当前IP访问拥有刷新、设置封面等管理权限)
* [我的设备cpu性能较差，缩略图显示有点慢且cpu占用较高怎么解决？](#我的设备cpu性能较差缩略图显示有点慢且cpu占用较高怎么解决)
* [在启用Imagick扩展后，CPU占用过高或者大图片缩略图无法生成怎么解决？](#在启用Imagick扩展后，CPU占用过高或者大图片缩略图无法生成怎么解决)
* [更多问题如何联系？](#更多问题如何联系)


## Filesite/Machete家庭相册系统有哪些特点？

以下为machete家庭相册的几个与众不同的地方：

* 以**你的照片目录**为数据源、**所见即所得**，保留你的照片管理习惯
* 开放源代码，源码公开且免费使用
* **无数据库**非常轻量、毫秒级响应
* 点击图片放大浏览时**默认使用原图**，适合大屏幕欣赏高清图片
* 支持背景音乐播放
* 支持单个目录下所有图片自动循环播放
* 支持单个目录下所有视频**自动循环播放**
* 支持白天/夜晚两种浏览模式


## 用machete家庭相册系统管理我的照片安全吗？

请放心使用machete家庭相册系统来把本地图片目录转化为一个网站，只要你遵循我们推荐的方式安装部署，它是绝对安全的！

理由如下：
1. machete是开源项目，代码公开，接受任何人/机构的安全检测；
2. 推荐使用docker安装部署machete，docker的安全机制很完善；
3. 只给runtime/目录写入权限，其它文件和目录只读；


Filesite/machete是**开放源代码**的，它托管在以下几个git网站：
* [GitHub](https://github.com/filesite-io)
* [GitCode](https://gitcode.net/filesite/machete)
* [Gitee](https://gitee.com/filesite/machete)

并遵循**MIT License**，任何个人或公司，只要在保留来源申明的情况下，都可以基于它根据需要做修改后免费使用。


另外，推荐使用docker来安装部署machete家庭相册，
这样基于docker提供的安全沙箱环境，最大程度地保护你的电脑、服务器不受machete程序影响。

最后，machete程序只有临时文件存放目录runtime/需要开放**写入权限**，其它文件和目录都只需开放“只读”权限即可，
所以你在使用docker为machete配置volume或者本地映射目录时，相册目录只开放只读权限给docker容器，
由此可以进一步确保你的照片目录不会被machete程序修改/删除。


## 怎么升级machete家庭相册代码？

执行容器命令即可：

```
docker exec -it machete_album /var/www/machete/bin/upgrade.sh
```

说明：
其中容器名称**machete_album**请更换为你所启动的machete容器名字。


## 系统配置文件在哪里？

* 全局配置文件：conf/app.php
* 自定义配置文件：runtime/custom_config.json

系统默认从全局配置文件里读取数据，如果有自定义配置，则优先使用自定义配置文件中的数据。

推荐使用自定义配置文件，以免系统升级后配置被覆盖。

如果是使用docker安装的machete，请自行了解如何修改docker容器里的文件，或者如何把本地文件复制到容器替换。


## 怎么开启密码授权访问？

在目录runtime/下创建配置文件：custom_password.json，
可复制conf/custom_password.json到runtime/目录下进行修改。

也可按下面示例创建：
```
{
    "password_auth": {
        "enable": true,
        "default": "",
        "allow": {
            "目录1": "hello",
            "目录2": "world"
        }
    }
}
```

**配置说明：**

* enable开关设置true则启用，默认为false关闭状态
* default选项配置全局默认密码，针对所有目录生效
* allow里配置单个目录的授权密码，如果default配置为空，则只有allow里所设置的目录需要密码授权访问


## 怎么关闭网页底部的二维码？

在runtime/custom_config.json中增加以下配置：
```
"showQRImageInFooter": false
```


## 怎么修改网站简介信息？

请修改图片根目录下的**README.md**文件内容（如果你安装的是老版本，看不到这个文件，请手动创建），它是markdown格式的内容。

保存后，点网页右上角“刷新”图标，清空缓存查看效果。


## 怎么修改网站标题？

网站标题显示在网页左上角，以及浏览器tab栏上，系统默认的标题是“家庭相册”或者“Filesite/Machete”。

请在图片根目录下，创建文件**README_title.txt**，用一行文字来设置，示例如下：

```
我的相册
```


## 怎么修改底部的版权信息？

请在图片根目录下，创建文件**README_copyright.txt**，内容参考下面示例代码：

```
<span>&copy;2022 - 2024</span>
by <a href="https://filesite.io/" target="_blank">FileSite.io</a>
<br>
执行耗时: {page_time_cost} ms
```


## 怎么修改浏览器地址栏左侧的小图标？

请创建自己的icon文件，命名为favicon.ico，把它复制到你的图片根目录；

**以容器目录为例：**
```
/var/www/machete/www/girls/favico.ico
```


## 相册部署到外网了，点击图片打开有点慢怎么办？

machete家庭相册默认为局域网使用，配置**enableSmallImageForWan**开启查看大图显示缩略图是关闭的，
如果你的照片文件大小很大，那么在外网打开可能会很慢。

解决办法，为外网点击图片浏览大图开启缩略图功能，
在自定义配置：runtime/custom_config.json里增加以下配置：
```
"enableSmallImageForWan": true
```


## 在局域网内使用，能否所有图片使用原图而不是缩略图？

系统配置**enableSmallImage**默认为true打开的，在自定义配置文件中，加入以下配置保存即可关闭所有缩略图功能：
```
"enableSmallImage": false
```


## 怎么对照片、视频、音乐文件进行排序？

在**runtime/custom_config.json**中增加以下配置：
```
"sortFilesByName": true,
"sortOrderOfFiles": "asc"
```

其中sortFilesByName设置为true，打开文件按名称排序，
sortOrderOfFiles则设定排序方式，asc顺序，desc倒序。

除此之外，还可以在照片目录下增加排序文件：sort.txt，
内容为当前目录下的文件名，格式一行一个，示例：
```
2_20240406223804.jpg
1_20240406223752.jpg
3_20240406223809.jpg
```

如果配置了此排序文件，且sortFilesByName为false关闭状态，则系统会按照排序文件中的文件名从上到下排序。


## 怎么设置每页照片、视频、音乐文件数量？

在自定义配置：runtime/custom_config.json里增加以下配置：
```
"default_page_size": 50
```


## 怎么设置自动播放时每张照片停留时间？

在自定义配置：runtime/custom_config.json里增加以下配置：
```
"slide_show_timeout": 10
```

单位：秒。


## 怎么设置局域网内访问拥有刷新、设置封面等管理权限？

在自定义配置：runtime/custom_config.json里增加以下配置：
```
"adminForLanIps": true
```

系统支持这三类IP：
```
127.0.0.1
172.17.0.1,
192.168.xxx.xxx
```


## 外网访问怎么设置允许当前IP访问拥有刷新、设置封面等管理权限？

在自定义配置：runtime/custom_config.json里增加以下配置：
```
"adminWhiteIps": [
    "你的外网ip地址"
]
```

如果部署的时候相册是经过nginx反向代理转发的，请在nginx的配置中设置转发用户ip地址：
```
proxy_set_header         Host                $host;
proxy_set_header         X-Real-IP           $remote_addr;
proxy_set_header         X-Forwarded-For     $proxy_add_x_forwarded_for;
```


## 我的设备cpu性能较差，缩略图显示有点慢且cpu占用较高怎么解决？

machete家庭相册在设计的时候考虑到在嵌入式设备中运行，缩略图功能可由用户的浏览器完成。

在自定义配置中把**disableGenerateSmallImageInServer**开关打开即可关闭服务器端生成缩略图，从而节省cpu消耗：
```
"disableGenerateSmallImageInServer": true
```


## 在启用Imagick扩展后，CPU占用过高或者大图片缩略图无法生成怎么解决？

Imagick库的特性是生成的缩略图画质更高，但缺点是CPU占用比较高。

使用最新版filesite/machete的docker镜像，在自定义配置中加上开启配置项就能使用Imagick库来生成缩略图：
```
"enable_lib_imagick": true
```

请在启动容器的时候根据你的服务器配置，指定容器最大能使用的内存和cpu数量，
这将改善缩略图生成，避免因为过高的CPU占用导致php进程被docker杀死从而无法生成大图片的缩略图。

例如***1G内存双核cpu服务器***，允许最大使用512M内存，1.5个cpu核心：
```
docker run --name machete -p 1080:80 \
    -m 512m \
    --cpus="1.5" \
    -v /d/图片目录/:/var/www/machete/www/girls/ \
    -itd filesite/machete \
    beauty
```


## 更多问题如何联系？

请打开官方网站查看底部QQ群和微信：
<a href="https://filesite.io" target="_blank">FileSite.io</a>
