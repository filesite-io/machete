<?php
/**
 * Site Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';

Class SiteController extends Controller {

    public function actionIndex() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $scanResults = $scanner->getScanResults();

        //获取目录
        $menus = $scanner->getMenus();

        $titles = array();
        $htmlReadme = '';
        $readmeFile = $scanner->getDefaultReadme();
        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }

            $titles = $scanner->getMDTitles($readmeFile['id']);

            $Parsedown = new Parsedown();
            $content = file_get_contents($readmeFile['realpath']);
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);
        }

        //默认显示的目录
        $cateId = $this->get('id', $menus[0]['id']);
        $subcate = $scanResults[$cateId];

        //获取当前目录下的readme
        $cateReadmeFile = $scanner->getDefaultReadme($cateId);
        if (!empty($cateReadmeFile)) {
            $Parsedown = new Parsedown();
            $content = file_get_contents($cateReadmeFile['realpath']);
            $htmlCateReadme = $Parsedown->text($content);
            $htmlCateReadme = $scanner->fixMDUrls($cateReadmeFile['realpath'], $htmlCateReadme);
        }

        //获取tags分类
        $tags = $this->getTags($dirTree);

        //排序
        if (!empty($menus_sorted) && !empty($tags)) {
            $tags = $this->sortTags($menus_sorted, $tags);
        }

        //昵称支持
        $nickname = $this->getNickname($readmeFile);

        $pageTitle = $defaultTitle = !empty($titles) ? $titles[0]['name'] : FSC::$app['config']['site_name'];
        if (!empty($readmeFile['title'])) {
            $pageTitle = "{$readmeFile['title']}，来自{$defaultTitle}";
        }

        $viewName = 'index';
        $params = compact(
                'cateId', 'dirTree', 'scanResults', 'menus', 'htmlReadme', 'htmlCateReadme', 'tags',
                'nickname'
            );
        return $this->render($viewName, $params, $pageTitle);
    }

    //获取tag分类
    protected function getTags($dirTree, $noFiles = false) {
        $tags = array();

        $tagDir = null;
        $tagSaveDirName = str_replace('/', '', FSC::$app['config']['tajian']['tag_dir']);
        foreach($dirTree as $id => $item) {
            if (!empty($item['directory']) && $item['directory'] == $tagSaveDirName) {
                $tagDir = $item;
                break;
            }
        }

        if (!empty($tagDir) && !empty($tagDir['files'])) {
            foreach($tagDir['files'] as $id => $item) {
                if (empty($item['realpath'])) {        //如果是txt描述文件
                    $tag = $this->getTagItem($item, $noFiles);
                    $tags[$tag['id']] = $tag;
                }
            }
        }

        return $tags;
    }

    protected function getTagItem($tagFile, $noFiles = false) {
        $tag = array();

        foreach($tagFile as $name => $item) {
            if ($name == 'id') {
                $tag['id'] = $item;
            }else {
                $tag['name'] = $name;
                if ($noFiles == false) {
                    $tag['files'] = explode("\n", $item);
                }
            }
        }

        return $tag;
    }

    protected function sortTags($menus_sorted, $tags) {
        $sorted_tags = array();

        foreach($menus_sorted as $tag) {
            foreach($tags as $id => $item) {
                if ($item['name'] == $tag) {
                    $sorted_tags[$id] = $item;
                }
            }

        }

        return $sorted_tags;
    }

    protected function getNickname($readmeFile) {
        $nickname = '';

        if (!empty($readmeFile['nickname'])) {
            $nickname = $readmeFile['nickname'];
        }else if (!empty(FSC::$app['config']['multipleUserUriParse']) && !empty(FSC::$app['user_id'])) {
            $nickname = FSC::$app['user_id'];
        }

        return $nickname;
    }

    //添加新视频
    public function actionNew() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序

        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 4);
        $scanResults = $scanner->getScanResults();

        $titles = array();
        $htmlReadme = '';
        $readmeFile = $scanner->getDefaultReadme();
        if (!empty($readmeFile)) {
            if (!empty($readmeFile['sort'])) {
                $menus_sorted = explode("\n", $readmeFile['sort']);
            }

            $titles = $scanner->getMDTitles($readmeFile['id']);

            $Parsedown = new Parsedown();
            $content = file_get_contents($readmeFile['realpath']);
            $htmlReadme = $Parsedown->text($content);
            $htmlReadme = $scanner->fixMDUrls($readmeFile['realpath'], $htmlReadme);
        }

        //获取tags分类
        $tags = $this->getTags($dirTree);

        //排序
        if (!empty($menus_sorted) && !empty($tags)) {
            $tags = $this->sortTags($menus_sorted, $tags);
        }

        //昵称支持
        $nickname = $this->getNickname($readmeFile);

        $pageTitle = '添加视频收藏';
        $viewName = 'new';
        $params = compact('dirTree', 'scanResults', 'htmlReadme', 'tags', 'nickname');
        return $this->render($viewName, $params, $pageTitle);
    }


}
