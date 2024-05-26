# Theme - WebDirectory

导航网站皮肤**WebDirectory**。


## 皮肤特点

* 一级目录为网站菜单，链接支持二级目录和文件两种形式，优先展示目录类型链接
* 支持根目录下用Readme_sort.txt描述文件指定一级目录排序
* 支持根目录下用Readme_contact.txt描述文件指定“联系我”内容
* 自适应PC和手机



## conf/app.php配置

```
'content_directory' => 'navs/',         //内容存放目录
'theme' => 'webdirectory',              //皮肤名称
```


## 添加导航网址

进入目录：www/navs/，按照你需要的分类，创建子目录。

例如：**filesite**


再进入子目录filesite，添加导航网址。

导航网址文件.url格式如下：
```
[InternetShortcut]
URL=https://filesite.io/
```

导航网址文件名就是导航链接的名称。
