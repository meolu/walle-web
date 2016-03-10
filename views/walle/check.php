<?php
/**
 * @var yii\web\View $this
 */
use yii\helpers\Url;

$this->title = yii::t('walle', 'md5 title');
?>

<div class="box">
    <div class="box-body">
        <div class="form-group">
            <label for="exampleInputEmail1"><?= yii::t('walle', 'file') ?></label>
            <input class="form-control" placeholder="<?= yii::t('walle', 'file placeholder') ?>" type="name" name="file">
        </div>
        <div class="form-group">
            <label><?= yii::t('walle', 'project') ?></label>
            <select name="project" aria-hidden="true" tabindex="-1" class="form-control select2 select2-hidden-accessible">
                <?php foreach ($projects as $project) { ?>
                    <option value="<?= $project['id'] ?>"><?= $project['name'] ?> - <?= \Yii::t('w', 'conf_level_' . $project['level']) ?></option>
                <?php } ?>
            </select>
        </div>
    </div><!-- /.box-body -->

    <div class="box-footer">
        <input type="button" class="btn btn-primary get-md5" value="<?= yii::t('walle', 'file md5') ?>"><i class="getting icon-spinner icon-spin orange bigger-125" style="display: none"></i>
    </div>
    <br>
    <div class="alert alert-info md5-msg" style="display: none">

    </div>
</div>


<script type="text/javascript">
    $(function() {
        $('.get-md5').click(function() {
            $('.getting').show()
            $.get("<?= Url::to('@web/walle/file-md5?projectId=') ?>" + $("select[name=project]").val() + "&file=" + $('input[name=file]').val(), function(o) {
                $('.md5-msg').html(o.data).show()
                $('.getting').hide()
            });
        })
    })

</script>
