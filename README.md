# TrustOcean Encryption365™ 宝塔面板插件
此项目是由 环智中诚™ (TrustOcean Limited) 专为希望通过宝塔面板来为网站部署自动化SSL证书的用户开发。您可以安装并使用此模块实现下列这些目的：

- 为站点配置自动化申请免费证书 TrustOcean Encryption365 SSL，可配置自动续期
- 为站点配置自动化申请商业证书 TrustOcean/Sectigo 系列全球信任的 SSL 产品, 可配置自动续期
- 实现证书申请的自动化验证, 通过(HTTP/HTTPS)文件自动验证
- 验证完成后自动安装(更新)站点的SSL证书

## 在线QQ客服处理问题
客服QQ: 2852368244

客服QQ：228591665

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
Aliyun 2.1903 (Hunting Beagle)(Py3.7.8)|Liunx Beta7.6.10|>=7.1|√|√|√| 由[@shiertx](https://github.com/shiertx)提供测试反馈

## Windows Server 系统支持情况

操作系统/Python版本 | 宝塔版本 | PHP版本 | IIS | Nginx | Apache | 测试信息
 --- | --- | --- | --- | --- | --- | --- 
Windows 2012 (Py3.8.6)|7.1.0|>=7.1|√| √ | 待修复 |



### CentOS6、Debain8 请注意
这两个系统我们目前测试到兼容性会存在问题，且暂时无法从插件代码入手进行兼容。如果您确实需要在CentOS6、Debain8系统的宝塔面板上安装插件，则必须要确保您系统的OpenSSL版本 >= 1.0.2b 版本, 否则将会因缺少部分OpenSSL API而无法正常使用。
经过测试，即便是在安装完插件后，将系统OpenSSL升级到1.1.1a版本，依然会出现不兼容，请考虑在更加新版本的系统中安装和使用。

同时，您的宝塔面板还需要安装至少一个PHP7语言版本，PHP 版本需要 >= 7.1 。PHP 还需要开启下列这些扩展：
- OpenSSL
- cURL
- Sqlite3
- Mbstring

## 安装方法
1. 下载最新的安装包文件 [Encryption365_BtPanel_v**.zip](https://github.com/londry/Encryption365_Baota/releases)
3. 登录您的宝塔面板后台。
4. 依次进入菜单【软件商店】->【第三方应用】->找到【导入插件】并点击开始上传->等待上传完成弹出提示框->确认进行安装->右侧开启首页显示。
5. 点击宝塔面板首页的 Encryption365 图标打开证书管理窗口开始申请和管理证书。

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