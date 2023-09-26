
# 视频分享皮肤TaJian

## 功能

* 把抖音等平台复制的分享视频内容加入收藏
* 显示用户收藏的视频列表，支持分页显示
* 显示系统生成或用户自己添加的视频分类，点击分类可以查看分类下的所有视频，支持分页显示


## API

* 添加新视频：/api/addfav/

参数：
```
content: 从抖音或其它平台复制出来的视频分享内容，或者视频网址
title: 视频标题
tag: 分类名称
tagid: 分类id
```

其中title、tag和tagid为可选值。

请求方法：
POST

返回值：
```
{
    code: 1,
    msg: '操作结果',
    err: '异常信息',
    data: []
}
```

返回JSON格式数据。


* 获取分类列表：/api/tags/

参数：
无

请求方法：
GET

返回值：
```
[
    {
        id: '编号',
        name: '分类名称',
        total: 0          //内容总数
    },
    ...
]
```


* 获取视频列表：/api/videos/

参数：
```
page: 页码
limit: 每页数量
tag: 分类名
tagid: 分类id
```

上述参数都为可选值。


请求方法：
GET

返回值：
```
[
    {
        'id': '根据完整路径生成的唯一编号',
        'pid': '父目录id',              //如果有父目录的话
        'filename': 'filesite.io',
        'realpath': '/www/webroot/content/网址导航/filesite.io.url',
        'path': '/link/?id={id}',
        'extension': 'url',
        'fstat': [...],       //同php方法fstat: https://www.php.net/manual/en/function.fstat.php
        'shortcut': [    
            'name': 'filesite.io',
            'url': 'https://filesite.io',
            'cover': '封面图网址',
            'brand': '视频来源厂商ico图标网址',
        ],
    },
    ...
]
```

分享视频数据结构同“URL快捷方式”，但shortcut属性里比它多了cover和brand两个属性。

