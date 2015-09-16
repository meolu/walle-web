<?php
/**
 * @var yii\web\View $this
 */
$this->title = '发起上线';
?>
<div class="box">
    <form role="form" method="POST" action="/walle/submit?projectId=<?= (int)$_GET['projectId'] ?>">
      <div class="box-body">
        <div class="form-group">
          <label for="exampleInputEmail1">任务标题</label>
          <input class="form-control" id="exampleInputEmail1" placeholder="上线标题" type="name" name="title">
        </div>
        <div class="form-group">
	        <label>版本选取 <i class="get-history icon-spinner icon-spin orange bigger-125"></i></label>
	        <select name="commit" aria-hidden="true" tabindex="-1" id="commit_history" class="form-control select2 select2-hidden-accessible">
	        </select>
	      </div>
      </div><!-- /.box-body -->

      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="提交">
      </div>
    </form>
</div>

<script type="text/javascript">
	$(function() {
		$.get("/walle/get-commit-history?projectId=" + <?= (int)$_GET['projectId'] ?>, function(data) {
			var select = '';
			$.each(data.data, function (key, value) {
				select += '<option value="' + value.id + '">' + value.message +'</option>';
			})
			$('#commit_history').append(select);
            $('.get-history').hide()
		});
		
	})

</script>