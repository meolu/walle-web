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
    
  </tbody></table>
</div><!-- /.box-body -->
</div>

