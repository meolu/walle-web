<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \app\models\forms\ResetPasswordForm $model
 */
$this->title = '重置密码';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password">

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                <?= $form->field($model, 'password')->passwordInput()->label('新密码') ?>
                <div class="form-group">
                    <?= Html::submitButton('更新', ['class' => 'btn btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
