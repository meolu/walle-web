<?php
/**
 * @var yii\web\View $this
 */
$this->title = '上线任务列表';
use \app\models\Task;
?>
<div class="box">
    <div class="box-header">
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
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <tbody><tr>
                <th>开发者</th>
                <th>任务名称</th>
                <th>项目</th>
                <th>上线commit号</th>
                <th>当前状态</th>
                <th>操作</th>
            </tr>
            <?php foreach ($list as $item) { ?>
            <tr>
                <td><?= $item['user']['realname'] ?></td>
                <td><?= $item['title'] ?></td>
                <td><?= $item['conf']['name'] ?></td>
                <td><?= $item['commit_id'] ?></td>
                <td><?= \Yii::t('status', 'task_status_' . $item['status']) ?></td>
                <td class="<?= \Yii::t('status', 'task_status_' . $item['status'] . '_color') ?>">
                    <?php if ($item['user_id'] == \Yii::$app->user->id) { ?>
                    <a class="btn btn-xs btn-success task-operation <?= $item['status'] == Task::STATUS_PASS ? '' : 'disabled' ?> " href="/walle/deploy?taskId=<?= $item['id'] ?>">发起上线</a>
                    <?php } ?>
                    <?php if (!in_array($item['status'],[Task::STATUS_DONE, Task::STATUS_FAILED])) { ?>
                        <button class="btn btn-xs btn-success task-operation" data-id="<?= $item['id'] ?>" data-action="pass">通过</button>
                        <button class="btn btn-xs btn-danger task-operation" data-id="<?= $item['id'] ?>" data-action="refuse">拒绝</button>
                    <?php } else { ?>
                        <?= \Yii::t('status', 'task_status_' . $item['status']) ?>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>

            </tbody></table>
    </div><!-- /.box-body -->
</div>

<script type="text/javascript">
    $(function() {
        $('.task-operation').click(function() {
            $this = $(this);
            $.get("/walle/task-operation", {id: $(this).data('id'), operation: $(this).data('action')},
                function(data) {
                    $this.parent().prev().text(data.data.status);
                    $this.addClass('disabled')
                    if ($this.data('action') == 'pass') {
                        $this.parent().children('a').removeClass('disabled')
                    } else {
                        console.log($this.data('action'));
                        $this.parent().children('a').addClass('disabled')
                        console.log($this.parent().children('a'))

                    }
                    $this.siblings().removeClass('disabled');
                }
            );
        })
    })
</script>