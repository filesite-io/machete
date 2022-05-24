<?php
/**
 * Class App
 */
Class FSC {
    public static $app = array();
    protected static $start_time = 0;

    //call function in controller
    public static function run($config = []) {   
        self::$start_time = !empty($config['start_time']) ? $config['start_time'] : microtime(true);

        try {
            self::loadController($config);
        }catch(Exception $e) {
            $content = htmlspecialchars($e->getMessage(), ENT_QUOTES);
            $code = $e->getCode();
            if (empty($code)) {$code = 500;}
            $title = "HTTP/1.0 {$code} Internal Server Error";
            header($title, true, $code);

            //render error layout
            $errors = compact('code', 'title', 'content');
            $layoutName = !empty($config['error_layout']) ? $config['error_layout'] : 'error';
            $error_layout = __DIR__ . "/../views/layout/{$layoutName}.php";
            if (!empty($config['theme'])) {
                $theme_error_layout = __DIR__ . "/../themes/{$config['theme']}/views/layout/{$layoutName}.php";
                if (file_exists($theme_error_layout)) {
                    $error_layout = $theme_error_layout;
                }
            }
            
            ob_start();
            include_once $error_layout;

            $htmlCode = ob_get_contents();
            ob_end_clean();

            //enable gzip
            ob_start('ob_gzhandler');
            echo $htmlCode;
            ob_end_flush();
        }
    }

    //parse url to controller and action name
    protected static function getControllerAndAction($url, $config) {    
        $arr = parse_url($url);
        $path = !empty($arr['path']) ? $arr['path'] : '/';

        @list(, $controller, $action) = explode('/', $path);
        if (empty($controller)) {
            $controller = !empty($config['defaultController']) ? $config['defaultController'] : 'site';
        }else if(preg_match('/\w+\.\w+/i', $controller)) { //not controller but some file not exist
            throw new Exception("File {$controller} not exist.", 404);
        }else {
            $controller = preg_replace('/\W/', '', $controller);
        }

        if (empty($action)) {
            $action = 'index';
        }else {
            $action = preg_replace('/\W/', '', $action);
        }

        return compact('controller', 'action');
    }

    //add themes support
    protected static function loadController($config) { 
        //parse url to controller and action
        $requestUrl = $_SERVER['REQUEST_URI'];
        $arr = self::getControllerAndAction($requestUrl, $config);
        $controller = $arr['controller'];
        $action = $arr['action'];
        $start_time = self::$start_time;

        //set parameters
        self::$app = compact('config', 'controller', 'action', 'requestUrl', 'start_time');

        //call class and function
        $className = ucfirst($controller) . 'Controller';
        $funName = 'action' . ucfirst($action);
        $controllerFile = __DIR__ . "/../controller/{$className}.php";
        if (!empty($config['theme'])) {
            $controllerFile = __DIR__ . "/../themes/{$config['theme']}/controller/{$className}.php";
        }
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $cls = new $className();
            if (method_exists($className, $funName)) {
                $cls->$funName();
            }else {
                $error_message = "Function {$funName} not exist in class {$className}.";
                if (!empty($config['theme'])) {
                    $error_message = "Function {$funName} not exist in class {$className} of theme {$config['theme']}.";
                }
                throw new Exception($error_message, 500);
            }
        }else {
            $error_message = "Controller {$className}.php not exist.";
            if (!empty($config['theme'])) {
                $error_message = "Controller {$className}.php not exist in theme {$config['theme']}.";
            }
            throw new Exception($error_message, 500);
        }
    }

}
