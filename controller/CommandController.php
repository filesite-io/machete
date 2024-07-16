<?php
/**
 * Command Controller
 */
Class CommandController extends Controller {

    public function actionIndex() {
        $commands = <<<eof
Actions:
    - test

Usage:
    php command.php action parameters


eof;
        echo $commands;
        exit;
    }

    public function actionConfig() {
        $themeName = FSC::$app['config']['theme'];

        $code = 1;
        $data = '';

        //修改配置文件
        $param_do = $this->get('do', 'set');    //支持：set, get, all, del
        $param_key = $this->get('key', '');
        $param_value = $this->get('val', '');

        if ($param_do == 'set' && empty($param_value)) {
            throw new Exception("缺少val参数！", 403);
        }else if (in_array($param_do, array('set', 'get', 'del')) && empty($param_key)) {
            throw new Exception("缺少key参数！", 403);
        }


        $config_file = __DIR__ . "/../runtime/custom_config.json";
        if (file_exists($config_file)) {
            $content = file_get_contents($config_file);
            $configs = @json_decode($content, true);
            if (empty($configs)) {
                $config_file_template = __DIR__ . "/../conf/custom_config_{$themeName}.json";
                $content = file_get_contents($config_file_template);
                $configs = @json_decode($content, true);
            }
        }

        if (!empty($configs)) {
            switch($param_do) {
                case 'set':
                    $configs[$param_key] = $param_value;
                    file_put_contents($config_file, json_encode($configs, JSON_PRETTY_PRINT));
                    $data = $configs;
                    break;

                case 'get':
                    $data = !empty($configs[$param_key]) ? $configs[$param_key] : '';
                    break;

                case 'del':
                    unset($configs[$param_key]);
                    file_put_contents($config_file, json_encode($configs, JSON_PRETTY_PRINT));
                    $data = $configs;
                    break;

                case 'all':
                default:
                    $data = $configs;
                    break;
            }
        }


        $res = compact('code', 'data');

        echo "命令参数：\n";
        print_r($this->get());
        echo "\n";
        echo "命令执行结果：\n";
        print_r($res);
        echo "\n\n";
        exit;
    }

    public function actionTest() {
        echo "## App variables:\n";
        print_r(FSC::$app);
        echo "\n";

        echo "## GET parameters:\n";        
        print_r($this->get());
        echo "\n";

        exit;
    }

}
