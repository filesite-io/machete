<?php
/**
 * Config
 */
$configs = array(
    'version' => '0.1.0',
    'default_timezone' => 'Asia/Shanghai',   //timezone
    'site_name' => 'FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统',

    //文档站皮肤
    //'content_directory' => 'content/',      //directory of contents in /www/
    //when it's empty, use layout and views in directory views/
    //'theme' => 'manual',                    //name of theme which is enabled

    //导航站皮肤
    //'content_directory' => 'navs/',      //directory of contents in /www/
    //'theme' => 'webdirectory', 

    //图片站皮肤
    'content_directory' => 'girls/',           //directory of contents in /www/
    //'theme' => 'googleimage',                 //name of theme which is enabled
    'theme' => 'beauty',                        //皮肤美图

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
        'supportedImageExts' => array('jpg', 'jpeg', 'png', 'webp', 'gif'),
    ),

    /*
    //视频皮肤配置
    'videoblog' => array(
        'imageHeight' => 180,           //图片高度，单位：px
        'contact' => 'FileSite视频网站订制联系：<a href="https://filesite.io" target="_blank">FileSite.io</a>',
    ),
    */

    //目前支持的皮肤
    'allowedThemes' => array(
        'manual' => '文档站-默认',
        'webdirectory' => '导航站-默认',
        'googleimage' => '图片站-默认',
        'beauty' => '图片站-美图',
        'videoblog' => '视频站-默认',
    ),

    //md5加密前缀
    'md5Prefix' => 'some_code_here',

    //后台管理相关配置
    'admin' => array(
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

return $configs;