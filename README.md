# Filesite/Machete

一个实现了FileSite.io “基于文件和目录管理网址、文章、图片、视频标准”的开源PHP源码。

可以用它快速搭建：

* 图片网站
* 视频网站
* 文档/文章/博客网站
* 导航网站，视频收藏/分享网站


## 目录

* [在线体验](#在线体验)
* [基于Docker部署](#基于Docker部署)
    * [视频教程](#视频教程)
    * [下载镜像](#下载镜像)
    * [启动machete容器](#启动machete容器)
    * [查看容器](#查看容器)
    * [升级容器代码](#升级容器代码)
    * [配置修改](#配置修改)
* [手动部署](#手动部署)
* [常见问题与解答](#常见问题与解答)
* [联系方式](#联系方式)


## 在线体验

Machete是单入口模式PHP源码，**不支持子目录方式访问**，以下示例都是以子域名或根域名配置nginx根目录指向代码目录下的www/index.php。


  | 类型 | 名称 | 网址 |
| ---- | ---- | ---- |
| 图片站 | 家庭相册演示 | <a href="https://demo.jialuoma.cn" target="_blank">Demo</a> |
| 视频分享站 | Ta荐 | <a href="https://tajian.tv" target="_blank">TaJian.tv</a> |
| 文档站 | FileSite | <a href="https://filesite.io" target="_blank">Filesite.io</a> |
| 导航站 | 站长手册 | <a href="https://webdirectory.filesite.io" target="_blank">WebDir</a> |


## 基于Docker部署

在Docker中如何用filesite/machete源码快速搭建支持文件/目录管理内容的图片网站、视频网站、导航网站和文档站的视频教程。

主要步骤：
1. docker pull filesite/machete
2. docker run ...
3. 本地测试网站和后台
4. 在docker容器中升级最新版


### 视频教程

点击下面图片观看如何使用Docker部署安装machete的视频教程。

[![用Docker安装machete](https://static.jialuoma.cn/img/video_docker_pull_run_machete_1210_snap.png)](https://static.jialuoma.cn/mp4/video_docker_pull_run_machete_1210.mp4)


### 下载镜像

从dockerhub下载：
```
docker pull filesite/machete
```

如果不能直接访问dockerhub，可从备用网址下载镜像后导入：
```
wget https://static.jialuoma.cn/docker_images/machete.tar
docker image load -i machete.tar
```


### 启动machete容器

```
docker run --name machete -p 1080:80 -itd filesite/machete [皮肤名]
```


其中皮肤名称可选值：

```
[
    'beauty',           //图片网站，设计精美
    'tajian',           //视频分享网站
    'manual',           //文档网站
    'webdirectory',     //导航网站
]
```

本地监听端口``1080``请根据自己需要修改。


machete在容器中的目录：

```
/var/www/machete/
```

可根据自己的需要，通过``-v``参数映射本地内容目录到容器目录，
示例如下：
```
docker run --name machete -p 1080:80 \
    -v /d/图片目录/:/var/www/machete/www/girls/ \
    -itd filesite/machete \
    beauty
```

这样可以通过更新本地内容目录```/d/图片目录/```的文件来实时更新网站内容。

不同皮肤对应的容器目录如下：

| 皮肤名 | 容器目录 |
| ---- | ---- | ---- |
| beauty | /var/www/machete/www/girls/ |
| tajian | /var/www/machete/www/tajian/ |
| manual | /var/www/machete/www/content/ |
| webdirectory | /var/www/machete/www/navs/ |


如果开启Imagick库支持，请在启动容器的时候根据你的服务器配置，指定容器最大能使用的内存和cpu数量。

例如***1G内存双核cpu服务器***，允许最大使用512M内存，1.5个cpu核心：
```
docker run --name machete -p 1080:80 \
    -m 512m \
    --cpus="1.5" \
    -v /d/图片目录/:/var/www/machete/www/girls/ \
    -itd filesite/machete \
    beauty
```


### 查看容器

```
docker ps
```

如果看到名字为``machete``的容器正在运行，说明容器启动完成，访问本地网址测试：

```
http://127.0.0.1:1080
```


### 升级容器代码

```
docker exec -it machete /var/www/machete/bin/upgrade.sh
```


### 配置修改

修改配置有两种方式：
1. 进入容器目录：/var/www/machete/runtime/，修改**custom_config.json**后保存即可；
2. 命令行修改
```
docker exec -it machete php /var/www/machete/bin/command.php config "do=set&key=screenshot_start&val=1000"
```

上述命令为修改配置项**screenshot_start**的示例。

配置读取、修改、删除命令：
```
php /var/www/machete/bin/command.php config "参数"
```

参数说明：
* do  - 操作，可选值：get, all, set, del（分别对应获取单个配置项、所有配置项、设置单个配置项、删除单个配置项）
* key - 配置项名称
* val - 配置项值


说明：
不支持数组类型的配置修改，可用配置项明细参考：**conf/app.php**。


## 手动部署

视频教程：

[![手动配置Nginx部署machete](https://static.jialuoma.cn/img/machete/setup_nginx_conf.jpg)](https://static.jialuoma.cn/mp4/machete/setup_nginx_conf.mp4)


Machete使用非常简单，一旦部署好之后，以后只需将本地最新内容上传覆盖即可。

1. 下载Machete源码，并参考[Nginx配置示例](./conf/nginx_machete.conf)部署到你的服务器上；

  说明：

  Machete**不支持子目录方式访问**，需要为nginx配置根目录指向代码目录下的www/。
  
  用git下载：
```
git clone https://git.filesite.io/filesite/machete.git
```
  或下载zip压缩包（[点我直接下载](https://git.filesite.io/filesite/machete/archive/master.zip)）：
```
https://git.filesite.io/filesite/machete/archive/master.zip
```

2. 修改配置文件``config/app.php``，简单设置后上传到服务器完成部署；

  指定内容目录和使用的皮肤：
```
'content_directory' => 'content/',      //内容存放目录
'theme' => 'manual',                    //皮肤名称，如：文档站选manual，图片站选googleimage
```

还可以复制conf/目录下的custom_config_皮肤名.json到runtime/目录，
通过修改runtime/custom_config.json或者runtime/custom_config_皮肤名.json来设置当前网站使用的皮肤名和内容目录。


3. 将本地内容目录及文件上传到网站目录：``www/content/``

  不同皮肤请上传到对应的目录，不同类型的网站数据格式参考：[Machete使用手册](./www/content/Machete_Doc.md)。

4. 打开网址浏览最新内容；

有了Machete，你可以保留现有的本地内容创作习惯，并非常容易地把它们制作成一个网站分享给他人。


## 常见问题与解答

请参考文档：
* [Machete家庭相册系统使用FAQ常见问题](./FAQ.md)


## 联系方式

请打开官方网站查看底部QQ群和微信：
<a href="https://filesite.io" target="_blank">FileSite.io</a>
