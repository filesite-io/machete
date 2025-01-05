
# FileSite.io，一个基于文件和目录管理网址、文章、图片、视频的标准

**摘要：**

我们基于已有的文件格式定义了一个管理几种用户常用数据类型的**标准**，旨在帮助用户在保留现有内容管理习惯的前提下，更容易地把自己的创作建设成网站、App，同时还能更简单地把创作发布到各种平台。

此标准目前支持网址、文章、图片和视频四种类型的数据，如有需要还将扩展其它的数据类型。


## 简介

我们相信每个人都是一名创作者，他/她可以是作家，可以是摄影师，还可以是摄像师，也许他/她还会把自己喜欢的网站收藏并加以分类。

因为现有的平台和环境，多数人的创作只能静静地躺在电脑的硬盘里，但终将有一天，在许许多多的从业者们的推动下，把个人的创作内容建设成网站、App的门槛会越来越低，那时人人都可以轻松、快捷地创建自己的网站和App，能用一键上传的功能就能把作品发布到各大平台。

我们还坚信，**每个人的作品所有权始终都归作者所有**，无论他/她已经把作品发布到哪个平台，只要他/她愿意，随时都能删除某个平台上他/她所有的数据，从电脑把作品快速上传到另一个平台是如此简单，这将改变平台和创作者之间的关系，不再是创作者依赖平台，而是**平台依赖创作者**。

正因为如此，我们提出了一个新标准，它没有采用任何新技术，完全基于已有的操作系统、文件系统和文件类型，以及大部分人现有的使用习惯，它也是一次抛砖引玉，让这一天早点到来吧！


## 版本

名称：filesite_2023

版本号：20230130

修改时间：2023-01-30


## 目录和文件

本标准所说的目录和文件，是指Windows、Linux、MacOS等常用操作系统中的文件和目录。

如果市面上不同操作系统中对文件和目录的命名规范存在差异，本标准则采用他们都支持的部分。


## 数据类型

当前版本支持以下几种类型的数据：

| 类型 | 格式 |
| ---- | ---- |
| 网址 | .url 快捷方式 |
| 文章 | .md markdown文件 |
| 图片 | .jpg, .png, .gif, .ico |
| 视频 | .mp4, .m3u8, .ts |


## 数据说明

如果需要对上述类型的数据进行扩展说明，请使用.txt格式的纯文本文件保存，我们把这类.txt文件称为“**描述文件**”。

描述文件命名规则如下：
```
目录的描述文件：{英文小写属性名}.txt
文件的描述文件：{被描述文件名_}{英文小写属性名}.txt
```

几个常用的属性描述文件如下：

| 文件名 | 说明 | 属性名 |
| ---- | ---- | ---- |
| title.txt | 标题 | title |
| description.txt | 描述信息 | description |
| keywords.txt | 关键词信息 | keywords |
| snapshot.txt | 快照图片 | snapshot |


## 目录和文件结构

目录里可包含子目录和文件，目录层级**最多支持 5 级**。

用目录对数据进行**分组**，同一分组的文件放在同一个目录里。

示例（字母**d**代表目录，字母**f**代表文件）：
```
-d- 小说
  |_d_ 金庸小说
       |_f_ 最爱金庸网站图标.ico
       |_f_ 最爱金庸.url
  |_d_ 古龙小说
       |_f_ 最爱古龙网站图标.ico
       |_f_ 最爱古龙.url
-d- 图片
  |_d_ 图片搜索
       |_f_ 谷歌图片搜索图标.ico
       |_f_ 谷歌图片搜索.url
       |_f_ description.txt
  |_d_ 必应图片搜索
       |_f_ bing图标.ico
       |_f_ bing.url
       |_f_ title.txt
```



## API数据结构

### 目录-Directory

```
[
    'id' => '根据完整路径生成的唯一编号',
    'pid' => '父目录id',              //如果有父目录的话
    'directory' => '倚天屠龙记',
    'realpath' => '/www/webroot/content/倚天屠龙记/',
    'path' => '/list/?id={id}',
    'snapshot' => '/content/倚天屠龙记封面图.jpg',
    'files' => [...],         //文件列表
    'directories' => [...]    //目录列表
]
```

### 文件-File

除网址之外的文章、图片、视频文件。

```
[
    'id' => '根据完整路径生成的唯一编号',
    'pid' => '父目录id',              //如果有父目录的话
    'filename' => '第一章',
    'realpath' => '/www/webroot/content/倚天屠龙记/第一章.md',
    'path' => '/view/?id={id}',
    'extname' => 'md',
    'extension' => 'MD',
    'fstat' => [...],       //同php方法fstat: https://www.php.net/manual/en/function.fstat.php
    'content' => '文章内容...',
    'description' => '文章简介...',
    'keywords' => '文章关键词...',
    'snapshot' => '/content/倚天屠龙记/第一章封面图.jpg',
]
```

其中：  
extension区分大小写，extname只有小写。


.txt，.md和.url 3 种文件说明：
* .txt文件是其它所有文件的描述文件，不会出现在文件列表里；
* .md会读取文件内容并存储在属性content里；
* .url读取文件内容并存储在属性shortcut里；


### 网址-Shortcut

.url文件是一种通用的网页快捷方式，它的数据结构比上述文件数据结构多了一个属性：**shortcut**。

```
[
    'id' => '根据完整路径生成的唯一编号',
    'pid' => '父目录id',              //如果有父目录的话
    'filename' => 'filesite.io',
    'realpath' => '/www/webroot/content/网址导航/filesite.io.url',
    'path' => '/link/?id={id}',
    'extname' => 'url',
    'extension' => 'url',
    'fstat' => [...],       //同php方法fstat: https://www.php.net/manual/en/function.fstat.php
    'shortcut' => [    
        'name' => 'filesite.io',
        'url' => 'https://filesite.io',
    ],
]
```

.url文件内容示例：
```
[InternetShortcut]
URL=https://microsoft.com/
```


## PHP版实现

我们基于本标准用php做了一个名为Machete的开源程序，你可以在github上找到它：
```
https://github.com/filesite-io/machete/
```

详细介绍参见：

[Machete - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统](./Machete_Doc.md)


## 联系我们

如果你觉得Filesite.io对你有帮助，并愿意在某个项目中使用它，很高兴你跟我们分享你的故事：

* 加入QQ群：

  <a href="https://jq.qq.com/?_wv=1027&k=WoH3Pv7d" target="_blank">Machete图片、视频源码交流群：44123711</a>

* 扫码加微信好友：

  <img src="./wx_jialuoma.jpeg" alt="扫描微信二维码加好友" width="240" />


## English Version

[FileSite.io - A standard for managing URLs, articles, images, and videos based on files and directories](./en/README.md)
