<?php
use yii\helpers\Html;
use yii\helpers\Url;
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
                <?= yii::t('w','log-platform')?>
            </h4>

            <div class="space-6"></div>
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <?= $form->field($model, 'username')->label(yii::t('w','login username')) ?>
            <?= $form->field($model, 'password')->label(yii::t('w','login password'))->passwordInput() ?>
            <?= $form->field($model, 'rememberMe')->label('记住我')->checkbox() ?>

            <div class="form-group">
                <?= Html::submitButton(yii::t('w','submit'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div><!-- /widget-main -->

        <div class="toolbar clearfix">
            <?php if($isLdapLigin !== true) { ?>
            <div>
                <a href="<?= Url::to('@web/site/request-password-reset') ?>" class="forgot-password-link">
                    <i class="icon-arrow-left"></i>
                    <?= yii::t('w','login forgot password')?>
                </a>
            </div>

            <div>
                <a href="<?= Url::to('@web/site/signup') ?>" class="user-signup-link">
                    <?= yii::t('w','login register user')?>
                    <i class="icon-arrow-right"></i>
                </a>
            </div>
            <?php } ?>
        </div>

    </div><!-- /widget-body -->
</div><!-- /login-box -->

</div><!-- /position-relative -->