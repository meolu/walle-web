<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('user', 'add user');
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\user;
?>

<div class="box col-xs-8">
    <?php $form = ActiveForm::begin(); ?>
    <div class="box-body">
        <?= $form->field($model, 'email')
            ->textInput(['class' => 'col-xs-5',])
            ->label(Yii::t('user', 'email'), ['class' => 'text-right bolder blue col-xs-2']) ?>
        <div class="clearfix"></div>
        <?= $form->field($model, 'password')
            ->passwordInput(['class' => 'col-xs-5',])
            ->label(Yii::t('user', 'password'), ['class' => 'text-right bolder blue col-xs-2']) ?>
        <div class="clearfix"></div>

        <?= $form->field($model, 'realname')
            ->textInput(['class' => 'col-xs-5',])
            ->label(Yii::t('user', 'realname'), ['class' => 'text-right bolder blue col-xs-2']) ?>
        <div class="clearfix"></div>
        <?= $form->field($model, 'role')->label(Yii::t('user', 'role'), ['class' => 'text-right bolder blue col-xs-2'])
            ->dropDownList([
            User::ROLE_DEV => \Yii::t('w', 'user_role_' . User::ROLE_DEV),
            User::ROLE_ADMIN => \Yii::t('w', 'user_role_' . User::ROLE_ADMIN),
        ], ['class' => 'col-xs-5',]) ?>
        <div class="clearfix"></div>
        <div class="box-footer">
        <div class="col-xs-2"></div>
        <div class="form-group" style="margin-top:40px">
            <?= Html::submitButton(yii::t('user','add user'), ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
        </div>
            </div>
    </div><!-- /.box-body -->
    <?php ActiveForm::end(); ?>
</div>
