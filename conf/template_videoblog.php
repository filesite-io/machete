<?php
/**
 * Config
 */
$configs = array(
    'default_timezone' => 'Asia/Shanghai',   //timezone

    //视频站皮肤
    'content_directory' => 'videos/',           //directory of contents in /www/
    'theme' => 'videoblog',                 //name of theme which is enabled

    'maxScanDirLevels' => 4,                //max directory levels to scan
    'default_layout' => 'main',             //default layout
    'error_layout' => 'error',              //exception layout, show error title and content

    //for debug, log directory: ../runtime/logs/
    'debug' => true,

    //视频皮肤配置
    'videoblog' => array(
        'imageHeight' => 180,           //图片高度，单位：px
        'contact' => 'FileSite视频网站订制联系：<a href="https://filesite.io" target="_blank">FileSite.io</a>',
    ),

    //目前支持的皮肤
    'allowedThemes' => array(
        'manual',
        'webdirectory',
        'googleimage',
        'videoblog',
    ),

    //md5加密前缀
    'md5Prefix' => 'some_code_here',

    //后台管理相关配置
    'admin' => array(
        'username' => 'filesite',
        'password' => '88888888',
        'captcha' => true,      //后台登陆是否开启验证码

        'maxUploadFileSize' => 20,       //单位：Mb
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
