<?php
/**
 * @var yii\web\View $this
 */
$this->title = '配置项目';
use app\models\Project;
use yii\widgets\ActiveForm;
?>
<style>
    .control-label {text-align: right;}
</style>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
    <div class="box-body">
        <?= $form->field($conf, 'name')
            ->textInput([
                'class'          => 'col-xs-11',
            ])
            ->label('项目名字:', ['class' => 'control-label bolder blue col-xs-1']) ?>

        <div class="clearfix"></div>
        <?= $form->field($conf, 'level')->dropDownList([
            Project::LEVEL_TEST => \Yii::t('status', 'conf_level_' . Project::LEVEL_TEST),
            Project::LEVEL_SIMU => \Yii::t('status', 'conf_level_' . Project::LEVEL_SIMU),
            Project::LEVEL_PROD => \Yii::t('status', 'conf_level_' . Project::LEVEL_PROD),
        ],[
            'class'          => 'col-xs-11',])
            ->label('项目环境:', ['class' => 'control-label bolder blue col-xs-1']) ?>
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
                'class'          => 'col-xs-11',
                'placeholder'    => 'git@github.com:meolu/walle-web.git',
                'data-placement' => 'top',
                'data-rel'       => 'tooltip',
                'data-title'     => '支持git/svn。git格式:ssh-url，需要把宿主机php进程用户的ssh-key加入git信任',
            ])
            ->label('地址:', ['class' => 'control-label bolder blue col-xs-1']) ?>
        <!-- 地址 配置 end-->
        <div class="clearfix"></div>
        <?php if (empty($_GET['projectId']) || $conf->repo_type == Project::REPO_SVN) { ?>
        <div class="username-password" style="<?= empty($_GET['projectId']) ? 'display:none' : '' ?>">
        <?= $form->field($conf, 'repo_username')
            ->textInput([
                'class'          => 'col-xs-3',
            ])
            ->label('用户名:', ['class' => 'control-label bolder blue col-xs-1']) ?>
        <?= $form->field($conf, 'repo_password')
            ->textInput([
                'class'          => 'col-xs-3',
            ])
            ->label('密码:', ['class' => 'control-label bolder blue col-xs-1']); ?>
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
                  <h4 class="lighter"><i class="icon-dashboard orange"></i>宿主机</h4>
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
                                  'placeholder'    => '/var/www/deploy',
                                  'data-placement' => 'top',
                                  'data-rel'       => 'tooltip',
                                  'data-title'     => '代码的检出存放路径',
                              ])
                          ->label('代码存储仓库<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'excludes')
                          ->textarea([
                              'placeholder'    => '.git' . PHP_EOL . 'README.md',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '剔除不上线的文件、目录，每行一个',
                          ])
                          ->label('排除文件', ['class' => 'control-label bolder']) ?>
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
                              'data-title'     => '代码的部署的用户，一般是运行的服务的用户，如php进程用户www',
                          ])
                          ->label('用户<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'release_to')
                          ->textInput([
                              'placeholder'    => '/var/www/walle',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '代码的最终部署路径',
                          ])
                          ->label('webroot<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'release_library')
                          ->textInput([
                              'placeholder'    => '/var/releases',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '代码发布的版本库，每次发布更新webroot的软链到当前最新版本，请勿新建该目录，会自动创建软链',
                          ])
                          ->label('发布版本库<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'hosts')
                          ->textarea([
                              'placeholder'    => '192.168.0.1' . PHP_EOL . '192.168.0.2',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '要发布的机器列表，一行一个',
                          ])
                          ->label('机器列表<small><i class="light-blue icon-asterisk"></i></small>', ['class' => 'control-label bolder']) ?>
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
                  <span class="help-button" data-rel="popover" data-trigger="hover" data-placement="right" data-content="{WORKSPACE}：webroot    {VERSION}：发布的版本库的当前版本" title="" data-original-title="辅助变量">?</span>
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
                              'placeholder'    => 'cd /var/www/yii2 && composer update',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '在部署代码之前的准备工作，如git的一些前置检查、vendor的安装（更新），一行一条',
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('pre_deploy', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'post_deploy')
                          ->textarea([
                              'placeholder'    => 'cp -rf {WORKSPACE}/web/index-prod.php {WORKSPACE}/web/index.php' . PHP_EOL . 'cp -rf /var/www/yii2/vendor {WORKSPACE}/',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => 'git代码检出之后，可能做一些调整处理，如vendor拷贝，环境适配（mv config-test.php config.php），一行一条',
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('post_deploy', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'pre_release')
                          ->textarea([
                              'placeholder'    => '/var/www/xxx stop',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '同步完所有目标机器之后，更改版本软链之前触发任务。java可能要做一些暂停服务的操作(双引号将会被转义为\")',
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('pre_release', ['class' => 'control-label bolder']) ?>
                      <?= $form->field($conf, 'post_release')
                          ->textarea([
                              'placeholder'    => '/usr/local/nginx/sbin/nginx -s reload',
                              'data-placement' => 'top',
                              'data-rel'       => 'tooltip',
                              'data-title'     => '所有目标机器都部署完毕之后，做一些清理工作，如删除缓存、重启服务（nginx、php、task），一行一条(双引号将会被转义为\")',
                              'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                              'onfocus'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                              'onblur'         => "clearInterval(this.clock);",
                          ])
                          ->label('post_release', ['class' => 'control-label bolder']) ?>
                  </div>
              </div>
          </div>
        </div>
        </div>
        <!-- 目标机器 配置 end-->
        <div class="hr hr-dotted"></div>

        <div class="form-group">
            <label class="control-label bolder blue">分支/tag上线:</label>
            <div class="radio" style="display: inline;" data-rel="tooltip" data-title="测试环境推荐选项，可以选择branch和commit" data-placement="right">
                <label>
                    <input name="Project[repo_mode]" value="<?= Project::REPO_BRANCH ?>" <?= $conf->repo_mode == Project::REPO_BRANCH ? 'checked' : '' ?> type="radio" checked class="ace">
                    <span class="lbl"> branch </span>
                </label>
            </div>

            <div class="radio" style="display: inline;" data-rel="tooltip" data-title="仿真和生产环境推荐选项" data-placement="right">
                <label>
                    <input name="Project[repo_mode]" value="<?= Project::REPO_TAG ?>" <?= $conf->repo_mode == Project::REPO_TAG ? 'checked' : '' ?> type="radio" class="ace">
                    <span class="lbl"> tag </span>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label bolder blue" for="form-field-2">
                是否开启审核:
                <input name="Project[audit]" value="0" type="hidden">
                <input name="Project[audit]" value="1" type="checkbox" <?= $conf->audit ? 'checked' : '' ?> class="ace ace-switch ace-switch-5"  data-rel="tooltip" data-title="开启时，用户提交上线任务需要审核方可上线" data-placement="right">
                <span class="lbl"></span>
            </label>
        </div>

        <div class="form-group">
            <label class="control-label bolder blue">是否启用:
                <input name="Project[status]" value="0" type="hidden">
                <input name="Project[status]" value="1" <?= $conf->status ? 'checked' : '' ?> type="checkbox" class="ace ace-switch ace-switch-6"  data-rel="tooltip" data-title="关闭时，用户不能对此项目发起上线" data-placement="right">
                <span class="lbl"></span>
            </label>
        </div>
      </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="提交">
      </div>
    <?php ActiveForm::end(); ?>

</div>

<script>
    jQuery(function($) {
        $('[data-rel=tooltip]').tooltip({container:'body'});
        $('[data-rel=popover]').popover({container:'body'});
        $('.show-git').click(function() {
            $('.username-password').hide();
            $('#project-repo_type').val('git')
        })
        $('.show-svn').click(function() {
            $('.username-password').show();
            $('#project-repo_type').val('svn')
        })
    });
</script>