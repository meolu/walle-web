<?php
use yii\helpers\Html;
use yii\helpers\Url;
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
                    <?= yii::t("w","login forgot password")?>
                </h4>

                <div class="space-6"></div>
                <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
                <?= $form->field($model, 'email')->label(yii::t("w",'login resend email')) ?>
                <div class="form-group">
                    <?= Html::submitButton(yii::t("w","submit"), ['class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div><!-- /widget-main -->

            <div class="toolbar center">
                <a href="<?= Url::to('@web/site/login') ?>" class="back-to-login-link">
                    <?= yii::t("w","back")?><i class="icon-arrow-right"></i>
                </a>
            </div>
        </div><!-- /widget-body -->
    </div><!-- /forgot-box -->

    </div><!-- /signup-box -->
</div><!-- /position-relative -->
