<?php
/**
 * @var yii\web\View $this
 */
$this->title = '选择项目';
?>
<div class="box">
    <br><br>
    <?php foreach ($projects as $project) { ?>
    <a class="btn btn-inline btn-warning" style="width:150px;margin-left: 40px;" href="/walle/submit?projectId=<?= $project['id'] ?>"><?= $project['name'] ?></a>
    <?php } ?>
    <br><br><br><br><br><br><br><br><br><br><br><br>

</div>
