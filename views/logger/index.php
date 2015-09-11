<?php
/**
 * @var yii\web\View $this
 */
$this->title = 'My Yii Application';
?>
<div class="box">
<div class="box-header">
  <h3 class="box-title">Responsive Hover Table</h3>
  <form action="/logger/search" method="POST">
  <div class="box-tools">
    <div class="input-group" style="width: 150px;">
    <input type="text" name="kw" class="form-control input-sm pull-right" placeholder="Search" value="social">
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
      <th>错误域</th>
      <th>日志细节</th>
      <th>时间</th>
<!--      <th>debug</th>-->
      <th>级别</th>
    </tr>
    <?php foreach ($rows as $row) { ?>
    <tr>
    <td><?= $row['name'] ?></td>
    <td class="click-show-traces" data-id="<?= $row['_id'] ?>">
        <div class="box box-default-clear collapsed-box">
            <div class="box-header with-border">
                <span class="box-title f14"><?= $row['log'] ?></span>
                <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                </div><!-- /.box-tools -->
            </div><!-- /.box-header -->
            <div class="box-body" style="display: none;">
                <?= join('<br>', (array)json_decode($row['trace'])) ?>
            </div>
        </div>
    </td>
    <td><?= $row['time'] ?></td>
<!--    <td>--><?//= json_decode($row['trace'])[0] ?><!--</td>-->
      <td><span class="label <?= \Yii::t('log', 'class-' . $row['level']) ?>"><?= \Yii::t('log', $row['level']) ?></span></td>
    </tr>
    <?php } ?>
  </tbody></table>
</div><!-- /.box-body -->
</div>

