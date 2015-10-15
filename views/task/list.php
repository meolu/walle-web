<?php
/**
 * @var yii\web\View $this
 */
$this->title = '上线任务列表';
use \app\models\Task;
use yii\widgets\LinkPager;

?>
<div class="box">
    <div class="box-header">
        <form action="/task/" method="POST">
            <input type="hidden" value="<?= \Yii::$app->request->getCsrfToken(); ?>" name="_csrf">
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
        <a class="btn btn-default btn-sm" href="/task/submit/">
            <i class="icon-pencil align-top bigger-125"></i>
            创建上线任务
        </a>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding clearfix">
        <table class="table table-striped table-bordered table-hover">
            <tbody><tr>
                <?php if ($audit) { ?>
                <th>开发者</th>
                <?php } ?>
                <th>项目</th>
                <th>任务名称</th>
                <th>上线时间</th>
                <th>分支</th>
                <th>上线commit号</th>
                <th>当前状态</th>
                <th>操作</th>
            </tr>
            <?php foreach ($list as $item) { ?>
            <tr>
                <?php if ($audit) { ?>
                <td><?= $item['user']['realname'] ?></td>
                <?php } ?>
                <td><?= $item['project']['name'] ?></td>
                <td><?= $item['title'] ?></td>
                <td><?= $item['updated_at'] ?></td>
                <td><?= $item['branch'] ?></td>
                <td><?= $item['commit_id'] ?></td>
                <td class="<?= \Yii::t('status', 'task_status_' . $item['status'] . '_color') ?>">
                    <?= \Yii::t('status', 'task_status_' . $item['status']) ?></td>
                <td>
                    <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                    <?php if ($audit && !in_array($item['status'],[Task::STATUS_DONE, Task::STATUS_FAILED])) { ?>
                        <label>
                            <input class="ace ace-switch ace-switch-5 task-operation"
                                <?= $item['status'] == Task::STATUS_PASS ? 'checked' : '' ?>
                                   type="checkbox" data-id="<?= $item['id'] ?>">
                            <span class="lbl"></span>
                        </label>
                    <?php } ?>
                    <?php if ($item['user_id'] == \Yii::$app->user->id) { ?>
                        <!-- 通过审核可以上线的任务-->
                        <?php if (Task::canDeploy($item['status'])) { ?>
                            <a href="/walle/deploy?taskId=<?= $item['id'] ?>" class="green">
                                <i class="icon-cloud-upload text-success bigger-130" data-id="<?= $item['id'] ?>"></i>上线
                            </a>
                        <?php } ?>
                        <!-- 回滚的任务不能再回滚-->
                        <?php if ($item['status'] == Task::STATUS_DONE && $item['action'] == Task::ACTION_ONLINE) { ?>
                            <a href="javascript:;" class="brown">
                                <i class="icon-reply bigger-130 task-rollback" data-id="<?= $item['id'] ?>"></i>回滚
                            </a>
                        <?php } ?>
                        <?php if ($item['status'] != Task::STATUS_DONE) { ?>
                            <a class="red btn-delete" href="javascript:;" data-id="<?= $item['id'] ?>">
                                <i class="icon-trash bigger-130"></i>删除
                            </a>
                        <?php } ?>
                    <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>

            </tbody>
        </table>
    </div><!-- /.box-body -->
    <?= LinkPager::widget(['pagination' => $pages]); ?>
</div>

<script type="text/javascript">
    $(function() {
        // 发起上线
        $('.task-operation').click(function() {
            $this = $(this);
            $.get("/task/task-operation", {id: $this.data('id'), operation: $this.is(':checked') ? 1 : 0},
                function(data) {
                    if (data.code == 0) {
                        $this.closest('td').prev().text(data.data.status);
                    } else {
                        alert(data.msg);
                    }
                }
            );
        })
        // 回滚任务
        $('.task-rollback').click(function(e) {
            $this = $(this);
            $.get('/task/rollback?taskId=' + $this.data('id'), function(o) {
                if (!o.code) {
                    window.location.href=o.data.url;
                } else {
                    alert(o.msg);
                }
            })
        })
        // 垃圾任务删除
        $('.btn-delete').click(function(e) {
            $this = $(this);
            if (confirm('确定要删除该记录？')) {
                $.get('/task/delete', {taskId: $this.data('id')}, function(o) {
                    if (!o.code) {
                        $this.closest("tr").remove();
                    } else {
                        alert('删除失败: ' + o.msg);
                    }
                })
            }
        })
    })
</script>