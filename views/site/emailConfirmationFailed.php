<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \frontend\models\User $model
 */
$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
$mail = Yii::$app->mail->messageConfig['from'];
?>
<div class="site-signup alert alert-danger">
    <h1>Could not complete registration</h1>

    <p>You either supplied an invalid confirmation link or the link has meanwhile expired.
    Please contact our support under <?= Html::mailTo(key($mail), current($mail)) ?>.
    </p>

</div>
