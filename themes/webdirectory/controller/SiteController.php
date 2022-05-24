<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';

Class SiteController extends Controller {

    public function actionIndex() {
        //获取数据
        $menus = [];        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlContact = '';  //Readme_contact.txt 说明文件内容，右侧悬浮菜单里的“联系我”
        $menus_sorted = []; //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $titles = [];
        $content = '';

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $readmeFile = $scanner->getDefaultReadme();
        
        $menus = $scanner->getMenus();

        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }

            $titles = $scanner->getMDTitles($readmeFile['id']);
            $content = file_get_contents($readmeFile['realpath']);

            $Parsedown = new Parsedown();
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);

            if (!empty($readmeFile['contact'])) {
                $htmlContact = $Parsedown->text($readmeFile['contact']);
                $htmlContact = $scanner->fixMDUrls($readmeFile['realpath'], $htmlContact);
            }
        }

        //排序
        if (!empty($menus_sorted) && !empty($menus)) {
            //一级目录菜单排序
            $menu_dirs = array_column($menus, 'directory');
            $names = array_replace(array_flip($menus_sorted), array_flip($menu_dirs));
            if (!empty($names)) {
                $menus_sorted = array_keys($names);

                $arr = [];
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

                $arr = [];
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
        }


        $pageTitle = !empty($titles) ? $titles[0]['name'] : "FileSite.io - 无数据库、基于文件和目录的Markdown文档、网址导航、图书、图片、视频网站PHP开源系统";
        $viewName = 'index';
        $params = compact('dirTree', 'menus', 'htmlReadme', 'htmlContact');
        return $this->render($viewName, $params, $pageTitle);
    }

}
