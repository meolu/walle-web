<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('conf', 'index');
use yii\helpers\Url;
?>
<div class="box">
    <div class="page-header">
        <form action="<?= Url::to('@web/conf') ?>" method="POST">
            <input type="hidden" value="<?= \Yii::$app->request->getCsrfToken(); ?>" name="_csrf">
            <div class="col-xs-12 col-sm-8" style="padding-left: 0;margin-bottom: 10px;">
                <div class="input-group">
                    <input type="text" name="kw" class="form-control search-query" placeholder="<?= yii::t('conf', 'index search placeholder') ?>">
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
        <a class="btn btn-default btn-sm" href="<?= Url::to('@web/conf/edit') ?>">
            <i class="icon-pencil align-top bigger-125"></i>
            <?= yii::t('conf', 'create project') ?>
        </a>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding clearfix">
        <table class="table table-striped table-bordered table-hover">
            <tbody><tr>
                <th><?= yii::t('conf', 'p_name') ?></th>
                <th><?= yii::t('conf', 'p_env') ?></th>
                <th><?= yii::t('conf', 'p_mode') ?></th>
                <th><?= yii::t('conf', 'p_audit') ?></th>
                <th><?= yii::t('conf', 'p_status') ?></th>
                <th><?= yii::t('conf', 'p_opera') ?></th>
            </tr>
            <?php foreach ($list as $item) { ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= \Yii::t('w', 'conf_level_' . $item['level']) ?></td>
                    <td><?= $item['repo_mode'] ?></td>
                    <td><?= \Yii::t('w', 'conf_audit_' . $item['audit']) ?></td>
                    <td><?= \Yii::t('w', 'conf_status_' . $item['status']) ?></td>
                    <td class="<?= \Yii::t('w', 'conf_status_' . $item['status'] . '_color') ?>">
                        <div class="action-buttons">
                            <a href="<?= Url::to("@web/conf/preview/?projectId={$item['id']}") ?>" data-toggle="modal" class="viewmodal_hook" data-target="#viewModal">
                                <i class="icon-zoom-in bigger-130"></i>
                                <?= yii::t('conf', 'p_preview') ?>
                            </a>
                            <a href="<?= Url::to("@web/conf/detection/?projectId={$item['id']}") ?>" data-toggle="modal" class="viewmodal_hook"  data-target="#viewModal">
                                <i class="icon-screenshot bigger-130"></i>
                                <?= yii::t('conf', 'p_detection') ?>
                            </a>
                            <a class="btn-copy" href="javascript:;" data-id="<?= $item['id'] ?>">
                                <i class="icon-copy bigger-130"></i>
                                <?= yii::t('conf', 'p_copy') ?>
                            </a>
                            <a href="<?= Url::to("@web/conf/group/?projectId={$item['id']}") ?>">
                                <i class="icon-group bigger-130"></i>
                                <?= yii::t('conf', 'p_member') ?>
                            </a>
                            <a href="<?= Url::to("@web/conf/edit?projectId={$item['id']}") ?>">
                                <i class="icon-pencil bigger-130"></i>
                                <?= yii::t('conf', 'p_edit') ?>
                            </a>
                            <a class="red btn-delete" data-id="<?= $item['id'] ?>" href="javascript:;">
                                <i class="icon-trash bigger-130"></i>
                                <?= yii::t('conf', 'p_delete') ?>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 模态框（Modal） -->
        <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
				</div><!-- /.modal-content -->
			</div><!-- /.modal -->
		</div>

    </div><!-- /.box-body -->
</div>

<script>
    jQuery(function($) {
        $('.btn-delete').click(function(e) {
            $this = $(this);
            if (confirm('<?= yii::t('conf', 'js delete project') ?>')) {
                $.get('<?= Url::to('@web/conf/delete') ?>', {projectId: $this.data('id')}, function(o) {
                    if (!o.code) {
                        $this.closest("tr").remove();
                    } else {
                        alert('<?= yii::t('w', 'js delete failed') ?>' + o.msg);
                    }
                })
            }
        })
        $('.btn-copy').click(function(e) {
            $this = $(this);
            if (confirm('<?= yii::t('conf', 'js copy project confirm') ?>')) {
                $.get('<?= Url::to('@web/conf/copy') ?>', {projectId: $this.data('id')}, function(o) {
                    if (!o.code) {
                        location.reload();
                    } else {
                        alert('<?= yii::t('conf', 'js copy failed') ?>' + o.msg);
                    }
                })
            }
        })
        $("#viewModal").on("hidden.bs.modal", function() {
            $(this).removeData("bs.modal");
        });
       
    });
</script>
