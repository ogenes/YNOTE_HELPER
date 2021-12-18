# 批量下载有道云笔记

### 一、执行php脚本 或者 python脚本

```shell script
➜  YNOTE_HELPER git:(master) ✗ php downloader.php
```
或者
```shell script
➜  YNOTE_HELPER git:(master) ✗ python3 downloader.py
```
下载下来的笔记会保存到 note 目录中。


### 二、Cookie
目前没有做到自动登录，需要在网页上登录，复制cookie信息到配置文件中执行；

![image-20211218192738225](https://ogenes.oss-cn-beijing.aliyuncs.com/img/2021/202112181927313.png)
在网页端登录之后， 打开控制台， 点击 “应用”， 在 "存储" -> "cookie" 里面搜索 YNOTE ,

找到 YNOTE_SESS / YNOTE_LOGIN  项， 复制对应的值,

保存到 config.json 中；

```json
{
  "YNOTE_SESS": "",
  "YNOTE_LOGIN": ""
}
```
