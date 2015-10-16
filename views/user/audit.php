<?php
/**
 * @var yii\web\View $this
 */
$this->title = '审核管理员';
use app\models\User;

?>
<div class="row">
    <div class="row col-sm-4">
        <h4 class="pink">
            <i class="icon-hand-right green"></i>
            <a href="javascript:;" role="button" class="blue" data-toggle="modal"> <span class="green"> 项目管理员审核 </a>
        </h4>
    </div>
</div>
<div class="col-sm-12 hr hr-18 dotted hr-double"></div>

<div class="row col-sm-12 profile-users clearfix" id="relation-users">
    <?php foreach ($apply as $user) { ?>
        <div class="itemdiv memberdiv">
            <div class="inline position-relative">
                <div class="user">
                    <a href="javascript:;">
                        <img src="<?= User::AVATAR_ROOT . ($user['avatar'] ?: 'default.jpg') ?>" alt="Bob Doe's avatar">
                    </a>
                </div>

                <div class="body">
                    <div class="name">
                        <?= $user['realname'] ?>
                        <a href="javascript:void(0)" class="pink remove-relation" data-id="<?= $user['id'] ?>">
                            <i class="icon-trash"></i>
                        </a>
                    </div>
                </div>

                <div class="popover" style="min-width:200px">
                    <div class="arrow"></div>

                    <div class="popover-content">
                        <div class="bolder"><?= $user['email'] ?></div>
                        <div class="hr dotted hr-8"></div>

                        <div class="tools action-buttons">
                            <a href="javascript:;" class="active-admin" data-id="<?= $user['id'] ?>" title="项目管理员可创建项目">
                                <i class="icon-user-md light-orange bigger-110"></i>
                                同意设置为项目管理员
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
                $.get('/user/delete-admin?id=' + $this.data('id'), function(o) {
                    if (!o.code) {
                        $this.closest(".memberdiv").remove();
                    } else {
                        alert('删除失败: ' + o.msg);
                    }
                })
            }
        })
        // 组关系成员设为管理员
        $('.active-admin').click(function(e) {
            $this = $(this);
            var url = '/user/active-admin'
                + '?id=' + $this.data('id');
            $.get(url , function(o) {
                if (!o.code) {
                    alert('审核通过：）')
                    location.reload()
                } else {
                    alert('通过失败: ' + o.msg);
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