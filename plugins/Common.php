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
            '/',
            '\\',
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

    //保存用户多收藏夹目录映射关系
    public static function saveUserDirMap($cellphone, $username, $new_dir) {
        $my_user_map = self::getMyDirs($cellphone, $username);
        if (!in_array($new_dir, $my_user_map)) {
            array_push($my_user_map, $new_dir);
        }else {
            return true;
        }

        $my_id = self::getUserId($cellphone);
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $rootDir = str_replace("/{$username}", "/{$my_id}", $rootDir);          //获取自己的目录
        if (!is_dir($rootDir)) {
            $my_first_id = self::getMappedUsername($cellphone);
            $rootDir = str_replace("/{$my_id}", "/{$my_first_id}", $rootDir);   //获取自己的目录
        }

        $saved = false;
        if (is_dir($rootDir)) {
            $cache_filename = "{$rootDir}/custom_config_usermap.json";
            $saved = file_put_contents($cache_filename, json_encode($my_user_map, JSON_PRETTY_PRINT));
        }

        return $saved === false ? false : true;
    }

    //获取用户共享目录记录
    public static function getMyShareDirs($cellphone, $username) {
        $my_id = self::getUserId($cellphone);
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $rootDir = str_replace("/{$username}", "/{$my_id}", $rootDir);          //获取自己的目录
        if (!is_dir($rootDir)) {
            $my_first_id = self::getMappedUsername($cellphone);
            $rootDir = str_replace("/{$my_id}", "/{$my_first_id}", $rootDir);   //获取自己的目录
        }

        $map = array();
        if (is_dir($rootDir)) {
            $cache_filename = "{$rootDir}/share_dirs.json";
            if (file_exists($cache_filename)) {
                $json = file_get_contents($cache_filename);
                $map = json_decode($json, true);
            }
        }

        return $map;
    }

    //保存用户共享目录记录
    public static function saveMyShareDirs($cellphone, $username, $friends_cellphone, $share_dir) {
        $shareDirs = self::getMyShareDirs($cellphone, $username);
        if (empty($shareDirs) || empty($shareDirs[$friends_cellphone])) {
            $shareDirs[$friends_cellphone] = array($share_dir);
        }else if(!in_array($share_dir, $shareDirs[$friends_cellphone])) {
            array_push($shareDirs[$friends_cellphone], $share_dir);
        }

        $my_id = self::getUserId($cellphone);
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $rootDir = str_replace("/{$username}", "/{$my_id}", $rootDir);          //获取自己的目录
        if (!is_dir($rootDir)) {
            $my_first_id = self::getMappedUsername($cellphone);
            $rootDir = str_replace("/{$my_id}", "/{$my_first_id}", $rootDir);   //获取自己的目录
        }

        $saved = false;
        if (is_dir($rootDir)) {
            $cache_filename = "{$rootDir}/share_dirs.json";
            $saved = file_put_contents($cache_filename, json_encode($shareDirs, JSON_PRETTY_PRINT));
        }

        return $saved === false ? false : true;
    }

    //从用户共享目录记录里删除一个共享
    public static function deleteFromMyShareDirs($cellphone, $username, $friends_cellphone, $share_dir) {
        $shareDirs = self::getMyShareDirs($cellphone, $username);
        if(!empty($shareDirs[$friends_cellphone]) && in_array($share_dir, $shareDirs[$friends_cellphone])) {
            $shareDirs[$friends_cellphone] = array_diff($shareDirs[$friends_cellphone], array($share_dir));
            $shareDirs[$friends_cellphone] = array_values($shareDirs[$friends_cellphone]);
        }else {
            return true;
        }

        $my_id = self::getUserId($cellphone);
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $rootDir = str_replace("/{$username}", "/{$my_id}", $rootDir);          //获取自己的目录
        if (!is_dir($rootDir)) {
            $my_first_id = self::getMappedUsername($cellphone);
            $rootDir = str_replace("/{$my_id}", "/{$my_first_id}", $rootDir);   //获取自己的目录
        }

        $saved = false;
        if (is_dir($rootDir)) {
            $cache_filename = "{$rootDir}/share_dirs.json";
            $saved = file_put_contents($cache_filename, json_encode($shareDirs, JSON_PRETTY_PRINT));
        }

        return $saved === false ? false : true;
    }

    //获取新收藏夹目录名
    public static function getNewFavDir($cellphone) {
        $new_dir = 2000;       //默认从编号2000开始

        $cache_filename = __DIR__ . '/../runtime/userCustomFavDirs.json';
        if (file_exists($cache_filename)) {
            $json = file_get_contents($cache_filename);
            $data = json_decode($json, true);
            if (!empty($data['dir'])) {
                $new_dir = $data['dir'] + 1;
            }
        }

        return $new_dir;
    }

    //老用户创建新的收藏夹
    public static function createNewFavDir($cellphone, $username, $new_dir, $nickname) {
        try {
            $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
            $rootDir = str_replace("/{$username}", '', $rootDir);   //获取当前收藏夹的上一级目录

            $userDir = "{$rootDir}/{$new_dir}";     //新收藏夹目录
            if (is_dir($userDir)) {     //如果已经存在
                return false;
            }

            mkdir("{$userDir}/data/", 0755, true);      //分享视频目录
            if (!is_dir("{$userDir}/data/")) {
                throw new Exception("创建用户数据目录失败，请检查目录 www/" . FSC::$app['config']['content_directory'] . " 权限配置，允许PHP写入");
            }

            mkdir("{$userDir}/tags/", 0700, true);      //分类目录
            copy("{$rootDir}README.md", "{$userDir}/README.md");
            copy("{$rootDir}README_title.txt", "{$userDir}/README_title.txt");

            if (!empty($nickname)) {
                file_put_contents("{$userDir}/README_nickname.txt", $nickname);
            }

            if (!empty($_COOKIE['friends_code'])) {
                $friends_code = $_COOKIE['friends_code'];
                file_put_contents("{$userDir}/README_friendscode.txt", $friends_code);
            }

            file_put_contents("{$userDir}/README_cellphone.txt", $cellphone);

            //用户新收藏夹创建成功后，保存最新用户创建的收藏夹记录
            $data = array(
                'dir' => $new_dir,
                'update' => time(),
                'lastUser' => $cellphone,
            );
            $cache_filename = __DIR__ . '/../runtime/userCustomFavDirs.json';
            file_put_contents($cache_filename, json_encode($data, JSON_PRETTY_PRINT));

            //保存用户手机和收藏夹映射关系
            self::saveUserDirMap($cellphone, $username, $new_dir);
        }catch(Exception $e) {
            return false;
        }

        return true;
    }

    //删除被共享的收藏夹
    public static function deleteSharedFavDir($friends_cellphone, $current_username, $share_dir) {
        //不能删除朋友自己的收藏夹
        if (self::isMyFavDir($friends_cellphone, $current_username, $share_dir)) {return false;}

        $friends_dirs = self::getMyDirs($friends_cellphone, $current_username);
        $dirs_after_delete = $friends_dirs;
        if (in_array($share_dir, $friends_dirs)) {
            $dirs_after_delete = array_diff($friends_dirs, array($share_dir));
            $dirs_after_delete = array_values($dirs_after_delete);
        }else {
            return true;
        }

        try {
            $my_id = self::getUserId($friends_cellphone);
            $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
            $rootDir = str_replace("/{$current_username}", "/{$my_id}", $rootDir);          //获取自己的目录
            if (!is_dir($rootDir)) {
                $my_first_id = self::getMappedUsername($friends_cellphone);
                $rootDir = str_replace("/{$my_id}", "/{$my_first_id}", $rootDir);   //获取自己的目录
            }

            $cache_filename = "{$rootDir}/custom_config_usermap.json";
            file_put_contents($cache_filename, json_encode($dirs_after_delete, JSON_PRETTY_PRINT));
        }catch(Exception $e) {
            return false;
        }

        return true;
    }

    //新用户注册时初始化用户数据目录
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

    //判断某个收藏夹是否属于当前用户
    public static function isMyFavDir($cellphone, $username, $fav_dir) {
        try {
            $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
            $rootDir = str_replace("/{$username}", '', $rootDir);   //获取当前收藏夹的上一级目录

            $userDir = "{$rootDir}/{$fav_dir}";     //目标收藏夹目录
            if (!is_dir($userDir)) {     //如果不存在
                return false;
            }

            $filepath = "{$userDir}/README_cellphone.txt";
            $content = file_get_contents($filepath);
            if (!empty($content) && strpos($content, $cellphone) !== false) {
                return true;
            }
        }catch(Exception $e) {
            return false;
        }

        return false;
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
    //支持数组格式，一个手机号码管理多个收藏夹
    public static function getMappedUsername($cellphone){
        $username = $cellphone;

        $user_map = FSC::$app['config']['tajian_user_map'];
        if (!empty($user_map[$cellphone])) {
            $userDirs = $user_map[$cellphone];
            if (is_string($userDirs)) {
                $username = $userDirs;
            }else if (is_array($userDirs) && !empty($userDirs)) {
                $username = $userDirs[0];
            }
        }else {
            $username = self::getUserId($cellphone);
        }

        return $username;
    }

    public static function dictToArray($dict) {
        $arr = array();

        foreach($dict as $key => $value) {
            array_push($arr, $value);
        }

        return $arr;
    }

    //从自己的目录里获取收藏夹映射关系
    //返回：数组
    public static function getMyDirs($cellphone, $username){
        $map = array();

        $my_id = self::getUserId($cellphone);
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $rootDir = str_replace("/{$username}", "/{$my_id}", $rootDir);          //获取自己的目录
        if (!is_dir($rootDir)) {
            $my_first_id = self::getMappedUsername($cellphone);
            $rootDir = str_replace("/{$my_id}", "/{$my_first_id}", $rootDir);   //获取自己的目录
        }

        if (is_dir($rootDir)) {
            $cache_filename = "{$rootDir}/custom_config_usermap.json";
            if (file_exists($cache_filename)) {
                $mapContent = file_get_contents($cache_filename);
                $map = json_decode($mapContent);
                if (is_object($map)) {
                    $map = self::dictToArray($map);
                }
            }
        }

        //跟公用配置合并
        $tajian_user_map = FSC::$app['config']['tajian_user_map'];
        if (!empty($tajian_user_map[$cellphone])) {
            $map = is_array($tajian_user_map[$cellphone]) ?
                    array_merge($map, $tajian_user_map[$cellphone]) : array_push($map, $tajian_user_map[$cellphone]);
        }

        return array_values(array_unique($map));
    }

    public static function getNicknameByDir($dir, $username){
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $dirPath = str_replace("/{$username}", "/{$dir}", $rootDir);
        $filepath = "{$dirPath}/README_nickname.txt";

        $nickname = '';
        if (file_exists($filepath)) {
            $nickname = file_get_contents($filepath);
            if (!empty($nickname)) {
                $nickname = trim($nickname);
            }
        }

        return $nickname;
    }

    //获取用户数据目录
    public static function getUserDataDir($cellphone, $currentUsername = '') {
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];

        $username = self::getMappedUsername($cellphone);
        if (!empty($currentUsername)) {
            $userDir = str_replace("/{$currentUsername}", "/{$username}", $rootDir);
        }else {
            $userDir = "{$rootDir}{$username}";
        }

        return is_dir($userDir) ? $userDir : false;
    }

    //判断用户数据目录是否存在
    public static function existUserDataDir($dir, $currentUsername = '') {
        $rootDir = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];

        if (!empty($currentUsername)) {
            $userDir = str_replace("/{$currentUsername}", "/{$dir}", $rootDir);
        }else {
            $userDir = "{$rootDir}{$dir}";
        }

        return is_dir($userDir) ? true : false;
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
    //增加账号映射支持，配置项：tajian_user_map
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
        $_SESSION['cellphone'] = $cellphone;
        $_SESSION['friends_code'] = $friends_code;

        //cookie保存 1 年
        if (!empty($friends_code)) {
            setcookie('friends_code', $friends_code, $login_time + 86400*365, '/');
        }

        return compact('login_time', 'username', 'friends_code', 'cellphone');
    }

    public static function switchUserDir($dir) {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $currentDir = $_SESSION['username'];
        FSC::$app['config']['content_directory'] = str_replace($currentDir, $dir, FSC::$app['config']['content_directory']);

        $_SESSION['username'] = $dir;

        return $_SESSION['username'];
    }

    //从session里获取用户数据
    public static function getUserFromSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $login_time = !empty($_SESSION['login_time']) ? $_SESSION['login_time'] : 0;
        $username = !empty($_SESSION['username']) ? $_SESSION['username'] : '';
        $cellphone = !empty($_SESSION['cellphone']) ? $_SESSION['cellphone'] : '';
        $friends_code = !empty($_SESSION['friends_code']) ? $_SESSION['friends_code'] : '';

        //尝试从cookie中获取
        if (empty($friends_code) && !empty($_COOKIE['friends_code'])) {
            $friends_code = $_COOKIE['friends_code'];
        }

        return compact('login_time', 'username', 'friends_code', 'cellphone');
    }

    public static function isVipUser($loginedUser) {
        $vipUsers = FSC::$app['config']['tajian_vip_user'];
        if (empty($vipUsers)) {return false;}

        return !empty($loginedUser['cellphone']) && in_array($loginedUser['cellphone'], $vipUsers);
    }

    public static function logoutUserFromSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return session_destroy();
    }

    public static function getShareUrlFromContent($content) {
        $url = '';

        preg_match("/http(s)?:\/\/[\w\-\.]+\.([a-z]){2,}[\/\w\-\.\?\=]*/i", $content, $matches);
        if (!empty($matches)) {
            $url = $matches[0];
        }

        return $url;
    }

    public static function maskCellphone($cellphone) {
        return preg_replace("/^(.{3,})\d{4}(.{4})$/i", '$1****$2', $cellphone);
    }

}