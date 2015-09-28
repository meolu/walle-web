<?php
/**
 * @var yii\web\View $this
 */
$this->title = '配置项目';
use app\models\Conf;

use yii\widgets\ActiveForm;
?>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
      <div class="box-body">
          <?= $form->field($conf, 'name')->label('项目名字<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder blue']) ?>
          <?= $form->field($conf, 'level')->dropDownList([
              Conf::LEVEL_TEST => \Yii::t('status', 'conf_level_' . Conf::LEVEL_TEST),
              Conf::LEVEL_SIMU => \Yii::t('status', 'conf_level_' . Conf::LEVEL_SIMU),
              Conf::LEVEL_PROD => \Yii::t('status', 'conf_level_' . Conf::LEVEL_PROD),
          ])->label('项目环境级别<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder blue']) ?>
        <!-- 宿主机 配置-->
        <div class="row">
        <div class="col-sm-4">
          <div class="widget-box transparent">
              <div class="widget-header widget-header-flat">
                  <h4 class="lighter"><i class="icon-dashboard orange"></i>宿主机</h4>
                  <div class="widget-toolbar">
                      <a href="#" data-action="collapse">
                          <i class="icon-chevron-up"></i>
                      </a>
                  </div>
              </div>

              <div class="widget-body">
                  <div class="widget-main">
                      <?= $form->field($conf, 'deploy_from')->label('检出仓库<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'excludes')->textarea()->label('排除文件', ['class' => 'control-label bolder']) ?>
                  </div>
              </div>
          </div>
        </div>
        <!-- 宿主机 配置 end-->
        <!-- 目标机器 配置-->
        <div class="col-sm-4">
          <div class="widget-box transparent">
              <div class="widget-header widget-header-flat">
                  <h4 class="lighter"><i class="icon-cloud-upload orange"></i>目标机器</h4>
                  <div class="widget-toolbar">
                      <a href="#" data-action="collapse">
                          <i class="icon-chevron-up"></i>
                      </a>
                  </div>
              </div>

              <div class="widget-body">
                  <div class="widget-main">
                      <?= $form->field($conf, 'release_user')->label('用户<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'release_to')->label('webroot<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'release_library')->label('发布版本库<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'hosts')->textarea()->label('机器列表<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                  </div>
              </div>
          </div>
        </div>

        <!-- 目标机器 配置 end-->
        <!-- 任务配置-->
        <div class="col-sm-4">
          <div class="widget-box transparent">
              <div class="widget-header widget-header-flat">
                  <h4 class="lighter"><i class="icon-tasks orange"></i>高级任务</h4>
                  <div class="widget-toolbar">
                      <a href="#" data-action="collapse">
                          <i class="icon-chevron-up"></i>
                      </a>
                  </div>
              </div>

              <div class="widget-body">
                  <div class="widget-main">
                      <?= $form->field($conf, 'pre_deploy')->textarea()->label('pre_deploy', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'post_deploy')->textarea()->label('post_deploy', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'post_release')->textarea()->label('post_release', ['class' => 'control-label bolder']) ?>
                  </div>
              </div>
          </div>
        </div>
        </div>
        <!-- 目标机器 配置 end-->
        <div class="hr hr-dotted"></div>

        <!-- git 配置-->
          <?= $form->field($conf, 'git_url')
              ->textInput(['placeholder' => 'gitlab、bitbucket、github ssh-url eg: git@github.com:meolu/walle-web.git'])
              ->label('git地址', ['class' => 'control-label bolder blue']) ?>
        <!-- git 配置 end-->

        <div class="form-group">
            <label class="control-label bolder blue">分支/tag上线:</label>
            <div class="radio" style="display: inline;">
                <label>
                    <input name="Conf[git_type]" value="<?= Conf::GIT_BRANCH ?>" <?= $conf->git_type == Conf::GIT_BRANCH ? 'checked' : '' ?> type="radio" checked class="ace">
                    <span class="lbl"> branch </span>
                </label>
            </div>

            <div class="radio" style="display: inline;">
                <label>
                    <input name="Conf[git_type]" value="<?= Conf::GIT_TAG ?>" <?= $conf->git_type == Conf::GIT_TAG ? 'checked' : '' ?> type="radio" class="ace">
                    <span class="lbl"> tag </span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label bolder blue" for="form-field-2">
                是否开启审核:
                <input name="Conf[audit]" value="0" type="hidden">
                <input name="Conf[audit]" value="1" type="checkbox" <?= $conf->audit ? 'checked' : '' ?> class="ace ace-switch ace-switch-5">
                <span class="lbl"></span>
            </label>
        </div>

        <div class="form-group">
            <label class="control-label bolder blue">是否启用:
                <input name="Conf[status]" value="0" type="hidden">
                <input name="Conf[status]" value="1" id="xxxx" <?= $conf->status ? 'checked' : '' ?> type="checkbox" class="ace ace-switch ace-switch-6">
                <span class="lbl"></span>
            </label>
        </div>
      </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="提交">
      </div>
    <?php ActiveForm::end(); ?>

</div>