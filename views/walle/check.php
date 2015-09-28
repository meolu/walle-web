<?php
/**
 * @var yii\web\View $this
 */
$this->title = '线上文件指纹';
?>

<div class="box">
    <div class="box-body">
        <div class="form-group">
            <label for="exampleInputEmail1">文件</label>
            <input class="form-control" placeholder="项目的相对地址：backend/web/index.php" type="name" name="file">
        </div>
        <div class="form-group">
            <label>项目</label>
            <select name="project" aria-hidden="true" tabindex="-1" class="form-control select2 select2-hidden-accessible">
                <?php foreach ($projects as $project) { ?>
                    <option value="<?= $project['id'] ?>"><?= $project['name'] ?></option>
                <?php } ?>
            </select>
        </div>
    </div><!-- /.box-body -->

    <div class="box-footer">
        <input type="button" class="btn btn-primary get-md5" value="查询文件md5"><i class="getting icon-spinner icon-spin orange bigger-125" style="display: none"></i>
    </div>
    <br>
    <div class="alert alert-info md5-msg" style="display: none">

    </div>
</div>


<script type="text/javascript">
    $(function() {
        $('.get-md5').click(function() {
            $('.getting').show()
            $.get("/walle/file-md5?projectId=" + $("select[name=project]").val() + "&file=" + $('input[name=file]').val(), function(o) {
                $('.md5-msg').text(o.data).show()
                $('.getting').hide()
            });
        })
    })

</script>