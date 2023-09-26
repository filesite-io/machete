
# FileSite.io - A standard for managing URLs, articles, images, and videos based on files and directories

**Summary:**

Based on existing file formats, we have defined a **standard** for managing several commonly used data types by users, aiming to help users more easily build their own creations into websites while retaining their existing content management habits. App, while also making it easier to publish your creations to various platforms.

This standard currently supports four types of data: URLs, articles, images, and videos, and will expand to other data types if necessary.


## Introduction

We believe that everyone is a creator. He/she can be a writer, photographer, or videographer. Maybe he/she will also collect and classify his/her favorite websites.

Because of the existing platform and environment, most people's creations can only lie quietly on the computer's hard drive, but one day, with the promotion of many practitioners, personal creative content will be built into a website , The threshold for apps will become lower and lower. At that time, everyone can easily and quickly create their own websites and apps, and can publish their works to major platforms using the one-click upload function.

We also firmly believe that **the ownership of everyone's work always belongs to the author**, no matter which platform he/she has published the work on, he/she can delete all his/her works on a certain platform at any time as long as he/she wishes. Data, it is so simple to quickly upload works from a computer to another platform, which will change the relationship between the platform and the creator. It is no longer the creator who depends on the platform, but the platform depends on the creator.

Because of this, we have proposed a new standard that does not use any new technology. It is completely based on existing operating systems, file systems and file types, as well as the existing usage habits of most people. It is also an introduction to the future. Come early!


## Version

Name: filesite_2023

Version number: 20230130

Modification time: 2023-01-30


## Directories and files

The directories and files mentioned in this standard refer to files and directories in common operating systems such as Windows, Linux, and MacOS.

If there are differences in the naming conventions of files and directories in different operating systems on the market, this standard adopts the parts supported by them.


## Data type

The current version supports the following types of data:

| Type | Format |
| ---- | ---- |
| URL | .url shortcut |
| Article | .md markdown file |
| Pictures | .jpg, .png, .gif, .ico |
| Video | .mp4, .m3u8, .ts |


## Data description

If you need to extend the description of the above types of data, please use a plain text file in .txt format to save it. We call this type of .txt file a "**description file**".

The description file naming rules are as follows:
```
Directory description file: {English lowercase attribute name}.txt
File description file: {described file name_}{English lowercase attribute name}.txt
```

Several commonly used attribute description files are as follows:

| File name | Description | Property name |
| ---- | ---- | ---- |
| title.txt | title | title |
| description.txt | Description information | description |
| keywords.txt | keyword information | keywords |
| snapshot.txt | snapshot picture | snapshot |


## Directory and file structure

The directory can contain subdirectories and files, and the directory hierarchy supports up to 5 levels**.

Use directories to **group** data, and put files in the same group in the same directory.

Example (the letters **d** represent directories and the letters **f** represent files):
```
-d- novel
   |_d_ Jin Yong’s novels
        |_f_ Favorite Jin Yong website icon.ico
        |_f_ Favorite Jin Yong.url
   |_d_ Gu Long Novels
        |_f_ Favorite cologne website icon.ico
        |_f_ favorite cologne.url
-d- pictures
   |_d_ Image Search
        |_f_ Google image search icon.ico
        |_f_ Google Image Search.url
        |_f_ description.txt
   |_d_ Bing Image Search
        |_f_ bingicon.ico
        |_f_ bing.url
        |_f_ title.txt
```



## API data structure

### Directory

```
[
     'id' => 'A unique number generated based on the complete path',
     'pid' => 'parent directory id', //if there is a parent directory
     'directory' => 'Eternal Dragon Sword',
     'realpath' => '/www/webroot/content/天杀龙记/',
     'path' => '/list/?id={id}',
     'snapshot' => '/content/The cover image of Yitian Slaying the Dragon.jpg',
     'files' => [...], //file list
     'directories' => [...] //Directory list
]
```

### File

Articles, pictures, and video files other than URLs.

```
[
     'id' => 'A unique number generated based on the complete path',
     'pid' => 'parent directory id', //if there is a parent directory
     'filename' => 'Chapter 1',
     'realpath' => '/www/webroot/content/天杀龙记/Chapter 1.md',
     'path' => '/view/?id={id}',
     'extension' => 'md',
     'fstat' => [...], //Same as PHP method fstat: https://www.php.net/manual/en/function.fstat.php
     'content' => 'Article content...',
     'description' => 'Article introduction...',
     'keywords' => 'Article keywords...',
     'snapshot' => '/content/Eternal Dragon Sword/Chapter 1 Cover Picture.jpg',
]
```

.txt, .md and .url 3 file descriptions:
*.txt files are description files for all other files and will not appear in the file list;
* .md will read the file content and store it in the attribute content;
* .url reads the file content and stores it in the attribute shortcut;


### URL-Shortcut

The .url file is a universal web page shortcut. Its data structure has one more attribute than the above file data structure: **shortcut**.

```
[
     'id' => 'A unique number generated based on the complete path',
     'pid' => 'parent directory id', //if there is a parent directory
     'filename' => 'filesite.io',
     'realpath' => '/www/webroot/content/URL Navigation/filesite.io.url',
     'path' => '/link/?id={id}',
     'extension' => 'url',
     'fstat' => [...], //Same as PHP method fstat: https://www.php.net/manual/en/function.fstat.php
     'shortcut' => [
         'name' => 'filesite.io',
         'url' => 'https://filesite.io',
     ],
]
```

Example of .url file content:
```
[InternetShortcut]
URL=https://microsoft.com/
```


## PHP implementation

We made an open source program called Machete using PHP based on this standard. You can find it on github:
```
https://github.com/filesite-io/machete/
```

For detailed introduction, see:

[Machete - database-free, file- and directory-based Markdown document, website navigation, book, picture, video website PHP open source system](../Machete_Doc.md)


## Contact us

If you find Filesite.io helpful and would like to use it in a project, we'd love you to share your story with us:

* Send email to us:
```
machete@filesite.io
```

* Join QQ group:

  <a href="https://jq.qq.com/?_wv=1027&k=WoH3Pv7d" target="_blank">Website navigation, picture, video website exchange group</a>

* Scan the QR code to add WeChat friends:

  <img src="../wx_jialuoma.jpeg" alt="Scan the WeChat QR code to add friends" width="240" />


## 简体中文版

[FileSite.io，一个基于文件和目录管理网址、文章、图片、视频的标准](../README.md)
