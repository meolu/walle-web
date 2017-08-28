<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\models\User;
use app\assets\AppAsset;
use app\widgets\Alert;

$user = User::findOne(\Yii::$app->user->id);
?>
<?php $this->beginPage() ?>
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
			&times;
		</button>
		<h4 class="modal-title" id="myModalLabel"><?= $this->title ?></h4>
	</div>
	<div class="modal-body">

	<?= $content ?>
	</div>
<?php $this->endPage() ?>


