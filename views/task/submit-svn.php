<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('task', 'submit task title');
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\Project;
use app\models\Task;

?>
<style>
.tooltip-inner {
    max-width: none;
    white-space: nowrap;
    text-align:left;
}
</style>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
      <div class="box-body">
        <?= $form->field($task, 'title')->label(yii::t('task', 'submit title'), ['class' => 'control-label bolder blue']) ?>
          <!-- 无trunk时，不需要查看所有分支-->
          <?php if ($conf->repo_mode == Project::REPO_MODE_NONTRUNK) { ?>
              <input type="hidden" id="branch" class="form-control" name="Task[branch]" value="">
          <?php } else { ?>
              <!-- 分支选取 -->
              <div class="form-group">
                  <label class="control-label bolder blue">
                      <?= yii::t('task', 'select branches') ?>
                      <a class="show-tip icon-refresh green" href="javascript:;"></a>
                      <span class="tip"><?= yii::t('task', 'all branches') ?></span>
                      <i class="get-branch icon-spinner icon-spin orange bigger-125" style="display: none"></i>
                  </label>
                  <select name="Task[branch]" aria-hidden="true" tabindex="-1" id="branch" class="form-control select2 select2-hidden-accessible">
                      <?php if ($conf->repo_mode == Project::REPO_MODE_BRANCH) { ?>
                          <option value="trunk">trunk</option>
                      <?php } ?>
                  </select>
              </div>
          <?php } ?>

          <!-- 分支选取 end -->
          <div class="clearfix"></div>

          <?= $form->field($task, 'commit_id')->dropDownList([])
              ->label(yii::t('task', 'select branch').'<i class="get-history icon-spinner icon-spin orange bigger-125"></i>', ['class' => 'control-label bolder blue']) ?>

          <!-- 全量/增量 -->
          <div class="form-group">
              <label class="text-right bolder blue">
                  <?= yii::t('task', 'file transmission mode'); ?>
              </label>
              <div id="transmission-full-ctl" class="radio" style="display: inline;" data-rel="tooltip" data-title="<?= yii::t('task', 'file transmission mode full tip') ?>" data-placement="right">
                  <label>
                      <input name="Task[file_transmission_mode]" value="<?= Task::FILE_TRANSMISSION_MODE_FULL ?>" checked="checked" type="radio" class="ace">
                      <span class="lbl"><?= yii::t('task', 'file transmission mode full') ?></span>
                  </label>
              </div>

              <div id="transmission-part-ctl" class="radio" style="display: inline;" data-rel="tooltip" data-title="<?= yii::t('task', 'file transmission mode part tip') ?>" data-placement="right">
                  <label>
                      <input name="Task[file_transmission_mode]" value="<?= Task::FILE_TRANSMISSION_MODE_PART ?>" type="radio" class="ace">
                      <span class="lbl"><?= yii::t('task', 'file transmission mode part') ?></span>
                  </label>
              </div>
          </div>
          <!-- 全量/增量 end -->

          <!-- 文件列表 -->
          <?= $form->field($task, 'file_list')
              ->textarea([
                  'rows'           => 12,
                  'placeholder'    => "index.php\nREADME.md\ndir_name\nfile*",
                  'data-html'      => 'true',
                  'data-placement' => 'top',
                  'data-rel'       => 'tooltip',
                  'data-title'     => yii::t('task', 'file list placeholder'),
                  'style'          => 'display: none',
              ])
              ->label(yii::t('task', 'file list'),
                  ['class' => 'control-label bolder blue', 'style' => 'display: none']) ?>

      </div><!-- /.box-body -->

      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="<?= yii::t('w', 'submit') ?>">
      </div>

    <!-- 错误提示-->
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="800px">
                <div class="modal-header">
                    <button type="button" class="close"
                            data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        <?= yii::t('w', 'modal error title') ?>
                    </h4>
                </div>
                <div class="modal-body"></div>
            </div><!-- /.modal-content -->
        </div>

    </div>
    <!-- 错误提示-->

    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
    jQuery(function($) {
        $('[data-rel=tooltip]').tooltip({container:'body'});

        var projectId = <?= (int)$_GET['projectId'] ?>;

        // 用户上次选择的分支作为转为分支
        var branch_name = 'pre_branch_' + projectId;
        var pre_branch = ace.cookie.get(branch_name);
        if (pre_branch) {
            var option = '<option value="' + pre_branch + '" selected>' + pre_branch + '</option>';
            $('#branch').html(option);
        }

        function getBranchList() {
            $('.get-branch').show();
            $('.tip').hide();
            $('.show-tip').hide();
            $.get("<?= Url::to('@web/walle/get-branch?projectId=') ?>" + projectId, function (data) {
                // 获取分支失败
                if (data.code) {
                    showError(data.msg);
                }
                var select = '';
                $.each(data.data, function (key, value) {
                    // 默认选中 trunk 主干
                    var checked = value.id == 'trunk' ? 'selected' : '';
                    select += '<option value="' + value.id + '"' + checked + '>' + value.message + '</option>';
                });
                $('#branch').html(select);
                $('.get-branch').hide();
                $('.show-tip').show();

                if(data.data.length == 1 || ace.cookie.get(branch_name) != 'trunk') {
                    // 获取分支完成后, 一定条件重新获取提交列表
                    $('#branch').change();
                }
            });
        }

        // 获取commit log
        function getCommitList() {
            $('.get-history').show();
            $.get("<?= Url::to('@web/walle/get-commit-history?projectId=') ?>" + projectId +"&branch=" + $('#branch').val(), function (data) {
                // 获取commit log失败
                if (data.code) {
                    showError(data.msg);
                }

                var select = '';
                $.each(data.data, function (key, value) {
                    select += '<option value="' + value.id + '">' + value.id + ' - ' + value.message + '</option>';
                });
                $('#task-commit_id').html(select);
                $('.get-history').hide()
            });
        }

        $('#branch').change(function() {
            // 添加cookie记住最近使用的分支名字
            ace.cookie.set(branch_name, $(this).val(), 86400*30)
            getCommitList();
        });

        // 页面加载完默认拉取master的commit log
        getCommitList();

        // 查看所有分支提示
        $('.show-tip')
            .hover(
            function() {
                $('.tip').show()
            },
            function() {
                $('.tip').hide()
            })
            .click(function() {
                getBranchList();
            });

        // 错误提示
        function showError($msg) {
            $('.modal-body').html($msg);
            $('#myModal').modal({
                backdrop: true,
                keyboard: true,
                show: true
            });
        }

        // 清除提示框内容
        $("#myModal").on("hidden.bs.modal", function () {
            $(this).removeData("bs.modal");
        });

        // 公共提示
        $('[data-rel=tooltip]').tooltip({container:'body'});
        $('[data-rel=popover]').popover({container:'body'});

        // 切换显示文件列表
        $('body').on('click', '#transmission-full-ctl', function() {
            $('#task-file_list').hide();
            $('label[for="task-file_list"]').hide();
        }).on('click', '#transmission-part-ctl', function() {
            $('#task-file_list').show();
            $('label[for="task-file_list"]').show();
        });
    })

</script>
