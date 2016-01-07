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
    <div id="signup-box" class="signup-box visible widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header green lighter bigger">
                    <i class="icon-group blue"></i>
                    <?= yii::t('w','register-platform')?>
                </h4>

                <div class="space-6"></div>
                <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                <?= $form->field($model, 'email')->label(yii::t('w','register email')) ?>
                <?= $form->field($model, 'username')->label(yii::t('w','register firstname')) ?>
                <?= $form->field($model, 'password')->label(yii::t('w','register password'))->passwordInput() ?>
                <?= $form->field($model, 'role')->label(yii::t('w','register role'))->dropDownList([
                    User::ROLE_DEV => \Yii::t('w', 'user_role_' . User::ROLE_DEV),
                    User::ROLE_ADMIN => \Yii::t('w', 'user_role_' . User::ROLE_ADMIN),
                ]) ?>
                <div class="form-group">
                    <?= Html::submitButton(yii::t('w','register-platform'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <div class="toolbar center">
                <a href="<?= Url::to('@web/site/login') ?>" class="back-to-login-link">
                    <?= yii::t('w','back')?><i class="icon-arrow-right"></i>
                </a>
            </div>
        </div>
        <!-- /widget-body -->
    </div>
    <!-- /signup-box -->
</div><!-- /position-relative -->
