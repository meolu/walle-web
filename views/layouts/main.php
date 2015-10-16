<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\models\User;
use app\assets\AppAsset;
use app\widgets\Alert;
use app\components\GlobalHelper;

$user = User::findOne(\Yii::$app->user->id);
$userName =  \Yii::$app->user->id ? $user->getName() : '';
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?= Html::encode($this->title) ?> - Walle 瓦力平台</title>
    <link href="/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/dist/css/font-awesome.min.css" rel="stylesheet" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="/dist/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <!-- ace styles -->
    <link rel="stylesheet" href="/dist/css/chosen.css" />
    <link rel="stylesheet" href="/dist/css/ace.min.css" />
    <link rel="stylesheet" href="/dist/css/ace-rtl.min.css" />
    <link rel="stylesheet" href="/dist/css/ace-skins.min.css" />
    <link rel="stylesheet" href="/dist/css/walle.css" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="/dist/css/ace-ie.min.css" />
    <![endif]-->

    <!--[if !IE]> -->
    <script type="text/javascript">
        window.jQuery || document.write("<script src='/dist/js/jquery-2.0.3.min.js'>"+"<"+"script>");
    </script>
    <!-- <![endif]-->

    <!--[if IE]>
    <script src='/dist/js/jquery-1.10.2.min.js'> <script>;
    <![endif]-->


    <!-- ace settings handler -->
    <script src="/dist/js/ace-extra.min.js"></script>
    <script src="/dist/js/bootstrap.min.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>
    <script src="/dist/js/html5shiv.js"></script>
    <script src="/dist/js/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-default" id="navbar">
    <script type="text/javascript">
        try{ace.settings.check('navbar' , 'fixed')}catch(e){}
    </script>

    <div class="navbar-container" id="navbar-container">
        <div class="navbar-header pull-left">
            <a href="javascript:;" class="navbar-brand">
                <small>Walle</small>
            </a><!-- /.brand -->
        </div><!-- /.navbar-header -->

        <div class="navbar-header pull-right" role="navigation">
            <ul class="nav ace-nav">
                <?php if (GlobalHelper::isValidAdmin() && ($count = count(User::getInactiveAdminList()))) { ?>
                <li class="light-blue">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon-bell-alt"></i>
                        <span class="badge badge-important"><?= $count ?></span>
                    </a>

                    <ul class="pull-right dropdown-navbar navbar-green dropdown-menu dropdown-caret dropdown-close">

                        <li class="dropdown-header">
                            <i class="icon-envelope"></i>
                            消息通知
                        </li>
                        <li>
                            <a href="/user/audit/">
                                <div class="clearfix">
                                    <span class="pull-left">
                                        <i class="btn btn-xs btn-primary icon-user"></i>
                                        项目管理员申请
                                    </span>
                                    <span class="pull-right badge badge-info"><?= $count ?></span>
                                </div>
                            </a>
                        </li>
                        <!-- 等待开启
                        <li>
                            <a href="#">
                                <div class="clearfix">
											<span class="pull-left">
												<i class="btn btn-xs no-hover btn-pink icon-comment"></i>
												新闻评论
											</span>
                                    <span class="pull-right badge badge-info">+12</span>
                                </div>
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <div class="clearfix">
											<span class="pull-left">
												<i class="btn btn-xs no-hover btn-success icon-shopping-cart"></i>
												新订单
											</span>
                                    <span class="pull-right badge badge-success">+8</span>
                                </div>
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <div class="clearfix">
											<span class="pull-left">
												<i class="btn btn-xs no-hover btn-info icon-twitter"></i>
												粉丝
											</span>
                                    <span class="pull-right badge badge-info">+11</span>
                                </div>
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                查看所有通知
                                <i class="icon-arrow-right"></i>
                            </a>
                        </li>
                        -->
                    </ul>
                </li>
                <?php } ?>

                <li class="light-blue">
                    <a data-toggle="dropdown" href="javascript:;" class="dropdown-toggle">
                        <img class="nav-user-photo" src="<?= GlobalHelper::formatAvatar($user->avatar) ?>">
                        <span class="user-info" style="top:12px"><?= $userName ?></span>
                        <i class="icon-caret-down"></i>
                    </a>

                    <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                        <!-- 设置功能暂无
                        <li>
                            <a href="javascript:;">
                                <i class="icon-cog"></i>
                                设置
                            </a>
                        </li>
                        -->

                        <li>
                            <a href="/user/">
                                <i class="icon-user"></i>
                                个人资料
                            </a>
                        </li>

                        <li class="divider"></li>

                        <li>
                            <a href="/site/logout">
                                <i class="icon-off"></i>
                                退出
                            </a>
                        </li>
                    </ul>
                </li>
            </ul><!-- /.ace-nav -->
        </div><!-- /.navbar-header -->
    </div><!-- /.container -->
</div>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="javascript:;">
            <span class="menu-text"></span>
        </a>

        <div class="sidebar" id="sidebar">
            <script type="text/javascript">
                try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
            </script>

            <ul class="nav nav-list">
                <?php if (\Yii::$app->user->identity->role == app\models\User::ROLE_ADMIN) { ?>
                <li class="<?= \Yii::$app->controller->id == 'conf' ? 'active' : '' ?>">
                    <a href="/conf/">
                        <i class="icon-cogs"></i>
                        <span class="menu-text"> 项目配置 </span>
                    </a>
                </li>
                <?php } ?>
                <li class="<?= \Yii::$app->controller->id == 'task' && \Yii::$app->controller->action->id == 'index'
                    ? 'active' : '' ?>">
                    <a href="/task/">
                        <i class="icon-list-alt"></i>
                        <span class="menu-text"> 我的上线任务 </span>
                    </a>
                </li>
                <li class="<?= \Yii::$app->controller->id == 'task' && \Yii::$app->controller->action->id == 'submit'
                    ? 'active' : '' ?>">
                    <a href="/task/submit/">
                        <i class="icon-cloud-upload"></i>
                        <span class="menu-text"> 提交上线任务 </span>
                    </a>
                </li>

                <li class="<?= \Yii::$app->controller->action->id == 'check' ? 'active' : '' ?>">
                    <a href="/walle/check/">
                        <i class=" icon-eye-open"></i>
                        <span class="menu-text"> 线上检查 </span>
                    </a>
                </li>
            </ul><!-- /.nav-list -->
        </div>

        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="/">首页</a>
                    </li>
                    <li class="active"><?= $this->title ?></li>
                </ul><!-- .breadcrumb -->
            </div>

            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                    <?= $content ?>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div><!-- /.main-content -->

    </div><!-- /.main-container-inner -->

    <a href="javascript:;" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
        <i class="icon-double-angle-up icon-only bigger-110"></i>
    </a>
</div><!-- /.main-container -->

<!-- basic scripts -->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='/dist/js/jquery.mobile.custom.min.js'>"+"<"+"script>");
</script>
<script src="/dist/js/typeahead-bs2.min.js"></script>

<!-- page specific plugin scripts -->

<!--[if lte IE 8]>
<script src="/dist/js/excanvas.min.js"></script>
<![endif]-->

<script src="/dist/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="/dist/js/jquery.ui.touch-punch.min.js"></script>
<script src="/dist/js/jquery.slimscroll.min.js"></script>
<script src="/dist/js/jquery.easy-pie-chart.min.js"></script>
<script src="/dist/js/jquery.sparkline.min.js"></script>
<script src="/dist/js/flot/jquery.flot.min.js"></script>
<script src="/dist/js/chosen.jquery.min.js"></script>
<script src="/dist/js/flot/jquery.flot.pie.min.js"></script>
<script src="/dist/js/flot/jquery.flot.resize.min.js"></script>

<!-- ace scripts -->

<script src="/dist/js/ace-elements.min.js"></script>
<script src="/dist/js/ace.min.js"></script>

<!-- inline scripts related to this page -->
<script>

    jQuery(function($) {
        $(".chosen-select").chosen();
        $('#chosen-multiple-style').on('click', function (e) {
            var target = $(e.target).find('input[type=radio]');
            var which = parseInt(target.val());
            if (which == 2) $('#form-field-select-4').addClass('tag-input-style');
            else $('#form-field-select-4').removeClass('tag-input-style');
        });


    })
</script>
    <?php $this->endBody() ?>

  </body>
</html>
<?php $this->endPage() ?>


