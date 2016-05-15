<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('task', 'select project title');
use app\models\Project;
use yii\helpers\Url;
?>
<div class="box">
    <!-- 测试环境 -->
    <div class="widget-box transparent">
        <div class="widget-header">
            <h4 class="lighter"><?= yii::t('w', 'conf_level_1') ?></h4>

            <div class="widget-toolbar no-border"><a href="javascript:;" data-action="collapse">
                    <i class="icon-chevron-up"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-6 no-padding-left no-padding-right">
                <?php foreach ($projects as $project) { ?>
                    <?php if ($project['level'] == Project::LEVEL_TEST) { ?>
                    <a class="btn btn-inline btn-info" style="min-width:120px;margin:auto auto 20px 40px;" href="<?= Url::to("@web/task/submit?projectId={$project['id']}") ?>"><?= $project['name'] ?></a>
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
            <h4 class="lighter"><?= yii::t('w', 'conf_level_2') ?></h4>

            <div class="widget-toolbar no-border"><a href="javascript:;" data-action="collapse">
                    <i class="icon-chevron-up"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-6 no-padding-left no-padding-right">
                <?php foreach ($projects as $project) { ?>
                    <?php if ($project['level'] == Project::LEVEL_SIMU) { ?>
                        <a class="btn btn-inline btn-warning" style="min-width:120px;margin: auto auto 20px 40px;" href="<?= Url::to("@web/task/submit?projectId={$project['id']}") ?>"><?= $project['name'] ?></a>
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
            <h4 class="lighter"><?= yii::t('w', 'conf_level_3') ?></h4>

            <div class="widget-toolbar no-border"><a href="javascript:;" data-action="collapse">
                    <i class="icon-chevron-up"></i>
                </a>
            </div>
        </div>

        <div class="widget-body">
            <div class="widget-main padding-6 no-padding-left no-padding-right">
                <?php foreach ($projects as $project) { ?>
                    <?php if ($project['level'] == Project::LEVEL_PROD) { ?>
                        <a class="btn btn-inline btn-success" style="min-width:120px;margin: auto auto 20px 40px;" href="<?= Url::to("@web/task/submit?projectId={$project['id']}") ?>"><?= $project['name'] ?></a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- 模拟线上环境 -->
    <br>

</div>
