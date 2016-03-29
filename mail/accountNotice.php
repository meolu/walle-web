<?php
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\User $user
 */
?>

<?= yii::t('user-management', 'dear') ?><strong><?= $user->realname ?></strong>:

<br><br>
<span style="text-indent: 2em"><?= yii::t('user-mangement', 'notice account has been opened') ?></span>
