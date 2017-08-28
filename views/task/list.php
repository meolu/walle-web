<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('task', 'list title');
use \app\models\Task;
use yii\widgets\LinkPager;
use yii\helpers\Url;

?>
<div class="box">
    <div class="page-header">
        <form action="/task/" method="POST">
            <input type="hidden" value="<?= \Yii::$app->request->getCsrfToken(); ?>" name="_csrf">
            <div class="col-xs-2 col-sm-2">
                <div class="form-group">
                    <select name="project_id" class="form-control">
                        <option value="0"><?= yii::t('task', 'list project') ?></option>
                        <?php foreach ($projects as $project) { ?>
                            <option value="<?= $project['id'] ?>"<?= ($projectId == $project['id'] ? ' selected' : '') ?>><?= $project['name'] ?>
                                - <?= \Yii::t('w', 'conf_level_' . $project['level']) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-sm-8" style="padding-left: 0;margin-bottom: 10px;">
                <div class="input-group">
                    <input type="text" name="kw" class="form-control search-query"
                           placeholder="<?= yii::t('task', 'list placeholder') ?>" value="<?= $kw ?>">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default btn-sm">
                            Search
                            <i class="icon-search icon-on-right bigger-110"></i>
                        </button>
                    </span>
                </div>
            </div>
        </form>
        <a class="btn btn-default btn-sm" href="<?= Url::to('@web/task/submit/') ?>">
            <i class="icon-pencil align-top bigger-125"></i>
            <?= yii::t('task', 'create task') ?>
        </a>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding clearfix">
        <table class="table table-striped table-bordered table-hover">
            <tbody>
            <tr>
                <?php if ($audit) { ?>
                    <th><?= yii::t('task', 'l_user') ?></th>
                <?php } ?>
                <th><?= yii::t('task', 'l_project') ?></th>
                <th><?= yii::t('task', 'l_title') ?></th>
                <th><?= yii::t('task', 'l_time') ?></th>
                <th><?= yii::t('task', 'l_branch') ?></th>
                <th><?= yii::t('task', 'l_commit') ?></th>
                <th><?= yii::t('task', 'l_status') ?></th>
                <th><?= yii::t('task', 'l_opera') ?></th>
            </tr>
            <?php foreach ($list as $item) { ?>
                <tr>
                    <?php if ($audit) { ?>
                        <td><?= $item['user']['realname'] ?></td>
                    <?php } ?>
                    <td><?= $item['project']['name'] ?> - <?= \Yii::t('w',
                            'conf_level_' . $item['project']['level']) ?></td>
                    <td><?= $item['title'] ?></td>
                    <td><?= $item['updated_at'] ?></td>
                    <td><?= $item['branch'] ?></td>
                    <td><?= $item['commit_id'] ?></td>
                    <td class="<?= \Yii::t('w', 'task_status_' . $item['status'] . '_color') ?>">
                        <?= \Yii::t('w', 'task_status_' . $item['status']) ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($audit && !in_array($item['status'],
                                    [Task::STATUS_DONE, Task::STATUS_FAILED])
                            ) { ?>
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
                                    <a href="<?= Url::to("@web/walle/deploy?taskId={$item['id']}") ?>" class="green">
                                        <i class="icon-cloud-upload text-success bigger-130"
                                           data-id="<?= $item['id'] ?>"></i>
                                        <?= yii::t('task', 'deploy') ?>
                                    </a>
                                <?php } ?>
                                <!-- 回滚的任务不能再回滚-->
                                <?php if ($item['status'] == Task::STATUS_DONE && $item['enable_rollback'] == Task::ROLLBACK_TRUE) { ?>
                                    <a href="javascript:;" class="brown task-rollback" data-id="<?= $item['id'] ?>">
                                        <i class="icon-reply bigger-130"></i>
                                        <?= yii::t('task', 'rollback') ?>
                                    </a>
                                <?php } ?>
                                <?php if ($item['status'] != Task::STATUS_DONE) { ?>
                                    <a class="red btn-delete" href="javascript:;" data-id="<?= $item['id'] ?>">
                                        <i class="icon-trash bigger-130"></i>
                                        <?= yii::t('task', 'delete') ?>
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
            $.get("<?= Url::to('@web/task/task-operation') ?>", {
                    id: $this.data('id'),
                    operation: $this.is(':checked') ? 1 : 0
                },
                function(data) {
                    if(data.code == 0) {
                        window.location.reload();
                    } else {
                        alert(data.msg);
                    }
                }
            );
        })
        // 回滚任务
        $('.task-rollback').click(function(e) {
            $this = $(this);
            $.get('<?= Url::to('@web/task/rollback?taskId=') ?>' + $this.data('id'), function(o) {
                if(!o.code) {
                    window.location.href = o.data.url;
                } else {
                    alert(o.msg);
                }
            })
        })
        // 垃圾任务删除
        $('.btn-delete').click(function(e) {
            $this = $(this);
            if(confirm('<?= yii::t('w', 'js delete confirm') ?>')) {
                $.get('<?= Url::to('@web/task/delete') ?>', {taskId: $this.data('id')}, function(o) {
                    if(!o.code) {
                        $this.closest("tr").remove();
                    } else {
                        alert('<?= yii::t('task', 'js delete failed') ?>' + o.msg);
                    }
                })
            }
        })
    })
</script>