<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\User;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \frontend\models\User $model
 */
$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="position-relative">
<div id="login-box" class="login-box visible widget-box no-border">
    <div class="widget-body">
        <div class="widget-main">
            <h4 class="header blue lighter bigger">
                <i class="icon-coffee green"></i>
                登录平台
            </h4>

            <div class="space-6"></div>
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <?= $form->field($model, 'username')->label('用户名') ?>
            <?= $form->field($model, 'password')->label('密码')->passwordInput() ?>
            <?= $form->field($model, 'rememberMe')->label('记住我')->checkbox() ?>

            <div class="form-group">
                <?= Html::submitButton('登录', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div><!-- /widget-main -->

        <div class="toolbar clearfix">
            <div>
                <a href="/site/request-password-reset" class="forgot-password-link">
                    <i class="icon-arrow-left"></i>
                    忘记密码
                </a>
            </div>

            <div>
                <a href="/site/signup" class="user-signup-link">
                    注册账号
                    <i class="icon-arrow-right"></i>
                </a>
            </div>
        </div>

    </div><!-- /widget-body -->
</div><!-- /login-box -->

</div><!-- /position-relative -->