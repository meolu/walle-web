<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */

$confirmationLink = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm-email', 'token' => $user->email_confirmation_token]);
?>
<?= yii::t('user', 'dear') ?><strong><?= $user->realname ?></strong>:

<br><br>
<span style="text-indent: 2em"><?= yii::t('user', 'confirm email by click url') ?></span>

<br><br>
<h3><?= Html::a(yii::t('user', 'active'), $confirmationLink) ?></h3>
