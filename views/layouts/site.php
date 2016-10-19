<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \app\models\forms\LoginForm $model
 */
$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
    <title><?= yii::t("w","log-platform")?> - <?= yii::t("w","w")?></title>
    <!-- basic styles -->

    <link href="<?= Url::to('@web/dist/css/bootstrap.min.css') ?>" rel="stylesheet" />
    <link rel="stylesheet" href="<?= Url::to('@web/dist/css/font-awesome.min.css') ?>" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="<?= Url::to('@web/dist/css/font-awesome-ie7.min.css') ?>" />
    <![endif]-->

    <!-- page specific plugin styles -->
    <!-- ace styles -->

    <link rel="stylesheet" href="<?= Url::to('@web/dist/css/ace.min.css') ?>" />
    <link rel="stylesheet" href="<?= Url::to('@web/dist/css/ace-rtl.min.css') ?>" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="<?= Url::to('@web/dist/css/ace-ie.min.css') ?>" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>
    <script src="/dist/js/html5shiv.js"></script>
    <script src="/dist/js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="login-layout">

<style>
    A.applink:hover {border: 2px dotted #DCE6F4;padding:2px;background-color:#ffff00;color:green;text-decoration:none}
    A.applink       {border: 2px dotted #DCE6F4;padding:2px;color:#2F5BFF;background:transparent;text-decoration:none}
    A.info          {color:#2F5BFF;background:transparent;text-decoration:none}
    A.info:hover    {color:green;background:transparent;text-decoration:underline}
</style>


<div class="main-container">
    <div class="main-content">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="login-container">
                    <div class="center">
                        <h1>
                            <i class="icon-leaf green"></i>
                            <span class="red">Walle</span>
                            <span class="white"><?= yii::t("w","w platform") ?></span>
                        </h1>
                    </div>

                    <div class="space-6"></div>

                    <?= $content ?>
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div>
</div><!-- /.main-container -->

<!-- basic scripts -->


<!--[if !IE]> -->

<script type="text/javascript">
    window.jQuery || document.write("<script src='/dist/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='/dist/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='/dist/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<!-- inline scripts related to this page -->

<script type="text/javascript">
    function show_box(id) {
        jQuery('.widget-box.visible').removeClass('visible');
        jQuery('#'+id).addClass('visible');
    }
</script>
</body>
</html>




