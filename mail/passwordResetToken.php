<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
<?= yii::t('user', 'dear') ?><strong><?= $user->realname ?></strong>:

<br><br>
<span style="text-indent: 2em"><?= yii::t('user', 'reset password by click url') ?></span>

<br><br>
<h3><?= Html::a(yii::t('user', 'reset password'), $resetLink) ?></h3>
<br>
<a href="<?= $resetLink ?>"> <?= $resetLink ?></a>
