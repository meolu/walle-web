<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\models\User;
use app\assets\AppAsset;
use app\widgets\Alert;

$user = User::findOne(\Yii::$app->user->id);
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
<div class="modal show " id="myModal" style="display: block">
    <div class="modal-dialog">
        <div class="modal-content" style="800px">
            <div class="modal-header">
                <button type="button" class="close"
                        data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?= $this->title ?>
                </h4>
            </div>
            <div class="modal-body">
                <?= $content ?>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal -->
</div>

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

    <?php $this->endBody() ?>

  </body>
</html>
<?php $this->endPage() ?>


