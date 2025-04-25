<?php
/**
 * Class DirScanner
 */

Class DirScanner {
    private $nginxSecureOn = false;                     //Nginx防盗链开启状态
    private $nginxSecret = 'foo=bar';                   //Nginx防盗链密钥
    private $userIp = '127.0.0.1';                      //用户IP地址
    private $nginxSecureTimeout = 1800;                 //Nginx防盗链有效期，单位：秒
    private $nginxSecureLinkMd5Pattern = '{secure_link_expires}{uri}{remote_addr} {secret}';         //Nginx防盗链MD5加密方式
    private $allowReadContentFileExtensions = array(     //允许读取文件内容的文件类型
        'txt',
        'md',
        'url',
    );
    private $fields = array(                             //私有属性字段名和说明
        'directory' => '目录名',
        'filename' => '文件名',
        'realpath' => '完整路径',
        'path' => '相对网址',
        'extension' => '文件后缀',
        'fstat' => '资源状态',      //同php方法fstat: https://www.php.net/manual/en/function.fstat.php
        'content' => 'MD文件内容',
        'shortcut' => 'URL快捷方式',

        'description' => '描述',
        'keywords' => '关键词',
        'snapshot' => '快照图片',

        'original_ctime' => '照片拍摄时间',

        'exif' => '照片EXIF信息',
        'iptc' => '照片IPTC信息',
    );
    private $rootDir;                               //当前扫描的根目录
    private $webRoot = '/content/';                 //网站静态文件相对路径的根目录
    private $scanningDirLevel = 0;                  //当前扫描的目录深度
    private $scanStartTime = 0;                     //扫描开始时间，单位：秒
    private $scanResults = array();                 //目录扫描结果
    private $tree = array();                        //目录扫描树形结构

    public $supportFileExtensions = array(            //支持的文件类型
        'txt',     //纯文本
        'md',      //纯文本
        'url',     //快捷方式
        'jpg',     //图片
        'jpeg',    //图片
        'png',     //图片
        'webp',    //图片
        'svg',     //图片
        'gif',     //图片
        'ico',     //图标
        'mp3',     //音乐
        'mp4',     //视频
        'mov',     //视频
        'ts',      //视频
        'm3u8',    //视频
    );

    public $exifSupportFileExtensions = array(
        'jpg',     //图片
        'jpeg',    //图片
    );

    public $iptcSupportFileExtensions = array(
        'jpg',     //图片
        'jpeg',    //图片
    );

    public $exiftoolSupported = false;
    public $exiftoolSupportFileExtensions = array(
        'mp4',     //视频
        'mov',     //视频
    );

    public $scanTimeCost = 0;                       //上一次目录扫描耗时，单位：毫秒
    public $isApi = false;                          //如果为API获取数据，则realpath只返回相对路径


    //判断目录名或文件名是否合法
    //不允许包含斜杠/，反斜杠\，单引号'，双引号"字符
    //忽略.开头的隐藏文件
    private function isValid($name) {
        return str_replace(['/', '\\', "'", '"'], '', $name) == $name && !preg_match('/^\..+/', $name);
    }

    //解析描述文件内容
    //snapshot相对路径完善，支持secure_link
    //bug fix: 允许空内容文件，如：刚创建的分类文件
    private function parseDescriptionFiles($realpath) {
        $filename = $this->getFilenameWithoutExtension($realpath);
        $pathinfo = pathinfo($realpath);
        $tmp = explode('_', $filename);
        $field = array_pop($tmp);        //['title', 'snapshot']
        $content = @file_get_contents($realpath);
        if (!empty($content)) {
            $content = trim($content);
        }

        $data = array();

        //如果是快照图片，获取图片相对路径
        if (!empty($content) && $field == 'snapshot') {
            $img_realpath = realpath("{$pathinfo['dirname']}/{$content}");
            if (file_exists($img_realpath)) {
                $id = $this->getId($img_realpath);
                $fp = fopen($img_realpath, 'r');
                $fstat = fstat($fp);
                fclose($fp);
                $img_filename = $this->getFilenameWithoutExtension($img_realpath);
                $img_pathinfo = pathinfo($img_realpath);
                $content = $this->getFilePath( $id, $this->getRelativeDirname($img_pathinfo['dirname']), $img_filename, $img_pathinfo['extension'], $fstat['mtime'] );
            }
        }

        //返回文件数据
        $data[$field] = $content;

        return $data;
    }

    //解析快捷方式文件内容
    private function parseShortCuts($realpath, $filename) {
        $content = @file_get_contents($realpath);
        if (empty($content) || !preg_match('/\[InternetShortcut\]/i', $content)) {return false;}
        $content = trim($content);

        preg_match('/URL=(\S+)/i', $content, $matches);
        if (empty($matches) || empty($matches[1])) {
            return false;
        }

        return [
            'name' => $filename,
            'url' => $matches[1],
        ];
    }

    //根据文件路径生成唯一编号
    public function getId($realpath) {
        if ($realpath == '/') {return md5($realpath);}

        return !empty($realpath) ? md5(preg_replace('/\/$/', '', $realpath)) : '';
    }

    //判断Nginx防盗链MD5加密方式字符串是否合格
    private function isNginxSecureLinkMd5PatternValid($pattern) {
        $valid = true;

        $fieldsNeeded = array(
                '{secure_link_expires}',
                '{uri}',
                '{remote_addr}',
                '{secret}',
            );
        foreach($fieldsNeeded as $needle) {
            if (strstr($pattern, $needle) === false) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    //获取路径中的最后一个目录名，支持中文
    private function basename($realpath) {
        $realpath = preg_replace('/\/$/', '', $realpath);
        $arr = explode('/', $realpath);
        if (count($arr) < 2) {return $realpath;}

        return array_pop($arr);
    }

    //获取文件名，不含文件后缀
    private function getFilenameWithoutExtension($realpath) {
        return preg_replace('/\.[^\.]+$/i', '', $this->basename($realpath));
    }

    //根据路径生成目录数组
    private function getDirData($realpath, $files) {
        $id = $this->getId($realpath);
        $data = array(
            'id' => $id,
            'directory' => $this->basename($realpath),
            'realpath' => $this->isApi ? $this->getRelativeDirname($realpath) : $realpath,
            'path' => $this->getDirPath($id),
        );

        $sub_dirs = array();
        $sub_files = array();

        //try to merge description data
        if (!empty($files[$id])) {
            $data = array_merge($data, $files[$id]);
            unset($files[$id]);
        }

        //区分目录和文件
        foreach ($files as $id => $item) {
            if (!empty($item['directory'])) {
                $sub_dirs[$id] = $item;
            }else {
                $sub_files[$id] = $item;
            }
        }

        if (!empty($sub_dirs)) {
            $data['directories'] = $sub_dirs;
        }
        if (!empty($sub_files)) {
            $data['files'] = $sub_files;
        }

        return $data;
    }

    //format: Ymd
    //example: 20240322
    //return: timestamp
    private function getCreateDateFromIPTCMeta($realpath) {
        $createTime = 0;

        try {
            if (function_exists('getimagesize') && function_exists('iptcparse')) {
                $size = getimagesize($realpath, $info);
                if(!empty($info['APP13'])) {
                    $iptc = iptcparse($info['APP13']);
                    if (!empty($iptc['2#055'])) {
                        $createTime = strtotime($iptc['2#055'][0]);
                    }
                }
            }
        }catch(Exception $e) {
            //do nothing
        }

        return $createTime;
    }

    //format: Y:m:d H:i:s
    //example: 2024:03:22 00:23:02
    //return: timestamp
    private function getCreateDateFromExifMeta($realpath) {
        $createTime = 0;

        try {
            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($realpath, 0, true);
                if(!empty($exif['EXIF']['DateTimeOriginal'])) {
                    $createTime = strtotime($exif['EXIF']['DateTimeOriginal']);
                }
            }
        }catch(Exception $e) {
            //do nothing
        }

        return $createTime;
    }

    //根据路径生成文件数组，兼容URL文件
    private function getFileData($realpath) {
        $id = $this->getId($realpath);
        $fp = fopen($realpath, 'r');
        $fstat = fstat($fp);
        fclose($fp);
        $pathinfo = pathinfo($realpath);
        $extension = $pathinfo['extension'];
        $extname = strtolower($pathinfo['extension']);
        $filename = $this->getFilenameWithoutExtension($realpath);
        $data = array(
            'id' => $id,
            'filename' => $filename,
            'extname' => $extname,          //把后缀转小写，程序内部使用
            'extension' => $extension,      //保持原文件后缀大小写
            'fstat' => array(
                'size' => $fstat['size'],
                'atime' => $fstat['atime'],
                'mtime' => $fstat['mtime'],
                'ctime' => $fstat['ctime'],
            ),
            'realpath' => $this->isApi ? $this->getRelativeDirname($realpath) : $realpath,
            'path' => $this->getFilePath( $id, $this->getRelativeDirname($pathinfo['dirname']), $filename, $pathinfo['extension'], $fstat['mtime'] ),
        );

        if ($extname == 'url') {
            $data['shortcut'] = $this->parseShortCuts($realpath, $filename);
        }else if (in_array($extname, $this->exifSupportFileExtensions) || in_array($extname, $this->iptcSupportFileExtensions)) {
            $photo_create_time = $this->getCreateDateFromExifMeta($realpath);
            if ($photo_create_time == 0) {
                $photo_create_time = $this->getCreateDateFromIPTCMeta($realpath);
            }

            if ($photo_create_time > 0) {
                $data['original_ctime'] = $photo_create_time;
            }
        }else if ($this->exiftoolSupported && in_array($extname, $this->exiftoolSupportFileExtensions)) {
            //try to exec command to get original create time of videos
            try {
                $output = shell_exec( sprintf("exiftool -createdate %s", escapeshellarg($realpath)) );

                //output samples:
                //Create Date                     : 2024:06:01 20:47:06
                //Create Date                     : 0000:00:00 00:00:00
                if (!empty($output) && strpos($output, ': 0000:') === false) {
                    $dateArr = explode(' : ', $output);
                    $data['original_ctime'] = strtotime($dateArr[1]);
                }
            }catch(Exception $e) {
                //do nothing
            }
        }

        return $data;
    }

    //根据路径和根目录获取当前扫描的目录深度
    private function getScanningLevel($rootDir, $dir) {
        $level = 0;

        $rootDir = realpath($rootDir);
        $dir = realpath($dir);

        if ($dir == $rootDir) {
            $level = 1;
        }else {
            $dirs = explode('/', str_replace($rootDir, '', $dir));
            $level = count($dirs);
        }

        return $level;
    }

    //根据路径和当前扫描深度获取目录名
    private function getRelativeDirname($dirname) {
        return str_replace($this->rootDir, '', $dirname);
    }

    //合并描述文件内容到md文件或者目录数据
    //增加视频文件：mp4, mov, m3u8描述文件支持
    //增加.url文件支持
    //增加.mp3文件支持
    private function mergeDescriptionData($realpath) {
        $data = array();
        $ext = $this->parseDescriptionFiles($realpath);

        //try to find the md file
        $targetFile = '';
        $targetFile_md = preg_replace('/_?[a-z0-9]+\.txt$/U', '.md', $realpath);
        $targetFile_mp4 = preg_replace('/_?[a-z0-9]+\.txt$/U', '.mp4', $realpath);
        $targetFile_mov = preg_replace('/_?[a-z0-9]+\.txt$/U', '.mov', $realpath);
        $targetFile_m3u8 = preg_replace('/_?[a-z0-9]+\.txt$/U', '.m3u8', $realpath);
        $targetFile_url = preg_replace('/_?[a-z0-9]+\.txt$/U', '.url', $realpath);
        $targetFile_mp3 = preg_replace('/_?[a-z0-9]+\.txt$/U', '.mp3', $realpath);

        if (file_exists($targetFile_md)) {
            $targetFile = $targetFile_md;
        }else if (file_exists($targetFile_mp4)) {
            $targetFile = $targetFile_mp4;
        }else if (file_exists($targetFile_mov)) {
            $targetFile = $targetFile_mov;
        }else if (file_exists($targetFile_m3u8)) {
            $targetFile = $targetFile_m3u8;
        }else if (file_exists($targetFile_url)) {
            $targetFile = $targetFile_url;
        }else if (file_exists($targetFile_mp3)) {
            $targetFile = $targetFile_mp3;
        }

        if (!empty($targetFile) && $targetFile != $realpath) {
            $fileId = $this->getId($targetFile);
            if (empty($this->scanResults[$fileId])) {
                $ext['id'] = $fileId;
                $this->scanResults[$fileId] = $ext;
                $data = $ext;
            }else {
                $data = $this->scanResults[$fileId];
                $data = array_merge($data, $ext);
                $this->scanResults[$fileId] = $data;
            }
        }else {
            //try to merge to the parent directory
            $targetDir = preg_replace('/\/[a-z0-9]+\.txt$/U', '', $realpath);
            if (is_dir($targetDir)) {
                $dirId = $this->getId($targetDir);
                if (empty($this->scanResults[$dirId])) {
                    $ext['id'] = $dirId;
                    $this->scanResults[$dirId] = $ext;
                    $data = $ext;
                }else {
                    $data = $this->scanResults[$dirId];
                    $data = array_merge($data, $ext);
                    $this->scanResults[$dirId] = $data;
                }
            }else {
                //keep it in files
                $fileId = $this->getId($realpath);
                if (empty($this->scanResults[$fileId])) {
                    $ext['id'] = $fileId;
                    $this->scanResults[$fileId] = $ext;
                    $data = $ext;
                }else {
                    $data = $this->scanResults[$fileId];
                    $data = array_merge($data, $ext);
                    $this->scanResults[$fileId] = $data;
                }
            }
        }

        return $data;
    }


    //根据文件生成防盗链网址
    //参考：https://nginx.org/en/docs/http/ngx_http_secure_link_module.html#secure_link
    //防盗链参数名：md5, expires
    public function getSecureLink($path) {
        $expires = time() + $this->nginxSecureTimeout;
        $originStr = str_replace([
                '{secure_link_expires}',
                '{uri}',
                '{remote_addr}',
                '{secret}',
            ], [
                $expires,
                $path,
                $this->userIp,
                $this->nginxSecret,
            ], $this->nginxSecureLinkMd5Pattern);

        $md5 = base64_encode( md5($originStr, true) );
        $md5 = strtr($md5, '+/', '-_');
        $md5 = str_replace('=', '', $md5);

        return "{$path}?md5={$md5}&expires={$expires}";
    }

    //根据文件生成相对路径
    //@ver: 增加静态文件的更新时间戳作为文件版本参数
    protected function getFilePath($id, $directory, $filename, $extension, $mtime) {
        if (empty($directory)) {
            $directory = '/';
        }
        if (!preg_match('/\/$/', $directory)) {
            $directory .= '/';
        }
        if (!preg_match('/^\//', $directory)) {
            $directory = "/{$directory}";
        }

        $webRoot = preg_replace('/\/$/', '', $this->webRoot);
        $extensionPathMap = array(                  //默认每种文件读取内容最大大小
            'txt' => '',
            'md' => '/view/',
            'url' => '/link/',
            'm3u8' => '/m3u8/',
            'jpg' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'jpeg' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'png' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'webp' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'svg' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'gif' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'ico' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'mp3' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'mp4' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'mov' => "{$webRoot}{$directory}{$filename}.{$extension}",
            'ts' => "{$webRoot}{$directory}{$filename}.{$extension}",
        );

        $extname = strtolower($extension);
        $path = isset($extensionPathMap[$extname]) ? $extensionPathMap[$extname] : '';

        if (!empty($path) && in_array($extname, ['md', 'url', 'm3u8'])) {
            if ($this->nginxSecureOn && $extname == 'm3u8') {
                $path = $this->getSecureLink($path);
                $path = "{$path}&id={$id}";
            }else {
                $path = "{$path}?id={$id}";
            }
        }else if (!empty($path) && $this->nginxSecureOn) {
            $path = $this->getSecureLink($path);
        }

        //增加版本号ver参数
        if (!in_array($extname, ['md', 'url', 'txt'])) {
            $path .= strpos($path, '?') !== false ? "&ver={$mtime}" : "?ver={$mtime}";
        }

        return $path;
    }

    //根据目录生成相对路径
    protected function getDirPath($id) {
        return "/list/?id={$id}";
    }


    //设置Nginx防盗链开启或关闭，以及密钥、加密方式、超时时长
    public function setNginxSecure($secureOn, $secret = '', $userIp = '', $pattern = '', $timeout = 0) {
        $status = false;
        if (is_string($secureOn) && strtolower($secureOn) == 'on') {
            $status = true;
        }else if (is_string($secureOn) && strtolower($secureOn) == 'off') {
            $status = false;
        }else if ((bool)$secureOn == true) {
            $status = true;
        }
        $this->nginxSecureOn = $status;

        if (!empty($secret) && is_string($secret)) {
            $this->nginxSecret = $secret;
        }

        if (!empty($userIp) && is_string($userIp)) {
            $this->userIp = $userIp;
        }

        if (!empty($pattern) && is_string($pattern)) {
            if ($this->isNginxSecureLinkMd5PatternValid($pattern) == false) {
                throw new Exception("Invalid Nginx secure link md5 pattern: {$pattern}", 500);
            }
            $this->nginxSecureLinkMd5Pattern = $pattern;
        }

        if ((int)$timeout > 0) {
            $this->nginxSecureTimeout = (int)$timeout;
        }
    }

    //设置Nginx防盗链密钥
    public function setNginxSecret($secret) {
        if (!empty($secret) && is_string($secret)) {
            $this->nginxSecret = $secret;
        }
    }

    //获取Nginx防盗链密钥
    public function getNginxSecret() {
        return $this->nginxSecret;
    }

    //设置用户IP
    public function setUserIp($userIp) {
        if (!empty($userIp) && is_string($userIp)) {
            $this->userIp = $userIp;
        }
    }

    //获取用户IP
    public function getUserIp() {
        return $this->userIp;
    }

    //设置Nginx防盗链MD5加密方式
    /**
     * Nginx防盗链MD5加密方式参考下面网址中的示例，
     * 将Nginx的变量替换$符号为英文大括号；
     * 
     * 示例：
     * ```
     * {secure_link_expires}{uri}{remote_addr} {secret}
     * ```
     * Nginx文档参考：http://nginx.org/en/docs/http/ngx_http_secure_link_module.html#secure_link_md5
     */
    public function setNginxSecureLinkMd5Pattern($pattern) {
        if (!empty($pattern) && is_string($pattern)) {
            if ($this->isNginxSecureLinkMd5PatternValid($pattern) == false) {
                throw new Exception("Invalid Nginx secure link md5 pattern: {$pattern}", 500);
            }
            $this->nginxSecureLinkMd5Pattern = $pattern;
        }
    }

    //获取Nginx防盗链MD5加密方式
    public function getNginxSecureLinkMd5Pattern() {
        return $this->nginxSecureLinkMd5Pattern;
    }

    //设置Nginx防盗链超时时长，单位：秒
    public function setNginxSecureTimeout($timeout) {
        if ((int)$timeout > 0) {
            $this->nginxSecureTimeout = (int)$timeout;
        }
    }

    //获取Nginx防盗链超时时长，单位：秒
    public function getNginxSecureTimeout() {
        return $this->nginxSecureTimeout;
    }

    //设置网站静态文件相对根目录
    public function setWebRoot($webRoot) {
        if (!empty($webRoot) && !preg_match('/^\//', $webRoot)) {
            $webRoot = "/{$webRoot}";
        }

        if (!empty($webRoot) && !preg_match('/\/$/', $webRoot)) {
            $webRoot = "{$webRoot}/";
        }

        $this->webRoot = $webRoot;
    }

    //获取网站静态文件相对根目录
    public function getWebRoot() {
        return $this->webRoot;
    }

    //设置扫描绝对根目录
    public function setRootDir($dir) {
        $this->rootDir = realpath($dir);
    }

    //获取是否开启防盗链
    public function isSecureOn() {
        return $this->nginxSecureOn;
    }

    //扫描目录获取目录和文件列表，支持指定目录扫描深度（目录级数）
    public function scan($dir, $levels = 3) {
        if (empty($this->scanStartTime)) {
            $this->scanStartTime = microtime(true);
        }

        $tree = array();

        $ignore_files = array('.', '..');
        if (is_dir($dir)) {
            if (!preg_match('/\/$/', $dir)) {$dir .= '/';}
            if (empty($this->rootDir)) {
                $this->rootDir = realpath($dir);
            }
            $this->scanningDirLevel = $this->getScanningLevel($this->rootDir, $dir);
            $nextLevels = $levels - $this->scanningDirLevel;

            $files = scandir($dir);
            foreach($files as $file) {
                if (in_array($file, $ignore_files) || !$this->isValid($file)) {continue;}

                $branch = array();
                $realpath = realpath("{$dir}{$file}");
                if (is_dir($realpath)) {
                    $files = array();
                    if ($nextLevels >= 0) {
                        $files = $this->scan($realpath, $levels);
                        if (!empty($files)) {
                            foreach($files as $file) {
                                $this->scanResults[$file['id']] = $file;
                            }
                        }
                    }

                    $branch = $this->getDirData($realpath, $files);

                    //add parent directory's id
                    $pid = $this->getId(realpath($dir));
                    if (!empty($pid)) {
                        $branch = array_merge(['pid' => $pid], $branch);
                    }
                }else {
                    $pathinfo = pathinfo($realpath);
                    $extension = !empty($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';
                    if ( in_array($extension, $this->supportFileExtensions) ) {
                        if ($extension != 'txt') {
                            $branch = $this->getFileData($realpath);

                            //add parent directory's id
                            $pid = $this->getId(realpath($dir));
                            if (!empty($pid)) {
                                $branch = array_merge(['pid' => $pid], $branch);
                            }
                        }else {
                            //把描述文件内容合并到被描述的目录或md文件数据中
                            $branch = $this->mergeDescriptionData($realpath);
                        }
                    }
                }

                if (!empty($branch)) {
                    $this->scanResults[$branch['id']] = $branch;
                    $tree[$branch['id']] = $branch;
                }
            }
        }

        $this->tree = $tree;

        $time = microtime(true);
        $this->scanTimeCost = $this->scanStartTime > 0 ? ceil( ($time - $this->scanStartTime)*1000 ) : 0;

        return $tree;
    }

    //获取扫描结果
    public function getScanResults() {
        return $this->scanResults;
    }

    //设置扫描结果，以支持缓存
    public function setScanResults($data) {
        $this->scanResults = $data;
    }

    //设置tree，以支持缓存
    public function setTreeData($data) {
        $this->tree = $data;
    }

    //获取菜单，扫描结果中的目录结构
    public function getMenus($tree = array()) {
        $results = empty($tree) ? $this->tree : $tree;
        $menus = array();
        if (empty($results)) {return $menus;}

        foreach ($results as $id => $item) {
            $dir = array();
            if (!empty($item['directory'])) {
                $dir = array(
                            'id' => $item['id'],
                            'directory' => $item['directory'],
                            'path' => $item['path'],
                        );
                if (!empty($item['snapshot'])) {
                    $dir['snapshot'] = $item['snapshot'];
                }
                if (!empty($item['title'])) {
                    $dir['title'] = $item['title'];
                }
                if (!empty($item['description'])) {
                    $dir['description'] = $item['description'];
                }
                if (!empty($item['pid'])) {
                    $dir['pid'] = $item['pid'];
                }
            }

            if (!empty($item['directories'])) {
                $dirs = $this->getMenus($item['directories']);
                if (!empty($dirs)) {
                    $dir['directories'] = $dirs;
                }
            }

            if (!empty($dir)) {
                $menus[] = $dir;
            }
        }

        return $menus;
    }

    //获取.md文件中的h1-h6标题
    public function getMDTitles($id) {
        if (empty($this->scanResults[$id])) {return [];}
        $file = $this->scanResults[$id];

        $content = @file_get_contents($file['realpath']);
        if (empty($content)) {return [];}
        $content = trim($content);

        # standardize line breaks
        $content = str_replace(array("\r\n", "\r"), "\n", $content);
        # remove surrounding line breaks
        $content = trim($content, "\n");
        # split text into lines
        $lines = explode("\n", $content);

        $titles = array();
        if (!empty($lines)) {
            foreach($lines as $line) {
                preg_match_all('/^#(.+)/u', $line, $matches);
                if (!empty($matches[1])) {
                    foreach($matches[1] as $title) {
                        $num = substr_count($title, '#');
                        $titles[] = array(
                            'name' => trim(str_replace('#', '', $title)),
                            'heading' => 'h' . ($num+1),
                        );
                    }
                }
            }
        }

        return $titles;
    }

    //替换.md文件解析之后的HTML中的静态文件URL为相对路径path
    public function fixMDUrls($realpath, $html) {
        $pathinfo = pathinfo($realpath);

        $matches = array();

        //匹配图片地址
        $reg_imgs = '/src="([^"]+)"/i';
        preg_match_all($reg_imgs, $html, $img_matches);
        if (!empty($img_matches[1])) {
            $matches = $img_matches[1];
        }

        //匹配a链接
        $reg_links = '/href="([^"]+)"/i';
        preg_match_all($reg_links, $html, $link_matches);
        if (!empty($link_matches[1])) {
            $matches = array_merge($matches, $link_matches[1]);
        }

        if (!empty($matches)) {
            foreach ($matches as $url) {
                if (preg_match('/^http(s)?:\/\//i', $url) || preg_match('/^\//i', $url)) {continue;}

                $src_realpath = realpath("{$pathinfo['dirname']}/{$url}");
                if (file_exists($src_realpath)) {
                    $id = $this->getId($src_realpath);
                    $fp = fopen($src_realpath, 'r');
                    $fstat = fstat($fp);
                    fclose($fp);
                    $src_filename = $this->getFilenameWithoutExtension($src_realpath);
                    $src_pathinfo = pathinfo($src_realpath);
                    $src_path = $this->getFilePath( $id, $this->getRelativeDirname($src_pathinfo['dirname']), $src_filename, $src_pathinfo['extension'], $fstat['mtime'] );

                    $html = str_replace("\"{$url}\"", "\"{$src_path}\"", $html);
                }
            }
        }

        return $html;
    }

    //获取指定目录下名称为README.md的文件
    public function getDefaultReadme($dirid = '') {
        return $this->getDefaultFile('md', $dirid);
    }

    //根据扩展名在根目录下或者指定目录下获取一个文件
    public function getDefaultFile($extension, $dirid = '') {
        $readme = null;
        $md = null;

        if (empty($dirid) && !empty($this->tree)) {
            foreach($this->tree as $id => $file) {
                if (!empty($file['extension']) && $file['extension'] == $extension) {
                    $md = $file;
                    if ($extension == 'md' && strtoupper($file['filename']) == 'README') {
                        $readme = $file;
                        break;
                    }
                }
            }
        }else if (!empty($dirid) && !empty($this->scanResults) && !empty($this->scanResults[$dirid])) {
            $directory = $this->scanResults[$dirid];
            if (!empty($directory) && !empty($directory['files'])) {
                foreach($directory['files'] as $id => $file) {
                    if (!empty($file['extension']) && $file['extension'] == $extension) {
                        if (empty($md)) {$md = $file;}      //取第一个md文件
                        if ($extension == 'md' && strtoupper($file['filename']) == 'README') {
                            $readme = $file;
                            break;
                        }
                    }
                }
            }
        }

        if (empty($readme) && !empty($md)) {
            $readme = $md;
        }

        return $readme;
    }

    //获取目录下第一个图片作为封面图返回
    public function getSnapshotImage($realpath, $imgExts = array('jpg', 'jpeg', 'png', 'webp', 'gif', 'svg')) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realpath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $imgData = null;
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $extension = $item->getExtension();
                if (in_array(strtolower($extension), $imgExts)) {
                    $imgPath = $item->getPath() . '/' . $item->getFilename();
                    $imgData = $this->getFileData($imgPath);
                    break;
                }
            }
        }

        return $imgData;
    }

}
