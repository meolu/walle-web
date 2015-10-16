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
        width: 14%;
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
        <a class="btn btn-success btn-return" href="/task/index">返回</a></h4>

    <div class="status">
        <span><i class="fa fa-circle-o text-yellow step-1"></i>权限、目录检查</span>
        <span><i class="fa fa-circle-o text-yellow step-2"></i>pre-deploy任务</span>
        <span><i class="fa fa-circle-o text-yellow step-3"></i>代码检出</span>
        <span><i class="fa fa-circle-o text-yellow step-4"></i>post-deploy任务</span>
        <span><i class="fa fa-circle-o text-yellow step-5"></i>同步至服务器</span>
        <span style="width: 28%"><i class="fa fa-circle-o text-yellow step-6"></i>全量更新(pre-release、更新版本、post-release)</span>
    </div>
    <div style="clear:both"></div>
    <div class="progress progress-small progress-striped active">
        <div class="progress-bar progress-status progress-bar-success" style="width: <?= $task->status == Task::STATUS_DONE ? 100 : 0 ?>%;"></div>
    </div>

    <div class="alert alert-block alert-success result-success" style="<?= $task->status != Task::STATUS_DONE ? 'display: none' : '' ?>">
        <h4><i class="icon-thumbs-up"></i>上线成功!</h4>
        <p>辛苦了，小主：）</p>

    </div>

    <div class="alert alert-block alert-danger result-failed" style="display: none">
        <h4><i class="icon-bell-alt"></i>上线出错:（</h4>
        <span class="error-msg">
        </span>
        <br><br>
        <i class="icon-bullhorn"></i><span>请联系SA或者重新部署</span>
    </div>

</div>

<script type="text/javascript">
    $(function() {
        $('.btn-deploy').click(function() {
            $this = $(this);
            $this.addClass('disabled');
            var task_id = $(this).data('id');
            var action = '';
            var detail = '';
            $.post("/walle/start-deploy", {taskId: task_id}, function(o) {
                action = o.code ? o.msg + ':' : '';
                $('.error-msg').text(action + detail);
            });
            $('.progress-status').attr('aria-valuenow', 10).width('5%');
            $('.result-failed').hide();
            function getProcess() {
                $.get("/walle/get-process?taskId=" + task_id, function (o) {
                    data = o.data;
                    // 执行失败
                    if (0 == data.status) {
                        clearInterval(timer);
                        $('.step-' + data.step).removeClass('text-yellow').addClass('text-red');
                        $('.progress-status').removeClass('progress-bar-success').addClass('progress-bar-danger');
                        $('.error-msg').text(o.msg + ':' + data.memo);
                        detail = data.memo + '<br>' + data.command;
                        $('.error-msg').html(action + detail);
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