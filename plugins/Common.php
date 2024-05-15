<?php
/**
 * 常用的公用方法
 */
Class Common {
    public static function cleanSpecialChars($str) {
        $findChars = array(
            '"',
            "'",
            '&',
            '<',
            '>',
            '\/',
            ' ',
            ';',
            '；',
            '.',
            '%',
            ':',
        );

        return str_replace($findChars, '', $str);
    }

	public static function isCellphoneNumber($number) {
        return preg_match("/^1[3456789][0-9]{9}$/", $number);
    }

    //朋友手机号码的末 6 位
    public static function isFriendsCode($number) {
        return preg_match("/^[0-9]{6}$/", $number);
    }

    //用户注册成功后，保存他的手机号码 6 位尾号作为邀请码
    public static function saveFriendsCode($cellphone, $friends_code) {
        $logTime = date('Y-m-d H:i:s');
        $logDir = __DIR__ . '/../runtime/friendscode/';
        $logFilename = substr($cellphone, -6) . '.log';
        $logOk = @error_log("{$logTime} created by {$cellphone}\n", 3, "{$logDir}{$logFilename}");
        if (!$logOk) {      //try to mkdir
            @mkdir($logDir, 0700, true);
            @error_log("{$logTime} created by {$cellphone}\n", 3, "{$logDir}{$logFilename}");
        }

        //保存邀请记录
        $friendsLogfile = "{$friends_code}.log";
        $logOk = @error_log("{$logTime} invite {$cellphone}\n", 3, "{$logDir}{$friendsLogfile}");
    }

    //初始化用户数据目录
    public static function initUserData($cellphone, $friends_code = '') {
        $userDir = self::getUserDataDir($cellphone);
        if (!empty($userDir)) {
            return true;
        }

        try {
            $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
            $username = self::getMappedUsername($cellphone);
            $userDir = "{$rootDir}{$username}";
            mkdir("{$userDir}/data/", 0755, true);      //分享视频目录
            if (!is_dir("{$userDir}/data/")) {
                throw new Exception("创建用户数据目录失败，请检查目录 www/" . FSC::$app['config']['content_directory'] . " 权限配置，允许PHP写入");
            }

            mkdir("{$userDir}/tags/", 0700, true);      //分类目录
            copy("{$rootDir}README.md", "{$userDir}/README.md");
            copy("{$rootDir}README_title.txt", "{$userDir}/README_title.txt");

            if (!empty($friends_code)) {
                file_put_contents("{$userDir}/README_friendscode.txt", $friends_code);
            }

            file_put_contents("{$userDir}/README_cellphone.txt", $cellphone);
        }catch(Exception $e) {
            throw new Exception("创建用户数据目录失败：" . $e->getMessage());
        }

        return true;
    }

    //根据手机号码获取用户名ID
    //规则：前6位对 97 求余数，再拼接后5位
    public static function getUserId($cellphone){
        $user_id = $cellphone;

        $prefix = substr($cellphone, 0, 6);
        $prefix = str_pad( (int)$prefix % 97, 2, '0', STR_PAD_LEFT);
        $suffix = substr($cellphone, -5);

        return "{$prefix}{$suffix}";
    }

    //根据手机号码获取映射的用户名
    public static function getMappedUsername($cellphone){
        $username = $cellphone;

        if (!empty(FSC::$app['config']['tajia_user_map']) && !empty(FSC::$app['config']['tajia_user_map'][$username])) {
            $username = FSC::$app['config']['tajia_user_map'][$username];
        }else {
            $username = self::getUserId($cellphone);
        }

        return $username;
    }

    //判断用户数据目录是否存在
    public static function getUserDataDir($cellphone) {
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];

        $username = self::getMappedUsername($cellphone);
        $userDir = "{$rootDir}{$username}";
        return is_dir($userDir) ? $userDir : false;
    }

    //判断当前用户数据目录是否存在
    public static function existCurrentUser() {
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];

        return is_dir($rootDir);
    }

    //检查朋友的邀请码是否存在
    public static function existFriendsCode($code) {
        if (self::isFriendsCode($code) == false) {return false;}

        if (!empty(FSC::$app['config']['default_friends_code']) && $code == FSC::$app['config']['default_friends_code']) {
            return true;
        }

        $logDir = __DIR__ . '/../runtime/friendscode/';
        $logFilename = "{$logDir}{$code}.log";
        return file_exists($logFilename);
    }

    //用户注册或登录成功时保存用户信息到session
    //login_time, username, friends_code
    //增加账号映射支持，配置项：tajia_user_map
    public static function saveUserIntoSession($cellphone, $friends_code = '') {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $login_time = time();
        $username = self::getMappedUsername($cellphone);

        if (empty($friends_code) && !empty($_COOKIE['friends_code'])) {
            $friends_code = $_COOKIE['friends_code'];
        }

        $_SESSION['login_time'] = $login_time;
        $_SESSION['username'] = $username;
        $_SESSION['friends_code'] = $friends_code;

        //cookie保存 1 年
        if (!empty($friends_code)) {
            setcookie('friends_code', $friends_code, $login_time + 86400*365, '/');
        }

        return compact('login_time', 'username', 'friends_code');
    }

    //从session里获取用户数据
    public static function getUserFromSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $login_time = !empty($_SESSION['login_time']) ? $_SESSION['login_time'] : 0;
        $username = !empty($_SESSION['username']) ? $_SESSION['username'] : '';
        $friends_code = !empty($_SESSION['friends_code']) ? $_SESSION['friends_code'] : '';

        //尝试从cookie中获取
        if (empty($friends_code) && !empty($_COOKIE['friends_code'])) {
            $friends_code = $_COOKIE['friends_code'];
        }

        return compact('login_time', 'username', 'friends_code');
    }

    public static function logoutUserFromSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return session_destroy();
    }

}