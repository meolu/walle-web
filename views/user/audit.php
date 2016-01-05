<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('user', 'audit title');
use app\models\User;
use yii\helpers\Url;

?>
<div class="row">
    <div class="row col-sm-4">
        <h4 class="pink">
            <i class="icon-hand-right green"></i>
            <a href="javascript:;" role="button" class="blue" data-toggle="modal"> <span class="green"> <?= yii::t('user', 'projecter audit') ?> </a>
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
                        <img src="<?= Url::to('@web' . User::AVATAR_ROOT) .($user['avatar'] ?: 'default.jpg') ?>">
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
                            <a href="javascript:;" class="active-admin" data-id="<?= $user['id'] ?>" title="<?= yii::t('user', 'projecter audit tip') ?>">
                                <i class="icon-user-md light-orange bigger-110"></i>
                                <?= yii::t('user', 'pass projecter audit') ?>
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
            if (confirm('<?= yii::t('w', 'js delete confirm') ?>')) {
                $.get(' <?= Url::to('@web/user/delete-admin?id=') ?>' + $this.data('id'), function(o) {
                    if (!o.code) {
                        $this.closest(".memberdiv").remove();
                    } else {
                        alert('<?= yii::t('w', 'js delete failed') ?>' + o.msg);
                    }
                })
            }
        })
        // 组关系成员设为管理员
        $('.active-admin').click(function(e) {
            $this = $(this);
            var url = '<?= Url::to('@web/user/active-admin') ?>'
                + '?id=' + $this.data('id');
            $.get(url , function(o) {
                if (!o.code) {
                    alert('<?= yii::t('user', 'js pass') ?>')
                    location.reload()
                } else {
                    alert('<?= yii::t('user', 'js pass failed') ?> ' + o.msg);
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