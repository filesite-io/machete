# Machete

Source code of filesite.io.


## 简介

**Machete**是**砍刀**，它能砍、能削、能切、能剁，最适合披荆斩棘；

砍刀不像大刀、长剑、长矛为战场而生，但在日常生活中使用也是得心应手。

Filesite.io也一样，它短小精悍，使用它把常见的本地文件制作成网站，就像拿起砍刀一样简单，
所以我们将filesite.io的**源码**命名为```machete```。


## Docker使用

从dockerhub下载镜像：

```
docker pull filesite/machete
```


启动machete容器：

```
docker run --name machete -p 1080:80 -itd filesite/machete [皮肤名]
```

其中皮肤名称可选值：

```
[
    'manual',           //文档网站
    'webdirectory',     //导航网站
    'googleimage'       //图片网站
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

这样可以通过更新本地内容目录的文件来实时更新网站内容。


查看容器：

```
docker ps
```

如果看到名字为``machete``的容器正在运行，说明容器启动完成，访问本地网址测试：

```
http://127.0.0.1:1080
```