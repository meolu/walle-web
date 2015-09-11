<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \app\models\forms\PasswordResetRequestForm $model
 */
$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="position-relative">
    <div id="forgot-box" class="forgot-box visible widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header red lighter bigger">
                    <i class="icon-key"></i>
                    忘记密码
                </h4>

                <div class="space-6"></div>
                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                <?= $form->field($model, 'email')->label('请确认你的邮箱') ?>
                <div class="form-group">
                    <?= Html::submitButton('发送', ['class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div><!-- /widget-main -->

            <div class="toolbar center">
                <a href="/site/login" class="back-to-login-link">
                    返回登录<i class="icon-arrow-right"></i>
                </a>
            </div>
        </div><!-- /widget-body -->
    </div><!-- /forgot-box -->

    </div><!-- /signup-box -->
</div><!-- /position-relative -->
