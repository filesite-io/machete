# Machete家庭相册系统使用FAQ常见问题


## 系统配置文件在哪里？

全局配置文件：conf/app.php
自定义配置文件：runtime/custom_config.json

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


## 怎么修改浏览器地址栏左侧的小图标？

请创建自己的icon文件，命名为favicon.ico，替换根目录下的这个图标即可；

**注意：**
请清空浏览器缓存后验证新图标，否则可能因为浏览器缓存而看到老的图标。


## 怎么关闭网页底部的二维码？

在runtime/custom_config.json中增加配置：
```
"showQRImageInFooter": false
```


## 怎么修改底部的版权信息？

请编辑目录下的php文件：
```
themes/beauty/views/layout/
```

找到“尾部网站信息”，按自己的需要修改并保存。

注意本地保存相关文件存档，machete升级系统时会覆盖此目录下的文件。


## 相册部署到外网了，点击图片打开有点慢怎么办？

machete家庭相册默认为局域网使用，配置**enableSmallImageForWan**开启查看大图显示缩略图是关闭的，
如果你的照片文件大小很大，那么在外网打开可能会很慢。

解决办法，为外网点击图片浏览大图开启缩略图功能，
在自定义配置：runtime/custom_config.json里增加以下配置即可：
```
"enableSmallImageForWan": true
```


## 更多问题如何联系？

请查看README.md里的联系方式，
或者进官方网站查看QQ群：
https://filesite.io