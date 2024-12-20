<?php
/**
 * 常用的公用方法
 */
Class Common {
    public static $cache = array();

    public static function cleanSpecialChars($str) {
        if (empty($str)) {return $str;}

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
                $map = json_decode($mapContent, true);
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

    //保存数据到文件缓存
    //缓存数据格式：{ctime: timestamp, data: anything}
    public static function saveCacheToFile($key, $data, $cacheSubDir = '') {
        $cacheData = array(
            "ctime" => time(),
            "data" => $data,
        );
        $jsonData = json_encode($cacheData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cacheDir = __DIR__ . '/../runtime/cache/';

        //子目录支持
        if (!empty($cacheSubDir)) {
            $cacheDir .= preg_match('/\/$/', $cacheSubDir) ? $cacheSubDir : "{$cacheSubDir}/";
        }

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cache_filename = "{$cacheDir}{$key}.json";
        return file_put_contents($cache_filename, $jsonData);
    }

    //从文件缓存读取数据
    //expireSeconds: 缓存失效时间，默认10分钟
    public static function getCacheFromFile($key, $expireSeconds = 600, $cacheSubDir = '', $withCreateTime = false) {
        $cacheDir = __DIR__ . '/../runtime/cache/';
        //子目录支持
        if (!empty($cacheSubDir)) {
            $cacheDir .= preg_match('/\/$/', $cacheSubDir) ? $cacheSubDir : "{$cacheSubDir}/";
        }
        $cache_filename = "{$cacheDir}{$key}.json";

        if (file_exists($cache_filename)) {
            try {
                $jsonData = file_get_contents($cache_filename);
                $data = json_decode($jsonData, true);

                //如果缓存没有失效
                $now = time();
                if ($now - $data['ctime'] <= $expireSeconds) {
                    return empty($withCreateTime) ? $data['data'] : $data;
                }else {
                    return null;
                }
            }catch(Exception $e) {
                return false;
            }
        }

        return false;
    }

    //删除缓存文件
    public static function cleanFileCache($key, $cacheSubDir = '') {
        $cacheDir = __DIR__ . '/../runtime/cache/';
        //子目录支持
        if (!empty($cacheSubDir)) {
            $cacheDir .= preg_match('/\/$/', $cacheSubDir) ? $cacheSubDir : "{$cacheSubDir}/";
        }
        $cache_filename = "{$cacheDir}{$key}.json";

        if (file_exists($cache_filename)) {
            return unlink($cache_filename);
        }

        return false;
    }

    //从字符串中解析时间戳、日期，返回Y-m-d格式的日期字符串
    public static function getDateFromString($str) {
        $date = '';

        try {
            preg_match('/^.*((?:19|20|21)\d{2}[01][0-9][0123]\d).*$/U', $str, $matches);        //尝试Y-m-d格式的日期
            if (empty($matches[1])) {                                                           //再尝试单位秒的时间戳
                preg_match('/^.*(\d{10}).*$/U', $str, $matches);
                if (empty($matches[1])) {
                    preg_match('/^.*(\d{13}).*$/U', $str, $matches);                            //单位毫秒的时间戳
                    if (!empty($matches[1])) {
                        $date = date('Y-m-d', (int)$matches[1] / 1000);
                    }
                }else {
                    $date = date('Y-m-d', (int)$matches[1]);
                }
            }else {
                $date = date('Y-m-d', strtotime($matches[1]));
            }

        }catch(Exception $e) {}

        return $date;
    }

    //从session里获取密码授权身份
    public static function getPwdAuthDirsFromSession() {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return !empty($_SESSION['auth_dirs']) ? $_SESSION['auth_dirs'] : array();
    }

    //保存已通过密码授权的目录
    public static function savePwdAuthDirToSession($dir) {
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $authDirs = !empty($_SESSION['auth_dirs']) ? $_SESSION['auth_dirs'] : array();
        if (!in_array($dir, $authDirs)) {
            array_push($authDirs, $dir);

            $_SESSION['auth_dirs'] = $authDirs;
        }

        return $authDirs;
    }

    //判断当前文件是否允许访问
    public static function isUserAllowedToFile($filepath) {
        if( empty(FSC::$app['config']['password_auth']) ) {
            return true;
        }

        $authConfig = FSC::$app['config']['password_auth'];
        if (empty($authConfig['enable']) || $authConfig['enable'] === 'false') {
            return true;
        }

        $allowed = true;

        $filepath = preg_replace('/\/[^\/]+$/', '', $filepath);
        $filepath = trim($filepath, '/');
        $arr = explode('/', $filepath);
        if (!empty($arr)) {
            foreach($arr as $dir) {
                $allowed = self::isUserAllowedToDir($dir);
                if (!$allowed) {
                    break;
                }
            }
        }

        return $allowed;
    }

    //判断当前目录是否允许访问
    public static function isUserAllowedToDir($dir) {
        if( empty(FSC::$app['config']['password_auth']) ) {
            return true;
        }

        $authConfig = FSC::$app['config']['password_auth'];
        if (empty($authConfig['enable']) || $authConfig['enable'] === 'false') {
            return true;
        }

        $allowed = true;
        $authDirs = self::getPwdAuthDirsFromSession();
        if (!empty($authConfig['default']) && empty($authConfig['allow'][$dir]) && !in_array('default', $authDirs)) {
            //所有目录都需要授权，且没有单独配置此目录需要密码
            $allowed = false;
        }else if (!empty($authConfig['allow'][$dir]) && !in_array($dir, $authDirs)) {
            //当前目录需要授权
            $allowed = false;
        }

        return $allowed;
    }

    //密码授权检查，如果密码正确，则增加目录到已授权列表
    public static function pwdAuthToDir($dir, $userPassword) {
        if( empty(FSC::$app['config']['password_auth']) ) {
            return true;
        }

        $authConfig = FSC::$app['config']['password_auth'];
        if (empty($authConfig['enable']) || $authConfig['enable'] === 'false') {
            return true;
        }

        $authed = false;
        $authDirs = self::getPwdAuthDirsFromSession();
        if (!empty($authConfig['default']) && empty($authConfig['allow'][$dir]) && $userPassword == $authConfig['default']) {
            self::savePwdAuthDirToSession($dir);
            $authed = true;
        }else if (empty($authConfig['default']) && !empty($authConfig['allow'][$dir]) && $authConfig['allow'][$dir] == $userPassword) {
            self::savePwdAuthDirToSession($dir);
            $authed = true;
        }

        return $authed;
    }

    //判断当前用户IP是否拥有管理权限
    public static function isAdminIp($ip) {
        $admin = false;

        $localhostIps = array(
            '127.0.0.1',
            '172.17.0.1',
            'localhost',
        );

        if ( !empty(FSC::$app['config']['adminForLanIps']) && (
                preg_match("/^(10|172\.16|192\.168)\./", $ip)
                ||
                in_array($ip, $localhostIps)
            )
        ) {
            $admin = true;
        }else if (!empty(FSC::$app['config']['adminWhiteIps']) && in_array($ip, FSC::$app['config']['adminWhiteIps'])) {
            $admin = true;
        }

        return $admin;
    }

    //根据指定的数组元素值对数组进行排序
    public static function sortArrayByValue($array, $keyName, $sortOrder = 'asc') {
        if (empty($array) || count($array) == 0) {return $array;}

        $sorted = $array;

        $tmp = [];
        foreach ($array as $index => $item) {
            $tmp[$item[$keyName]] = $index;
        }

        if ($sortOrder == 'asc') {
            ksort($tmp);
        }else {
            krsort($tmp);
        }

        $newArr = [];
        foreach ($tmp as $key => $index) {
            $newArr[$index] = $array[$index];
        }

        return !empty($newArr) ? $newArr : $sorted;
    }

    //根据指定的数组对数组进行排序
    public static function sortArrayByFilenameList($array, $sortedArray) {
        if (empty($array) || count($array) == 0) {return $array;}

        $sorted = $array;

        $tmp = [];
        foreach ($sortedArray as $filename) {
            foreach ($array as $index => $val) {
                if (!empty($filename) && "{$val['filename']}.{$val['extension']}" == $filename) {
                    $tmp[$filename] = $index;
                    break;
                }
            }
        }

        $newArr = [];
        $sortIndexes = [];
        foreach ($tmp as $filename => $index) {
            $newArr[$index] = $array[$index];
            array_push($sortIndexes, $index);
        }

        //append others
        if (count($newArr) < count($array)) {
            foreach ($array as $index => $val) {
                if (in_array($index, $sortIndexes)) {
                    continue;
                }

                $newArr[$index] = $val;
            }
        }

        return !empty($newArr) ? $newArr : $sorted;
    }

    public static function setCache($key, $val) {
        self::$cache[$key] = $val;
    }

    public static function getCache($key) {
        return !empty(self::$cache[$key]) ? self::$cache[$key] : null;
    }

    public static function getFileCreateTime($file) {
        return !empty($file['fstat']['mtime']) && !empty($file['fstat']['ctime']) ? min($file['fstat']['mtime'], $file['fstat']['ctime']) : 0;
    }

    //根据文件id、索引分批存储数量，返回当前文件所属索引序号
    public static function getIndexNumByFileId($id, $dirNum) {
        $firstChar = substr($id, 0, 1);
        $ascNum = ord($firstChar);
        return $ascNum % $dirNum;
    }

}