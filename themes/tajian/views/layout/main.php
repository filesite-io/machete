<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';

?><!DocType html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $pageTitle;?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="icon" type="image/x-icon" href="/favicon.ico?v1.0">
<link href="/css/main.css?v.1.1" rel="stylesheet">
<!--for theme videoblog-->
<link href="/css/github-markdown-dark.css" rel="stylesheet">
<link href="/css/video-js.min.css" rel="stylesheet">
<link href="/css/videoblog.css?v<?=Html::getStaticFileVersion('videoblog.css', 'css')?>" rel="stylesheet">
<style>
<?php if (!empty(FSC::$app['config']['videoblog']['imageHeight'])) { ?>
    .img-item img{height: <?php echo FSC::$app['config']['videoblog']['imageHeight']; ?>px;}
<?php } ?>
</style>
</head>
<body>

    <div class="header">
        <a href="/" class="logo">
            <img src="/content/machete_icon.png" alt="Logo of FileSite.io" height="34">
            TaJian.tv - Ta荐
        </a>
        <a href="#modal_about" role="button" class="about btn-open">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M191.1 224c0-17.72-14.34-32.04-32-32.04L144 192c-35.34 0-64 28.66-64 64.08v47.79C80 339.3 108.7 368 144 368H160c17.66 0 32-14.36 32-32.06L191.1 224zM256 0C112.9 0 4.583 119.1 .0208 256L0 296C0 309.3 10.75 320 23.1 320S48 309.3 48 296V256c0-114.7 93.34-207.8 208-207.8C370.7 48.2 464 141.3 464 256v144c0 22.09-17.91 40-40 40h-110.7C305 425.7 289.7 416 272 416H241.8c-23.21 0-44.5 15.69-48.87 38.49C187 485.2 210.4 512 239.1 512H272c17.72 0 33.03-9.711 41.34-24H424c48.6 0 88-39.4 88-88V256C507.4 119.1 399.1 0 256 0zM368 368c35.34 0 64-28.7 64-64.13V256.1C432 220.7 403.3 192 368 192l-16 0c-17.66 0-32 14.34-32 32.04L320 335.9C320 353.7 334.3 368 352 368H368z"/></svg>
        </a>
    </div>

<?php
//### Render view file
if (!empty($viewFile) && file_exists($viewFile)) {
    include_once $viewFile;
}
?>

    <div class="modal-mask" id="modal_about">
        <div class="modal-about">
            <div class="modal-head">
                <h3>联系我</h3>
                <span class="btn-close" role="button"><svg width="24" height="24" viewBox="0 0 24 24" focusable="false" class=" NMm5M"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg></span>
            </div>
            <div class="hr"></div>
            <div class="modal-body markdown-body">
                <?php echo !empty($viewData['htmlReadme']) ? $viewData['htmlReadme'] : ''; ?>
            </div>
        </div>
    </div>

    <div class="footer">
        <?php if (!empty(FSC::$app['config']['theme'])) { ?>
            Theme name <strong><?php echo FSC::$app['config']['theme']; ?></strong>
            <br>
        <?php } ?>
        &copy;FSC 2022 - execute time: {page_time_cost} ms
        <?php if (!empty(FSC::$app['config']['videoblog']['contact'])) {
            $contactInfo = FSC::$app['config']['videoblog']['contact'];
            echo <<<eof
        <p>{$contactInfo}</p>
eof;
        } ?>
    </div>

    <!-- The Gallery as lightbox dialog, should be a document body child element -->
    <div
      id="blueimp-gallery"
      class="blueimp-gallery blueimp-gallery-controls"
      aria-label="image gallery"
      aria-modal="true"
      role="dialog"
    >
      <div class="slides" aria-live="polite"></div>
      <h3 class="title"></h3>
      <a
        class="prev"
        aria-controls="blueimp-gallery"
        aria-label="previous slide"
        aria-keyshortcuts="ArrowLeft"
      ></a>
      <a
        class="next"
        aria-controls="blueimp-gallery"
        aria-label="next slide"
        aria-keyshortcuts="ArrowRight"
      ></a>
      <a
        class="close"
        aria-controls="blueimp-gallery"
        aria-label="close"
        aria-keyshortcuts="Escape"
      ></a>
      <a
        class="play-pause"
        aria-controls="blueimp-gallery"
        aria-label="play slideshow"
        aria-keyshortcuts="Space"
        aria-pressed="false"
        role="button"
      ></a>
      <ol class="indicator"></ol>
    </div>

    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/js.cookie.min.js"></script>
    <script src="/js/main.js?v.1.0"></script>
    <!--for theme tajian-->
    <script src="/js/lazysizes.min.js"></script>
    <script src="/js/tajian.js?v<?=Html::getStaticFileVersion('tajian.js', 'js')?>"></script>
</body>
</html>
