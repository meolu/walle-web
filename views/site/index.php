<?php
/**
 * @var yii\web\View $this
 */
$this->title = 'My Yii Application';
?>
<div class="box">
<div class="box-header">
  <h3 class="box-title">Responsive Hover Table</h3>
  <form action="/log/search" method="POST">
  <div class="box-tools">
    <div class="input-group" style="width: 150px;">
    <input type="text" name="kw" class="form-control input-sm pull-right" placeholder="Search">
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
      <th>ID</th>
      <th>User</th>
      <th>Date</th>
      <th>Status</th>
      <th>Reason</th>
    </tr>
    <tr>
      <td>183</td>
      <td>John Doe</td>
      <td>11-7-2014</td>
      <td><span class="label label-success">Approved</span></td>
      <td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
    </tr>
    <tr>
      <td>219</td>
      <td>Alexander Pierce</td>
      <td>11-7-2014</td>
      <td><span class="label label-warning">Pending</span></td>
      <td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
    </tr>
    <tr>
      <td>657</td>
      <td>Bob Doe</td>
      <td>11-7-2014</td>
      <td><span class="label label-primary">Approved</span></td>
      <td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
    </tr>
    <tr>
      <td>175</td>
      <td>Mike Doe</td>
      <td>11-7-2014</td>
      <td><span class="label label-danger">Denied</span></td>
      <td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
    </tr>
  </tbody></table>
</div><!-- /.box-body -->
</div>
