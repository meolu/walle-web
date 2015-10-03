<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */

$confirmationLink = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm-email', 'token' => $user->email_confirmation_token]);
?>
亲爱的<strong><?= $user->realname ?></strong>:

<br><br>
<span style="text-indent: 2em">点击以下链接完成注册，如不是本人操作，请忽略，谢谢！</span>

<br><br>
<h3><?= Html::a('确认激活', $confirmationLink) ?></h3>
