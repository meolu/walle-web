<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('user', 'users');
use yii\helpers\Url;
use yii\widgets\LinkPager;
use app\models\User;
?>
<div class="box">
    <div class="box-header">
        <form action="<?= Url::to('@web/user/list') ?>" method="POST">
            <input type="hidden" value="<?= \Yii::$app->request->getCsrfToken(); ?>" name="_csrf">
            <div class="col-xs-12 col-sm-8" style="padding-left: 0;margin-bottom: 10px;">
                <div class="input-group">
                    <input type="text" name="kw" class="form-control search-query" placeholder="<?= yii::t('user', 'search placeholder') ?>">
                    <span class="input-group-btn">
                        <button type="submit"
                                class="btn btn-default btn-sm">
                            Search
                            <i class="icon-search icon-on-right bigger-110"></i>
                        </button>
                    </span>
                </div>
            </div>
        </form>
        <a class="btn btn-default btn-sm" href="<?= Url::to('@web/user/add') ?>">
            <i class="icon-pencil align-top bigger-125"></i>
            <?= yii::t('user', 'add user') ?>
        </a>
    </div><!-- /.box-header -->

    <div class="box-body table-responsive no-padding clearfix">
        <table class="table table-striped table-bordered table-hover">
            <tbody>
                <tr>
                    <th><?= yii::t('user', 'realname') ?></th>
                    <th><?= yii::t('user', 'email') ?></th>
                    <th><?= yii::t('user', 'status') ?></th>
                    <th><?= yii::t('user', 'option') ?></th>
                </tr>
                <?php foreach ($userList as $row) {?>
                    <tr>
                        <td><?= $row['realname'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td>
                            <?php if ($row['role'] == User::ROLE_ADMIN) { ?>
                                <i class="icon icon-user-md green" data-placement="top" data-rel="tooltip" data-title="<?= yii::t('w', 'user_role_' . User::ROLE_ADMIN) ?>"></i>
                            <?php } else { ?>
                                <i class="icon icon-user" data-placement="top" data-rel="tooltip" data-title="<?= yii::t('w', 'user_role_' . User::ROLE_DEV) ?>"></i>
                            <?php } ?>

                            <?php if ($row['status'] == User::STATUS_INVALID) { ?>
                                <i class="icon icon-ban-circle red" data-placement="top" data-rel="tooltip" data-title="<?= yii::t('user', 'status to opposite 10') ?>"></i>
                            <?php } ?>
                            <?php if ($row['is_email_verified'] == User::MAIL_INACTIVE) { ?>
                                <i class="icon icon-envelope red" data-placement="top" data-rel="tooltip" data-title="<?= yii::t('user', 'inactive') ?>"></i>
                            <?php } ?>
                        </td>
                        <td>
                            <div class="nav">
                                <li>
                                    <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void();">
                                        <i class="icon-cog"></i>&nbsp;<?= yii::t('w', 'option') ?>
                                        <i class="icon-caret-down bigger-110 width-auto"></i>
                                    </a>
                                    <ul class="dropdown-menu data-user"
                                        data-user-id="<?= $row['id']?>"
                                        data-user-realname="<?= $row['realname']?>"
                                        data-user-email="<?= $row['email']?>"
                                        data-rename-url="<?= Url::to('@web/user/rename') ?>"
                                        data-status-url="<?= $row['status'] == User::STATUS_INVALID ? Url::to('@web/user/un-ban') : Url::to('@web/user/ban') ?>"
                                        data-role-url="<?= $row['role'] == User::ROLE_ADMIN ? Url::to('@web/user/to-dev') : Url::to('@web/user/to-admin') ?>"
                                        data-delete-url="<?= Url::to('@web/user/delete') ?>"
                                        >
                                        <li><a href="###" data-toggle="modal" data-target="#update-real-name" ><i class="icon-pencil"></i> <?= yii::t('w', 'edit') ?></a></li>
                                        <li><a href="###" class="cnt-user-option" data-url-key="status-url" data-confirm="<?= yii::t('user', 'label status to opposite ' . $row['status']) ?>"><i class="<?= $row['status'] == User::STATUS_INVALID ? 'icon-ok-circle' : 'icon-ban-circle' ?>"></i> <?= yii::t('user', 'status to opposite ' . $row['status']) ?> </a></li>
                                        <li><a href="###" class="cnt-user-option" data-url-key="delete-url" data-confirm="<?= yii::t('user', 'js delete user') ?>"><i class="icon-trash"></i> <?= yii::t('w', 'delete') ?></a></li>
                                        <li class="divider"></li>
                                        <li><a href="###" class="cnt-user-option" data-url-key="role-url" data-confirm="<?= yii::t('user', 'label role to opposite ' . $row['role']) ?>"><i class="i"></i> <?= yii::t('user', 'role to opposite ' . $row['role']) ?></a></li>
                                    </ul>
                                </li>

                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?= LinkPager::widget(['pagination' => $pages]); ?>
        <!-- 模态框（Modal） -->
        <div class="modal fade" id="update-real-name" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><?= yii::t('user', 'rename') ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="real-name" class="control-label"><?= yii::t('user', 'label real name') ?>:</label>
                            <input type="text" class="form-control" id="real-name">
                          </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= yii::t('user', 'btn cancel') ?></button>
                        <button type="button" class="btn btn-primary btn-submit"><?= yii::t('w', 'bnt sure') ?></button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.box-body -->
</div>

<script>
    jQuery(function($) {

        $('[data-rel=tooltip]').tooltip({container:'body'});
        $('[data-rel=popover]').popover({container:'body'});

        $('.cnt-user-option').click(function(e) {
            var uid = $(this).parents('.data-user').data('user-id');
            var urlKey = $(this).data('url-key')
            var url = $(this).parents('.data-user').data(urlKey);
            var confirmLabel = $(this).data('confirm')
            if (confirm(confirmLabel)) {
                $.get(url, {uid: uid}, function(o) {
                    if (!o.code) {
                        location.reload();
                    } else {
                        alert(o.msg);
                    }
                })
            }
        })

        $('#update-real-name').on('show.bs.modal', function (e) {
            var me = $(this),
                srcTar = $(e.relatedTarget).parents('.data-user'),
                modalTit = me.find('.modal-title'),
                uid = srcTar.attr('data-user-id'),
                email = srcTar.attr('data-user-email'),
                realname = srcTar.attr('data-user-realname'),
                url = srcTar.attr('data-rename-url'),
                subBtn = me.find('.btn-submit'),
                name = me.find('#real-name');
            name.val(realname)

            var title = modalTit.html();
            modalTit.html(title + '：' + email);

            subBtn.click(function () {
                $.get(url, {uid: uid, realName: name.val()}, function(o) {
                    if (!o.code) {
                        location.reload();
                    } else {
                        alert(o.msg);
                    }
                })
            });
        });
    });
</script>
