# 家庭相册

Machete是一款简单易用的家庭相册系统，可方便更新升级、支持Docker安装。


## 使用场景

* 家庭相册，局域网搭建图片、视频网站，在家庭成员之间、不同设备里共享照片、视频
* 摄影工作室，搭建照片集方便客户在手机里挑选
* 摄影发烧友，收藏、展示自己的照片、视频

## 使用方法

1. 通过iPhone、iPad的“隔空传送”发送照片、视频到安装了“家庭相册”的Mac mini电脑；
2. 在Mac mini从“下载”中把收到照片、视频拖动到**桌面**的目录“**machete家庭相册**”；
3. 双击桌面的快捷方式“相册.url”打开网页浏览照片、视频；
4. 用iPhone扫码网页底部的二维码，打开后分享给家庭成员的微信、QQ等，他们就可以在自己的手机里浏览你们共同的照片、视频啦；

注意：
* 网页看到的照片、视频有 **10分钟** 的缓存，如果你打开网页后十分钟内有增加新照片、视频，
请点击网页右上角的“**刷新**”图标清空网页缓存
* 如果你的相册安装目录不是默认的“**machete家庭相册**”，请拖动照片、视频到你的相册目录


如有其它相关疑问，欢迎加QQ群交流：

* <a href="https://jq.qq.com/?_wv=1027&k=WoH3Pv7d" target="_blank">Machete交流群</a>


## 网站配置

网站名称和底部的版权申明支持文本文件配置，在girls/目录（或你本地映射到girls/的目录）下增加以下文件：
* README_title.txt - 网站名称配置文件
* README_copyright.txt - 版权申明配置文件

注意：
* 配置文件名大小写必须一致！
* 如果映射的目录里没有README.md文件，请先从源码www/girls/目录复制README.md


配置文件内容填写纯文本的中英文信息（支持html代码），示例：

* README_title.txt
```
家庭相册演示
```

* README_copyright.txt
```
<span>&copy;2022 - 2024</span>
by <a href="https://filesite.io/" target="_blank">FileSite.io</a>
```


## 友情链接

* <a href="https://tajian.tv" target="_blank">Ta荐 - 你的聚宝盆</a>