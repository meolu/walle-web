<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */

$confirmationLink = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm-email', 'token' => $user->email_confirmation_token]);
?>
点击完成注册
 <?= Html::a('确认激活', $confirmationLink) ?>
