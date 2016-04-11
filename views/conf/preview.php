<?php
/**
 * @var yii\web\View $this
 */
$this->title = $conf->name . yii::t('conf', 'edit');

use yii\widgets\ActiveForm;
?>

<div class="profile-user-info">
    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'name') ?> </div>

        <div class="profile-info-value">
            <span><?= $conf->name ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'env') ?> </div>

        <div class="profile-info-value">
            <i class="icon-map-marker light-orange bigger-110"></i>
            <span><?= \Yii::t('w', 'conf_level_' . $conf->level) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'url') ?> </div>

        <div class="profile-info-value">
            <span><?= $conf->repo_url ?></span>
        </div>
    </div>

    <!-- 宿主机 配置-->
    <h4 class="lighter"><i class="icon-dashboard orange"></i><?= yii::t('conf', 'host') ?></h4>
    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'deploy from') ?> </div>

        <div class="profile-info-value">
            <span><?= $conf->deploy_from ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'excludes') ?> </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->excludes) ?></span>
        </div>
    </div>
    <!-- 宿主机 配置 end-->

    <!-- 目标机器 配置-->
    <h4 class="lighter"><i class="icon-cloud-upload orange"></i><?= yii::t('conf', 'servers') ?></h4>
    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'target user') ?> </div>

        <div class="profile-info-value">
            <span><?= $conf->release_user ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> webroot </div>

        <div class="profile-info-value">
            <span><?= $conf->release_to ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'releases') ?> </div>

        <div class="profile-info-value">
            <span><?= $conf->release_library ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'servers') ?> </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->hosts) ?></span>
        </div>
    </div>
    <!-- 目标机器 配置 end-->

    <!-- 任务配置-->

    <h4 class="lighter"><i class="icon-tasks orange"></i><?= yii::t('conf', 'tasks') ?></h4>
    <div class="profile-info-row">
        <div class="profile-info-name"> pre_deploy </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->pre_deploy) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> post_deploy </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->post_deploy) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> pre_release </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->pre_release) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> post_release </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->post_release) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'post_release_delay') ?> </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->post_release_delay) ?></span>
        </div>
    </div>
    <!-- 目标机器 配置 end-->

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'branch/tag') ?> </div>

        <div class="profile-info-value">
            <span><?= $conf->repo_mode ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'enable audit') ?> </div>

        <div class="profile-info-value">
            <span><?= \Yii::t('w', 'bool_' . $conf->audit) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'enable ansible') ?> </div>

        <div class="profile-info-value">
            <span><?= \Yii::t('w', 'bool_' . $conf->ansible) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> <?= yii::t('conf', 'enable open') ?> </div>

        <div class="profile-info-value">
            <span><?= \Yii::t('w', 'bool_' . $conf->status) ?></span>
        </div>
    </div>
</div>
