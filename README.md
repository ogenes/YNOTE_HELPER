#批量下载有道云笔记

目前没有做到自动登录，需要在网页上登录，复制cookie信息到配置文件中执行；


### 查看本地PHP环境

```sh
#Mac自带的php就可以
➜  ~ php -v
PHP 7.3.29 (cli) (built: Aug 15 2021 23:10:16) ( NTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.3.29, Copyright (c) 1998-2018 Zend Technologies

```

### 下载脚本

```sh
➜  www git clone https://github.com/ogenes/YNOTE_HELPER.git
Cloning into 'YNOTE_HELPER'...
remote: Enumerating objects: 5, done.
remote: Counting objects: 100% (5/5), done.
remote: Compressing objects: 100% (4/4), done.
remote: Total 5 (delta 0), reused 0 (delta 0), pack-reused 0
Receiving objects: 100% (5/5), done.
➜  www 
➜  www cd YNOTE_HELPER 
➜  YNOTE_HELPER git:(master) ✗ ll
total 40
-rw-r--r--  1 ogenes  staff   1.0K Dec 16 13:59 LICENSE
-rw-r--r--  1 ogenes  staff    46B Dec 16 13:59 README.md
-rw-r--r--  1 ogenes  staff    64B Dec 16 14:45 config.json
-rwxr-xr-x  1 ogenes  staff   5.3K Dec 16 14:38 downloader.php

```

### 网页端登录并复制Cookie

<img src="https://ogenes.oss-cn-beijing.aliyuncs.com/img/2021/202112161411610.png" alt="image-20211216141110524" style="zoom:50%;" />

在网页端登录之后， 打开控制台， 点击 “应用”， 在 "存储" -> "cookie" 里面搜索 YNOTE ,

找到 YNOTE_SESS / YNOTE_LOGIN /  YNOTE_CSTK  三项， 复制对应的值,

保存到 config.json 中；

```json
{
  "YNOTE_SESS": "",
  "YNOTE_LOGIN": "",
  "YNOTE_CSTK": ""
}
```



### 执行脚本

```sh
➜  YNOTE_HELPER git:(master) ✗ php downloader.php 
………………
………………
………………

 Over !
➜  YNOTE_HELPER git:(master) ✗ ll
total 40
-rw-r--r--   1 ogenes  staff   1.0K Dec 16 13:59 LICENSE
-rw-r--r--   1 ogenes  staff    46B Dec 16 13:59 README.md
-rw-r--r--   1 ogenes  staff    64B Dec 16 14:45 config.json
-rwxr-xr-x   1 ogenes  staff   5.3K Dec 16 14:38 downloader.php
drwxr-xr-x  13 ogenes  staff   416B Dec 16 14:33 note

```

下载下来的笔记会保存到 note 目录中。
