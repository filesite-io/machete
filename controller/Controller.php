<?php
/**
 * Controller
 */
Class Controller {
    protected $layout = 'main';

    function __construct() {
        //set default layout
        if (!empty(FSC::$app['config']['default_layout'])) {
            $this->layout = FSC::$app['config']['default_layout'];
        }

        //json body auto serialization to $_POST
        try {
            $json = file_get_contents('php://input');
            $jsonData = json_decode($json, true);

            if (!empty($jsonData)) {
                $_POST = array_merge($_POST, $jsonData);
            }
        }catch(Exception $e) {}
    }

    function __destruct() {
        $this->logTimeCost();
    }

    //redirect url
    protected function redirect($url, $code = 302) {
        header("Location: {$url}", true, $code);
        exit;
    }

    //render view
    protected function render($viewName, $viewData = array(), $pageTitle = '') {
        $baseLayoutFile = $themeLayoutFile = $layoutFile = '';
        $baseViewFile = $themeViewFile = $viewFile = '';

        $baseLayoutFile = __DIR__ . '/../views/layout/' . $this->layout . '.php';
        $baseViewFile = __DIR__ . '/../views/' . FSC::$app['controller'] . '/' . $viewName . '.php';
        //双斜杠//开头的共享视图支持
        if (preg_match('/^\/\//', $viewName)) {
            $baseViewFile = __DIR__ . '/../views/' . str_replace('//', '/', $viewName) . '.php';
        }

        if (!empty(FSC::$app['config']['theme'])) {
            $themeLayoutFile = __DIR__ . '/../themes/' . FSC::$app['config']['theme'] . '/views/layout/' . $this->layout . '.php';
            $themeViewFile = __DIR__ . '/../themes/' . FSC::$app['config']['theme'] . '/views/' . FSC::$app['controller'] . '/' . $viewName . '.php';
            //双斜杠//开头的共享视图支持
            if (preg_match('/^\/\//', $viewName)) {
                $themeViewFile = __DIR__ . '/../themes/' . FSC::$app['config']['theme'] . '/views/' . 
                            str_replace('//', '/', $viewName) . '.php';
            }
        }

        if (!empty($themeLayoutFile) && file_exists($themeLayoutFile)) {
            $layoutFile = $themeLayoutFile;
            $viewFile = $themeViewFile;
        }else if (file_exists($baseLayoutFile)) {
            $layoutFile = $baseLayoutFile;
            $viewFile = $baseViewFile;
        }

        //include layout and view
        if (!empty($layoutFile)) {
            ob_start();
            include_once $layoutFile;

            $htmlCode = ob_get_contents();
            ob_end_clean();

            //enable gzip
            ob_start('ob_gzhandler');

            //show time cost
            $end_time = microtime(true);
            $page_time_cost = ceil( ($end_time - FSC::$app['start_time']) * 1000 );   //ms
            echo str_replace('{page_time_cost}', $page_time_cost, $htmlCode);

            ob_end_flush();
        }else {
            $error_message = "Layout file {$this->layout}.php is not exist.";
            if (!empty(FSC::$app['config']['theme'])) {
                $error_message = "Layout file {$this->layout}.php in theme " . FSC::$app['config']['theme'] . " is not exist.";
            }
            throw new Exception($error_message, 500);
        }
    }

    //render json data
    protected function renderJson($data, $httpStatus = 200) {  
        if (!empty(FSC::$app['config']['debug'])) {
            $end_time = microtime(true);
            $data['page_time_cost'] = ceil( ($end_time - FSC::$app['start_time']) * 1000 );   //ms
        }

        header("Content-Type: application/json; charset=utf-8");
        if ($httpStatus != 200 && is_numeric($httpStatus)) {
            $title = "HTTP/1.0 {$httpStatus} Internal Server Error";
            switch($httpStatus) {
                case 401:
                    $title = "HTTP/1.0 {$httpStatus} 未授权";
                    break;
                case 402:
                    $title = "HTTP/1.0 {$httpStatus} 未购买";
                    break;
                case 403:
                    $title = "HTTP/1.0 {$httpStatus} 禁止访问";
                    break;
                case 404:
                    $title = "HTTP/1.0 {$httpStatus} 不存在";
                    break;
                case 500:
                    $title = "HTTP/1.0 {$httpStatus} 系统错误";
                    break;
            }

            header($title, true, $httpStatus);
        }

        echo json_encode($data);
        exit;
    }

    //render m3u8 file
    protected function renderM3u8($content) {  
        header("Content-Type: application/x-mpegURL; charset=utf-8");
        echo $content;
        exit;
    }

    //get params by key
    protected function get($key = '', $defaultValue = '') {   
        if (empty($key)) {
            return $_GET;
        }
        return !empty($_GET[$key]) ? $_GET[$key] : $defaultValue;
    }

    //post params by key
    protected function post($key = '', $defaultValue = '') {   
        if (empty($key)) {
            return $_POST;
        }
        return !empty($_POST[$key]) ? $_POST[$key] : $defaultValue;
    }

    //debug log
    protected function logTimeCost() {   
        if (!empty(FSC::$app['config']['debug'])) {
            $end_time = microtime(true);
            $timeCost = ceil( ($end_time - FSC::$app['start_time']) * 1000 );   //ms
            $thisUrl = FSC::$app['requestUrl'];
            $logTime = date('Y-m-d H:i:s');
            $logDir = __DIR__ . '/../runtime/logs/';
            $logOk = @error_log("{$logTime}\t{$thisUrl}\ttime cost: {$timeCost} ms\n", 3, "{$logDir}debug.log");
            if (!$logOk) {      //try to mkdir
                @mkdir($logDir, 0700, true);
                @error_log("{$logTime}\t{$thisUrl}\ttime cost: {$timeCost} ms\n", 3, "{$logDir}debug.log");
            }
        }
    }

    //get user real ip
    protected function getUserIp() {     
        $ip = false;

        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (!empty($ip)) {
                array_unshift($ips, $ip);
                $ip = false;
            }

            if (!empty($ips)) {
                for ($i = 0; $i < count($ips); $i++) {
                    if (!preg_match("/^(10│172\.16│192\.168)\./", $ips[$i])) {
                        $ip = $ips[$i];
                        break;
                    }
                }
            }
        }

        return !empty($ip) ? $ip : $_SERVER['REMOTE_ADDR'];
    }

    //request url via curl
    protected function request($url, $postFields = array(), $timeout = 10, $pc = false) { 
        $s = curl_init();

        curl_setopt($s, CURLOPT_URL, $url);
        curl_setopt($s, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

        if (!empty($postFields)) {
            curl_setopt($s, CURLOPT_POST, true);
            curl_setopt($s, CURLOPT_POSTFIELDS, $postFields);
        }

        //iphone client
        $user_agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1';
        //mac os client
        if ($pc) {
            $user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36';
        }
        curl_setopt($s, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($s, CURLOPT_REFERER, '-');

        $curlResult = curl_exec($s);
        $curlStatus = curl_getinfo($s, CURLINFO_HTTP_CODE);
        curl_close($s);

        return array(
            'status' => $curlStatus,
            'result' => $curlResult,
        );
    }

    //set cookie for message show
    //type: info, warning, danger, success
    protected function sendMsgToClient($msg, $type = 'info') {   
        $cookieKey = "alert_msg_{$type}";
        $expires = time() + 15;
        $path = '/';

        //empty character replace to "%20"
        $encoded = urlencode($msg);
        $noempty = str_replace('+', '%20', $encoded);
        $val = base64_encode( $noempty );

        setcookie($cookieKey, $val, $expires, $path);
    }

    //sort menus and dirTree
    protected function sortMenusAndDirTree($menus_sorted, $menus, $dirTree) {
        if (empty($menus_sorted) || empty($menus) || empty($dirTree)) {return false;}

        //一级目录菜单排序
        $menu_dirs = array_column($menus, 'directory');
        $names = array_replace(array_flip($menus_sorted), array_flip($menu_dirs));
        if (!empty($names)) {
            $menus_sorted = array_keys($names);

            $arr = array();
            foreach($menus_sorted as $name) {
                $index = array_search($name, $menu_dirs);
                array_push($arr, $menus[$index]);
            }
            $menus = $arr;
        }

        //dirTree一级目录排序
        $sorted_dirs = array_column($menus, 'directory');
        $tree_dirs = array_column($dirTree, 'directory');
        $names = array_replace(array_flip($sorted_dirs), array_flip($tree_dirs));
        if (!empty($names)) {
            $sorted_dirs = array_keys($names);

            $arr = array();
            foreach($sorted_dirs as $name) {
                foreach($dirTree as $index => $item) {
                    if (!empty($item['directory']) && $item['directory'] == $name) {
                        array_push($arr, $item);
                        break;
                    }
                }
            }
            $dirTree = $arr;
        }

        return compact('menus', 'dirTree');
    }

    //get basename of realpath, support chinese
    protected function basename($realpath) {
        $realpath = preg_replace('/\/$/', '', $realpath);
        $arr = explode('/', $realpath);
        if (count($arr) < 2) {return $realpath;}

        return array_pop($arr);
    }

}
