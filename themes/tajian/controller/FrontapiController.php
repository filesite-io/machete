<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Html.php';
require_once __DIR__ . '/../../../plugins/Common.php';
require_once __DIR__ . '/SiteController.php';

Class FrontApiController extends SiteController {

    public function actionIndex() {
        $code = 0;
        $err = 'Not allowed';

        return $this->renderJson(compact('code', 'err'));
    }

    public function actionTags() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序
        
        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);

        $code = 1;
        $msg = '';
        $err = '';
        
        //获取tags分类
        $noFiles = true;
        $data = $this->getTags($dirTree, $noFiles);

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    /*
     * 参数：
     * content: 从抖音或其它平台复制出来的视频分享内容，或者视频网址
     * title: 视频标题
     * tag: 分类名称
     * tagid: 分类id
     * 其中title、tag和tagid为可选值。
     */
    public function actionAddfav() {
        $ip = $this->getUserIp();
        $check_time = 60;            //1 分钟内
        $max_time_in_minutes = 10;   //最多 10 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许添加到自己的收藏夹
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }

        $content = $this->post('content', '');
        $title = $this->post('title', '');
        $tag = $this->post('tag', '');
        $tagid = $this->post('tagid', '');

        $code = 1;
        $msg = '';
        $err = '';

        if (empty($content)) {
            $code = 0;
            $err = '请粘贴填写分享内容！';
        }else {
            $content = urldecode($content);
        }

        //分享内容来源平台检查
        $shareUrl = $this->getShareUrlFromContent($content);
        $platform = Html::getShareVideosPlatform($shareUrl);
        if (!in_array($platform, FSC::$app['config']['tajian']['supportedPlatforms'])) {
            $code = 0;
            $err = '目前只支持抖音、快手、西瓜视频和Bilibili的分享网址哦！';
        }

        $tagName = '';
        if ($code == 1 && (!empty($tag) || !empty($tagid))) {        //检查分类名称或id是否存在
            $scanner = new DirScanner();
            $scanner->setWebRoot(FSC::$app['config']['content_directory']);
            $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);
            //获取tags分类
            $tags = $this->getTags($dirTree);

            if (!empty($tagid) && empty($tags[$tagid])) {        //检查tagid是否存在
                $code = 0;
                $err = "分类ID {$tagid} 不存在！";
            }

            if (!empty($tag)) {        //检查tag是否存在
                $tag_exists = false;
                foreach($tags as $id => $item) {
                    if ($item['name'] == $tag) {
                        $tag_exists = true;
                        $tagName = $tag;
                        break;
                    }
                }

                if ($tag_exists == false) {
                    $code = 0;
                    $err = "分类 {$tag} 不存在！";
                }
            }

            if (empty($tagName) && !empty($tags[$tagid])) {
                $tagName = $tags[$tagid]['name'];
            }
        }

        if ($code == 1) {        //保存视频
            $msg = $this->saveShareVideo($content, $title, $tagName) ? '视频保存完成，系统开始自动处理，1 - 3 分钟后刷新就能看到新添加的视频了。' : '视频保存失败，请稍后重试！';
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    protected function getVideoId($url) {
        return md5($url);
    }

    protected function getShareUrlFromContent($content) {
        $url = '';

        preg_match("/https:\/\/[\w\.]+(\/\w+){1,}\/?/i", $content, $matches);
        if (!empty($matches)) {
            $url = $matches[0];
        }

        return $url;
    }

    //保存分享视频
    protected function saveShareVideo($content, $title, $tagName) {
        $done = true;

        $shareUrl = $this->getShareUrlFromContent($content);
        if (!empty($shareUrl)) {
            //如果没有对接HeroUnion则保存本地任务文件
            if (empty(FSC::$app['config']['heroUnionEnable'])) {
                $done = $done && $this->saveBotTask($shareUrl);
            }

            if (!empty($tagName)) {
                $video_id = $this->getVideoId($shareUrl);
                $done = $done && $this->saveVideoToTag($video_id, $tagName);
            }

            //保存任务日志
            $this->saveTaskLog($shareUrl, $title, $tagName);

            //调用HeroUnion联盟接口，提交新的数据抓取任务
            if (!empty(FSC::$app['config']['heroUnionEnable'])) {
                $platformName = Html::getShareVideosPlatform($shareUrl);
                $heroUnionConfig = FSC::$app['config']['heroUnion'];
                $this->addHeroUnionTask($shareUrl, $heroUnionConfig['supportedPlatforms'][$platformName]);
            }
        }

        return $done;
    }

    //保存分享视频到任务文件
    protected function saveBotTask($url) {
        $task_dir = __DIR__ . '/../../../runtime/' . FSC::$app['config']['tajian']['task_dir'];
        if (!is_dir($task_dir)) {
            mkdir($task_dir, 0755, true);
        }

        $video_id = $this->getVideoId($url);
        $filepath = realpath($task_dir) . "/{$video_id}.task";
        return file_put_contents($filepath, $url) !== false;
    }

    //保存分享视频到tag分类
    //TODO: 如果高并发，需要避免数据被覆盖的问题
    protected function saveVideoToTag($video_id, $tagName) {
        if (empty($video_id) || empty($tagName)) {return false;}

        $tag_dir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['tag_dir'];
        if (!is_dir($tag_dir)) {
            mkdir($tag_dir, 0755, true);
        }

        $filepath = realpath($tag_dir) . "/{$tagName}.txt";
        if (file_exists($filepath)) {
            $content = file_get_contents($filepath);
            $videos = explode("\n", $content);
            $last_id = array_pop($videos);
            if (!empty($last_id)) {
                array_push($videos, $last_id);
            }

            if (!in_array($video_id, $videos)) {
                array_push($videos, $video_id);
            }

            return file_put_contents($filepath, implode("\n", $videos)) !== false;
        }else {
            return file_put_contents($filepath, $vidoe_id) !== false;
        }
    }

    //从分类中删除视频
    protected function deleteVideoFromTag($filename, $tagName) {
        if (empty($filename) || empty($tagName)) {return false;}

        $tag_dir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['tag_dir'];
        if (!is_dir($tag_dir)) {
            mkdir($tag_dir, 0755, true);
        }

        $filepath = realpath($tag_dir) . "/{$tagName}.txt";
        if (file_exists($filepath)) {
            $content = file_get_contents($filepath);
            $videos = explode("\n", $content);
            $last_id = array_pop($videos);
            if (!empty($last_id)) {
                array_push($videos, $last_id);
            }

            $key = array_search($filename, $videos);
            if ($key !== false) {
                unset($videos[$key]);
            }

            return file_put_contents($filepath, implode("\n", $videos)) !== false;
        }

        return false;
    }

    //保存任务日志
    protected function saveTaskLog($url, $title, $tagName) {
        $logFile = __DIR__ . '/../../../runtime/' . FSC::$app['config']['tajian']['task_log'];

        $saved = true;
        try {
            $fp = fopen($logFile, 'a');

            $content = array(
                'url' => $url,
                'title' => $title,
                'tag' => $tagName,
                'created' => time(),
            );

            if (!empty(FSC::$app['config']['multipleUserUriParse'])) {
                $content['user'] = !empty(FSC::$app['user_id']) ? FSC::$app['user_id'] : '';
            }

            fwrite($fp, json_encode($content) . "\n");
        }catch(Exception $err) {
            $saved = false;
        }

        return $saved;
    }

    protected function sign($params, $token) {                    //对参数做MD5签名
        ksort($params);
        return md5( json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . $token );
    }

    //提交视频抓取任务到HeroUnion英雄联盟
    protected function addHeroUnionTask($shareUrl, $platform) {
        $notify_prefix = '';
        if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
            $notify_prefix = '/' . FSC::$app['user_id'];
        }

        $heroUnionConfig = FSC::$app['config']['heroUnion'];
        $params = array(
            'uuid' => $heroUnionConfig['uuid'],
            'url' => $shareUrl,
            'platform' => $platform,
            'contract' => $heroUnionConfig['contract'],
            'data_mode' => $heroUnionConfig['data_mode'],
            'country' => $heroUnionConfig['country'],
            'lang' => $heroUnionConfig['lang'],
            'notify_url' => $heroUnionConfig['notify_domain'] . $notify_prefix . '/frontapi/hunotify/',
        );
        $params['sign'] = $this->sign($params, $heroUnionConfig['token']);

        $api = $heroUnionConfig['server_url'] . '/api/newtask/';
        $timeout = 10;
        $pc = false;
        $headers = array("Content-Type: application/json");
        //以json格式post数据
        $res = $this->request($api, json_encode($params), $timeout, $pc, $headers);

        return !empty($res) && $res['status'] == 200 ? $res['result'] : false;
    }

    //保存快捷方式
    protected function saveUrlShortCut($video_id, $task_url) {
        $data_dir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }

        $shortUrlContent = <<<eof
[InternetShortcut]
URL={$task_url}
eof;

        $filepath = realpath($data_dir) . "/{$video_id}.url";
        return file_put_contents($filepath, $shortUrlContent) !== false;
    }

    //保存描述文件：标题和图片
    protected function saveDescriptionFiles($video_id, $task_data) {
        $data_dir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }

        $done = true;

        try {
            $data_dir = realpath($data_dir);

            //保存标题
            $filepath_title = "{$data_dir}/{$video_id}_title.txt";
            file_put_contents($filepath_title, $task_data['title']);

            //保存图片文件
            if (!empty($task_data['cover_base64'])) {
                $filepath_cover = "{$data_dir}/{$video_id}.{$task_data['cover_type']}";
                file_put_contents($filepath_cover, base64_decode($task_data['cover_base64']));

                $filepath_desc = "{$data_dir}/{$video_id}_cover.txt";
                file_put_contents($filepath_desc, "{$video_id}.{$task_data['cover_type']}");
            }else if (!empty($task_data['cover'])) {
                $filepath_desc = "{$data_dir}/{$video_id}_cover.txt";
                file_put_contents($filepath_desc, "{$task_data['cover']}");
            }
        }catch(Exception $err) {
            $done = false;
        }

        return $done;
    }

    //删除收藏的视频相关的所有文件
    protected function deleteVideoFiles($filename) {
        $data_dir = __DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];
        if (!is_dir($data_dir)) {
            return false;
        }

        $done = true;

        try {
            $data_dir = realpath($data_dir);

            //删除.url文件
            $filepath_url = "{$data_dir}/{$filename}.url";
            if (file_exists($filepath_url)) {
                unlink($filepath_url);
            }

            //删除标题
            $filepath_title = "{$data_dir}/{$filename}_title.txt";
            if (file_exists($filepath_title)) {
                unlink($filepath_title);
            }

            //删除图片文件
            $imgTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            foreach ($imgTypes as $cover_type) {
                $filepath_cover = "{$data_dir}/{$filename}.{$cover_type}";
                if (file_exists($filepath_cover)) {
                    unlink($filepath_cover);
                }
            }

            //删除图片描述文件
            $filepath_desc = "{$data_dir}/{$filename}_cover.txt";
            if (file_exists($filepath_desc)) {
                unlink($filepath_desc);
            }
        }catch(Exception $err) {
            $done = false;
        }

        return $done;
    }

    //HeroUnion任务数据通知回传接口
    /**
     * task_id
     * task_result
     * timestamp
     * sign
     **/
    public function actionHuNotify() {
        $task_id = $this->post('task_id', '');
        $task_result = $this->post('task_result', '');
        $timestamp = $this->post('timestamp', '');
        $sign = $this->post('sign', '');

        $code = 1;
        $msg = '';
        $err = '';

        //参数检查
        if (empty($task_id) || empty($task_result) || empty($timestamp) || empty($sign)) {
            $code = 0;
            $err = '参数缺失！';
        }

        $heroUnionConfig = FSC::$app['config']['heroUnion'];

        //验证签名
        if ($code == 1) {
            $checkParams = array(
                'task_id' => $task_id,
                'task_result' => $task_result,
                'timestamp' => $timestamp,
            );
            $mySign = $this->sign($checkParams, $heroUnionConfig['token']);

            if (strtolower($mySign) != strtolower($sign)) {
                $code = 0;
                $err = '签名验证不通过！';
            }else if (!empty($task_result['done'])) {    //如果任务成功抓取到数据
                $video_id = $this->getVideoId($task_result['url']);
                $saveUrlRes = $this->saveUrlShortCut($video_id, $task_result['url']);
                $saveDescRes = $this->saveDescriptionFiles($video_id, $task_result);
                if (!$saveUrlRes) {
                    $code = 0;
                    $err = '网址快捷方式文件保存失败！';
                }else if (!$saveDescRes) {
                    $code = 0;
                    $err = '标题文件、图片及其描述文件保存失败！';
                }else {
                    $msg = '视频相关文件保存完成。';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    //TODO: 把自己的收藏视频压缩成zip包
    protected function createZip() {
        
    }

    //TODO: 打包下载自己的收藏记录
    public function actionDownloadfavs() {
        $this->createZip();
        exit;
    }

    //请求频率限制
    /**
     * key: 检查频率限制的唯一标识
     * max: 最大次数
     * time: 检查时间，单位：秒
     */
    protected function requestLimit($key, $max, $time) {
        $isLimited = false;

        try {
            if(session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $current_time = microtime(true)*1000;

            $field = md5("requestLimit_by_{$key}");
            $field_update_time = "{$field}_updated";
            if (!empty($_SESSION[$field]) && !empty($_SESSION[$field_update_time]) && $current_time - $_SESSION[$field_update_time] <= $time*1000) {
                $_SESSION[$field] ++;
            }else {
                $_SESSION[$field] = 1;
                $_SESSION[$field_update_time] = $current_time;
            }

            if ($_SESSION[$field] > $max) {
                $isLimited = true;
            }
        }catch(Exception $e) {
            $this->logError("Request limit by session failed: " . $e->getMessage());
        }

        return $isLimited;
    }

    //生成4随机数，并保存生成时间，10 分钟内有效
    protected function generateRandSmsCode($cellphone) {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $rndCode = rand(1000, 9999);        //4位随机数

        $_SESSION['randSmsCode'] = $rndCode;
        $_SESSION['randSmsCode_created'] = time();
        $_SESSION['smsCodePhone'] = $cellphone;         //保存发送验证码的手机号码，便于在登录、注册的时候验证

        return $rndCode;
    }

    //短信验证码 10 分钟内有效
    protected function getMySmsCode($cellphone) {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $rndCode = !empty($_SESSION['randSmsCode']) ? $_SESSION['randSmsCode'] : 0;
        $rndCode_created = !empty($_SESSION['randSmsCode_created']) ? $_SESSION['randSmsCode_created'] : 0;
        $codeSentPhoneNumber = !empty($_SESSION['smsCodePhone']) ? $_SESSION['smsCodePhone'] : 0;
        $current_time = time();

        $max_cache_time = !empty(FSC::$app['config']['sms_code_cache_time']) ? FSC::$app['config']['sms_code_cache_time'] : 600;
        if (!empty($rndCode_created) && $current_time - $rndCode_created > $max_cache_time) {
            $rndCode = 0;
        }else if (empty($codeSentPhoneNumber) || $cellphone != $codeSentPhoneNumber) {  //检查发送验证码的手机号码跟提交的是否一致
            $rndCode = 0;
        }

        return $rndCode;
    }

    //获取短信验证码
    public function actionSendsmscode() {
        $ip = $this->getUserIp();
        $check_time = 300;          //5 分钟内
        $max_time_in_minutes = 3;   //最多 3 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        $postParams = $this->post();
        if (!empty($postParams)) {
            $cellphone = $this->post('phoneNum', '');
            $action = $this->post('action', 'register');

            if (empty($cellphone) || Common::isCellphoneNumber($cellphone) == false) {
                $err = "手机号码格式错误，请填写正确的手机号码";
            }else {
                //判断是否已经注册
                $userDataDir = Common::getUserDataDir($cellphone);
                if (!empty($userDataDir) && $action == 'register') {
                    $err = '你已经注册，请直接登录';
                    return $this->renderJson(compact('code', 'msg', 'err'));
                }else if (empty($userDataDir) && $action == 'login') {
                    $err = '你还没注册，请先注册';
                    return $this->renderJson(compact('code', 'msg', 'err'));
                }


                //尝试发送短信验证码
                $params = array(
                    'phoneNumber' => $cellphone,
                    'codeNumber' => $this->generateRandSmsCode($cellphone),
                    'action' => $action,
                );
                $params['sign'] = $this->sign($params, FSC::$app['config']['service_3rd_api_key']);

                $api = FSC::$app['config']['service_3rd_api_domain'] . '/aliyun/sendverifycode/';
                $timeout = 10;
                $pc = false;
                $headers = array("Content-Type: application/json");
                //以json格式post数据
                $res = $this->request($api, json_encode($params), $timeout, $pc, $headers);

                if (!empty($res) && $res['status'] == 200) {
                    $resData = json_decode($res['result'], true);
                    if ($resData['code'] == 1) {
                        $code = 1;
                        $msg = '短信验证码已成功发送';
                    }else {
                        $err = '短信验证码发送失败：' . $resData['message'];
                    }
                }else {
                    $err = '短信验证码发送失败，请稍后再试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    //新用户注册
    public function actionCreateuser() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 5;   //最多 5 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';
        $shareUrl = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $friends_code = $this->post('friendscode', '');
            $cellphone = $this->post('username', '');
            $sms_code = $this->post('smscode', '');

            if (empty($friends_code) || empty($cellphone) || empty($sms_code)) {
                $err = "请填写注册邀请码、手机号码和短信验证码哦";
            }else if (Common::isCellphoneNumber($cellphone) == false) {
                $err = "手机号码格式错误，请填写正确的手机号码";
            }else if (Common::isFriendsCode($friends_code) == false) {
                $err = "邀请码格式错误，请填写邀请你的朋友的手机号码末 6 位，还可以加客服微信索取";
            }else if (Common::existFriendsCode($friends_code) == false) {
                $err = "邀请码不存在，请填写邀请你的朋友的手机号码末 6 位，或者加客服微信索取";
            }

            //验证短信验证码是否正确
            $mySmsCode = $this->getMySmsCode($cellphone);
            if (empty($mySmsCode) || $mySmsCode != $sms_code) {
                $err = "{$sms_code} 验证码已过期或错误，请检查是否输入正确";
            }

            if (empty($err)) {      //如果数据检查通过，尝试注册新用户
                $userDataDir = Common::getUserDataDir($cellphone);
                if (empty($userDataDir)) {
                    $newUser = Common::saveUserIntoSession($cellphone, $friends_code);
                    if (!empty($newUser)) {
                        Common::saveFriendsCode($cellphone);
                        Common::initUserData($cellphone, $friends_code);

                        $shareUrl = "/{$newUser['username']}/";
                        $msg = "注册完成，开始收藏你喜欢的视频吧，正在为你跳转到专属网址...";
                        $code = 1;
                    }else {
                        $err = '注册失败，请稍后再试';
                    }
                }else {
                    $username = Common::getMappedUsername($cellphone);
                    $shareUrl = "/{$username}/";
                    $msg = "已注册，正在为你跳转到专属网址...";
                    $code = 1;
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'shareUrl'));
    }

    //用户登录
    public function actionLoginuser() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 5;   //最多 5 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';
        $shareUrl = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $cellphone = $this->post('username', '');
            $sms_code = $this->post('smscode', '');

            if (empty($cellphone) || empty($sms_code)) {
                $err = "请填写注册邀请码、手机号码和短信验证码哦";
            }else if (Common::isCellphoneNumber($cellphone) == false) {
                $err = "手机号码格式错误，请填写正确的手机号码";
            }else if (Common::getUserDataDir($cellphone) == false) {
                $err = "{$cellphone}还没注册哦，先去注册吧";
            }

            //验证短信验证码是否正确
            $mySmsCode = $this->getMySmsCode($cellphone);
            if (empty($mySmsCode) || $mySmsCode != $sms_code) {
                $err = "{$sms_code} 验证码已过期或错误，请检查是否输入正确";
            }

            if (empty($err)) {      //如果数据检查通过，尝试登录
                $newUser = Common::saveUserIntoSession($cellphone);
                if (!empty($newUser)) {
                    $shareUrl = "/{$newUser['username']}/";

                    $msg = "登录成功，开始收藏你喜欢的视频吧";
                    $code = 1;
                }else {
                    $err = '登录失败，请稍后重试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'shareUrl'));
    }

    //昵称设置
    public function actionSetnickname() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 5;   //最多 5 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许修改自己的昵称
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }


        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $nickname = $this->post('nickname', '');

            if (empty($nickname)) {
                $err = "请填写注册你的昵称";
            }else {
                $nickname = Common::cleanSpecialChars($nickname);
            }

            if (mb_strlen($nickname, 'utf-8') < 2 || mb_strlen($nickname, 'utf-8') > 5) {
                $err = "昵称至少 2 个汉字，最多 5 个汉字，请按规则填写";
            }

            if (empty($err)) {      //如果数据检查通过，尝试保存
                $saved = $this->saveNickname($nickname);
                if (!empty($saved)) {
                    $msg = "昵称设置完成";
                    $code = 1;
                }else {
                    $err = '昵称设置失败，请稍后重试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    //分类管理
    public function actionSavetags() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 10;   //最多 10 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许修改自己的数据
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }


        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $tags = $this->post('tags', '');

            if (empty($tags)) {
                $err = "请至少保留一个分类";
            }else {
                $tags_ok = array();
                foreach($tags as $index => $tag) {
                    $tag = Common::cleanSpecialChars($tag);
                    if (!empty($tag) && !is_numeric($tag)) {
                        array_push($tags_ok, mb_substr($tag, 0, 15, 'utf-8'));
                    }
                }

                if (empty($tags_ok)) {
                    $err = "请按规则填写分类：2 - 15 个汉字、数字、英文字符";
                }else {
                    $tags = $tags_ok;
                }
            }

            if (empty($err)) {      //如果数据检查通过，尝试保存
                //获取已有的分类
                $scanner = new DirScanner();
                $scanner->setWebRoot(FSC::$app['config']['content_directory']);
                $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);

                $menus_sorted = array();        //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序
                $readmeFile = $scanner->getDefaultReadme();
                if (!empty($readmeFile)) {
                    if (!empty($readmeFile['sort'])) {
                        $menus_sorted = explode("\n", $readmeFile['sort']);
                    }
                }

                //获取tags分类
                $tags_current = $this->getTags($dirTree);
                //排序
                if (!empty($menus_sorted) && !empty($tags_current)) {
                    $tags_current = $this->sortTags($menus_sorted, $tags_current);
                }
                //获取只包含分类名的数组
                $allTags = Html::getTagNames($tags_current);

                //保存
                $saved = $this->saveTags($tags, $allTags);
                if (!empty($saved)) {
                    $msg = "分类已保存";
                    $code = 1;
                }else {
                    $err = '分类保存失败，请稍后重试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    //删除分类
    public function actionDeletetag() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 10;   //最多 10 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许修改自己的数据
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }


        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $tag_to_delete = $this->post('tag', '');

            if (empty($tag_to_delete)) {
                $err = "参数错误，缺少tag传参";
            }else {
                $tag_to_delete = Common::cleanSpecialChars($tag_to_delete);
            }

            if (empty($err)) {      //如果数据检查通过，尝试保存
                //保存
                $saved = $this->deleteTag($tag_to_delete);
                if (!empty($saved)) {
                    $msg = "分类已删除";
                    $code = 1;
                }else {
                    $err = '分类删除失败，请稍后重试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    //添加分类
    public function actionAddtag() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 15;   //最多 15 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许修改自己的数据
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }


        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $tag_to_add = $this->post('tag', '');

            if (empty($tag_to_add)) {
                $err = "参数错误，缺少tag传参";
            }else {
                $tag_to_add = Common::cleanSpecialChars($tag_to_add);
                $tagLen = mb_strlen($tag_to_add, 'utf-8');
                if ($tagLen < 2 || $tagLen > 15) {
                    $err = '分类名长度不符合规则，请填写 2 - 15 个汉字、数字、英文字符';
                }
            }

            if (empty($err)) {      //如果数据检查通过，尝试保存
                //获取已有的分类
                $scanner = new DirScanner();
                $scanner->setWebRoot(FSC::$app['config']['content_directory']);
                $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);

                //获取tags分类
                $tags_current = $this->getTags($dirTree);
                
                //获取只包含分类名的数组
                $allTags = Html::getTagNames($tags_current);
                
                //最多添加 20 个分类
                if (count($allTags) >= 20) {
                    $err = '最多添加 20 个分类，请合理规划视频分类哦';
                }else {
                    //保存
                    $saved = $this->addTag(ucfirst($tag_to_add));
                    if (!empty($saved)) {
                        $msg = "分类已添加";
                        $code = 1;
                    }else {
                        $err = '添加分类失败，请稍后重试';
                    }
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    //视频管理：把视频从分类中删除、添加视频到某个分类、删除视频
    public function actionDeletefav() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 60;   //最多 60 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许修改自己的数据
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }


        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $video_id = $this->post('id', '');
            $video_filename = $this->post('filename', '');

            if (empty($video_id) || empty($video_filename)) {
                $err = "参数错误，缺少id和filename传参";
            }else {
                $video_id = Common::cleanSpecialChars($video_id);
                $video_filename = Common::cleanSpecialChars($video_filename);
            }

            if (empty($err)) {      //如果数据检查通过，尝试保存
                //获取已有的分类
                $scanner = new DirScanner();
                $scanner->setWebRoot(FSC::$app['config']['content_directory']);
                $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);

                //获取tags分类
                $tags_current = $this->getTags($dirTree);
                
                //获取当前视频的所属分类数组
                $myTags = Html::getFavsTags($video_filename, $tags_current);
                foreach ($myTags as $item) {
                    $this->deleteVideoFromTag($video_filename, $item);        //从分类中删除此视频
                }

                //删除此视频的所有文件
                $saved = $this->deleteVideoFiles($video_filename);
                if (!empty($saved)) {
                    $msg = "视频已删除";
                    $code = 1;
                }else {
                    $err = '视频删除失败，请稍后重试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    public function actionUpdatefavstag() {
        $ip = $this->getUserIp();
        $check_time = 120;          //2 分钟内
        $max_time_in_minutes = 60;   //最多 60 次

        $isUserGotRequestLimit = $this->requestLimit($ip, $max_time_in_minutes, $check_time);
        if ($isUserGotRequestLimit) {
            $this->logError("Request limit got, ip: {$ip}");
            throw new Exception('Oops，操作太快了，请喝杯咖啡休息会吧...');
        }

        //只允许修改自己的数据
        $loginedUser = Common::getUserFromSession();
        if (empty($loginedUser['username'])) {
            throw new Exception('Oops，你还没登录哦');
        }else if (
            !empty(FSC::$app['config']['multipleUserUriParse'])
            && (empty(FSC::$app['user_id']) || FSC::$app['user_id'] != $loginedUser['username'])
        ) {
            throw new Exception('Oops，请求地址有误');
        }


        //返回给视图的变量
        $code = 0;
        $msg = '';
        $err = '';

        //用户提交的数据检查
        $postParams = $this->post();
        if (!empty($postParams)) {
            $video_id = $this->post('id', '');
            $video_filename = $this->post('filename', '');
            $tagName = $this->post('tag', '');
            $action = $this->post('do', 'remove');      //添加：add，移除：remove

            if (empty($video_id) || empty($video_filename) || empty($tagName)) {
                $err = "参数错误，缺少id、filename、tag传参";
            }else {
                $video_id = Common::cleanSpecialChars($video_id);
                $video_filename = Common::cleanSpecialChars($video_filename);
                $tagName = Common::cleanSpecialChars($tagName);
            }

            if (empty($err)) {      //如果数据检查通过，尝试保存
                $saved = false;

                if ($action == 'add') {
                    $saved = $this->saveVideoToTag($video_filename, $tagName);                    //添加视频到分类
                }else {
                    $saved = $this->deleteVideoFromTag($video_filename, $tagName);          //从分类中删除此视频
                }

                if ($saved !== false) {
                    $msg = "操作完成";
                    $code = 1;
                }else {
                    $err = '操作失败，请稍后重试';
                }
            }
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }


}
