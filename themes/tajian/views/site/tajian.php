<?php
//常用方法
require_once __DIR__ . '/../../../../plugins/Html.php';
$imgPreffix = '/' . FSC::$app['config']['content_directory'] . FSC::$app['config']['tajian']['data_dir'];

$loginBackUrl = !empty($viewData['loginedUser']['username']) ? "/{$viewData['loginedUser']['username']}/my/share" : '/my/share';
?>
<main>
    <section class="hero text-center">
        <div class="container-sm">
            <div class="hero-inner">
                <h1 class="hero-title h2-mobile mt-0 is-revealing">
                    你的聚宝盆
                    <small><span class="hidden-xs">- </span>帮你收纳不同App/网站有价值的视频</small>
                </h1>
                <p class="hero-paragraph is-revealing">
                    可添加B站、抖音、快手等平台的视频、直播链接，Ta荐是你的视频收纳盒，分类整理你喜欢的内容，随时快速找到它们！
                </p>
                <div class="hero-form newsletter-form field field-grouped is-revealing">
                    <div class="control">
                        <a class="button button-primary button-block button-shadow" href="/site/register">
                            <img class="btn_icon" src="/img/favorite.png" alt="Create your favorite tajian link.">
                            创建聚宝盆
                        </a>
                    </div>
                    <div class="control">
                        <a class="button button-block button-shadow" href="/site/login/?go=<?=$loginBackUrl?>">
                            <img class="btn_icon" src="/img/share.png" alt="分享给朋友">
                            分享给朋友
                        </a>
                    </div>
                    <div class="control">
                        <a class="button button-block button-shadow button-cool" href="#contact">
                            <img class="btn_icon" src="/img/video-play.svg" alt="搭建视频网站">
                            搭建视频站
                        </a>
                    </div>
                </div>
                <div class="hero-browser">
                    <div class="bubble-3 is-revealing">
                        <svg width="427" height="286" viewBox="0 0 427 286" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">
                            <defs>
                                <path d="M213.5 286C331.413 286 427 190.413 427 72.5S304.221 16.45 186.309 16.45C68.396 16.45 0-45.414 0 72.5S95.587 286 213.5 286z" id="bubble-3-a"></path>
                            </defs>
                            <g fill="none" fill-rule="evenodd">
                                <mask id="bubble-3-b" fill="#fff">
                                    <use xlink:href="#bubble-3-a"></use>
                                </mask>
                                <use fill="#4E8FF8" xlink:href="#bubble-3-a"></use>
                                <path d="M64.5 129.77c117.913 0 213.5-95.588 213.5-213.5 0-117.914-122.779-56.052-240.691-56.052C-80.604-139.782-149-201.644-149-83.73c0 117.913 95.587 213.5 213.5 213.5z" fill="#1274ED" mask="url(#bubble-3-b)"></path>
                                <path d="M381.5 501.77c117.913 0 213.5-95.588 213.5-213.5 0-117.914-122.779-56.052-240.691-56.052C236.396 232.218 168 170.356 168 288.27c0 117.913 95.587 213.5 213.5 213.5z" fill="#75ABF3" mask="url(#bubble-3-b)"></path>
                            </g>
                        </svg>
                    </div>
                    <div class="bubble-4 is-revealing">
                        <svg width="230" height="235" viewBox="0 0 230 235" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">
                            <defs>
                                <path d="M196.605 234.11C256.252 234.11 216 167.646 216 108 216 48.353 167.647 0 108 0S0 48.353 0 108s136.959 126.11 196.605 126.11z" id="bubble-4-a"></path>
                            </defs>
                            <g fill="none" fill-rule="evenodd">
                                <mask id="bubble-4-b" fill="#fff">
                                    <use xlink:href="#bubble-4-a"></use>
                                </mask>
                                <use fill="#7CE8DD" xlink:href="#bubble-4-a"></use>
                                <circle fill="#3BDDCC" mask="url(#bubble-4-b)" cx="30" cy="108" r="108"></circle>
                                <circle fill="#B1F1EA" opacity=".7" mask="url(#bubble-4-b)" cx="265" cy="88" r="108"></circle>
                            </g>
                        </svg>
                    </div>
                    <div class="hero-browser-inner is-revealing">
                        <svg width="800" height="450" viewBox="0 0 800 450" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">
                            <defs>
                                <linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="browser-a">
                                    <stop stop-color="#F6F8FA" stop-opacity=".48" offset="0%"></stop>
                                    <stop stop-color="#F6F8FA" offset="100%"></stop>
                                </linearGradient>
                                <linearGradient x1="50%" y1="100%" x2="50%" y2="0%" id="browser-b">
                                    <stop stop-color="#F6F8FA" stop-opacity=".48" offset="0%"></stop>
                                    <stop stop-color="#F6F8FA" offset="100%"></stop>
                                </linearGradient>
                                <linearGradient x1="100%" y1="-12.816%" x2="0%" y2="-12.816%" id="browser-c">
                                    <stop stop-color="#F6F8FA" stop-opacity="0" offset="0%"></stop>
                                    <stop stop-color="#E3E7EB" offset="50.045%"></stop>
                                    <stop stop-color="#F6F8FA" stop-opacity="0" offset="100%"></stop>
                                </linearGradient>
                                <filter x="-500%" y="-500%" width="1000%" height="1000%" filterUnits="objectBoundingBox" id="dropshadow-1">
                                    <feOffset dy="16" in="SourceAlpha" result="shadowOffsetOuter"></feOffset>
                                    <feGaussianBlur stdDeviation="24" in="shadowOffsetOuter" result="shadowBlurOuter"></feGaussianBlur>
                                    <feColorMatrix values="0 0 0 0 0.12 0 0 0 0 0.17 0 0 0 0 0.21 0 0 0 0.2 0" in="shadowBlurOuter"></feColorMatrix>
                                </filter>
                            </defs>
                            <g fill="none" fill-rule="evenodd">
                                <rect width="800" height="450" rx="4" fill="#FFF" style="mix-blend-mode:multiply;filter:url(#dropshadow-1)"></rect>
                                <rect width="800" height="450" rx="4" fill="#FFF"></rect>
                                <g fill="url(#browser-a)" transform="translate(47 67)">
                                    <path d="M146 0h122v122H146zM0 0h122v122H0zM292 0h122v122H292zM438 0h122v122H438zM584 0h122v122H584z"></path>
                                </g>
                                <g transform="translate(47 239)" fill="url(#browser-b)">
                                    <path d="M146 0h122v122H146zM0 0h122v122H0zM292 0h122v122H292zM438 0h122v122H438zM584 0h122v122H584z"></path>
                                </g>
                                <path fill="url(#browser-c)" d="M0 146h706v2H0z" transform="translate(47 67)"></path>
                                <g transform="translate(0 12)">
                                    <circle fill="#FF6D8B" cx="36" cy="4" r="4"></circle>
                                    <circle fill="#FFCB4F" cx="52" cy="4" r="4"></circle>
                                    <circle fill="#7CE8DD" cx="68" cy="4" r="4"></circle>
                                    <path fill="url(#browser-c)" d="M0 20h800v2H0z"></path>
                                    <path fill="#E3E7EB" d="M742 2h24v4h-24z"></path>
                                </g>
                                <g>
                                    <path fill="#F6F8FA" d="M47 385h706v32H47z"></path>
                                    <path fill="#E3E7EB" d="M356 399h88v4h-88z"></path>
                                </g>
                            </g>
                        </svg>
                        <div class="hero-footer">
                            <iframe src="//player.bilibili.com/player.html?isOutside=true&aid=1754762795&bvid=BV1Tt421u7dF&cid=1556103473&p=1" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true" style="width:100%;height:100%"></iframe>
                        </div>
                    </div>
                    <div class="bubble-1 is-revealing">
                        <svg width="61" height="52" viewBox="0 0 61 52" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">
                            <defs>
                                <path d="M32 43.992c17.673 0 28.05 17.673 28.05 0S49.674 0 32 0C14.327 0 0 14.327 0 32c0 17.673 14.327 11.992 32 11.992z" id="bubble-1-a"></path>
                            </defs>
                            <g fill="none" fill-rule="evenodd">
                                <mask id="bubble-1-b" fill="#fff">
                                    <use xlink:href="#bubble-1-a"></use>
                                </mask>
                                <use fill="#FF6D8B" xlink:href="#bubble-1-a"></use>
                                <path d="M2 43.992c17.673 0 28.05 17.673 28.05 0S19.674 0 2 0c-17.673 0-32 14.327-32 32 0 17.673 14.327 11.992 32 11.992z" fill="#FF4F73" mask="url(#bubble-1-b)"></path>
                                <path d="M74 30.992c17.673 0 28.05 17.673 28.05 0S91.674-13 74-13C56.327-13 42 1.327 42 19c0 17.673 14.327 11.992 32 11.992z" fill-opacity=".8" fill="#FFA3B5" mask="url(#bubble-1-b)"></path>
                            </g>
                        </svg>
                    </div>
                    <div class="bubble-2 is-revealing">
                        <svg width="179" height="126" viewBox="0 0 179 126" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">
                            <defs>
                                <path d="M104.697 125.661c41.034 0 74.298-33.264 74.298-74.298s-43.231-7.425-84.265-7.425S0-28.44 0 12.593c0 41.034 63.663 113.068 104.697 113.068z" id="bubble-2-a"></path>
                            </defs>
                            <g fill="none" fill-rule="evenodd">
                                <mask id="bubble-2-b" fill="#fff">
                                    <use xlink:href="#bubble-2-a"></use>
                                </mask>
                                <use fill="#838DEA" xlink:href="#bubble-2-a"></use>
                                <path d="M202.697 211.661c41.034 0 74.298-33.264 74.298-74.298s-43.231-7.425-84.265-7.425S98 57.56 98 98.593c0 41.034 63.663 113.068 104.697 113.068z" fill="#626CD5" mask="url(#bubble-2-b)"></path>
                                <path d="M43.697 56.661c41.034 0 74.298-33.264 74.298-74.298s-43.231-7.425-84.265-7.425S-61-97.44-61-56.407C-61-15.373 2.663 56.661 43.697 56.661z" fill="#B1B6F1" opacity=".64" mask="url(#bubble-2-b)"></path>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features section text-center">
        <div class="container">
            <div class="features-inner section-inner has-bottom-divider">
                <div class="features-wrap">
                    <div class="feature is-revealing">
                        <div class="feature-inner">
                            <div class="feature-icon">
                                <svg width="80" height="80" xmlns="https://www.w3.org/2000/svg">
                                    <g fill="none" fill-rule="evenodd">
                                        <path d="M48.066 61.627c6.628 0 10.087-16.79 10.087-23.418 0-6.627-5.025-9.209-11.652-9.209C39.874 29 24 42.507 24 49.135c0 6.627 17.439 12.492 24.066 12.492z" fill-opacity=".24" fill="#A0A6EE"></path>
                                        <path d="M26 54l28-28" stroke="#838DEA" stroke-width="2" stroke-linecap="square"></path>
                                        <path d="M26 46l20-20M26 38l12-12M26 30l4-4M34 54l20-20M42 54l12-12" stroke="#767DE1" stroke-width="2" stroke-linecap="square"></path>
                                        <path d="M50 54l4-4" stroke="#838DEA" stroke-width="2" stroke-linecap="square"></path>
                                    </g>
                                </svg>
                            </div>
                            <h3 class="feature-title">谁在用Ta荐</h3>
                            <p class="text-sm">
                                布道者、意见领袖，发烧友、分享达人，有<strong>收藏、整理</strong>知识库习惯的朋友...
                            </p>
                        </div>
                    </div>
                    <div class="feature is-revealing">
                        <div class="feature-inner">
                            <div class="feature-icon">
                                <svg width="80" height="80" xmlns="https://www.w3.org/2000/svg">
                                    <g fill="none" fill-rule="evenodd">
                                        <path d="M48.066 61.627c6.628 0 10.087-16.79 10.087-23.418 0-6.627-5.025-9.209-11.652-9.209C39.874 29 24 42.507 24 49.135c0 6.627 17.439 12.492 24.066 12.492z" fill-opacity=".24" fill="#75ABF3"></path>
                                        <path d="M34 52V35M40 52V42M46 52V35M52 52V42M28 52V28" stroke="#4D8EF7" stroke-width="2" stroke-linecap="square"></path>
                                    </g>
                                </svg>
                            </div>
                            <h3 class="feature-title">使用步骤</h3>
                            <p class="text-sm">
                                从各视频App、网站<strong>复制分享链接</strong>，登录后在“<strong>添加收藏</strong>”里<strong>粘贴</strong>保存
                            </p>
                        </div>
                    </div>
                </div>
                <div class="features-wrap">
                    <div class="feature is-revealing">
                        <div class="feature-inner">
                            <div class="feature-icon">
                                <svg width="80" height="80" xmlns="https://www.w3.org/2000/svg">
                                    <g fill="none" fill-rule="evenodd">
                                        <path d="M48.066 61.627c6.628 0 10.087-16.79 10.087-23.418 0-6.627-5.025-9.209-11.652-9.209C39.874 29 24 42.507 24 49.135c0 6.627 17.439 12.492 24.066 12.492z" fill-opacity=".32" fill="#FF97AC"></path>
                                        <path stroke="#FF6D8B" stroke-width="2" stroke-linecap="square" d="M49 45h6V25H35v6M43 55h2v-2M25 53v2h2M27 35h-2v2"></path>
                                        <path stroke="#FF6D8B" stroke-width="2" stroke-linecap="square" d="M43 35h2v2M39 55h-2M33 55h-2M39 35h-2M33 35h-2M45 49v-2M25 49v-2M25 43v-2M45 43v-2"></path>
                                    </g>
                                </svg>

                            </div>
                            <h3 class="feature-title">支持的平台</h3>
                            <p class="text-sm">B站（bilibili）、抖音、快手、西瓜视频，其它<strong>任何网址</strong>（限VIP使用）</p>
                        </div>
                    </div>
                    <div class="feature is-revealing" id="contact">
                        <div class="feature-inner">
                            <div class="feature-icon">
                                <svg width="80" height="80" xmlns="https://www.w3.org/2000/svg">
                                    <g transform="translate(24 25)" fill="none" fill-rule="evenodd">
                                        <path d="M24.066 36.627c6.628 0 10.087-16.79 10.087-23.418C34.153 6.582 29.128 4 22.501 4 15.874 4 0 17.507 0 24.135c0 6.627 17.439 12.492 24.066 12.492z" fill-opacity=".32" fill="#A0EEE5"></path>
                                        <circle stroke="#39D8C8" stroke-width="2" stroke-linecap="square" cx="5" cy="4" r="4"></circle>
                                        <path stroke="#39D8C8" stroke-width="2" stroke-linecap="square" d="M23 22h8v8h-8zM11 10l9 9"></path>
                                    </g>
                                </svg>
                            </div>
                            <h3 class="feature-title">我要搭建</h3>
                            <p class="text-sm">
                                付费协助搭建<strong>视频分享网站</strong>，请发Email联系：<strong>machete#filesite.io</strong>，替换#为@
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="newsletter section">
        <div class="container-sm">
            <div class="newsletter-header text-center is-revealing">
                <h2 class="section-title">案例展示</h2>
            </div>
            <div class="hero-form newsletter-form field field-grouped is-revealing">
                <div class="control" style="min-width:33.33%">
                    <a class="button button-block button-shadow" href="/2001" target="_blank">
                        <img class="btn_icon" src="/img/avatar/women.svg" alt="women svg">
                        阅人无数
                    </a>
                </div>
                <div class="control" style="min-width:33.33%">
                    <a class="button button-block button-shadow" href="/1003" target="_blank">
                        <img class="btn_icon" src="/img/avatar/ai-bot.svg" alt="ai-bot svg">
                        秒懂AI
                    </a>
                </div>
                <div class="control" style="min-width:33.33%">
                    <a class="button button-block button-shadow" href="/1000" target="_blank">
                        <img class="btn_icon" src="/img/avatar/master.svg" alt="master svg">
                        一灯大师
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="newsletter section">
        <div class="container-sm">
            <div class="newsletter-inner section-inner">
                <div class="newsletter-header text-center is-revealing">
                    <h2 class="section-title mt-0">Ta荐核心数据</h2>
                    <div class="stats">
                        <span class="col success">
                            <strong><?=$viewData['stats']['video']?></strong>
                            <label>收藏</label>
                        </span>
                        <span class="col info">
                            <strong><?=$viewData['stats']['user']?></strong>
                            <label>用户</label>
                        </span>
                        <span class="col">
                            <strong><?=$viewData['stats']['tag']?></strong>
                            <label>分类</label>
                        </span>
                    </div>
                    <p class="section-paragraph">因为有你，从此世界变得不一样～</p>
                </div>
            </div>
        </div>
    </section>
</main>
