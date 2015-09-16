<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\models\User;
use app\assets\AppAsset;
use app\widgets\Alert;

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

    <!-- page specific plugin styles -->


    <!-- ace styles -->

    <link rel="stylesheet" href="/dist/css/ace.min.css" />
    <link rel="stylesheet" href="/dist/css/ace-rtl.min.css" />
    <link rel="stylesheet" href="/dist/css/ace-skins.min.css" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="/dist/css/ace-ie.min.css" />
    <![endif]-->
    <!--[if !IE]> -->

    <script type="text/javascript">
        window.jQuery || document.write("<script src='/dist/js/jquery-2.0.3.min.js'>"+"<"+"script>");
    </script>

    <!-- <![endif]-->

    <!--[if IE]>
    <script type="text/javascript">
        window.jQuery || document.write("<script src='/dist/js/jquery-1.10.2.min.js'>"+"<"+"script>");
    </script>
    <![endif]-->
    <!-- inline styles related to this page -->

    <!-- ace settings handler -->

    <script src="/dist/js/ace-extra.min.js"></script>

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
            <a href="#" class="navbar-brand">
                <small>Walle</small>
            </a><!-- /.brand -->
        </div><!-- /.navbar-header -->

        <div class="navbar-header pull-right" role="navigation">
            <ul class="nav ace-nav">

                <li class="light-blue">
                    <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                        <span class="user-info"><?= $userName ?></span>
                        <i class="icon-caret-down"></i>
                    </a>

                    <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                        <li>
                            <a href="#">
                                <i class="icon-cog"></i>
                                设置
                            </a>
                        </li>

                        <li>
                            <a href="#">
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
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>

        <div class="sidebar" id="sidebar">
            <script type="text/javascript">
                try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
            </script>

            <ul class="nav nav-list">
                <?php if (\Yii::$app->user->identity->role == app\models\User::ROLE_ADMIN) { ?>
                <li class="<?= \Yii::$app->controller->action->id == 'config' ? 'active' : '' ?>">
                    <a href="/walle/config/">
                        <i class="icon-cogs"></i>
                        <span class="menu-text"> 项目配置 </span>
                    </a>
                </li>
                <?php } ?>
                <li class="<?= \Yii::$app->controller->action->id == 'index' ? 'active' : '' ?>">
                    <a href="/walle/index/">
                        <i class="icon-list-alt"></i>
                        <span class="menu-text"> 我的上线任务 </span>
                    </a>
                </li>
                <li class="<?= \Yii::$app->controller->action->id == 'submit' ? 'active' : '' ?>">
                    <a href="/walle/submit/">
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

    <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
        <i class="icon-double-angle-up icon-only bigger-110"></i>
    </a>
</div><!-- /.main-container -->

<!-- basic scripts -->




<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='/dist/js/jquery.mobile.custom.min.js'>"+"<"+"script>");
</script>
<script src="/dist/js/bootstrap.min.js"></script>
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
<script src="/dist/js/flot/jquery.flot.pie.min.js"></script>
<script src="/dist/js/flot/jquery.flot.resize.min.js"></script>

<!-- ace scripts -->

<script src="/dist/js/ace-elements.min.js"></script>
<script src="/dist/js/ace.min.js"></script>

<!-- inline scripts related to this page -->

<script type="text/javascript">
    jQuery(function($) {
        $('.easy-pie-chart.percentage').each(function(){
            var $box = $(this).closest('.infobox');
            var barColor = $(this).data('color') || (!$box.hasClass('infobox-dark') ? $box.css('color') : 'rgba(255,255,255,0.95)');
            var trackColor = barColor == 'rgba(255,255,255,0.95)' ? 'rgba(255,255,255,0.25)' : '#E2E2E2';
            var size = parseInt($(this).data('size')) || 50;
            $(this).easyPieChart({
                barColor: barColor,
                trackColor: trackColor,
                scaleColor: false,
                lineCap: 'butt',
                lineWidth: parseInt(size/10),
                animate: /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase()) ? false : 1000,
                size: size
            });
        })

        $('.sparkline').each(function(){
            var $box = $(this).closest('.infobox');
            var barColor = !$box.hasClass('infobox-dark') ? $box.css('color') : '#FFF';
            $(this).sparkline('html', {tagValuesAttribute:'data-values', type: 'bar', barColor: barColor , chartRangeMin:$(this).data('min') || 0} );
        });




        var placeholder = $('#piechart-placeholder').css({'width':'90%' , 'min-height':'150px'});




        var $tooltip = $("<div class='tooltip top in'><div class='tooltip-inner'></div></div>").hide().appendTo('body');
        var previousPoint = null;

        placeholder.on('plothover', function (event, pos, item) {
            if(item) {
                if (previousPoint != item.seriesIndex) {
                    previousPoint = item.seriesIndex;
                    var tip = item.series['label'] + " : " + item.series['percent']+'%';
                    $tooltip.show().children(0).text(tip);
                }
                $tooltip.css({top:pos.pageY + 10, left:pos.pageX + 10});
            } else {
                $tooltip.hide();
                previousPoint = null;
            }

        });






        var d1 = [];
        for (var i = 0; i < Math.PI * 2; i += 0.5) {
            d1.push([i, Math.sin(i)]);
        }

        var d2 = [];
        for (var i = 0; i < Math.PI * 2; i += 0.5) {
            d2.push([i, Math.cos(i)]);
        }

        var d3 = [];
        for (var i = 0; i < Math.PI * 2; i += 0.2) {
            d3.push([i, Math.tan(i)]);
        }




        $('#recent-box [data-rel="tooltip"]').tooltip({placement: tooltip_placement});
        function tooltip_placement(context, source) {
            var $source = $(source);
            var $parent = $source.closest('.tab-content')
            var off1 = $parent.offset();
            var w1 = $parent.width();

            var off2 = $source.offset();
            var w2 = $source.width();

            if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
            return 'left';
        }


        $('.dialogs,.comments').slimScroll({
            height: '300px'
        });


        //Android's default browser somehow is confused when tapping on label which will lead to dragging the task
        //so disable dragging when clicking on label
        var agent = navigator.userAgent.toLowerCase();
        if("ontouchstart" in document && /applewebkit/.test(agent) && /android/.test(agent))
            $('#tasks').on('touchstart', function(e){
                var li = $(e.target).closest('#tasks li');
                if(li.length == 0)return;
                var label = li.find('label.inline').get(0);
                if(label == e.target || $.contains(label, e.target)) e.stopImmediatePropagation() ;
            });

        $('#tasks').sortable({
                opacity:0.8,
                revert:true,
                forceHelperSize:true,
                placeholder: 'draggable-placeholder',
                forcePlaceholderSize:true,
                tolerance:'pointer',
                stop: function( event, ui ) {//just for Chrome!!!! so that dropdowns on items don't appear below other items after being moved
                    $(ui.item).css('z-index', 'auto');
                }
            }
        );
        $('#tasks').disableSelection();
        $('#tasks input:checkbox').removeAttr('checked').on('click', function(){
            if(this.checked) $(this).closest('li').addClass('selected');
            else $(this).closest('li').removeClass('selected');
        });


    })
</script>
    <?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>

