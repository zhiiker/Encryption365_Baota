# TrustOcean Encryption365™ 宝塔面板插件
此项目是由 环智中诚™ (TrustOcean Limited) 专为希望通过宝塔面板来为网站部署自动化SSL证书的用户开发。您可以安装并使用此模块实现下列这些目的：

- 为站点配置自动化申请免费证书 TrustOcean Encryption365 SSL，可配置自动续期
- 为站点配置自动化申请商业证书 TrustOcean/Sectigo 系列全球信任的 SSL 产品, 可配置自动续期
- 实现证书申请的自动化验证, 通过(HTTP/HTTPS)文件自动验证
- 验证完成后自动安装(更新)站点的SSL证书

## 安装和使用指导
我们正在为此项目整理安装、登录、注册、绑定、续期、自动化申请相关的文档。目前已整理完成安装文档，请您参考文档中心连接查看如何安装此模块：
https://support.trustocean.com/doc/C4WDG8Zri0/55LGopN2XX

## 我们创建了问题交流群
QQ群: 709063775

接受实时的问题反馈和错误排除、安装帮助。

## 安装环境要求
目前，仅支持将插件客户端安装在基于下列这些平台(系统)搭建的宝塔面板中：
- CentOS
- Ubuntu(测试中)

同时，您的宝塔面板还需要安装至少一个PHP7语言版本，PHP 版本需要 >= 7.1 。PHP 还需要开启下列这些扩展：
- OpenSSL
- cURL
- Sqlite3
- Mbstring

## 安装包的使用
- `获取完整的安装包` 我们也会从环智中诚[产品交流网站](https://chat.trustocean.com/)或其他渠道发布此项目的完整安装包，完整安装包无需您执行任何依赖安装，直接上传至您的宝塔面板即可安装。

- `基于此项目的克隆安装` 如果您是从此项目主页进行克隆，或从发布页下载的压缩包，那么在您开始安装使用之前，您还需要通过命令行在本项目文件夹下执行命令来完成对应的依赖安装:
```shell
composer install -vvv
```
执行依赖安装完成后，请打包所有的文件为 `encryption365.zip`，并上传至您的宝塔面板进行安装。

## 使用反馈和兼容性
我们目前仅将此客户端在CentOS、Ubuntu部分版本中进行过兼容性测试，暂不支持Windows系统的宝塔面板进行安装使用。若在使用过程中遇到问题，请您反馈至此产品的交流版块下：
https://chat.trustocean.com
以便我们能够继续完善和跟进此项目。

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