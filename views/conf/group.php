<?php
/**
 * @var yii\web\View $this
 */
$this->title = '项目成员管理';

use yii\widgets\ActiveForm;
use app\models\User;

?>
<div class="row">
    <div class="row col-sm-4">
        <h4 class="pink">
            <i class="icon-hand-right green"></i>
            <a href="#modal-form" role="button" class="blue" data-toggle="modal"> <span class="green"><?= $conf->name ?></span>组关系 </a>
        </h4>
    </div>


    <div class="row col-sm-4" style="margin-top: 5px;float:right;">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <div class="input-group">
                <select multiple="" name="user[]" class="width-80 chosen-select" id="form-field-select-4" data-placeholder="通过邮箱查找用户">
                    <option value="">&nbsp;</option>
                    <?php foreach ($users as $info) { ?>
                        <option value="<?= $info['id'] ?>"><?=  $info['email'] ?> - <?= $info['realname'] ?></option>
                    <?php } ?>
                </select>
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-purple btn-sm" style="height: 29px;padding-top: 1px;">
                        添加
                        <i class="icon-search icon-on-right bigger-110"></i>
                    </button>
                </span>
            </div>
        <?php $form->end() ?>
    </div>
</div>

<div class="col-sm-12 hr hr-18 dotted hr-double"></div>

<div class="row col-sm-12 profile-users clearfix" id="relation-users">
    <?php foreach ($group as $relation) { ?>
    <div class="itemdiv memberdiv">
        <div class="inline position-relative">
            <div class="user">
                <a href="#">
                    <img src="<?= User::AVATAR_ROOT . ($relation['user']['avatar'] ?: 'default.jpg') ?>" alt="Bob Doe's avatar">
                </a>
            </div>

            <div class="body">
                <div class="name">
                    <a href="#"><span class="user-status status-online"></span>
                        <?= $relation['user']['realname'] ?>
                    </a>
                    <a href="javascript:void(0)" class="pink remove-relation" data-id="<?= $relation['id'] ?>">
                        <i class="icon-trash"></i>
                    </a>
                </div>
            </div>

            <div class="popover" style="min-width:200px">
                <div class="arrow"></div>

                <div class="popover-content">
                    <div class="bolder"><?= $relation['user']['email'] ?></div>
                    <div class="hr dotted hr-8"></div>
                    <div class="tools action-buttons">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

</div>

<script>
    jQuery(function($) {
        // 组关系删除
        $('.remove-relation').click(function(e) {
            console.log('xxx')
            $this = $(this);
            if (confirm('确定要删除该记录？')) {
                $.get('/conf/delete-relation?id=' + $this.data('id'), function(o) {
                    if (!o.code) {
                        $this.closest(".memberdiv").remove();
                    } else {
                        alert('删除失败: ' + o.msg);
                    }
                })
            }
        })
        // 浮出层
        $('#relation-users .memberdiv').on('mouseenter', function(){
            var $this = $(this);
            var place = 'right';

            $this.find('.popover').removeClass('right left').addClass(place);
        }).on('click', function() {
            return false;
        });

    })
</script>