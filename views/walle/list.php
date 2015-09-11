<?php
/**
 * @var yii\web\View $this
 */
$this->title = '上线任务列表';
?>
<div class="box">
    <div class="box-header">
<!--        <h3 class="box-title">Responsive Hover Table</h3>-->
        <form action="/logger/search" method="POST">
            <div class="box-tools">
                <div class="input-group" style="width: 150px;">
                    <input type="text" name="kw" class="form-control input-sm pull-right" placeholder="Search" value="">
                    <div class="input-group-btn">
                        <input class="btn btn-sm btn-default" type="submit" value="搜索"><i class="fa fa-search"></i></input>
                    </div>
                </div>
            </div>
        </form>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover">
            <tbody><tr>
                <th>开发者</th>
                <th>任务名称</th>
                <th>上线commit号</th>
                <th>当前状态</th>
                <th>操作</th>
            </tr>
            <?php foreach ($list as $item) { ?>
            <tr>
                <td><?= $item['username'] ?></td>
                <td><?= $item['title'] ?></td>
                <td><?= $item['commit_id'] ?></td>
                <td><?= \Yii::t('status', 'task_status_' . $item['status']) ?></td>
                <td>
                    <button class="btn btn-xs btn-success task-operation" data-id="<?= $item['id'] ?>" data-action="pass">通过</button>
                    <button class="btn btn-xs btn-danger task-operation" data-id="<?= $item['id'] ?>" data-action="refuse">拒绝</button>
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
                }
            );
        })
    })
</script>