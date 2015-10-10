<?php
/**
 * @var yii\web\View $this
 */
$this->title = '选择项目';
use app\models\Project;
?>
<div class="box">
    <!-- 测试环境 -->
    <div class="widget-box transparent">
        <div class="widget-header">
            <h4 class="lighter">测试环境</h4>

            <div class="widget-toolbar no-border"><a href="javascript:;" data-action="collapse">
                    <i class="icon-chevron-up"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-6 no-padding-left no-padding-right">
                <?php foreach ($projects as $project) { ?>
                    <?php if ($project['level'] == Project::LEVEL_TEST) { ?>
                    <a class="btn btn-inline btn-warning" style="width:150px;margin-left: 40px;" href="/task/submit?projectId=<?= $project['id'] ?>"><?= $project['name'] ?></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- 测试环境 -->
    <br>
    <!-- 仿真环境 -->
    <div class="widget-box transparent">
        <div class="widget-header">
            <h4 class="lighter">仿真环境</h4>

            <div class="widget-toolbar no-border"><a href="javascript:;" data-action="collapse">
                    <i class="icon-chevron-up"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-6 no-padding-left no-padding-right">
                <?php foreach ($projects as $project) { ?>
                    <?php if ($project['level'] == Project::LEVEL_SIMU) { ?>
                        <a class="btn btn-inline btn-warning" style="width:150px;margin-left: 40px;" href="/task/submit?projectId=<?= $project['id'] ?>"><?= $project['name'] ?></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- 仿真环境 -->
    <br>
    <!-- 线上环境 -->
    <div class="widget-box transparent">
        <div class="widget-header">
            <h4 class="lighter">线上环境</h4>

            <div class="widget-toolbar no-border"><a href="javascript:;" data-action="collapse">
                    <i class="icon-chevron-up"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-6 no-padding-left no-padding-right">
                <?php foreach ($projects as $project) { ?>
                    <?php if ($project['level'] == Project::LEVEL_PROD) { ?>
                        <a class="btn btn-inline btn-warning" style="width:150px;margin-left: 40px;" href="/task/submit?projectId=<?= $project['id'] ?>"><?= $project['name'] ?></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- 模拟线上环境 -->
    <br>

</div>
