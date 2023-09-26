<?php
/**
 * List Controller
 */
require_once __DIR__ . '/../../../lib/DirScanner.php';
require_once __DIR__ . '/../../../plugins/Parsedown.php';
require_once __DIR__ . '/../../../plugins/Html.php';
require_once __DIR__ . '/SiteController.php';

Class FrontApiController extends SiteController {

    public function actionIndex() {
        $code = 0;
        $err = 'Not allowed';

        return $this->renderJson(compact('code', 'err'));
    }

    public function actionTags() {
        //获取数据
        $menus = array();        //菜单，一级目录
        $htmlReadme = '';   //Readme.md 内容，底部网站详细介绍
        $htmlCateReadme = '';   //当前目录下的Readme.md 内容
        $menus_sorted = array(); //Readme_sort.txt 说明文件内容，一级目录菜单从上到下的排序
        
        $scanner = new DirScanner();
        $scanner->setWebRoot(FSC::$app['config']['content_directory']);
        $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);

        $code = 1;
        $msg = '';
        $err = '';
        
        //获取tags分类
        $noFiles = true;
        $data = $this->getTags($dirTree, $noFiles);

        return $this->renderJson(compact('code', 'msg', 'err', 'data'));
    }

    /*
     * 参数：
     * content: 从抖音或其它平台复制出来的视频分享内容，或者视频网址
     * title: 视频标题
     * tag: 分类名称
     * tagid: 分类id
     * 其中title、tag和tagid为可选值。
     */
    public function actionAddfav() {
        $content = $this->post('content', '');
        $title = $this->post('title', '');
        $tag = $this->post('tag', '');
        $tagid = $this->post('tagid', '');

        $code = 1;
        $msg = '';
        $err = '';

        if (empty($content)) {
            $code = 0;
            $err = '请粘贴填写分享内容！';
        }else {
            $content = urldecode($content);
        }

        //分享内容来源平台检查
        $shareUrl = $this->getShareUrlFromContent($content);
        $platform = Html::getShareVideosPlatform($shareUrl);
        if (!in_array($platform, FSC::$app['config']['tajian']['supportedPlatforms'])) {
            $code = 0;
            $err = '目前只支持抖音、快手、西瓜视频和Bilibili的分享网址哦！';
        }

        $tagName = '';
        if ($code == 1 && (!empty($tag) || !empty($tagid))) {        //检查分类名称或id是否存在
            $scanner = new DirScanner();
            $scanner->setWebRoot(FSC::$app['config']['content_directory']);
            $dirTree = $scanner->scan(__DIR__ . '/../../../www/' . FSC::$app['config']['content_directory'], 3);
            //获取tags分类
            $tags = $this->getTags($dirTree);

            if (!empty($tagid) && empty($tags[$tagid])) {        //检查tagid是否存在
                $code = 0;
                $err = "分类ID {$tagid} 不存在！";
            }

            if (!empty($tag)) {        //检查tag是否存在
                $tag_exists = false;
                foreach($tags as $id => $item) {
                    if ($item['name'] == $tag) {
                        $tag_exists = true;
                        $tagName = $tag;
                        break;
                    }
                }

                if ($tag_exists == false) {
                    $code = 0;
                    $err = "分类 {$tag} 不存在！";
                }
            }

            if (empty($tagName) && !empty($tags[$tagid])) {
                $tagName = $tags[$tagid]['name'];
            }
        }

        if ($code == 1) {        //保存视频
            $msg = $this->saveShareVideo($content, $title, $tagName) ? '视频保存完成，系统开始自动处理，1 - 3 分钟后刷新就能看到新添加的视频了。' : '视频保存失败，请稍后重试！';
        }

        return $this->renderJson(compact('code', 'msg', 'err'));
    }

    protected function getVideoId($url) {
        return md5($url);
    }

    protected function getShareUrlFromContent($content) {
        $url = '';

        preg_match("/https:\/\/[\w\.]+(\/\w+){1,}\/?/i", $content, $matches);
        if (!empty($matches)) {
            $url = $matches[0];
        }

        return $url;
    }

    //保存分享视频
    protected function saveShareVideo($content, $title, $tagName) {
        $done = true;

        $shareUrl = $this->getShareUrlFromContent($content);
        if (!empty($shareUrl)) {
            $done = $done && $this->saveBotTask($shareUrl);

            if (!empty($tagName)) {
                $done = $done && $this->saveVideoToTag($shareUrl, $tagName);
            }
        }

        return $done;
    }

    //保存分享视频到任务文件
    protected function saveBotTask($url) {
        $task_dir = __DIR__ . '/../../../runtime/' . FSC::$app['config']['tajian']['task_dir'];
        if (!is_dir($task_dir)) {
            mkdir($task_dir, 0755, true);
        }

        $video_id = $this->getVideoId($url);
        $filepath = "{$task_dir}/{$video_id}.task";
        return file_put_contents($filepath, $url) !== false;
    }

    //保存分享视频到tag分类
    //TODO: 如果高并发，需要避免数据被覆盖的问题
    protected function saveVideoToTag($url, $tagName) {
        $tag_dir = __DIR__ . '/../../../www/'
            . FSC::$app['config']['content_directory']
            . '/' . FSC::$app['config']['tajian']['tag_dir'];
        if (!is_dir($tag_dir)) {
            mkdir($tag_dir, 0755, true);
        }

        $video_id = $this->getVideoId($url);
        $filepath = "{$tag_dir}/{$tagName}.txt";
        if (file_exists($filepath)) {
            $content = file_get_contents($filepath);
            $videos = explode("\n", $content);
            $last_id = array_pop($videos);
            if (!empty($last_id)) {
                array_push($videos, $last_id);
            }

            if (!in_array($video_id, $videos)) {
                array_push($videos, $video_id);
            }

            return file_put_contents($filepath, implode("\n", $videos)) !== false;
        }else {
            return file_put_contents($filepath, $vidoe_id) !== false;
        }
    }

}
