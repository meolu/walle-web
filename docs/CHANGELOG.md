## Change Log

### v0.9.5 (2015/11/26 15:19 +00:00)
- 简化svn无trunk/branches提交上线单流程（去掉选择分支）
- cookie 记住上次选择的分支，优化提交上线流程
- 优化未激活时也可以重置密码
- 登录界面的用户名标签改为邮箱
- 把需要更改的参数移到local.php (@Richard87)
- moved language to local to keep language changes to a single file all these changes in a single (@Richard87)
- added /config/local.php to gitignore (@Richard87)
- move all credentials to local.php for easier modification and development (@Richard87)
- 修正js标签导致部分浏览器modal浮窗打开失败问题
- 优化git模式下的rsync
- git mode the same with svn mode, rsync the diff
- login translations (@Richard87)
- add doc of walle

### v0.9.2 (2015/11/19 14:59 +00:00)
- 兼容 svn 无 trunk/tags/branches
- 修正目标机pre-release、post-release任务中{WORKSPACE}变量

### v0.9.0 (2015/11/18 10:07 +00:00)
- reset && checkout => reset --hard
- walle的国际化版本
- 修正支持svn密码特殊字符
- 项目配置：修复了sql_mode在严格模式下提示key_version_num not null (@Liv1020)

### v0.8.5 (2015/11/07 13:06 +00:00)
- 优化项目初始化方式
- 添加最大版本保留数前端验证提示
- 修正第一次上线的任务不能回滚

### v0.8.0 (2015/11/01 09:50 +00:00)
- 增加保留最多版本数
- 回滚数量限制
- 增加用户修改密码
- 任务列表、文件指纹增加项目环境级别
- 部署页面优化
- 优化svn上线流程和文案提示
- 优化修改项目repo地址、svn无branches目录兼容问题
- 引入codeception、集成travis

### v0.7.5 (2015/10/25 22:16 +00:00)
- composer minimum-stability改为stable
- 增加 git、svn 配置指南
- 增加最最最详细安装指南

### v0.7.0 (2015/10/20 15:03 +00:00)
- 标题改为上线单
- 修正.文件
- svn 配置检测
- svn 上线的文件列表说明
- 增加对svn的上线支持

### v0.6.0 (2015/10/15 17:59 +00:00)
- 修正post-release中双引号问题
- 增加pre-release任务
- 增加项目管理员审核
- 增加项目配置复制
- 增加项目配置检测
- 回滚按钮改成文成也可点击 (@itbeihe)
- 非项目创建人不可修改其配置
- 修正前端分支列表获取append问题
- 通过ssh的rsync传输支持非22端口 (@telnetor)
- 初步引入travis

### v0.5.0 (2015/10/11 03:56 +00:00)
- ssh连接支持自定义端口
- 前端获取git信息失败弹窗提示
- 增加项目的审核管理员功能
- 项目用户选择支持批量
- 分支预览排除本地分支

### v0.4.0 (2015/10/05 03:56 +00:00)
- 部署时在一个隔离空间中进行
- 一些关于配置的优化

### v0.3.0 (2015/10/03 07:28 +00:00)
- 个人信息
- 项目的用户关系绑定、项目信息预览

### v0.2.0 (2015/09/29 07:37 +00:00)
- 失败前端提示更明确的原因
- 增加默认用户
- 配置转到mysql
- 环境支持分支选择、项目配置模块拆分
- 注册邮箱后缀可多个
- apache .htaccess
- walle组件调整到components

### v0.1.0 (2015/09/18 16:14 +00:00)
- 任务列表增加字段时间，项目，美化前端，以及管理员界面、配置优化
- 增加管理员操作配置、上线记录和回滚、注册邮箱可自定义
- 上线环境选择分类、上线任务分页、未审核通过，未上线任务可以删除、上线任务列表显示上线时间、线上文件md5