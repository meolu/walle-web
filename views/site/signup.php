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
    <div id="signup-box" class="signup-box visible widget-box no-border">
        <div class="widget-body">
            <div class="widget-main">
                <h4 class="header green lighter bigger">
                    <i class="icon-group blue"></i>
                    注册平台
                </h4>

                <div class="space-6"></div>
                <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                <?= $form->field($model, 'email')->label('邮箱') ?>
                <?= $form->field($model, 'username')->label('名字') ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'role')->dropDownList([
                    User::ROLE_DEV => \Yii::t('status', 'user_role_' . User::ROLE_DEV),
                    User::ROLE_ADMIN => \Yii::t('status', 'user_role_' . User::ROLE_ADMIN),
                ]) ?>
                <div class="form-group">
                    <?= Html::submitButton('提交', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <div class="toolbar center">
                <a href="/site/login" class="back-to-login-link">
                    返回登录<i class="icon-arrow-right"></i>
                </a>
            </div>
        </div>
        <!-- /widget-body -->
    </div>
    <!-- /signup-box -->
</div><!-- /position-relative -->
