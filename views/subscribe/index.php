<?php
/**
 * @var yii\web\View $this
 */
$this->title = 'My Yii Application';
?>
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">您订阅的日志</h3>
    </div><!-- /.box-header -->
    <div class="box-body">
        <table class="table table-bordered">
            <tbody><tr>
                <th>topic</th>
                <th>计数</th>
                <th>操作</th>
            </tr>
            <?php foreach ($detail as $name => $row) {?>
                <tr>
                    <td><?= $name ?></td>
                    <td><span class="badge bg-red"><?= $row['error'] ?></span>
                        <span class="badge bg-yellow"><?= $row['warning'] ?></span>
                        <span class="badge bg-light-blue"><?= $row['notice'] ?></span>
                    </td>
                    <td><span class="badge bg-red">55%</span></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div><!-- /.box-body -->
    <!--    <div class="box-footer clearfix">-->
    <!--        <ul class="pagination pagination-sm no-margin pull-right">-->
    <!--            <li><a href="#">«</a></li>-->
    <!--            <li><a href="#">1</a></li>-->
    <!--            <li><a href="#">2</a></li>-->
    <!--            <li><a href="#">3</a></li>-->
    <!--            <li><a href="#">»</a></li>-->
    <!--        </ul>-->
    <!--    </div>-->
</div><!-- /.box -->


