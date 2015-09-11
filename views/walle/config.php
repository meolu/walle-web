<?php
/**
 * @var yii\web\View $this
 */
$this->title = '项目配置';
?>
<div class="box">
    <div class="box-header">
        <!--        <h3 class="box-title">Responsive Hover Table</h3>-->
        <form action="/walle/config" method="POST">

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
        <a class="btn btn-default btn-sm" href="/walle/config-edit">
            <i class="icon-pencil align-top bigger-125"></i>
            新建项目
        </a>
    </div><!-- /.box-header -->
    <div class="box-body table-responsive no-padding">
        <table class="table table-striped table-bordered table-hover">
            <tbody><tr>
                <th>项目名称</th>
                <th>项目配置名</th>
                <th>环境</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
            <?php foreach ($list as $item) { ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= $item['conf'] ?></td>
                    <td><?= \Yii::t('status', 'conf_level_' . $item['level']) ?></td>
                    <td><?= \Yii::t('status', 'conf_status_' . $item['status']) ?></td>
                    <td class="<?= \Yii::t('status', 'conf_status_' . $item['status'] . '_color') ?>">

                        <a class="btn btn-xs btn-success" href="/walle/config-edit?projectId=<?= $item['id'] ?>">
                            <i class="icon-edit bigger-120"></i>修改
                        </a>
                    </td>
                </tr>
            <?php } ?>

            </tbody></table>
    </div><!-- /.box-body -->
</div>
