# Machete

Source code of filesite.io.


## 简介

**Machete**是**砍刀**，它能砍、能削、能切、能剁，最适合披荆斩棘；

砍刀不像大刀、长剑、长矛为战场而生，但在日常生活中使用也是得心应手。

Filesite.io也一样，它短小精悍，使用它把常见的本地文件制作成网站，就像拿起砍刀一样简单，
所以我们将filesite.io的**源码**命名为```machete```。


## 视频教程

在Docker中如何用filesite/machete源码快速搭建支持文件共享方式管理内容的图片网站、视频网站、导航网站和文档站的视频教程。

主要步骤：
1. docker pull filesite/machete
2. docker run ...
3. 本地测试网站和后台
4. 在docker容器中升级最新版
5. 如何在macos中挂载远程磁盘来管理图片等内容

[![IMAGE ALT TEXT HERE](https://static.jialuoma.cn/img/video_docker_pull_run_machete_1210_snap.png)](https://static.jialuoma.cn/mp4/video_docker_pull_run_machete_1210.mp4)


## Docker使用

从dockerhub下载镜像：

```
docker pull filesite/machete
```

支持samba文件共享管理内容的版本：
```
docker pull filesite/machete:samba
```


启动machete容器：

```
docker run --name machete -p 1080:80 -itd filesite/machete [皮肤名]
```

samba文件共享版本容器启动：
```
docker run --name machete_samba -p 1081:80 -p 445:445 -itd filesite/machete:samba [皮肤名]
```


其中皮肤名称可选值：

```
[
    'manual',           //文档网站
    'webdirectory',     //导航网站
    'googleimage',      //图片网站
    'beauty',           //图片网站，设计精美
    'videoblog'         //视频网站
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
    -v /mine/content/:/var/www/machete/www/content/ \
    -itd filesite/machete \
    manual
```

这样可以通过更新本地内容目录```/mine/content/```的文件来实时更新网站内容。

不同皮肤对应的容器目录如下：

| 皮肤名 | 容器目录 | 共享目录 |
| ---- | ---- | ---- |
| manual | /var/www/machete/www/content/ | content |
| webdirectory | /var/www/machete/www/navs/ | navs |
| googleimage | /var/www/machete/www/girls/ | girls |
| beauty | /var/www/machete/www/girls/ | girls |
| videoblog | /var/www/machete/www/videos/ | videos |


查看容器：

```
docker ps
```

如果看到名字为``machete``的容器正在运行，说明容器启动完成，访问本地网址测试：

```
http://127.0.0.1:1080
```

samba文件共享版本本地网址访问：
```
http://127.0.0.1:1081
```


## 后台管理内容

最新版本已经支持网页版后台和samba文件共享方式管理内容。

### 网页版后台

网址为域名后面加/admin/来访问，
网址格式为：
```
http://服务器ip或域名/admin/
```

默认账号密码：
> 账号：filesite
> 密码：88888888

账号密码可在```conf/app.php```里修改。


### samba文件共享

同时支持windows、macos和linux，
文件共享网址格式为：
```
//filesite:88888888@服务器ip或域名/machete
```

默认账号密码：
> 账号：filesite
> 密码：88888888

账号密码可在容器中执行命令修改：
```
smbpwd 新密码
```


### 文件共享使用方法


windows下在**运行**里输入：
```
\\服务器ip或域名\machete
```

然后在弹出的登陆框里输入账号密码就可以完成远程磁盘挂载。


macos下挂载共享目录的命令：
```
mount_smbfs //filesite:88888888@服务器ip或域名/machete 本地目录
```

挂载好之后就可以打开Finder看到共享目录了，
点击进去就可以跟管理本地文件和目录一样操作了。
