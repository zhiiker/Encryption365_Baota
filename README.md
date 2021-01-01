# TrustOcean Encryption365™ 宝塔面板插件
此项目是由 环智中诚™ (TrustOcean Limited) 专为希望通过宝塔面板来为网站部署自动化SSL证书的用户开发。您可以安装并使用此模块实现下列这些目的：

- 为站点配置自动化申请免费证书 TrustOcean Encryption365 SSL，可配置自动续期
- 为站点配置自动化申请商业证书 TrustOcean/Sectigo 系列全球信任的 SSL 产品, 可配置自动续期
- 实现证书申请的自动化验证, 通过(HTTP/HTTPS)文件自动验证
- 验证完成后自动安装(更新)站点的SSL证书

## 安装和使用指导
我们正在为此项目整理安装、登录、注册、绑定、续期、自动化申请相关的文档。目前已整理完成安装文档，请您参考文档中心连接查看如何安装此模块：
https://support.trustocean.com/doc/C4WDG8Zri0/55LGopN2XX

## 在线QQ客服处理问题
QQ群: 2852368244
提供实时的问题反馈和错误排除、安装帮助。

## 安装环境要求
下面这些环境是经过测试的，您可以在这些版本上完美运行而不受影响，如果您有在其他版本系统中正常运行，请反馈给我们已添加到此列表：

操作系统/Python版本 | 宝塔版本 | PHP版本 | 安装插件 | 定时任务 | 证书申请 | 测试信息
 --- | --- |---|---|---|---|---
CentOS 6.10 (Final)(Py3.7.8)|7.4.7|>=7.1|√|×|×|OpenSSL不支持
CentOS 7.8.2003(Py3.7.8)|7.4.7|>=7.1|√|√|√|
CentOS 8.2.2004(Py3.7.8)|7.4.7|>=7.1|√|√|√|
Ubuntu 16.04.3 LTS(Py3.7.8)|7.4.7|>=7.1|√|√|√|
Ubuntu 18.04 LTS(Py3.7.8)|7.4.7|>=7.1|√|√|√|
Ubuntu 20.04 LTS(Py3.7.8)|7.4.7|>=7.1|√|√|√|
Debian GNU/Linux 8(Py3.7.8)|7.4.7|>=7.1|√|×|×|OpenSSL不支持
Debian GNU/Linux 9(Py3.7.8)|7.4.7|>=7.1|√|√|√|
Debian GNU/Linux 10(Py3.7.8)|7.4.7|>=7.1|√|√|√|

### CentOS6、Debain8 请注意
这两个系统我们目前测试到兼容性会存在问题，且暂时无法从插件代码入手进行兼容。如果您确实需要在CentOS6、Debain8系统的宝塔面板上安装插件，则必须要确保您系统的OpenSSL版本 >= 1.0.2b 版本, 否则将会因缺少部分OpenSSL API而无法正常使用。
经过测试，即便是在安装完插件后，将系统OpenSSL升级到1.1.1a版本，依然会出现不兼容，请考虑在更加新版本的系统中安装和使用。

同时，您的宝塔面板还需要安装至少一个PHP7语言版本，PHP 版本需要 >= 7.1 。PHP 还需要开启下列这些扩展：
- OpenSSL
- cURL
- Sqlite3
- Mbstring

## 安装包的使用
- `获取完整的安装包` 请在本项目的发布页面或右侧链接查找发布的完整版安装包。

- `基于此项目的克隆安装` 如果您是从此项目主页进行克隆，或从发布页下载的压缩包，那么在您开始安装使用之前，您还需要通过命令行在本项目文件夹下执行命令来完成对应的依赖安装:
```shell
composer install -vvv
```
1.执行依赖安装完成后，请打包所有的文件赋值到新的文件夹 `encryption365` ，
2.并打包新的文件夹为 `encryption365.zip` 压缩包，
3.并登录您的宝塔面板->【软件商店】->【第三方应用】->【导入插件】->等待上传完成弹出提示框->确认进行安装->右侧开启首页显示。

 ## 授权方式 MIT License
 
 [百度百科解释](https://baike.baidu.com/item/MIT%E8%AE%B8%E5%8F%AF%E8%AF%81)
 [英文解释 MIT License](https://choosealicense.com/licenses/mit/#)
 
 Copyright (c) 2019 TrustOcean Limited
 
 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:
 
 The above copyright notice and this permission notice shall be included in all
 copies or substantial portions of the Software.
 
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 SOFTWARE.