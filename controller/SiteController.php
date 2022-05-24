<?php
/**
 * Site Controller
 */
Class SiteController extends Controller {

    public function actionIndex() {

        //alert message test
        $this->sendMsgToClient('Alert message - danger', 'danger');
        $this->sendMsgToClient('Alert message - warning', 'warning');
        $this->sendMsgToClient('Alert message - success', 'success');
        $this->sendMsgToClient('Alert message - info', 'info');

        $pageTitle = "Welcome to FSC";
        $viewName = 'index';
        $params = array(
            'foo' => 'bar',
        );
        return $this->render($viewName, $params, $pageTitle);
    }

}
