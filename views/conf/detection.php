<?php
/**
 * @var yii\web\View $this
 */
$this->title = $project->name . '项目配置检测';

?>
<div class="alert">
    <i class="icon-spinner icon-spin orange bigger-300"></i>

    正在检测...
</div>

<script>
    jQuery(function($) {
        $.get('/walle/detection?projectId=<?= $project->id ?>', function(o) {
            // 检测失败
            if (o.code) {
                $('.alert').addClass('alert-danger').html(o.data)
            } else {
                $('.alert').addClass('alert-success').html(o.data)
            }
        })
    });
</script>