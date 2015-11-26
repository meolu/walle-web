<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('task', 'submit task title');
use yii\widgets\ActiveForm;
use app\models\Project;

?>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
      <div class="box-body">
        <?= $form->field($task, 'title')->label(yii::t('task', 'submit title'), ['class' => 'control-label bolder blue']) ?>

        <!-- 分支选取 -->
        <?php if ($conf->repo_mode == Project::REPO_BRANCH) { ?>
          <div class="form-group">
              <label><?= yii::t('task', 'select branches') ?>
                  <a class="show-tip icon-refresh green" href="javascript:;"></a>
                  <span class="tip"><?= yii::t('task', 'all branches') ?></span>
                  <i class="get-branch icon-spinner icon-spin orange bigger-125" style="display: none"></i>
              </label>
              <select name="Task[branch]" aria-hidden="true" tabindex="-1" id="branch" class="form-control select2 select2-hidden-accessible">
                  <option value="master">master</option>
              </select>
          </div>
        <?php } ?>
        <!-- 分支选取 end -->
        <?= $form->field($task, 'commit_id')->dropDownList([])
          ->label(yii::t('task', 'select branch').'<i class="get-history icon-spinner icon-spin orange bigger-125"></i>', ['class' => 'control-label bolder blue']) ?>

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
        // 用户上次选择的分支作为转为分支
        var project_id = <?= (int)$_GET['projectId'] ?>;
        var branch_name= 'pre_branch_' + project_id;
        var pre_branch = ace.cookie.get(branch_name);
        if (pre_branch) {
            var option = '<option value="' + pre_branch + '" selected>' + pre_branch + '</option>';
            $('#branch').html(option)
        }

        function getBranchList() {
            $('.get-branch').show();
            $('.tip').hide();
            $('.show-tip').hide();
            $.get("/walle/get-branch?projectId=" + <?= (int)$_GET['projectId'] ?>, function (data) {
                // 获取分支失败
                if (data.code) {
                    showError(data.msg);
                }
                var select = '';
                $.each(data.data, function (key, value) {
                    // 默认选中 master 分支
                    var checked = value.id == 'master' ? 'selected' : '';
                    select += '<option value="' + value.id + '"' + checked + '>' + value.message + '</option>';
                })
                $('#branch').html(select);
                $('.get-branch').hide();
                $('.show-tip').show();
            });
        }

        function getCommitList() {
            $('.get-history').show();
            $.get("/walle/get-commit-history?projectId=" + <?= (int)$_GET['projectId'] ?> +"&branch=" + $('#branch').val(), function (data) {
                // 获取commit log失败
                if (data.code) {
                    showError(data.msg);
                }

                var select = '';
                $.each(data.data, function (key, value) {
                    select += '<option value="' + value.id + '">' + value.message + '</option>';
                })
                $('#task-commit_id').html(select);
                $('.get-history').hide()
            });
        }

        $('#branch').change(function() {
            // 添加cookie记住最近使用的分支名字
            ace.cookie.set(branch_name, $(this).val(), 86400*30)
            getCommitList();
        })

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
            })

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

    })

</script>