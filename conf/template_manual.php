<?php
/**
 * Config of 文档站manual
 */
return array(
    'default_timezone' => 'Asia/Shanghai',   //timezone

    //文档站皮肤
    'content_directory' => 'content/',      //directory of contents in /www/
    //when it's empty, use layout and views in directory views/
    'theme' => 'manual',                    //name of theme which is enabled

    //导航站皮肤
    //'content_directory' => 'navs/',      //directory of contents in /www/
    //'theme' => 'webdirectory', 

    //图片站皮肤
    //'content_directory' => 'girls/',           //directory of contents in /www/
    //'theme' => 'googleimage',                 //name of theme which is enabled
                                            
    'default_layout' => 'main',             //default layout
    'error_layout' => 'error',              //exception layout, show error title and content

    //for debug, log directory: ../runtime/logs/
    'debug' => false,

    //for themes
    //'googleimage' => [
    //    'imageHeight' => 180,           //图片高度，单位：px
    //    'contact' => 'FileSite图片网站订制联系：<a href="https://filesite.io" target="_blank">FileSite.io</a>',
    //],
);
