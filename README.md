# Machete

一个实现了FileSite.io “基于文件和目录管理网址、文章、图片、视频标准”的PHP源码。

可以用它快速搭建：

* 文档/文章/博客网站
* 导航网站，视频收藏/分享网站
* 图片网站
* 视频网站


## 在线体验

Machete是单入口模式PHP源码，**不支持子目录方式访问**，以下示例都是以子域名或根域名配置nginx根目录指向代码目录下的www/index.php。

1. 文档站

  | 名称 | 网址 |
| ---- | ---- |
| FileSite | <a href="https://filesite.io" target="_blank">Filesite.io</a> |

2. 导航站

  | 名称 | 网址 |
| ---- | ---- |
| 站长手册 | <a href="https://webdirectory.filesite.io" target="_blank">WebDirectory.FileSite.io</a> |
| Web3速查手册 | <a href="https://web3.filesite.io" target="_blank">Web3.FileSite.io</a> |


3. 图片站

  | 名称 | 网址 |
| ---- | ---- |
| 看美女 | <a href="https://googleimage.filesite.io" target="_blank">GoogleImage.Filesite.io</a> |
| 在线演示 | <a href="https://demo.jialuoma.cn" target="_blank">带后台版Machete在线演示</a> |

4. 视频站

  | 名称 | 网址 |
| ---- | ---- |
| 在线学 | <a href="https://duan.filesite.io" target="_blank">Duan.Filesite.io</a> |

5. 视频收藏/分享站

  | 名称 | 网址 |
| ---- | ---- |
| Ta荐 | <a href="https://tajian.tv" target="_blank">TaJian.tv</a> |


## 手动部署

Machete使用非常简单，一旦部署好之后，以后只需将本地最新内容上传覆盖即可。

1. 下载Machete源码，并参考[Nginx配置示例](./Nginx.conf.md)部署到你的服务器上；

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


## 用Docker部署

在Docker中如何用filesite/machete源码快速搭建支持文件共享方式管理内容的图片网站、视频网站、导航网站和文档站的视频教程。

主要步骤：
1. docker pull filesite/machete
2. docker run ...
3. 本地测试网站和后台
4. 在docker容器中升级最新版
5. 如何在macos中挂载远程磁盘来管理图片等内容

视频教程：

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
