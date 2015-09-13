<?php
/**
 * @var yii\web\View $this
 */
$this->title = '上线任务列表';
use app\models\Task;
?>
<div class="box">
    <div class="box-header">
<!--        <h3 class="box-title">Responsive Hover Table</h3>-->
        <form action="/walle/index" method="POST">

            <div class="col-xs-12 col-sm-8" style="padding-left: 0;margin-bottom: 10px;">
                <div class="input-group">
                    <input type="text" name="kw" class="form-control search-query" placeholder="上线标题、commit号">
                    <span class="input-group-btn">
                        <button type="submit"
                                class="btn btn-default btn-sm">
                            Search
                            <i class="icon-search icon-on-right bigger-110"></i>
                        </button>
                    </span>
                </div>
            </div>
        </form>
        <a class="btn btn-default btn-sm" href="/walle/submit/">
            <i class="icon-pencil align-top bigger-125"></i>
            创建上线任务
        </a>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-striped table-bordered table-hover">
            <tbody><tr>
                <th>任务名称</th>
                <th>项目</th>
                <th>上线commit号</th>
                <th>当前状态</th>
                <th>操作</th>
            </tr>
            <?php foreach ($list as $item) { ?>
            <tr>
                <td><?= $item['title'] ?></td>
                <td><?= $item['conf']['name'] ?></td>
                <td><?= $item['commit_id'] ?></td>
                <td class="<?= \Yii::t('status', 'task_status_' . $item['status'] . '_color') ?>">
                    <?= \Yii::t('status', 'task_status_' . $item['status']) ?>
                </td>
                <td>
                    <?php if (in_array($item['status'], [Task::STATUS_PASS, Task::STATUS_FAILED])) { ?>
                    <a class="btn btn-xs btn-success task-operation" href="/walle/deploy?taskId=<?= $item['id'] ?>">发起上线</a>
                    <?php } elseif ($item['status'] == Task::STATUS_DONE) { ?>
                        <button class="btn btn-xs btn-warning task-operation task-rollback" data-id="<?= $item['id'] ?>">回滚此次上线</button>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>

            </tbody></table>
    </div><!-- /.box-body -->
</div>
<script>
    $('.task-rollback').click(function(e) {
        $this = $(this);
        $.post('/walle/rollback', {taskId: $this.data('id')}, function(o) {
            if (!o.code) {
                window.location.href=o.data.url;
            } else {
                alert(o.msg);
            }
        })
    })
</script>
