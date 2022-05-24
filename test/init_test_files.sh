#!/bin/sh
##-d- 小说
##  |_d_ 金庸小说
##       |_f_ 最爱金庸网站图标.ico
##       |_f_ 最爱金庸.url
##  |_d_ 古龙小说
##       |_f_ 最爱古龙网站图标.ico
##       |_f_ 最爱古龙.url
##-d- 图片
##  |_d_ 图片搜索
##       |_f_ 谷歌图片搜索图标.ico
##       |_f_ 谷歌图片搜索.url
##       |_f_ description.txt
##  |_d_ 必应图片搜索
##       |_f_ bing图标.ico
##       |_f_ bing.url
##       |_f_ title.txt

rm -rf content/


#---for favs---
mkdir -p content/favs/
cd content/favs/

rm -f Readme.md
rm -rf "小说/"
rm -rf "图片/"

touch "Readme.md"
tee "Readme.md" <<EOF
# Favs我的收藏

把我收藏的常用网址分享给大家。

## 图片

常用的图片网站。

<a href="图片/图片搜索/谷歌图片搜索图标.ico" title="谷歌图片搜索">谷歌图片搜索</a>
<a href="./图片/必应图片搜索/bing图标.ico" title="Bing图片搜索">Bing图片搜索</a>
<a href="/content/favs/图片/必应图片搜索/bing图标.ico" title="Bing图片搜索">Bing图片搜索</a>

网站ICON：
<img src="https://www.google.com/favicon.ico" alt="Google">
<img src="http://www.bing.com/favicon.ico" alt="Bing">

## 小说

常用的小说网站。

<img src="小说/金庸小说/最爱金庸网站图标.ico" alt="金庸小说">
<img src="./小说/古龙小说/最爱古龙网站图标.ico" alt="古龙小说">
<img src="../favs/小说/古龙小说/最爱古龙网站图标.ico" alt="古龙小说2">


## 其它

<a href="Others.md" title="其它">其它</a>

EOF

tee "Others.md" <<EOF
# 其它

补充说明。

## 注意事项

测试md文件相互连接。


## 参考文档

Google、Bing等。

<a href="Readme.md" title="首页">返回首页</a>

EOF


mkdir -p "小说/金庸小说/"
mkdir -p "小说/古龙小说/"
mkdir -p "图片/图片搜索/"
mkdir -p "图片/必应图片搜索/"

touch "小说/金庸小说/最爱金庸网站图标.ico"
touch "小说/金庸小说/最爱金庸.url"
tee "小说/金庸小说/最爱金庸.url" <<EOF
[InternetShortcut]
URL=https://www.google.com
EOF
echo '最爱金庸网站图标.ico' > "小说/金庸小说/snapshot.txt"

touch "小说/古龙小说/最爱古龙网站图标.ico"
touch "小说/古龙小说/最爱古龙.url"
tee "小说/金庸小说/最爱古龙.url" <<EOF
[InternetShortcut]
URL=https://www.google.com
EOF

touch "图片/图片搜索/谷歌图片搜索图标.ico"
touch "图片/图片搜索/谷歌图片搜索.url"
tee "图片/图片搜索/谷歌图片搜索.url" <<EOF
[InternetShortcut]
URL=https://www.google.com
EOF
touch "图片/图片搜索/description.txt"
tee "图片/图片搜索/description.txt" <<EOF
什么图片都能搜，
输入关键词，然后点“搜索”
EOF
echo '谷歌图片搜索图标.ico' > "图片/图片搜索/snapshot.txt"

touch "图片/必应图片搜索/bing图标.ico"
touch "图片/必应图片搜索/bing.url"
tee "图片/必应图片搜索/bing.url" <<EOF
[InternetShortcut]
URL=https://www.bing.com
EOF
touch "图片/必应图片搜索/title.txt"
tee "图片/必应图片搜索/title.txt" <<EOF
Bing必应图片搜索
EOF
