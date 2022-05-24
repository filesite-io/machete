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
        $layoutFile = __DIR__ . '/../views/layout/' . $this->layout . '.php';
        $viewFile = __DIR__ . '/../views/' . FSC::$app['controller'] . '/' . $viewName . '.php';
        //双斜杠//开头的共享视图支持
        if (preg_match('/^\/\//', $viewName)) {
            $viewFile = __DIR__ . '/../views/' . str_replace('//', '/', $viewName) . '.php';
        }

        if (!empty(FSC::$app['config']['theme'])) {
            $layoutFile = __DIR__ . '/../themes/' . FSC::$app['config']['theme'] . '/views/layout/' . $this->layout . '.php';
            $viewFile = __DIR__ . '/../themes/' . FSC::$app['config']['theme'] . '/views/' . FSC::$app['controller'] . '/' . $viewName . '.php';
            //双斜杠//开头的共享视图支持
            if (preg_match('/^\/\//', $viewName)) {
                $viewFile = __DIR__ . '/../themes/' . FSC::$app['config']['theme'] . '/views/' . 
                            str_replace('//', '/', $viewName) . '.php';
            }
        }

        //include layout and view
        if (file_exists($layoutFile)) {
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
    protected function renderJson($data) {  
        header("Content-Type: application/json; charset=utf-8");
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

}
