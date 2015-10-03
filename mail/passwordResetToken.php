<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
亲爱的<strong><?= $user->realname ?></strong>:

<br><br>
<span style="text-indent: 2em">点击以下链接完成重置密码，如不是本人操作，请忽略，谢谢！</span>

<br><br>
<h3><?= Html::a('重置密码', $resetLink) ?></h3>

