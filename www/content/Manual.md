
# FileSite.io使用手册

## 目录和文件说明

### 命名规则

目录和文件名可以包含中文、英文、数字和下划线、中划线等字符，
符合Windows、Linux、MacOS操作系统文件命名规范即可。


### 文件类型及其说明

文件类型及其最大文件大小建议：

| 后缀 | 类型 | 最大建议 |
| ---- | ---- | ---- |
| .txt | 纯文本 | 100K |
| .md  | 纯文本 | 5M |
| .url | 快捷方式 | 20K |
| .jpg | 图片 | 500K |
| .png | 图片 | 500K |
| .gif | 图片 | 500K |
| .ico | 图标 | 50K |
| .mp4 | 视频 | 100M |
| .ts  | 视频 | 10M |
| .m3u8 | 视频 | 10M |


文件类型及其可用范围说明：

| 后缀 | 类型 | 读取内容 | 内容可用范围 | 网址可用范围 | 防盗链 |
| ---- | ---- | ---- | ---- | ---- | ---- |
| .txt | 纯文本 | 是 | 全局 | 无 | 无 |
| .md  | 纯文本 | 是 | 详情页 | 无 | 无 |
| .url | 快捷方式 | 是 | 列表页 | 无 | 无 |
| .jpg | 图片| 否 | 无 | 全局 | 是 |
| .png | 图片| 否 | 无 | 全局 | 是 |
| .gif | 图片| 否 | 无 | 全局 | 是 |
| .ico | 图标| 否 | 无 | 全局 | 是 |
| .mp4 | 视频| 否 | 无 | 全局 | 是 |
| .ts  | 视频| 否 | 无 | 全局 | 是 |
| .m3u8 | 视频| 否 | 无 | 全局 | 是 |

其中：
* .txt纯文本文件为说明性文件，可以用来包含标题、描述、关键词等信息；
* .md纯文本文件为详细内容文件，如：文章内容、视频详细介绍等；
* .url快捷方式文件请遵守URL文件格式规范；

只有.txt，.md和.url格式的文件会在网页展示它的内容，
其它格式的文件则以相对网址形式展示，不读取文件内容。



## 数据结构

### 目录

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

### 文件

```
[
    'id' => '根据完整路径生成的唯一编号',
    'pid' => '父目录id',              //如果有父目录的话
    'filename' => '第一章',
    'realpath' => '/www/webroot/content/倚天屠龙记/第一章.md',
    'path' => '/view/?id={id}',
    'extension' => 'md',
    'fstat' => [...],       //同php方法fstat: https://www.php.net/manual/en/function.fstat.php
    'content' => '文章内容...',
    'description' => '文章简介...',
    'keywords' => '文章关键词...',
    'snapshot' => '/content/倚天屠龙记/第一章封面图.jpg',
]
```

不同类型文件的path相对网址不一样，详情对照文件类型与path网址对照表：

| 后缀 | path网址 |
| ---- | ---- |
| .md  | /view/?id={id} |
| .url | /link/?id={id} |
| .m3u8 | /m3u8/?id={id}&{防盗链参数}&ver={更新时间戳} |
| .jpg | /{相对路径}/{filename}.{extension}?{防盗链参数}&ver={更新时间戳} |
| .png | /{相对路径}/{filename}.{extension}?{防盗链参数}&ver={更新时间戳} |
| .gif | /{相对路径}/{filename}.{extension}?{防盗链参数}&ver={更新时间戳} |
| .ico | /{相对路径}/{filename}.{extension}?{防盗链参数}&ver={更新时间戳} |
| .mp4 | /{相对路径}/{filename}.{extension}?{防盗链参数}&ver={更新时间戳} |
| .ts  | /{相对路径}/{filename}.{extension}?{防盗链参数}&ver={更新时间戳} |

.txt，.md和.url 3 种特殊文件类型：
* .txt文件是其它所有文件的描述文件，不会出现在文件列表里；
* .md会读取文件内容并存储在content字段里；
* .url读取文件内容并存储在shortcut字段里；


### URL快捷方式

.url文件是windows下的一种网页快捷方式文件。

```
[
    'id' => '根据完整路径生成的唯一编号',
    'pid' => '父目录id',              //如果有父目录的话
    'filename' => 'filesite.io',
    'realpath' => '/www/webroot/content/网址导航/filesite.io.url',
    'path' => '/link/?id={id}',
    'extension' => 'url',
    'fstat' => [...],       //同php方法fstat: https://www.php.net/manual/en/function.fstat.php
    'shortcut' => [    
        'name' => 'filesite.io',
        'url' => 'https://filesite.io',
    ],
]
```

URL快捷方式文件是一类特殊的文件，它比一般的文件多了一个字段：shortcut。


.url文件内容示例：
```
[InternetShortcut]
URL=https://microsoft.com/
```


## 字段说明

字段名采用小写英文字符串，除了下面列出来的还可以自定义。

对整个目录数据读取后所有资源跟数据字段对照表：

| 资源 | 字段 |
| ---- | ---- |
| 编号 | id |
| 父目录编号 | pid |
| 目录名 | directory |
| 子目录 | directories |
| 子文件 | files |
| 文件名 | filename |
| 完整路径 | realpath |
| 相对网址 | path |
| 文件后缀 | extension |
| 资源状态 | fstat |
| MD文件内容 | content |
| URL快捷方式 | shortcut |


特殊描述文件名称和字段对照表：

| 文件名 | 用途 | 字段 |
| ---- | ---- | ---- |
| title.txt | 标题 | title |
| description.txt | 描述信息 | description |
| keywords.txt | 关键词信息 | keywords |
| snapshot.txt | 快照图片 | snapshot |

特殊描述文件都是可选的，如果有则优先使用里面的内容，其它需要扩展的字段只需添加{英文小写}.txt后缀的文件即可。

说明：
其中snapshot.txt里的内容为跟当前txt文件**同目录的图片文件名**，
如下面示例中snapshot.txt的内容为：
```
金庸小说封面图_new.png
```

示例：
```
|_d_ 金庸小说
     |_f_ description.txt
     |_f_ keywords.txt
     |_f_ snapshot.txt
     |_f_ 金庸小说封面图_1.jpg
     |_f_ 金庸小说封面图_new.png
     |_f_ author.txt
```

描述文件和被描述的文件命名规则：
```
{被描述文件名_}{字段名}.txt
```

如果```{被描述文件名_}```为空，则认为该描述文件是用来说明所在目录的。

示例：
```
|_d_ 金庸小说
     |_f_ 第一章.md
     |_f_ 第一章_description.txt
     |_f_ 第一章_keywords.txt
     |_f_ 第二章.md
     |_f_ 第二章_price.txt
     |_f_ title.txt
```



## 目录结构约定

### 示例说明

* 用字母**d**代表目录，字母**f**代表文件
* 文件或目录名从空格字符后一位开始到行末


### 导航目录和文件结构示例

示例：
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



### 图书目录和文件结构示例

示例：
```
-d- 金庸小说
  |_f_ 金庸封面.jpg
  |_d_ 天龙八部
  |_d_ 射雕英雄传
       |_f_ 射雕英雄传封面.jpg
       |_f_ 第一章.md
       |_f_ 第二章.md
       |_f_ 第三章.md
-d- 古龙小说
  |_f_ 古龙封面.jpg
  |_d_ 多情剑客无情剑
  |_d_ 楚留香
       |_f_ 楚留香封面.jpg
       |_f_ 第一章.md
       |_f_ 第二章.md
       |_f_ 第三章.md
       .
       .
       .
```


### 图片目录和文件结构示例

示例：
```
-d- 动物
  |_d_ 宠物狗
       |_f_ 吉娃娃01.jpg
       |_f_ 吉娃娃02.jpg
       |_f_ 吉娃娃03.jpg
  |_d_ 兔子
       |_f_ 大白兔01.jpg
       |_f_ 大白兔02.jpg
       |_f_ 大白兔03.jpg
       .
       .
       .
-d- 植物
  |_d_ 鲜花
       |_d_ 玫瑰花
            |_f_ 玫瑰01.jpg
            |_f_ 玫瑰02.jpg
            |_f_ 玫瑰03.jpg
       |_d_ 菊花
            |_f_ 菊花01.jpg
            |_f_ 菊花02.jpg
            |_f_ 菊花03.jpg
       .
       .
       .
-d- 其它
     |_f_ 玫瑰01.jpg
     |_f_ 玫瑰02.jpg
     |_f_ 玫瑰03.jpg
       .
       .
       .
```



### 视频目录和文件结构示例

示例：
```
-d- 电影
  |_d_ 科幻片
       |_d_ 钢铁侠
            |_f_ 钢铁侠.mp4
            |_f_ 封面图.jpg
            |_f_ 简介.md
            |_f_ title.txt
            |_f_ description.txt
            |_f_ keywords.txt
  |_d_ 战争片
       |_d_ 拯救大兵瑞恩
            |_f_ 拯救大兵瑞恩.m3u8
            |_f_ 封面图.jpg
            |_f_ 简介.md
            |_f_ 001.ts
            |_f_ 002.ts
            |_f_ 003.ts
       .
       .
       .
-d- 综艺
    .
    .
    .
```



## 皮肤设计

### 皮肤设计规范


### 皮肤共享提交


