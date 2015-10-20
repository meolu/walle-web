<?php
/**
 * @var yii\web\View $this
 */
$this->title = $conf->name . '配置项目';

use yii\widgets\ActiveForm;
?>

<div class="profile-user-info">
    <div class="profile-info-row">
        <div class="profile-info-name"> 项目名字 </div>

        <div class="profile-info-value">
            <span><?= $conf->name ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> 项目环境 </div>

        <div class="profile-info-value">
            <i class="icon-map-marker light-orange bigger-110"></i>
            <span><?= \Yii::t('status', 'conf_level_' . $conf->level) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> git地址 </div>

        <div class="profile-info-value">
            <span><?= $conf->repo_url ?></span>
        </div>
    </div>

    <!-- 宿主机 配置-->
    <h4 class="lighter"><i class="icon-dashboard orange"></i>宿主机</h4>
    <div class="profile-info-row">
        <div class="profile-info-name"> 代码存储仓库 </div>

        <div class="profile-info-value">
            <span><?= $conf->deploy_from ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> 排除文件 </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->excludes) ?></span>
        </div>
    </div>
    <!-- 宿主机 配置 end-->

    <!-- 目标机器 配置-->
    <h4 class="lighter"><i class="icon-cloud-upload orange"></i>目标机器</h4>
    <div class="profile-info-row">
        <div class="profile-info-name"> 用户 </div>

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
        <div class="profile-info-name"> 发布版本库 </div>

        <div class="profile-info-value">
            <span><?= $conf->release_library ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> 机器列表 </div>

        <div class="profile-info-value">
            <span><?= str_replace(PHP_EOL, "<br>", $conf->hosts) ?></span>
        </div>
    </div>
    <!-- 目标机器 配置 end-->

    <!-- 任务配置-->

    <h4 class="lighter"><i class="icon-tasks orange"></i>高级任务</h4>
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
    <!-- 目标机器 配置 end-->

    <div class="profile-info-row">
        <div class="profile-info-name"> 上线方式 </div>

        <div class="profile-info-value">
            <span><?= $conf->repo_mode ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> 是否需要审核 </div>

        <div class="profile-info-value">
            <span><?= \Yii::t('status', 'bool_' . $conf->audit) ?></span>
        </div>
    </div>

    <div class="profile-info-row">
        <div class="profile-info-name"> 是否有效 </div>

        <div class="profile-info-value">
            <span><?= \Yii::t('status', 'bool_' . $conf->status) ?></span>
        </div>
    </div>
</div>