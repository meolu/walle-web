<?php
/**
 * @var yii\web\View $this
 */
$this->title = '提交上线单';
use yii\widgets\ActiveForm;
use app\models\Project;

?>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
      <div class="box-body">
        <?= $form->field($task, 'title')->label('任务标题', ['class' => 'control-label bolder blue']) ?>

        <!-- 分支选取 -->
          <div class="form-group">
              <label class="control-label bolder blue">选取分支
                  <a class="show-tip icon-refresh green" href="javascript:;"></a>
                  <span class="tip">查看所有分支</span>
                  <i class="get-branch icon-spinner icon-spin orange bigger-125" style="display: none"></i>
              </label>
              <select name="Task[branch]" aria-hidden="true" tabindex="-1" id="branch" class="form-control select2 select2-hidden-accessible">
                  <?php if ($conf->repo_mode == Project::REPO_BRANCH) { ?>
                  <option value="trunk">trunk</option>
                  <?php } ?>
              </select>
          </div>
          <div>
          <div class="form-group col-xs-3">
              <label class="control-label bolder blue">前提交历史</label>
              <i class="getting-history icon-spinner icon-spin orange bigger-125" style=""></i>
              <select name="i_don_not_care_this" id="start" class="form-control select2 col-xs-3 history-list">
              </select>
          </div>
          <div class="form-group col-xs-3">
              <label class="control-label bolder blue">后提交历史</label>
              <i class="getting-history icon-spinner icon-spin orange bigger-125" style=""></i>
              <select name="Task[commit_id]" id="end" class="form-control select2 col-xs-3 history-list">
              </select>
          </div>
          </div>
          <div class="clearfix"></div>

        <!-- 分支选取 end -->

          <?= $form->field($task, 'file_list')
              ->textarea([
                  'rows'           => 12,
                  'placeholder'    => 'index.php  1234',
                  'style'          => 'overflow:scroll;overflow-y:hidden;;overflow-x:hidden',
                  'onchange'        => "window.activeobj=this;this.clock=setInterval(function(){activeobj.style.height=activeobj.scrollHeight+'px';},200);",
                  'onblur'         => "clearInterval(this.clock);",
              ])
              ->label('文件列表<i class="getting-change-files icon-spinner icon-spin orange bigger-125" style="display: none"></i>', ['class' => 'control-label bolder blue']) ?>
      </div><!-- /.box-body -->

      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="提交">
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
                        发生了错误
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
        var projectId =  <?= (int)$_GET['projectId'] ?>;
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
                    // 默认选中 trunk 主干
                    var checked = value.id == 'trunk' ? 'selected' : '';
                    select += '<option value="' + value.id + '"' + checked + '>' + value.message + '</option>';
                })
                $('#branch').html(select);
                $('.get-branch').hide();
                $('.show-tip').show();
                getCommitList();
            });
        }
//
        function getCommitList() {
            $.get("/walle/get-commit-history?projectId=" + <?= (int)$_GET['projectId'] ?> +"&branch=" + $('#branch').val(), function (data) {
                // 获取commit log失败
                if (data.code) {
                    showError(data.msg);
                }

                var select = '';
                $.each(data.data, function (key, value) {
                    select += '<option value="' + value.id + '">' + value.message + '</option>';
                })
                $('.history-list').html(select);
                $('.getting-history').hide()
            });
        }

        function getChangeFiles(projectId, branch, start, end) {
            $.get("/walle/get-commit-file?projectId=" + projectId +"&branch=" + branch + "&start=" + start + "&end=" + end, function (data) {
                // 获取commit log失败
                if (data.code) {
                    showError(data.msg);
                }

                var files = '';
                $.each(data.data, function (key, value) {
                    files += value + "\n";
                })
                $('#task-file_list').html(files);
                $('.getting-change-files').hide();
            });
        }

        $('#branch').change(function() {
            $('.getting-history').show();
            getCommitList();
        })

        // 选择两个commit_id之间提交的文件
        $('.history-list').change(function() {
            var startId = $('#start').val();
            var endId   = $('#end').val();
            $('.getting-change-files').show();
            getChangeFiles(projectId, $('#branch').val(), startId, endId);
        })

        // 页面加载完默认拉取trunk
        getBranchList();
        // 页面加载完默认拉取trunk
        if ($('#branch').val()) {
            getCommitList();
        }


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