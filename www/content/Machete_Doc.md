# Machete - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统
![Filesite.io - Machete](./machete_icon.png)


Markdown及其编辑器的普及，以及类似Jekyll以文件为基础的博客系统的出现，为博主们提供了一种更为专注的写作方式，
在自己的电脑本地且不必联网的情况下就能创作，不再依赖于像WordPress这类系统后台。

对于喜欢写作的创作者们，显然已经从Markdown的使用中由原来的在线编辑转为本地编辑获得了更高的自由度和效率，
除了博客，放眼到其它行业，基于文件、目录就能管理内容的典型还有代码托管（如Github）、网盘和云存储（如iCloud），
那我们何不把这个成功的经验推广到更多的内容创作领域，如：小说、摄影、播客，帮助创作者简化将内容制作成网站的工作，
于是FileSite.io诞生了。


## 愿景

**进一步降低**内容分享者、创作者把内容变成网站的**门槛**，
以常见的本地磁盘目录+文件的形式作为网站数据来源，
修改磁盘文件内容上传到服务器就可以生成网页，
让大家更易于同时维护本地和网站的内容。

FileSite.io希望帮助到他们：
* 经常用Markdown写文档/文章的朋友
* 网址导航站经营者、收藏夹/网址分享者
* 小说网站经营者、小说创作者、博客主、作家、自媒体人
* 图片网站经营者、摄影师、摄影爱好者、驴友
* 视频网站经营者、播客、摄影师、航拍爱好者、运动爱好者、潜水爱好者


## 视频教程

在Docker中如何用filesite/machete源码快速搭建支持文件共享方式管理内容的图片网站、视频网站、导航网站和文档站的视频教程。

主要步骤：
1. docker pull filesite/machete
2. docker run ...
3. 本地测试网站和后台
4. 在docker容器中升级最新版
5. 如何在macos中挂载远程磁盘来管理图片等内容

<div style="max-width:1024px">
  <video width="100%" preload="metadata" playsinline controls poster="https://static.jialuoma.cn/img/video_docker_pull_run_machete_1210_snap.png">
    <source src="https://static.jialuoma.cn/mp4/video_docker_pull_run_machete_1210.mp4" type="video/mp4">
  </video>
</div>


## 使用流程

filesite使用非常简单，一旦部署好之后，以后只需将本地最新内容上传覆盖即可。

1. 下载filesite源码，并参考[Nginx配置示例](./Nginx.conf.md)部署到你的服务器上；
  
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

3. 将本地内容目录及文件上传到网站目录：``www/content/``

  不同皮肤请上传到对应的目录，详情参考Machete源码Readme。

4. 打开网址浏览最新内容；

有了filesite，你可以保留现有的本地内容创作习惯，并非常容易地把它们制作成一个网站分享给他人。


## 在线体验

1. 文档站

  | 名称 | 网址 |
| ---- | ---- |
| FileSite | <a href="https://filesite.io" target="_blank">Filesite.io</a> |

2. 导航站

  | 名称 | 网址 |
| ---- | ---- |
| 站长手册 | <a href="https://webdirectory.filesite.io" target="_blank">WebDirectory.FileSite.io</a> |
| Web3速查手册 | <a href="https://web3.filesite.io" target="_blank">Web3.FileSite.io</a> |

3. 小说站

  -

4. 图片站

  | 名称 | 网址 |
| ---- | ---- |
| 看美女 | <a href="https://googleimage.filesite.io" target="_blank">GoogleImage.Filesite.io</a> |
| 在线演示 | <a href="https://demo.jialuoma.cn" target="_blank">带后台版Machete在线演示</a> |

5. 视频站

  | 名称 | 网址 |
| ---- | ---- |
| 在线学 | <a href="https://duan.filesite.io" target="_blank">Duan.Filesite.io</a> |


## 源码下载

Machete是砍刀，它能砍、能削、能切、能剁，最适合披荆斩棘；

砍刀不像大刀、长剑、长矛为战场而生，但在日常生活中使用也是得心应手。

Filesite.io也一样，它短小精悍，使用它把常见的本地文件制作成网站，就像拿起砍刀一样简单， 所以我们将FileSite.io的源码命名为**machete**。

点击下面网址查看源码或者下载源码：

* 查看<a href="https://git.filesite.io/filesite/machete" target="_blank">FileSite.io源码Machete</a>。

* 用git下载：
```
git clone https://git.filesite.io/filesite/machete.git
```

* 下载zip压缩包（[点我直接下载](https://git.filesite.io/filesite/machete/archive/master.zip)）：
```
https://git.filesite.io/filesite/machete/archive/master.zip
```

* 从Docker Hub下载镜像：
```
docker pull filesite/machete
```

  支持samba文件共享管理内容的版本：
```
docker pull filesite/machete:samba
```

  启动容器：
```
docker run --name machete -p 1080:80 -itd filesite/machete
```

  samba文件共享版本容器启动：
```
docker run --name machete_samba -p 1081:80 -p 445:445 -itd filesite/machete:samba
```

  文件共享挂载远程目录默认账号密码：
> 账号：filesite
> 密码：88888888

  在浏览器打开本地网址预览：
```
http://127.0.0.1:1080
```

  samba文件共享版本本地网址访问：
```
http://127.0.0.1:1081
```

* 基于Vue的文件目录管理后台源码

  <a href="https://github.com/filesite-io/admin-vue" target="_blank">去Github下载</a> (海外推荐使用)
  
  <a href="https://gitcode.net/filesite/admin-vue" target="_blank">去Gitcode下载</a> (国内推荐使用)


更多使用说明见Machete源码README.md。



## 皮肤下载

FileSite.io官方提供的皮肤都包含在最新版的源码目录```themes/```中，详情如下：

| 皮肤名称 | 皮肤目录 | 适用网站类型 |
| ---- | ---- | ---- |
| WebDirectory | webdirectory | 导航站 |
| Manual | manual | 文档站 |
| GoogleImage | googleimage | 图片站 |
| Beauty | beauty | 图片站 |
| VideoBlog | videoblog | 视频站 |

官方皮肤介绍如下：
* [导航站皮肤WebDirectory](./Theme_WebDirectory.md)
* [文档站皮肤Manual](./Theme_Manual.md)
* [图片站皮肤GoogleImage](./Theme_GoogleImage.md)
* [图片站皮肤Beauty](./Theme_Beauty.md)
* [视频站皮肤VideoBlog](./Theme_VideoBlog.md)


第三方提供的皮肤下载：

| 皮肤名称 | 目录名 | 适用网站类型 | 下载地址 |
| ---- | ---- | ---- | ---- |
| - | - | - | - |

如果你基于Machete开发了皮肤想要共享给大家使用，请在联系我们提交下载地址。


## 使用手册

如果要基于Machete做二次开发，请先仔细阅读下面手册。

FileSite.io核心代码参考：
* [Filesite.io core lib](./FSC.md)
* [Nginx配置示例](./Nginx.conf.md)

FileSite.io二次开发参考：
* [FileSite.io使用手册](./Manual.md)
* [类DirScanner定义](./Class_DirScanner.md)


如果上面的手册还不能帮助解决你的问题，
请按下面方式提交反馈与建议。


## 反馈与建议

如果使用中遇到任何问题，
请在下面仓库提出：

* <a href="https://github.com/filesite-io/machete/issues" target="_blank">去Github提交问题/反馈</a> (海外推荐使用)
* <a href="https://gitcode.net/filesite/machete/-/issues" target="_blank">去Gitcode提交问题/反馈</a> (国内推荐使用)

如果觉得好用，别忘了点仓库右上角**⭐星号**加收藏以示支持。


## 联系我们

加入QQ群：
<a href="https://jq.qq.com/?_wv=1027&k=WoH3Pv7d" target="_blank">网址导航、图片、视频网站交流群</a>

扫码加微信好友：

<img src="./wx_jialuoma.jpeg" alt="扫描微信二维码加好友" width="240" />
