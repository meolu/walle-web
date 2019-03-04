![](https://raw.github.com/meolu/walle-web/master/screenshot/logo.jpg)

Walle 2.0 - [官方主页](https://www.walle-web.io)
=========================
[![Build Status](https://travis-ci.org/meolu/walle-web.svg?branch=master)](https://travis-ci.org/meolu/walle-web)

功能强大，且免费开源的`walle-web 瓦力`终于更新`2.0.0`了！！！

walle 让用户代码发布终于可以不只能选择 jenkins！支持各种web代码发布，php、java、python、go等代码的发布、回滚可以通过web来一键完成。walle 一个可自由配置项目，更人性化，高颜值，支持git、多用户、多语言、多项目、多环境同时部署的开源上线部署系统。

`2.0.0` 占用了我几乎所有业余时间，精力与金钱付出换各位使用收益，望各位喜欢不吝顺手 `star` 以示支持，项目更好亦反馈予你。目前 `2.0.0` 已经发布，请保持关注，我会在公众号更新（在最下面）。  


有推广资源（开源文章推荐、大会分享）的同学，请微信联系我，强烈需要帮助。另外，老版本已迁移到 [walle 1.x](https://github.com/meolu/walle-web-v1.x) 的同学**务必不要再更新了**，两个版本不兼容

Feature
=========================
- 类`gitlab`的`RESTful API`，类`gitlab`的权限模型。将来打通`gitlab`，良心的惊喜
- 空间管理。意味着有独立的空间资源：环境管理、用户组、项目、服务器等
- 灰度发布。呼声不断，终于来了
- 项目管理。Deploy、Release的前置及后置hook，自定义全局变量；自带检测、复制功能，都贴心到这种程度了
- `websocket` 实时展示部署中的 `shell console`，跟真的终端长得一样。
- 完善的通知机制。邮件、钉钉
- 全新的UI，我自己都被震憾到了，如丝般流畅

Architecture
=========================
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/walle-flow-relation.jpg)
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/permission.png)

Preview
=========================
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/user-list.png)
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/project-list.png)
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/task-list.png)
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/deploy-console.png)
![](https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/project_java_tomcat.png)

Installation
=========================
[快速安装](https://walle-web.io/docs/2/installation.html) | [安装错误](https://walle-web.io/docs/2/install-error.html) | [常见错误排解](https://walle-web.io/docs/2/troubleshooting.html)

Roadmap
=========================
- [x] **预览版**  2018-12-02
    - ~~安装文档、前后端代码、Data Migration~~
- [x] **Alpha** 2018-12-09
    - ~~使用文档、Trouble Shooting、公众号更新~~
- [x] **Beta** 2018-12-23 :santa:圣诞夜前夕
    - ~~钉钉/邮件消息通知~~
    - ~~接受官网logo企业的`Trouble Shooting`~~
- [x] **2.0.0**  2018-12-30 :one:元旦前夕
    - ~~项目检测、复制~~
    - ~~任务的回滚~~
    - ~~`released tag`、使用文档~~
    - ~~`Docker` 镜像~~
    - ~~Java配置模板~~
    - ~~PHP配置模板~~
    - ~~`github` 5000 `star`~~
- [x] **2.0.1**  2019-01-13
    - ~~项目配置添加自定义变量~~
    - ~~Python 3.7+兼容~~
- **2.1.0**  2019-03-22
    - 超管权限完善
    - `Dashboard` 1.0（全新的玩法，欢迎提issue）
    - 3月24日开源中国苏州源创会-[开源综合技术主题](https://www.oschina.net/event/2303765)《开源构建多空间可视化一键部署Devops平台》
    - 冲刺`github` 10000 `star`（靠你们和你们的同事们了）
- **2.2.0**  2019-04-22
    - webhook (gitlab)
    - 上线时间记录、命令与结果拆分、实时console
    - 宿主机资源监控
- **2.3.0**  2019-05-27
    - 插件化：maven、npm
    - pipeline式
- **2.4.0**  2019-06-17
    - i18n 国际化
- **2.5.0**  2019-07-29
    - 上线单Diff
    - 消息通知定制化：钉钉、邮件、企业微信
- **2.6.0**  2019-08-19
    - 批量管理服务器
    - 跨空间复制项目
    - App打包平台
    - `Dashboard` 2.0
- 更多需求收集中


Discussing
=========================
- [submit issue](https://github.com/meolu/walle-web/issues/new)


勾搭下
=========================
写开源是我的业余爱好，大数据平台和营销技术才是主业，无论哪个都欢迎交流。  
人脉也是一项重要能力，请备注姓名@公司，谢谢：）

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/screenshot/weixin-wushuiyong.jpg" width="244" height="314" alt="吴水永微信" align=left />

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/screenshot/weixin-huakai.jpg" width="244" height="314" alt="花开微信" align=left />

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/screenshot/weixin-ye.jpg" width="244" height="314" alt="叶歆昊微信" align=left />


<br><br><br><br><br><br><br><br><br><br><br><br><br><br>

新的惊喜
=========================
后续更新和解剖讨论、以及walle有趣的人和事，将会放到公众号：walle-web，晨间除了写开源，也会写千字文，关注不迷路：）

<img src="https://raw.githubusercontent.com/meolu/walle-web/master/screenshot/wechat-gzh.jpg" width="244" height="314" alt="公众号 walle-web" />


打赏作者杯咖啡
=========================
你也不一定要赞赏，芸芸众生，相遇相识是一种缘份。不过可以给点个star，或者关注公众号，哈

<img src="https://raw.github.com/meolu/docs/master/walle-web.io/docs/2/zh-cn/static/appreciation-wechat.jpg" width="220" height="220" alt="赞赏码" style="float: left;"/>

Code Visualization
=========================
感谢`gitviscode`组织制作的`commit history`视频，记录从15年萌芽发展，有那么多开发者加入完善。1'50的时候，以为项目都停止更新了，然后突然如烟花绽放的感觉，我他妈都感动得要哭了

 [![Watch the video](https://img.youtube.com/vi/AIir52mETMY/0.jpg)](https://www.youtube.com/watch?v=AIir52mETMY)

 [https://www.youtube.com/watch?v=AIir52mETMY](https://www.youtube.com/watch?v=AIir52mETMY)
