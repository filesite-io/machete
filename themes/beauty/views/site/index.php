<!-- 顶部导航栏模块 -->
<nav class="navbar navbar-default navbar-fixed-top navbarJS">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display navbar-inverse-->
        <div class="navbar-header">
            <div class="navbar-toggle">
                <img class="svg icon1 svgimg lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
                <img class="icon1 svg connectmeJS svgimg iconr2 verMiddle" src="/img/beauty/contactUs.svg" alt="联系我们" title="联系我们" />
                <button type="button" class="collapsed mr_button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <img class="svg icon1 svgimg verMiddle" src="/img/beauty/navshow.svg" alt="展开列表" title="展开列表">
                </button>
            </div>

            <a class="navbar-brand" href="/">
                <!--img class="verMiddle" src="/content/machete_icon.png" alt="logo图片"-->
                <span class="verMiddle">家庭相册</span>
            </a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <?php
                $selectedId = $viewData['cateId'];
                $breadcrumbs = !empty($viewData['breadcrumbs']) ? $viewData['breadcrumbs'] : [];
                if (!empty($viewData['menus'])) {        //只显示第一级目录
                    foreach ($viewData['menus'] as $index => $item) {
                        $selected = $item['id'] == $selectedId || (!empty($breadcrumbs) && $item['id'] == $breadcrumbs[0]['id']) ? 'active' : '';
                        echo <<<eof
        <li class="{$selected}"><a href="/?id={$item['id']}">{$item['directory']}</a></li>
eof;
                    }
                }
                ?>
            </ul>
            <?php /*
            <form class="navbar-form navbar-left">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="搜索图片名称">
                </div>
                <button type="submit" class="btn btn-default">搜索</button>
            </form>
            */ ?>
            <div class="nb_right nav navbar-nav navbar-right hidden-xs">
                <img class="svg icon1 svgimg lampJS verMiddle" src="/img/beauty/buld.svg" alt="点击关灯/开灯" title="点击关灯/开灯">
                <img class="icon1 svg connectmeJS svgimg iconr2 verMiddle" src="/img/beauty/contactUs.svg" alt="联系我们" title="联系我们" />
            </div>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>

<?php
if (!empty($breadcrumbs)) {
    echo <<<eof
    <div class="breadcrumbs text_dark">
        <small>当前位置：</small>
eof;

    foreach ($breadcrumbs as $bread) {
        if ($bread['id'] != $selectedId) {
            echo <<<eof
        <a href="{$bread['url']}">{$bread['name']}</a> / 
eof;
        } else {
            echo <<<eof
        <strong>{$bread['name']}</strong>
eof;
        }
    }

    echo <<<eof
    </div>
eof;
}
?>

<!-- 内容主题 -->
<div class="img_main">
    <div class="im_mainl row">
        <?php
        $imgExts = !empty(FSC::$app['config']['supportedImageExts']) ? FSC::$app['config']['supportedImageExts'] : array('jpg', 'jpeg', 'png', 'webp', 'gif');
        $category = !empty($viewData['scanResults'][$selectedId]) ? $viewData['scanResults'][$selectedId] : [];
        $total = 0;     //翻页支持

        //当前目录的描述介绍
        if (!empty($category['description'])) {
            echo <<<eof
    <p class="modal-body text_dark">{$category['description']}</p>
eof;
        }

        //当前目录的readme详细介绍
        if (!empty($viewData['htmlCateReadme'])) {
            echo <<<eof
    <div class="modal-body text_dark markdown-body">{$viewData['htmlCateReadme']}</div>
eof;
        }

        if (!empty($category['directories'])) {        //两级目录支持
            $total = count($category['directories']);     //翻页支持
            $index = 0;
            foreach ($category['directories'] as $dir) {
                echo <<<eof
            <div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
                <a href="{$dir['path']}" class="bor_radius">
eof;

                if (!empty($dir['snapshot'])) {
                    if ($index > 0) {
                        echo <<<eof
    <img src="/img/beauty/lazy.svg" data-original="{$dir['snapshot']}" class="bor_radius im_img lazy" alt="{$dir['directory']}">
eof;
                    } else {
                        echo <<<eof
    <img src="{$dir['snapshot']}" class="bor_radius im_img" alt="{$dir['directory']}">
eof;
                    }
                } else if (!empty($dir['files'])) {
                    $first_img = array_shift($dir['files']);
                    if (!in_array($first_img['extension'], $imgExts)) {
                        foreach ($dir['files'] as $file) {
                            if (in_array($file['extension'], $imgExts)) {
                                $first_img = $file;
                                break;
                            }
                        }
                    }

                    if (in_array($first_img['extension'], $imgExts)) {
                        if ($index > 0) {
                            echo <<<eof
    <img src="/img/beauty/lazy.svg" data-original="{$first_img['path']}" class="bor_radius im_img lazy" alt="{$first_img['filename']}">
eof;
                        } else {
                            echo <<<eof
    <img src="{$first_img['path']}" class="bor_radius im_img" alt="{$first_img['filename']}">
eof;
                        }
                    } else {
                        echo <<<eof
    <img src="/img/default.png" class="bor_radius im_img" alt="default image">
eof;
                    }
                }

                $title = !empty($dir['title']) ? $dir['title'] : $dir['directory'];
                echo <<<eof
                <div class="im_img_title">
                    <span>
                        <img src="/img/beauty/folder.svg" alt="folder" width="24">
                        {$title}
                    </span>
                </div>
            </a>
        </div>
eof;
                $index++;
            }


            //分割目录和文件
            echo '</div>';
            if (!empty($category['files']) && count($category['files']) > 3) {
                echo '<hr>';
            }
            echo '<div class="im_mainl row">';
        }

        if (!empty($category['files'])) {        //一级目录支持
            $total = count($category['files']);     //翻页支持
            $pageStartIndex = ($viewData['page']-1) * $viewData['pageSize'];
            $index = 0;
            foreach ($category['files'] as $file) {
                if (!in_array($file['extension'], $imgExts)) {
                    continue;
                }

                //翻页支持
                if ($index < $pageStartIndex) {
                    $index ++;
                    continue;
                }else if ($index >= $pageStartIndex + $viewData['pageSize']) {
                    break;
                }

                $title = !empty($file['title']) ? $file['title'] : $file['filename'];

                if ($index > 0) {
                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="javascript:;" class="bor_radius" data-fancybox="gallery" data-src="{$file['path']}" data-caption="{$title}" title="{$title}">
        <img src="/img/beauty/lazy.svg" data-original="{$file['path']}" class="bor_radius im_img lazy" alt="{$file['filename']}">
        <div class="im_img_title">
            <span>
                <img src="/img/beauty/image.svg" alt="image" width="20">
                {$title}
            </span>
        </div>
    </a>
</div>
eof;
                } else {
                    echo <<<eof
<div class="im_item bor_radius col-xs-6 col-sm-4 col-md-3 col-lg-2">
    <a href="javascript:;" class="bor_radius" data-fancybox="gallery" data-src="{$file['path']}" data-caption="{$title}" title="{$title}">
        <img src="{$file['path']}" class="bor_radius im_img" alt="{$file['filename']}">
        <div class="im_img_title">
            <span>
                <img src="/img/beauty/image.svg" alt="image" width="20">
                {$title}
            </span>
        </div>
    </a>
</div>
eof;
                }

                $index++;
            }
        }
        ?>

    </div>
</div>

<div class="text-center">
<?php
$pagination = Html::getPaginationHtmlCode($viewData['page'], $viewData['pageSize'], $total);
echo $pagination;
?>
</div>