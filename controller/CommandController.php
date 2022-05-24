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
