<?php
/**
 * @var yii\web\View $this
 */
$this->title = '项目成员管理';

use yii\widgets\ActiveForm;
use app\models\User;
use app\models\Group;

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
                <a href="javascript:;">
                    <img src="<?= User::AVATAR_ROOT . ($relation['user']['avatar'] ?: 'default.jpg') ?>" alt="Bob Doe's avatar">
                </a>
            </div>

            <div class="body">
                <div class="name">
                    <?php if ($relation['type'] == Group::TYPE_ADMIN) { ?>
                        <i class="icon-user-md light-orange bigger-110" title="审核管理员可审核上线任务"></i>
                    <?php } ?>
                    <?= $relation['user']['realname'] ?>
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
                        <a href="javascript:;" class="bind-admin" data-id="<?= $relation['id'] ?>" data-type="<?= (int)!$relation['type'] ?>" title="审核管理员可审核上线任务">
                            <i class="icon-user-md light-orange bigger-110"></i>
                            <?= $relation['type'] == Group::TYPE_USER ? '设为审核管理员' : '取消审核管理员' ?>
                        </a>
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
        // 组关系成员设为管理员
        $('.bind-admin').click(function(e) {
            $this = $(this);
            var url = '/conf/edit-relation'
                    + '?id=' + $this.data('id')
                    + '&type=' + $this.data('type');
            $.get(url , function(o) {
                if (!o.code) {
                    location.reload()
                } else {
                    alert('设置失败: ' + o.msg);
                }
            })
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