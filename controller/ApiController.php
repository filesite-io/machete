<?php
/**
 * Api Controller
 */
require_once __DIR__ . '/../lib/DirScanner.php';

//引入验证码类
require_once __DIR__ . '/../plugins/Captcha/vendor/autoload.php';
use Gregwar\Captcha\CaptchaBuilder;

Class ApiController extends Controller {
    protected $version = '1.0';
    protected $httpStatus = array(
        'notLogined' => 401,
        'notPurchased' => 402,
        'notAllowed' => 403,
        'notFound' => 404,
        'systemError' => 500,
    );

    //show api list
    public function actionIndex() {
        $params = array(
            'version' => $this->version,
            'list' => array(
                '验证码图片' => '/api/captcha/',
                '登陆' => '/api/login/',
                '目录/文件列表' => '/api/ls/',

                //文件操作
                '重命名' => '/api/rename/',
                '删除文件' => '/api/delete/',
                '移动目录/文件' => '/api/move/',
                'base64文件上传' => '/api/uploadbase64/',

                //目录操作
                '创建目录' => '/api/mkdir/',
                '删除目录' => '/api/rmdir/',
            ),
        );
        return $this->renderJson($params);
    }

    //判断父目录是否合法
    protected function isParentDirectoryValid($parentDir) {
        if (empty($parentDir) || strpos($parentDir, '../') !== false) {
            return false;
        }

        $valid = true;
        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'] . "/{$parentDir}";
        if (!is_dir($target)) {
            $valid = false;
        }

        return $valid;
    }

    //判断文件名是否合法，不能为空以及不能包含空白字符
    protected function isFilenameValid($filename) {
        $notAllowedLetters = array(
            '"',
            "'",
            '/',
            "\\",
            ';',
        );
        if (empty($filename) || preg_match('/\s/', $filename) || str_replace($notAllowedLetters, '', $filename) != $filename) {
            return false;
        }

        return true;
    }

    //目录、文件列表
    public function actionLs() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $scanner->isApi = true;     //realpath返回相对路径
        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        $maxLevels = FSC::$app['config']['maxScanDirLevels'];
        $dirTree = $scanner->scan($target, $maxLevels);
        $scanResults = $scanner->getScanResults();

        //获取目录
        $menus = $scanner->getMenus();
        if (empty($menus)) {
            $err = '没有任何目录/文件';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'));
        }

        //排序
        $menus_sorted = array();    //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序
        $readmeFile = $scanner->getDefaultReadme();
        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }
        }

        $sortedTree = $this->sortMenusAndDirTree($menus_sorted, $menus, $dirTree);
        if (!empty($sortedTree)) {
            $menus = $sortedTree['menus'];
            $dirTree = $sortedTree['dirTree'];
        }


        $cateId = $this->post('id', $menus[0]['id']);
        if (empty($scanResults[$cateId])) {
            $err = "目录ID {$cateId} 不存在！";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notFound']);
        }

        $data['menus'] = $menus;
        $data['dirTree'] = $scanResults[$cateId];

        $code = 1;
        $msg = '';
        $err = '';

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //创建目录
    //创建成功则在data中返回父目录数据结构
    public function actionMkdir() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }


        $parentDir = $this->post('parent', '');
        $newDir = $this->post('dir', '');
        $maxDirLen = 20;
        if (empty($newDir) || mb_strlen($newDir, 'utf-8') > $maxDirLen) {
            $err = "目录名不能为空且最长 {$maxDirLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!$this->isFilenameValid($newDir)) {
            $err = "待创建的目录名称中不能包含空格、单双引号、斜杠和分号字符！";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        if (!empty($parentDir)) {
            $target = "{$target}/{$parentDir}";

            //父目录合法性检查
            if ($this->isParentDirectoryValid($parentDir) == false) {
                $err = "父目录{$parentDir}不存在";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }
        }

        try {
            $res = mkdir("{$target}/{$newDir}");
            if ($res) {
                $code = 1;
                $msg = '目录创建完成';
            }else {
                $err = '目录创建失败，请确认参数格式正确及父目录权限配置正确！';
            }
        }catch(Exception $e) {
            $err = $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //删除目录
    public function actionRmdir() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }


        $parentDir = $this->post('parent', '');
        $delDir = $this->post('dir', '');
        $maxDirLen = 20;
        if (empty($delDir) || mb_strlen($delDir, 'utf-8') > $maxDirLen) {
            $err = "目录名不能为空且最长 {$maxDirLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (strpos($delDir, '/') !== false) {
            $err = "待删除的目录名称中不能包含斜杠字符！";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        if (!empty($parentDir)) {
            $target = "{$target}/{$parentDir}";

            //父目录合法性检查
            if ($this->isParentDirectoryValid($parentDir) == false) {
                $err = "父目录{$parentDir}不存在";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }
        }

        try {
            $res = rmdir("{$target}/{$delDir}");
            if ($res) {
                $code = 1;
                $msg = '目录删除完成';
            }else {
                $err = '目录删除失败，请确认被删除目录存在及父目录权限配置正确！';
            }
        }catch(Exception $e) {
            $err = $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //移动目录或文件
    public function actionMove() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }

        $fromDir = $this->post('from', '');
        $toDir = $this->post('to', '');
        $maxDirLen = 50;
        if (empty($fromDir) || mb_strlen($fromDir, 'utf-8') > $maxDirLen || empty($toDir) || mb_strlen($toDir, 'utf-8') > $maxDirLen) {
            $err = "目录名不能为空且最长 {$maxDirLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if ($this->isParentDirectoryValid($fromDir) == false) {       //目录合法性检查
            $err = "目录{$fromDir}不存在";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if ($this->isParentDirectoryValid($toDir) == false) {         //目录合法性检查
            $err = "目录{$toDir}不存在";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        try {
            $res = rename("{$target}/{$fromDir}", "{$target}/{$toDir}");
            if ($res) {
                $code = 1;
                $msg = '目录/文件移动完成';
            }else {
                $err = '目录/文件移动失败，请确认被移动目录/文件存在及目标目录权限配置正确！';
            }
        }catch(Exception $e) {
            $err = $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //重命名目录或文件
    public function actionRename() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }


        $parentDir = $this->post('parent', '');
        $fromDir = $this->post('from', '');
        $toDir = $this->post('to', '');
        $maxDirLen = 20;
        if (empty($fromDir) || mb_strlen($fromDir, 'utf-8') > $maxDirLen || empty($toDir) || mb_strlen($toDir, 'utf-8') > $maxDirLen) {
            $err = "目录名不能为空且最长 {$maxDirLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'));
        }else if (!$this->isFilenameValid($fromDir) || !$this->isFilenameValid($toDir)) {
            $err = "目录名称中不能包含空格、单双引号、斜杠和分号字符！";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        if (!empty($parentDir)) {
            $target = "{$target}/{$parentDir}";

            //父目录合法性检查
            if ($this->isParentDirectoryValid($parentDir) == false) {
                $err = "父目录{$parentDir}不存在";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }
        }

        try {
            $res = rename("{$target}/{$fromDir}", "{$target}/{$toDir}");
            if ($res) {
                $code = 1;
                $msg = '目录移动完成';
            }else {
                $err = '目录移动失败，请确认被移动目录存在及目标目录权限配置正确！';
            }
        }catch(Exception $e) {
            $err = $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //删除文件
    public function actionDelete() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }


        $parentDir = $this->post('parent', '');
        $delFile = $this->post('file', '');
        $maxDirLen = 30;
        if (empty($delFile) || mb_strlen($delFile, 'utf-8') > $maxDirLen) {
            $err = "文件名不能为空且最长 {$maxDirLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!$this->isFilenameValid($delFile)) {
            $err = "待删除的文件名称中不能包含空格、单双引号、斜杠和分号字符！";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        if (!empty($parentDir)) {
            $target = "{$target}/{$parentDir}";

            //父目录合法性检查
            if ($this->isParentDirectoryValid($parentDir) == false) {
                $err = "父目录{$parentDir}不存在";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }
        }

        try {
            $res = unlink("{$target}/{$delFile}");
            if ($res) {
                $code = 1;
                $msg = '文件删除完成';
            }else {
                $err = '文件删除失败，请确认被删除文件存在及父目录权限配置正确！';
            }
        }catch(Exception $e) {
            $err = $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //验证码图片，data属性里返回图片base64编码格式
    public function actionCaptcha() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        $refresh = (int)$this->post('refresh', 0);

        try {
            $builder = new CaptchaBuilder;
            $builder->build();
            $captcha_jpg = $builder->get();
            $captcha_code = $builder->getPhrase();
            $data = 'data:image/jpeg;base64,' . base64_encode($captcha_jpg);
            $code = 1;
            $msg = '验证码图片已生成';

            //save captcha code
            $userData = $this->getAdmUserData();
            $userData['captcha_code'] = $captcha_code;
            $this->saveAdmUserData($userData);
        }catch(Exception $e) {
            $err = '验证码图片生成失败：' . $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //从runtime/admin/目录里获取管理员当前ip相关的缓存数据
    protected function getAdmUserData() {
        $data = array();

        $ip = $this->getUserIp();
        $logDir = __DIR__ . '/../runtime/admin/';
        $logFile = "{$logDir}" . md5(FSC::$app['config']['md5Prefix'] . $ip) . ".cache";

        try {
            if (file_exists($logFile)) {
                $data = @json_decode(file_get_contents($logFile), true);
            }
        }catch(Exception $e) {}

        return $data;
    }

    protected function saveAdmUserData($data) {
        $ip = $this->getUserIp();
        $logDir = __DIR__ . '/../runtime/admin/';
        $logFile = "{$logDir}" . md5(FSC::$app['config']['md5Prefix'] . $ip) . ".cache";
        if (!is_dir($logDir)) {      //try to mkdir
            @mkdir($logDir, 0700, true);
        }

        return @file_put_contents($logFile, json_encode($data));
    }

    //登陆
    public function actionLogin() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        $username = $this->post('username', '');
        $password = $this->post('password', '');
        $captcha = $this->post('captcha', '');
        $maxUsernameLen = 20;
        $maxPasswordLen = 30;
        if (empty($username) || mb_strlen($username, 'utf-8') > $maxUsernameLen) {
            $err = "用户名不能为空且最长 {$maxUsernameLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (empty($password) || mb_strlen($password, 'utf-8') > $maxPasswordLen) {
            $err = "密码不能为空且最长 {$maxPasswordLen} 个字符";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        $admConfig = FSC::$app['config']['admin'];

        try {
            //get captcha code
            $userData = $this->getAdmUserData();
            $captcha_code = !empty($userData['captcha_code']) ? $userData['captcha_code'] : '';
            if (!empty($admConfig['captcha']) && empty($captcha_code)) {
                $err = "请刷新网页，如果验证码图片无法显示请联系管理员！";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }else if (!empty($admConfig['captcha']) && !empty($captcha_code) && $captcha != $captcha_code) {
                $err = "验证码不正确，请注意字母大小写！";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }

            if ($username == $admConfig['username'] && $password == $admConfig['password']) {
                //保存登陆成功信息
                $userData['login_user'] = $username;
                $userData['login_time'] = time();
                $this->saveAdmUserData($userData);

                $code = 1;
                $msg = '登陆成功。';
            }else {
                $err = "用户名或密码错误，请注意字母大小写！";
            }
        }catch(Exception $e) {
            $err = '登陆失败：' . $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    protected function isUserLogined() {
        $logined = false;

        try {
            $admConfig = FSC::$app['config']['admin'];

            //get user data
            $userData = $this->getAdmUserData();
            if (!empty($userData) && $userData['login_user'] == $admConfig['username']) {
                $logined = true;
            }
        }catch(Exception $e) {
        }

        return $logined;
    }

    //保存base64格式的文件
    //@return
    //-1 文件大小超出限制
    //0 保存失败
    //1 保存成功
    protected function saveBase64File($base64FileContent, $filePath) {
        $saved = 1;

        try {
            $base64 = preg_replace('/^data:[a-z0-9]+\/[a-z0-9]+;base64,/i', '', $base64FileContent);
            $base64 = str_replace(' ', '+', $base64);
            $fileContent = base64_decode($base64);
            file_put_contents($filePath, $fileContent);

            //判断文件大小
            $maxLength = FSC::$app['config']['admin']['maxUploadFileSize'] * 1024*1024;
            $filesize = filesize($filePath);
            if ($filesize > $maxLength) {
                unlink($filePath);
                $saved = -1;
            }
        }catch(Exception $e) {
            $saved = 0;
        }

        return $saved;
    }

    //在指定目录里创建一个新文件
    //@fileType 示例：image/jpeg, video/mp4
    protected function createNewFile($parentDir, $filename) {
        $target = __DIR__ . '/../www/' . FSC::$app['config']['content_directory'];
        return !empty($parentDir) ? "{$target}{$parentDir}/{$filename}" : "{$target}{$filename}";
    }

    //从文件名中解析文件后缀
    protected function getSuffixFromFilename($filename) {
        $arr = explode('.', $filename);
        if (count($arr) < 2) {
            return '';
        }

        return strtolower(array_pop($arr));
    }

    //从文件类型中解析文件后缀
    protected function getSuffixFromFileType($fileType) {
        $arr = explode('/', $fileType);
        if (count($arr) < 2) {
            return '';
        }

        $suffix = array_pop($arr);
        if (in_array($suffix, ['jpg', 'jpeg'])) {
            $suffix = 'jpg';
        }

        return strtolower($suffix);
    }

    //base64格式文件上传
    //@parent - 可选：文件保存目录，默认保存到根目录
    //@file - 单个文件base64内容
    //@name - 单个文件文件名
    public function actionUploadBase64() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }

        //参数检查
        $parentDir = $this->post('parent', '');
        $upfile = $this->post('file', '');
        $filename = $this->post('name', '');
        if (empty($upfile) || empty($filename)) {
            $err = '所有参数都不能为空！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!$this->isFilenameValid($filename)) {
            $err = '文件名不能包含空格、单双引号、斜杠和分号字符！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!preg_match('/^data:[a-z0-9]+\/[a-z0-9]+;base64,/i', $upfile)) {
            $err = '图片数据必需为base64格式！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!empty($parentDir) && $this->isParentDirectoryValid($parentDir) == false) {  //父目录合法性检查
            $err = "父目录{$parentDir}不存在";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        //base64格式数据支持
        $configs = FSC::$app['config']['admin'];
        try {
            preg_match('/^data:([a-z0-9]+\/[a-z0-9]+);base64,/i', $upfile, $matches);
            if (!empty($matches[1])) {
                $fileType = strtolower($matches[1]);
                if (strpos($filename, '.') === false) {$filename .= "." . $this->getSuffixFromFileType($fileType);}

                if (!in_array($fileType, $configs['allowedUploadFileTypes'])) {
                    $err = "不支持的文件格式：{$fileType}";
                    return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
                }else if ($this->getSuffixFromFilename($filename) != $this->getSuffixFromFileType($fileType)) {
                    $err = "文件格式和文件名后缀不匹配，请检查文件名后缀";
                    return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
                }

                $filePath = $this->createNewFile($parentDir, $filename);
                $saved = $this->saveBase64File($upfile, $filePath);
                if ($saved == 1) {
                    $code = 1;
                    $msg = '上传完成';
                }else if ($saved == -1) {
                    $maxSize = FSC::$app['config']['admin']['maxUploadFileSize'];
                    $err = "文件超出 {$maxSize}M 大小限制！";
                }else {
                    $err = '上传失败，请检查数据目录权限配置！';
                }
            }else {
                $err = "文件数据不是base64格式";
                return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
            }
        }catch(Exception $e) {
            $err = $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    //切换皮肤
    public function actionSwitchTheme() {
        $code = 0;
        $msg = $err = '';
        $data = array();

        if ($this->isUserLogined() == false) {
            $err = '没登陆或登陆已过期！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notLogined']);
        }

        $themeName = $this->post('theme', '');
        $contentDirectory = $this->post('contentdir', '');
        $allowedThemes = FSC::$app['config']['allowedThemes'];
        if (empty($themeName)) {
            $err = '参数不能为空！';
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!in_array($themeName, $allowedThemes)) {
            $err = "不支持的皮肤：{$themeName}";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }else if (!empty($contentDirectory) && strpos($contentDirectory, '/') !== false) {
            $err = "内容目录名称中不能包含斜杠字符！";
            return $this->renderJson(compact('code', 'msg', 'err', 'data'), $this->httpStatus['notAllowed']);
        }

        try {
            $customConfigFile = __DIR__ . '/../runtime/custom_config.json';
            $jsonData = array(
                'theme' => $themeName,
            );

            if (!empty($contentDirectory)) {
                $jsonData['content_directory'] = $contentDirectory;
            }

            if (file_exists($customConfigFile)) {
                $json = file_get_contents($customConfigFile);
                $customConfigs = json_decode($json, true);
                if (!empty($customConfigs)) {
                    $jsonData = array_merge($customConfigs, $jsonData);
                }
            }

            file_put_contents($customConfigFile, json_encode($jsonData));
            $code = 1;
            $msg = '皮肤修改完成';
        }catch(Exception $e) {
            $err = '皮肤修改失败：' . $e->getMessage();
        }

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

}
