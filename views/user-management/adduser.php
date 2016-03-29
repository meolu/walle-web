<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('user-management', 'add_user_title');
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\user;
?>
<style type="text/css">
    .panel {
        padding: 30px;
    }
</style>
<div class="box">
    <div class="box-body table-responsive no-padding clearfix">
            <div class="panel">
                <?php $form = ActiveForm::begin(); ?>
                <?= $form->field($model, 'email')->label(Yii::t('user-management', 'adduser email')) ?>
                <?= $form->field($model, 'password')->label(Yii::t('user-management', 'adduser password')) ?>
                <?= $form->field($model, 'realname')->label(Yii::t('user-management', 'adduser realname')) ?>
                <?= $form->field($model, 'role')->label(Yii::t('user-management', 'adduser role'))->dropDownList([
                    User::ROLE_DEV => \Yii::t('w', 'user_role_' . User::ROLE_DEV),
                    User::ROLE_ADMIN => \Yii::t('w', 'user_role_' . User::ROLE_ADMIN),
                ]) ?>
                <div class="form-group">
                    <?= Html::submitButton(yii::t('user-management','adduser-platform'), ['class' => 'btn btn-primary', 'name' => 'submit-button']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        <!-- 模态框（Modal） -->
        <div class="modal fade" id="update-real-name" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        </div>
    </div><!-- /.box-body -->
</div>

<script>
    jQuery(function($) {
        
    });
</script>