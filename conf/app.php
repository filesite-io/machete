<?php
/**
 * Config
 */
$configs = array(
    'version' => '0.4.0',
    'releaseDate' => '2024-11-29',
    'showVersion' => false,     //默认不显示版本号和发布日期

    'default_timezone' => 'Asia/Hong_Kong',      //timezone, check more: https://www.php.net/manual/en/timezones.asia.php
    'site_name' => 'Machete from FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统',

    //多用户网址解析开关，默认为关闭状态
    //规则：当此开关打开的情况下，网址中域名后面第一个目录，如果是纯数字，则把它作为用户ID
    //示例：https://tajian.tv/1000/site/index，其中的1000就是用户ID
    //解析成功后会将用户ID保存在FSC::$app全局变量中，通过：FSC::$app['user_id']访问
    //并自动把用户ID加入到数据目录content_directory路径后面
    'multipleUserUriParse' => false,
    //只有上面这个开关开启，此默认用户ID才会被使用
    'defaultUserId' => '',

    //文档站皮肤
    //'content_directory' => 'content/',      //directory of contents in /www/
    //when it's empty, use layout and views in directory views/
    //'theme' => 'manual',                    //name of theme which is enabled

    //导航站皮肤
    'content_directory' => 'navs/',      //directory of contents in /www/
    'theme' => 'webdirectory', 

    //图片站皮肤
    //'content_directory' => 'girls/',           //directory of contents in /www/
    //'theme' => 'googleimage',                 //name of theme which is enabled
    //'theme' => 'beauty',                        //皮肤美图

    //视频站皮肤
    //'content_directory' => 'videos/',           //directory of contents in /www/
    //'theme' => 'videoblog',                 //name of theme which is enabled

    'maxScanDirLevels' => 4,                //max directory levels to scan
    'default_layout' => 'main',             //default layout
    'error_layout' => 'error',              //exception layout, show error title and content

    //for debug, log directory: ../runtime/logs/
    'debug' => true,

    //for themes
    //图片皮肤配置
    'googleimage' => array(
        'imageHeight' => 350,           //图片高度，单位：px
        'contact' => 'FileSite图片网站订制联系：<a href="https://filesite.io" target="_blank">FileSite.io</a>',
        'supportedImageExts' => array('jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'),
    ),

    'supportedImageExts' => array('jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'),
    'supportedVideoExts' => array('mp4', 'mov', 'm3u8'),
    'supportedAudioExts' => array('mp3'),

    'screenshot_start' => 1000,                 //视频播放页快照截取开始时间，单位：毫秒
    'screenshot_expire_seconds' => 315360000,   //视频封面图在服务器端缓存时长，单位：秒
    'small_image_zoom_rate' => 2.5,             //浏览器生成缩略图在其展示尺寸的放大倍数，以确保清晰度

    //缩略图在浏览器端缓存时长，单位：秒
    'small_image_client_cache_seconds' => 600,
    //目录封面图在浏览器端缓存时长，单位：秒
    'dir_snapshot_client_cache_seconds' => 300,
    //视频、音乐meta在浏览器端缓存时长，单位：秒
    'meta_client_cache_seconds' => 300,

    //开启Imagick扩展，缩略图生成可能慢一点，但是会更清晰、稳定
    'enable_lib_imagick' => false,

    //列表页缩略图尺寸设置
    'small_image_min_width' => 360,     //缩略图最小宽度设置，以确保清晰度
    'small_image_min_height' => 270,    //缩略图最小高度设置，以确保清晰度

    //预览大图时，缩略图尺寸设置
    'middle_image_min_width' => 1080,    //打开图片浏览器显示大图时，大图的最小宽度设置，以确保清晰度
    'middle_image_min_height' => 720,   //打开图片浏览器显示大图时，大图的最小高度设置，以确保清晰度

    'enableSmallImage' => true,             //列表页面是否开启缩略图，true 为显示缩略图，false 则显示原图
    'enableSmallImageForWan' => false,      //外网使用时，点击图片打开fancybox时是否显示缩略图：true 显示缩略图， false 则显示原图
    'smallImageQuality' => 95,              //缩略图压缩比率，0 - 100，数字越大，清晰度越高，系统默认：95

    'default_page_size' => 48,              //每页显示图片数量，请设置6的倍数（因为电脑版一行时6个图片）
    'slide_show_timeout' => 3,              //自动播放图片时，切换下一张的延迟秒数，单位：秒

    //关闭服务器端生成缩略图，如果在cpu性能较低的设备（如路由器）里运行，开启此配置可以减少cpu消耗
    //如果在外网运行，开启此配置，则可能会因为服务器带宽较小导致图片加载缓慢
    'disableGenerateSmallImageInServer' => false,

    'showQRImageInFooter' => true,          //在网页底部显示当前网址二维码

    'defaultMenuStatusInPC' => 'closed',    //PC下左侧目录默认状态，可选值：opened, closed

    'sortFilesByName' => false,     //图片、视频、音乐文件按名字排序，默认关闭，以文件创建时间倒序排
    'sortOrderOfFiles' => 'asc',    //排序方式，asc顺序，desc倒序

    //开启局域网ip拥有管理权限，如：保存目录、视频封面图
    //默认只支持192.168网段以及本机（127.0.0.1、172.17.0.1, localhost）
    'adminForLanIps' => true,

    //拥有管理权限的ip白名单
    'adminWhiteIps' => array(
        //'192.168.1.105',
    ),

    /*
    //视频皮肤配置
    'videoblog' => array(
        'imageHeight' => 180,           //图片高度，单位：px
        'contact' => 'FileSite视频网站订制联系：<a href="https://filesite.io" target="_blank">FileSite.io</a>',
    ),
    */

    //皮肤TaJian相关
    'default_friends_code' => '888888',  //默认的注册邀请码
    'tajian' => array(
        'data_dir' => 'data/',    //数据目录
        'tag_dir' => 'tags/',     //tag分类目录
        'task_dir' => 'task/',    //分享视频下载任务文件保存目录
        'task_log' => 'tasks.log',    //分享视频下载任务文件日志文件
        'max_dir_num' => 3,           //普通用户：一个手机可创建的最大收藏夹数量
        'max_dir_num_vip' => 50,      //VIP用户：一个手机可创建的最大收藏夹数量
        'supportedPlatforms' => array(
            'B站',
            '抖音',
            '快手',
            '西瓜视频',
            '其它',
        ),
    ),

    //目前支持的皮肤
    'allowedThemes' => array(
        'manual' => '文档站-默认',
        'webdirectory' => '导航站-默认',
        'googleimage' => '图片站-默认',
        'beauty' => '图片站-美图',
        'videoblog' => '视频站-默认',
        'tajian' => 'TA荐-视频分享',
    ),

    //md5加密前缀
    'md5Prefix' => 'some_code_here',

    //后台管理相关配置
    'admin' => array(
        'disabled' => true,         //关闭后台相关功能

        'username' => 'filesite',
        'password' => '88888888',
        'captcha' => true,      //后台登陆是否开启验证码

        'maxUploadFileNumber' => 5,    //一次批量上传文件数量
        'maxUploadFileSize' => 10,       //单位：Mb
        'allowedUploadFileTypes' => array(
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
        ),
    ),

    //对接HeroUnion英雄联盟，实现提交任务和接收任务完成回调通知
    'heroUnionEnable' => false,
    'heroUnion' => array(
        'server_url' => 'https://herounion.filesite.io',
        'uuid' => 'machete_tajian',
        'token' => 'machete_tajian',
        'contract' => 'tajiantv',
        'country' => 'cn',
        'lang' => 'zh',
        'data_mode' => 'json',
        'notify_domain' => 'https://tajian.tv',

        'supportedPlatforms' => array(
            '抖音' => 'douyin',
            '快手' => 'kuaishou',
            '西瓜视频' => 'xigua',
            'B站' => 'bilibili',
            '其它' => 'website',
        ),
    ),

    //对接第三方API服务service_3rd
    //源码下载：https://git.filesite.io/filesite/service-3rd
    'service_3rd_api_domain' => 'https://service.filesite.io',
    'service_3rd_api_key' => '你的密钥',
    'sms_code_cache_time' => 600,           //短信验证码缓存时长，单位：秒

    //图片cdn加速域名配置
    'img_cdn_budget_url' => '',

    //Google Analytics MEASUREMENT ID
    'GA_MEASUREMENT_ID' => 'G-09MWT3Z9R0',

    //Google Adwords目标跟踪ID
    'GAD_MEASUREMENT_ID' => '',

    //密码授权默认关闭
    "password_auth" => array(
        "enable" => false,
    ),
);

//自定义配置支持
$customConfigFile = __DIR__ . '/../runtime/custom_config.json';
if (file_exists($customConfigFile)) {
    try {
        $json = file_get_contents($customConfigFile);
        $customConfigs = json_decode($json, true);
        $configs = array_merge($configs, $customConfigs);
    }catch(Exception $e) {}
}

//皮肤对应的自定义配置
$customConfigFile = __DIR__ . "/../runtime/custom_config_{$configs['theme']}.json";
if (file_exists($customConfigFile)) {
    try {
        $json = file_get_contents($customConfigFile);
        $customConfigs = json_decode($json, true);
        $configs = array_merge($configs, $customConfigs);
    }catch(Exception $e) {}
}

//密码配置支持
$customConfigFile = __DIR__ . '/../runtime/custom_password.json';
if (file_exists($customConfigFile)) {
    try {
        $json = file_get_contents($customConfigFile);
        $customConfigs = json_decode($json, true);
        $configs = array_merge($configs, $customConfigs);
    }catch(Exception $e) {}
}


//用户管理多账号自定义配置
$customConfigFile = __DIR__ . "/../runtime/custom_config_usermap.json";
if (file_exists($customConfigFile)) {
    try {
        $json = file_get_contents($customConfigFile);
        $customConfigs = json_decode($json, true);
        $configs = array_merge($configs, $customConfigs);
    }catch(Exception $e) {}
}

//VIP用户自定义配置
$customConfigFile = __DIR__ . "/../runtime/custom_config_vip.json";
if (file_exists($customConfigFile)) {
    try {
        $json = file_get_contents($customConfigFile);
        $customConfigs = json_decode($json, true);
        $configs = array_merge($configs, $customConfigs);
    }catch(Exception $e) {}
}

return $configs;