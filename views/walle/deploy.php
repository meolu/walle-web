<?php
/**
 * @var yii\web\View $this
 */
$this->title = '部署上线';
use \app\models\Task;
?>
<style>
    .status > span {
        float: left;
        font-size: 12px;
        width: 25%;
        text-align: right;
    }
    .btn-deploy {
        margin-left: 30px;
    }
    .btn-return {
        /*float: right;*/
        margin-left: 30px;
    }
</style>
<div class="box" style="height: 100%">
    <h4 class="box-title"><?= $task->title ?> - <?= $task->commit_id ?>
        <?php if (in_array($task->status, [Task::STATUS_PASS, Task::STATUS_FAILED])) { ?>
            <button type="submit" class="btn btn-primary btn-deploy" data-id="<?= $task->id ?>">部署</button>
        <?php } ?>
        <a class="btn btn-success btn-return" href="/walle/index">返回</a></h4>

    <div class="status">
        <span><i class="fa fa-circle-o text-yellow step-1"></i>权限、目录检查</span>
        <span><i class="fa fa-circle-o text-yellow step-2"></i>代码检出</span>
        <span><i class="fa fa-circle-o text-yellow step-3"></i>同步至服务器</span>
        <span><i class="fa fa-circle-o text-yellow step-4"></i>更新全量服务器</span>
    </div>
    <div style="clear:both"></div>
    <div class="progress progress-small progress-striped active">
        <div class="progress-bar progress-status progress-status" style="width: 0%;"></div>
    </div>

    <div class="alert alert-block alert-success result-success" style="<?php if ($task->status != Task::STATUS_DONE) { ?>display: none <?php } ?>">
        <h4>上线成功!</h4>
        <p>辛苦了，小主：）</p>

    </div>

    <div class="alert alert-block alert-danger result-failed" style="display: none">
        <h4>上线出错:（</h4>
        <span class="error-msg">
        </span>
    </div>

</div>

<script type="text/javascript">
    $(function() {
        $('.btn-deploy').click(function() {
            $this = $(this);
            $this.addClass('disabled');
            var task_id = $(this).data('id');
            $.post("/walle/start-deploy", {taskId: task_id});
            $('.progress-status').attr('aria-valuenow', 10).width('10%');
            $('.result-failed').hide();
            function getProcess() {
                $.get("/walle/get-process?taskId=" + task_id, function (ret) {
                    data = ret.data;
                    if (0 == data.status) {
                        clearInterval(timer);
                        $('.step-' + data.step).removeClass('text-yellow').addClass('text-red');
                        $('.progress-status').removeClass('progress-bar-success').addClass('progress-bar-danger');
                        $('.error-msg').text(data.memo);
                        $('.result-failed').show();
                        $this.removeClass('disabled');
                        return;
                    } else {
                        $('.progress-status')
                            .removeClass('progress-bar-danger progress-bar-striped')
                            .addClass('progress-bar-success')
                    }
                    if (0 != data.percent) {
                        $('.progress-status').attr('aria-valuenow', data.percent).width(data.percent + '%');
                    }
                    if (100 == data.percent) {
                        $('.progress-status').removeClass('progress-bar-striped').addClass('progress-bar-success');
                        $('.progress-status').parent().removeClass('progress-striped');
                        $('.result-success').show();
                        clearInterval(timer)
                    }
                    for (var i = 1; i <= data.step; i++) {
                        $('.step-' + i).removeClass('text-yellow text-red')
                            .addClass('text-green progress-bar-striped')
                    }
                });
            }
            timer = setInterval(getProcess, 600);
        })
    })

</script>