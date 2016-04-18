<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */
$confirmationLink = Yii::$app->urlManager->createAbsoluteUrl(['site/confirm-email', 'token' => $user->email_confirmation_token]);

?>

<?= yii::t('user', 'dear') ?>   <strong><?= $user->realname ?></strong>:

<br><br>
<span style="text-indent: 2em"><?= yii::t('user', 'notice account has been opened') ?></span>
<br>
<?= yii::t('user', 'email') ?> : <?= $user->email ?><br>
<?= yii::t('user', 'password') ?> : <?= $user->password ?><br>
<?= yii::t('user', 'role') ?> : <?= \Yii::t('w', 'user_role_' . $user->role) ?><br>
<h3><?= Html::a(yii::t('user', 'active'), $confirmationLink) ?></h3>
<a href="<?= $confirmationLink ?>"><?= $confirmationLink ?></a>


