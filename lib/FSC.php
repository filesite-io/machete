<?php
/**
 * Class App
 */
Class FSC {
    public static $app = array();
    protected static $start_time = 0;

    //call function in controller
    public static function run($config = array()) {
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

        $controller = $action = $user_id = '';
        if (!empty($config['multipleUserUriParse'])) {    //如果开启了多用户解析支持
            $pathArr = explode('/', $path);
            if (count($pathArr) >= 2 && is_numeric($pathArr[1])) {
                $user_id = $pathArr[1];
                if (!empty($pathArr[2])) {
                    $controller = $pathArr[2];
                }
                if (!empty($pathArr[3])) {
                    $action = $pathArr[3];
                }
            }else {
                @list(, $controller, $action) = $pathArr;
                if (!empty($config['defaultUserId'])) {
                    $user_id = $config['defaultUserId'];
                }
            }
        }else {
            @list(, $controller, $action) = explode('/', $path);
        }

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

        return compact('controller', 'action', 'user_id');
    }

    //add themes support
    protected static function loadController($config) { 
        //parse url to controller and action
        $requestUrl = $_SERVER['REQUEST_URI'];
        $arr = self::getControllerAndAction($requestUrl, $config);
        $controller = $arr['controller'];
        $action = $arr['action'];
        $user_id = $arr['user_id'];
        $start_time = self::$start_time;

        //如果多用户解析支持开启
        if (!empty($config['multipleUserUriParse']) && !empty($user_id)) {
            $config['content_directory'] = "{$config['content_directory']}{$user_id}/";
        }

        //set parameters
        self::$app = compact('config', 'controller', 'action', 'user_id', 'requestUrl', 'start_time');

        //call class and function
        $className = ucfirst($controller) . 'Controller';
        $funName = 'action' . ucfirst($action);

        $controllerFile = $baseControllerFile = $themeControllerFile = '';

        $baseControllerFile = __DIR__ . "/../controller/{$className}.php";
        if (!empty($config['theme'])) {
            $themeControllerFile = __DIR__ . "/../themes/{$config['theme']}/controller/{$className}.php";
        }

        //优先使用皮肤目录下的控制器，其次默认controller目录下的
        if (!empty($themeControllerFile) && file_exists($themeControllerFile)) {
            $controllerFile = $themeControllerFile;
        }else if (file_exists($baseControllerFile)) {
            $controllerFile = $baseControllerFile;
        }

        if (!empty($controllerFile)) {
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
