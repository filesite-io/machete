# 类 DirScanner 定义

## 私有属性

* nginxSecureOn: Nginx防盗链开启状态
* nginxSecret: Nginx防盗链密钥
* userIp: 用户IP地址
* nginxSecureTimeout: Nginx防盗链有效期，单位：秒
* nginxSecureLinkMd5Pattern: Nginx防盗链MD5加密方式
* allowReadContentFileExtensions: 允许读取文件内容的文件类型
* fields: 私有属性字段名和说明
* rootDir: 当前扫描的根目录
* webRoot: 网站静态文件相对路径的根目录，默认值：/content/
* scanningDirLevel: 当前扫描的目录深度
* scanStartTime: 开始扫描时间戳，单位：秒
* scanResults: 目录扫描结果
* tree: 目录扫描树形结构


## 受保护属性

* supportFileExtensions: 支持的文件类型
* maxReadFilesize: 默认每种文件读取内容最大大小
* securedFileExtensions: 开启Nginx防盗链的文件类型


## 公开属性

* scanTimeCost: 上一次目录扫描耗时，单位：毫秒


## 私有方法

* isValid: 判断目录名或者文件名是否合法
* parseDescriptionFiles: 解析描述文件内容
* parseShortCuts: 解析快捷方式文件内容
* getId: 根据文件路径生成唯一编号
* isNginxSecureLinkMd5PatternValid: 判断Nginx防盗链MD5加密方式字符串是否合格
* getDirData: 根据路径生成目录数组
* getFileData: 根据路径生成文件数组，兼容URL文件
* getScanningLevel: 根据路径和根目录获取当前扫描的目录深度
* getRelativeDirname: 根据相对目录名
* mergeDescriptionData: 合并描述文件内容到md文件或者目录数据


## 受保护的方法

* getSecureLink: 根据文件生成防盗链网址
* getFilePath: 根据文件生成相对路径
* getDirPath: 根据目录生成相对路径


## 公开方法

* setNginxSecure: 设置Nginx防盗链开启或关闭，以及密钥、用户IP、加密方式、超时时长
* setNginxSecret: 设置Nginx防盗链密钥
* setUserIp: 设置当前用户IP，用来生成加密网址
* setNginxSecureLinkMd5Pattern: 设置Nginx防盗链MD5加密方式
* setNginxSecureTimeout: 设置Nginx防盗链超时时长，单位：秒
* setWebRoot: 设置网站静态文件相对根目录
* getNginxSecret: 获取Nginx防盗链密钥
* getUserIp: 获取当前用户IP
* getNginxSecureLinkMd5Pattern: 获取Nginx防盗链MD5加密方式
* getNginxSecureTimeout: 获取Nginx防盗链超时时长，单位：秒
* getWebRoot: 获取网站静态文件相对根目录
* isSecureOn: 获取是否开启防盗链
* scan: 扫描目录获取目录和文件列表，支持指定目录扫描深度（目录级数）
* getScanResults: 获取扫描结果
* getMenus: 获取扫描结果中的目录结构，其中pid指向父目录
* getMDTitles: 获取.md文件中的h1-h6标题
* fixMDUrls: 替换.md文件解析之后的HTML中的静态文件URL为相对路径path
* getDefaultReadme: 获取名称为README.md的文件


## 参考说明

Nginx防盗链MD5加密方式参考下面网址中的示例，
将Nginx的变量替换$符号为英文大括号；

示例：
```
{secure_link_expires}{uri}{remote_addr} {secret}
```

* <a href="http://nginx.org/en/docs/http/ngx_http_secure_link_module.html#secure_link_md5" target="_blank">Nginx secure link module</a>
