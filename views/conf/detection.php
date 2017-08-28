<?php
/**
 * @var yii\web\View $this
 */
use yii\helpers\Url;

$this->title = $project->name . yii::t('conf', 'detection');

?>
<div class="alert">
    <i class="icon-spinner icon-spin orange bigger-300"></i>
    <?= yii::t('conf', 'detecting') ?>
</div>

<script>
    jQuery(function($) {
        $.get('<?= Url::to("@web/walle/detection?projectId={$project->id}") ?>', function(o) {
            // 检测失败
            if (o.code) {
                $('.alert').addClass('alert-danger').html(o.data)
            } else {
                $('.alert').addClass('alert-success').html(o.data)
            }
        })
    });
</script>