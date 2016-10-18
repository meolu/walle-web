<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('conf', 'edit');
use app\models\Project;
use yii\widgets\ActiveForm;
?>

<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
    <div class="box-body">
        <?= $form->field($conf, 'name')
            ->textInput([
                'class'          => 'col-sm-11',
            ])
            ->label(yii::t('conf', 'name'), ['class' => 'text-right bolder blue col-sm-1']) ?>

        <div class="clearfix"></div>
        <?= $form->field($conf, 'level')->dropDownList([
            Project::LEVEL_TEST => \Yii::t('w', 'conf_level_' . Project::LEVEL_TEST),
            Project::LEVEL_SIMU => \Yii::t('w', 'conf_level_' . Project::LEVEL_SIMU),
            Project::LEVEL_PROD => \Yii::t('w', 'conf_level_' . Project::LEVEL_PROD),
        ],[
            'class'          => 'col-sm-11',])
            ->label(yii::t('conf', 'env'), ['class' => 'text-right bolder blue col-sm-1']) ?>
        <div class="clearfix"></div>
        <?php if (empty($_GET['projectId'])) { ?>
        <div class="widget-box transparent" id="recent-box" style="margin-top:15px">
            <div class="tabbable no-border">
                <h4 class="lighter smaller" style="float:left; margin: 9px 26px -19px 9px">
                    <i class="icon-map-marker orange"></i>
                    Repo
                </h4>
                <ul class="nav nav-tabs" id="recent-tab">
                    <li class="active">
                        <a data-toggle="tab" class="show-git" href="#repo-tab">Git</a>
                    </li>

                    <li class="">
                        <a data-toggle="tab" class="show-svn" href="#repo-tab">Svn</a>
                    </li>
                </ul>
            </div>
        </div>
        <?php } ?>

        <!-- 地址 配置-->
        <?= $form->field($conf, 'repo_url')
            ->textInput([
                'class'          => 'col-sm-11',
                'placeholder'    => 'git@github.com:meolu/walle-web.git',
                'data-placement' => 'top',
                'data-rel'       => 'tooltip',
                'data-title'     => yii::t('conf', 'repo url tip'),
            ])
            ->label(yii::t('conf', 'url'), ['class' => 'text-right bolder blue col-sm-1']) ?>
        <!-- 地址 配置 end-->
        <div class="clearfix"></div>
        <?php if (empty($_GET['projectId']) || $conf->repo_type == Project::REPO_SVN) { ?>
        <div class="username-password" style="<?= empty($_GET['projectId']) ? 'display:none' : '' ?>">
        <?= $form->field($conf, 'repo_username')
            ->textInput([
                'class'          => 'col-sm-3',
            ])
            ->label(yii::t('conf', 'username'), ['class' => 'text-right bolder blue col-sm-1']) ?>
        <?= $form->field($conf, 'repo_password')
            ->passwordInput([
                'class'          => 'col-sm-3',
            ])
            ->label(yii::t('conf', 'password'), ['class' => 'text-right bolder blue col-sm-1']); ?>
        </div>
        <div class="clearfix"></div>

        <?php } ?>
        <?= $form->field($conf, 'repo_type')
            ->hiddenInput()
            ->label('') ?>

        <!-- 宿主机 配置-->
        <div class="row">
        <div class="col-sm-4">
          <div class="widget-box transparent">
              <div class="widget-header widget-header-flat">
                  <h4 class="lighter">
                      <i class="icon-dashboard orange"></i>
                      <?= yii::t('conf', 'host') ?>
                  </h4>
                  <div class="widget-toolbar">
                      <a href="javascript:;" data-action="collapse">
                          <i class="icon-chevron-up"></i>
                      </a>
                  </div>
              </div>

              <div class="widget-body">
                  <div class="widget-main">
                      <?= $form->field($conf, 'deploy_from')
                          ->textInput([
                                  'placeholder'    => '/data/www/deploy',
                                  'data-placement' => 'top',
                                  'data-rel'       => 'tooltip',
                                  'data-title'     => yii::t('conf', 'deploy from tip'),
                              ])
                          ->label(yii::t('conf', 'deploy from').'<small><i class="light-blue icon-asterisk"></i></small>',
                              ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'excludes')
                          ->textarea([
                              'placeholder'    => ".git\n.svn\nREADME.md",
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'excludes tip'),
                              'rows'           => 10,
                          ])
                          ->label(yii::t('conf', 'excludes'), ['class' => 'text-right bolder']) ?>
                  </div>
              </div>
          </div>
        </div>
        <!-- 宿主机 配置 end-->
        <!-- 目标机器 配置-->
        <div class="col-sm-4">
          <div class="widget-box transparent">
              <div class="widget-header widget-header-flat">
                  <h4 class="lighter">
                      <i class="icon-cloud-upload orange"></i>
                      <?= yii::t('conf', 'targets') ?>
                  </h4>
                  <div class="widget-toolbar">
                      <a href="javascript:;" data-action="collapse">
                          <i class="icon-chevron-up"></i>
                      </a>
                  </div>
              </div>

              <div class="widget-body">
                  <div class="widget-main">
                      <?= $form->field($conf, 'release_user')
                          ->textInput([
                              'placeholder'    => 'www',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'target user tip'),
                          ])
                          ->label(yii::t('conf', 'target user').'<small><i class="light-blue icon-asterisk"></i></small>',
                              ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'release_to')
                          ->textInput([
                              'placeholder'    => '/data/www/walle',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'webroot tip'),
                          ])
                          ->label('webroot<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'release_library')
                          ->textInput([
                              'placeholder'    => '/data/releases',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'releases tip'),
                          ])
                          ->label(yii::t('conf', 'releases').'<small><i class="light-blue icon-asterisk"></i></small>',
                              ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'keep_version_num')
                          ->textInput([
                              'placeholder'    => '20',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'keep version tip'),
                          ])
                          ->label(yii::t('conf', 'keep version').'<small><i class="light-blue icon-asterisk"></i></small>',
                              ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'hosts')
                          ->textarea([
                              'placeholder'    => '192.168.0.1' . PHP_EOL . '192.168.0.2:8888',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'servers tip'),
                              'rows'           => 5,
                          ])
                          ->label(yii::t('conf', 'servers').'<small><i class="light-blue icon-asterisk"></i></small>',
                              ['class' => 'text-right bolder']) ?>
                  </div>
              </div>
          </div>
        </div>

        <!-- 目标机器 配置 end-->
        <!-- 任务配置-->
        <div class="col-sm-4">
          <div class="widget-box transparent">
              <div class="widget-header widget-header-flat">
                  <h4 class="lighter">
                      <i class="icon-tasks orange"></i>
                      <?= yii::t('conf', 'tasks') ?>
                  </h4>
                  <span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right"
                        data-content="<?= yii::t('conf', 'task help') ?>"
                        title="" data-original-title="<?= yii::t('conf', 'task help head') ?>">?</span>
                  <div class="widget-toolbar">
                      <a href="javascript:;" data-action="collapse">
                          <i class="icon-chevron-up"></i>
                      </a>
                  </div>
              </div>

              <div class="widget-body">
                  <div class="widget-main">
                      <?= $form->field($conf, 'pre_deploy')
                          ->textarea([
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'pre_deploy tip'),
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('pre_deploy', ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'post_deploy')
                          ->textarea([
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'post_deploy tip'),
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('post_deploy', ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'pre_release')
                          ->textarea([
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'pre_release tip'),
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('pre_release', ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'post_release')
                          ->textarea([
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'post_release tip'),
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('post_release', ['class' => 'text-right bolder']) ?>
                      <?= $form->field($conf, 'post_release_delay')
                          ->textInput([
                              'placeholder'    => '0',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => yii::t('conf', 'post_release_delay tip'),
                          ])
                          ->label(yii::t('conf', 'post_release_delay'), ['class' => 'text-right bolder']) ?>
                  </div>
              </div>
          </div>
        </div>
        </div>
        <!-- 目标机器 配置 end-->
        <div class="hr hr-dotted"></div>

        <div class="form-group">
            <label class="text-right bolder blue">
                <?= yii::t('conf', 'branch/tag') ?>
            </label>
            <div class="radio" style="display: inline;" data-rel="tooltip" data-title="<?= yii::t('conf', 'branch tip') ?>" data-placement="right">
                <label>
                    <input name="Project[repo_mode]" value="<?= Project::REPO_MODE_BRANCH ?>" <?= $conf->repo_mode == Project::REPO_MODE_BRANCH ? 'checked="checked"' : '' ?> type="radio" class="ace">
                    <span class="lbl"> branch </span>
                </label>
            </div>

            <div class="radio" style="display: inline;" data-rel="tooltip" data-title="<?= yii::t('conf', 'tag tip') ?>" data-placement="right">
                <label>
                    <input name="Project[repo_mode]" value="<?= Project::REPO_MODE_TAG ?>" <?= $conf->repo_mode == Project::REPO_MODE_TAG ? 'checked="checked"' : '' ?> type="radio" class="ace">
                    <span class="lbl"> tag </span>
                </label>
            </div>

            <div id="div-repo_mode_nontrunk" class="radio" style="display: <?php if ($conf->repo_type == Project::REPO_SVN) { echo 'inline';} else {echo 'none';} ?>;" data-rel="tooltip" data-title="<?= yii::t('conf', 'nontrunk tip') ?>" data-placement="right">
                <label>
                    <input name="Project[repo_mode]" value="<?= Project::REPO_MODE_NONTRUNK ?>" <?= $conf->repo_mode == Project::REPO_MODE_NONTRUNK ? 'checked="checked"' : '' ?> type="radio" class="ace">
                    <span class="lbl"><?= yii::t('conf', 'nontrunk') ?></span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="text-right bolder blue" for="form-field-2">
                <?= yii::t('conf', 'enable audit') ?>
                <input name="Project[audit]" value="0" type="hidden">
                <input name="Project[audit]" value="1" type="checkbox" <?= $conf->audit ? 'checked' : '' ?>
                       class="ace ace-switch ace-switch-5"  data-rel="tooltip" data-title="<?= yii::t('conf', 'audit tip') ?>" data-placement="right">
                <span class="lbl"></span>
            </label>
        </div>

        <div class="form-group">
            <label class="text-right bolder blue" for="form-field-2">
                <?= yii::t('conf', 'enable ansible') ?>
                <input name="Project[ansible]" value="0" type="hidden">
                <input name="Project[ansible]" value="1" type="checkbox" <?= $conf->ansible ? 'checked' : '' ?>
                       class="ace ace-switch ace-switch-5"  data-rel="tooltip" data-title="<?= yii::t('conf', 'ansible tip') ?>" data-placement="right">
                <span class="lbl"></span>
            </label>
        </div>

        <div class="form-group">
            <label class="text-right bolder blue">
                <?= yii::t('conf', 'enable open') ?>
                <input name="Project[status]" value="0" type="hidden">
                <input name="Project[status]" value="1" <?= $conf->status ? 'checked' : '' ?> type="checkbox"
                       class="ace ace-switch ace-switch-6"  data-rel="tooltip" data-title="<?= yii::t('conf', 'open tip') ?>" data-placement="right">
                <span class="lbl"></span>
            </label>
        </div>

      </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="<?= yii::t('w', 'submit') ?>">
      </div>
    <?php ActiveForm::end(); ?>

</div>

<script>
    jQuery(function($) {
        $('[data-rel=tooltip]').tooltip({container:'body'});
        $('[data-rel=popover]').popover({container:'body'});
        $('.show-git').click(function() {
            $('.username-password').hide();
            $('#project-repo_type').val('git');
            $('#div-repo_mode_nontrunk').hide();
        });
        $('.show-svn').click(function() {
            $('.username-password').show();
            $('#project-repo_type').val('svn');
            $('#div-repo_mode_nontrunk').css({'display': 'inline'});
        });
    });
</script>
