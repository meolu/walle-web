<?php
/**
 * @var yii\web\View $this
 */
$this->title = '项目配置';
?>
<div class="box">
    <div class="box-header">
        <form action="/conf/" method="POST">
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
        <a class="btn btn-default btn-sm" href="/conf/edit">
            <i class="icon-pencil align-top bigger-125"></i>
            新建项目
        </a>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding clearfix">
        <table class="table table-striped table-bordered table-hover">
            <tbody><tr>
                <th>项目名称</th>
                <th>环境</th>
                <th>上线方式</th>
                <th>是否需要审核</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
            <?php foreach ($list as $item) { ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= \Yii::t('status', 'conf_level_' . $item['level']) ?></td>
                    <td><?= $item['repo_mode'] ?></td>
                    <td><?= $item['audit'] ? '是' : '否' ?></td>
                    <td><?= \Yii::t('status', 'conf_status_' . $item['status']) ?></td>
                    <td class="<?= \Yii::t('status', 'conf_status_' . $item['status'] . '_color') ?>">
                        <div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
                            <a href="/conf/preview/?projectId=<?= $item['id'] ?>" data-toggle="modal" data-target="#myModal">
                                <i class="icon-zoom-in bigger-130"></i>查看
                            </a>
                            <a href="/conf/detection/?projectId=<?= $item['id'] ?>" data-toggle="modal" data-target="#myModal">
                                <i class="icon-screenshot bigger-130"></i>检测
                            </a>
                            <a class="btn-copy" href="javascript:;" data-id="<?= $item['id'] ?>">
                                <i class="icon-copy bigger-130"></i>复制
                            </a>
                            <a href="/conf/group/?projectId=<?= $item['id'] ?>">
                                <i class="icon-group bigger-130"></i>成员
                            </a>
                            <a href="/conf/edit?projectId=<?= $item['id'] ?>">
                                <i class="icon-pencil bigger-130"></i>修改
                            </a>
                            <a class="red btn-delete" data-id="<?= $item['id'] ?>" href="javascript:;">
                                <i class="icon-trash bigger-130"></i>删除
                            </a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 模态框（Modal） -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        </div>

    </div><!-- /.box-body -->
</div>

<script>
    jQuery(function($) {
        $('.btn-delete').click(function(e) {
            $this = $(this);
            if (confirm('确定要删除该项目？')) {
                $.get('/conf/delete', {projectId: $this.data('id')}, function(o) {
                    if (!o.code) {
                        $this.closest("tr").remove();
                    } else {
                        alert('删除失败: ' + o.msg);
                    }
                })
            }
        })
        $('.btn-copy').click(function(e) {
            $this = $(this);
            if (confirm('确定要复制该项目？')) {
                $.get('/conf/copy', {projectId: $this.data('id')}, function(o) {
                    if (!o.code) {
                        location.reload();
                    } else {
                        alert('复制失败: ' + o.msg);
                    }
                })
            }
        })
        $("#myModal").on("hidden.bs.modal", function() {
            $(this).removeData("bs.modal");
        });
    });
</script>