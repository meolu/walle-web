<?php
/**
 * @var yii\web\View $this
 */
$this->title = '配置项目';
use app\models\Conf;
?>
<div class="box">
    <form role="form" method="POST" action="/walle/config-edit?projectId=<?= !isset($_GET['projectId']) ? '' : (int)$_GET['projectId'] ?>">
      <div class="box-body">
        <div class="form-group">
          <label for="exampleInputEmail1">项目名字</label>
          <input class="form-control" id="exampleInputEmail1" placeholder="瓦力" type="name" name="name" value="<?= $conf->name ?>">
        </div>
      <div class="form-group">
          <label for="exampleInputEmail1">项目配置英文名</label>
          <input class="form-control" id="exampleInputEmail1" placeholder="walle" type="name" name="conf" value="<?= $conf->conf ?>">
      </div>
        <div class="form-group">
	        <label>项目环境级别</label>
	        <select name="level" aria-hidden="true" tabindex="-1" id="commit_history" class="form-control select2 select2-hidden-accessible">
	            <option value="<?= Conf::LEVEL_TEST ?>" selected="<?= $conf->level == Conf::LEVEL_TEST ? true : false ?>">测试环境</option>
                <option value="<?= Conf::LEVEL_SIMU ?>" selected="<?= $conf->level == Conf::LEVEL_SIMU ? true : false ?>">模拟线上环境</option>
                <option value="<?= Conf::LEVEL_PROD ?>" selected="<?= $conf->level == Conf::LEVEL_PROD ? true : false ?>">线上环境</option>
            </select>
	      </div>
      </div><!-- /.box-body -->

        <div class="form-group">
            <label for="exampleInputEmail1">配置</label>
            <textarea class="form-control" type="name" name="context" style="width: 800px;height: 600px" value=""><?= $conf->context ?>
            </textarea>
        </div>
      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="提交">
      </div>
    </form>
</div>
