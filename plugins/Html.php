<?php
/**
 * 常用的处理HTML的方法
 */
Class Html {
    //获取js、css文件的修改时间作版本号
    public static function getStaticFileVersion($filename, $type = 'css') {
        $ver = 0;
        $filepath = '';

        switch ($type) {
            case 'css':
                $filepath = __DIR__ . '/../www/css/' . $filename;
                break;
            
            default:
                $filepath = __DIR__ . '/../www/js/' . $filename;
                break;
        }

        if (!empty($filepath) && file_exists($filepath)) {
            $fp = fopen($filepath, 'r');
            $fstat = fstat($fp);
            fclose($fp);

            $ver = $fstat['mtime'];
        }

        return $ver;
    }
}
