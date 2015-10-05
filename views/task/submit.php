<?php
/**
 * @var yii\web\View $this
 */
$this->title = '发起上线';
use yii\widgets\ActiveForm;
use app\models\Project;

?>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
      <div class="box-body">
        <?= $form->field($task, 'title')->label('任务标题', ['class' => 'control-label bolder blue']) ?>

        <!-- 分支选取 -->
        <?php if ($conf->git_type == Project::GIT_BRANCH) { ?>
          <div class="form-group">
              <label>选取分支
                  <a class="show-tip icon-refresh green" href="#"></a>
                  <span class="tip">查看所有分支</span>
                  <i class="get-branch icon-spinner icon-spin orange bigger-125" style="display: none"></i></label>
              <select name="commit" aria-hidden="true" tabindex="-1" id="branch" class="form-control select2 select2-hidden-accessible">
                  <option value="master">master</option>
              </select>
          </div>
        <?php } ?>
        <!-- 分支选取 end -->
        <?= $form->field($task, 'commit_id')->dropDownList([])
          ->label('版本选取<i class="get-history icon-spinner icon-spin orange bigger-125"></i>', ['class' => 'control-label bolder blue']) ?>

      </div><!-- /.box-body -->

      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="提交">
      </div>
    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
	$(function() {
        function getBranchList() {
            $('.get-branch').show();
            $('.tip').hide();
            $('.show-tip').hide();
            $.get("/walle/get-branch?projectId=" + <?= (int)$_GET['projectId'] ?>, function (data) {
                var select = '';
                $.each(data.data, function (key, value) {
                    select += '<option value="' + value.id + '">' + value.message + '</option>';
                })
                $('#branch').append(select);
                $('.get-branch').hide();
                $('.show-tip').show();
            });
        }

        function getCommitList() {
            $.get("/walle/get-commit-history?projectId=" + <?= (int)$_GET['projectId'] ?> +"&branch=" + $('#branch').val(), function (data) {
                var select = '';
                $.each(data.data, function (key, value) {
                    select += '<option value="' + value.id + '">' + value.message + '</option>';
                })
                $('#task-commit_id').html(select);
                $('.get-history').hide()
            });
        }

        $('#branch').change(function() {
            $('.get-history').show();
            getCommitList();
        })

        // 页面加载完默认拉取master的commit log
        getCommitList();

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

		
	})

</script>