<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('user-management', 'users');
use yii\helpers\Url;
use yii\widgets\LinkPager;
?>
<style type="text/css">
    .box-header {
        margin-bottom: 20px;
    }
</style>
<div class="box">
    <div class="box-header">
        <a href="<?= Url::to('@web/user-management/add-user') ?>" class="btn btn-default btn-add-user">
            <i class="icon icon-user"></i>
            <?= yii::t('user-management', 'u_add_user') ?>
        </a>
    </div>
    <div class="box-body table-responsive no-padding clearfix">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th><?= yii::t('user-management', 'u_username') ?></th>
                    <th><?= yii::t('user-management', 'u_realname') ?></th>
                    <th><?= yii::t('user-management', 'u_is_admin') ?></th>
                    <th><?= yii::t('user-management', 'u_status') ?></th>
                    <th><?= yii::t('user-management', 'u_oprea') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userList as $row) {?>
                    <tr>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['realname'] ?></td>
                        <td>
                            <?php if ($row['role'] == 1) { ?>
                                <i class="icon icon-user-md blue"></i>
                            <?php } else { ?>
                                <i class="icon icon-user"></i>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($row['status'] == 10) { ?>
                                <i class="icon icon-unlock"></i>
                            <?php } else { ?>
                                <i class="icon icon-lock red"></i>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($row['role'] != 1) { ?>
                                <button data-user-id="<?= $row['id']?>" data-username="<?= $row['username'] ?>" class="btn btn-primary update-to-admin"><?= yii::t('user-management', 'u_update_admin') ?></button>
                            <? } else { ?>
                                <button data-user-id="<?= $row['id']?>" data-username="<?= $row['username'] ?>" class="btn btn-primary update-to-user"><?= yii::t('user-management', 'u_update_user') ?></button>
                            <?php } ?>
                            <button data-user-id="<?= $row['id']?>" data-username="<?= $row['username'] ?>" class="btn btn-primary delete-user"><?= yii::t('user-management', 'u_delete_user') ?></button>
                            <?php if ($row['status'] == 10) { ?>
                                <button data-user-id="<?= $row['id']?>" data-username="<?= $row['username'] ?>" class="btn btn-primary blocked-account"><?= yii::t('user-management', 'u_blocked_account') ?></button>
                            <? } else { ?>
                                <button data-user-id="<?= $row['id']?>" data-username="<?= $row['username'] ?>" class="btn btn-primary un-blocked-account"><?= yii::t('user-management', 'u_un_blocked_account') ?></button>
                            <?php } ?>
                            <button data-user-id="<?= $row['id']?>" data-username="<?= $row['username'] ?>" class="btn btn-primary update-real-name" data-toggle="modal" data-target="#update-real-name"><?= yii::t('user-management', 'u_update_real_name') ?></button>
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
                        <h4 class="modal-title"><?= yii::t('user-management', 'u_title_update_real_name') ?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="real-name" class="control-label"><?= yii::t('user-management', 'u_notice_label_real_name') ?>:</label>
                            <input type="text" class="form-control" id="real-name">
                          </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= yii::t('user-management', 'u_btn_cancel') ?></button>
                        <button type="button" class="btn btn-primary"><?= yii::t('user-management', 'u_btn_submit') ?></button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.box-body -->
</div>

<script>
    jQuery(function($) {
        var handlerList = {
            // 改为管理员
            '.update-to-admin': {
                url: '<?= Url::to('@web/user-management/update-to-admin') ?>',
                notice: '<?= yii::t('user-management', 'js update to admin') ?>',
                username: 'data-username',
                params: {
                    uid: 'data-user-id'
                }
            },
            // 改为普通用户
            '.update-to-user': {
                url: '<?= Url::to('@web/user-management/update-to-user') ?>',
                notice: '<?= yii::t('user-management', 'js update to user') ?>',
                username: 'data-username',
                params: {
                    uid: 'data-user-id'
                }
            },
            // 冻结帐号
            '.blocked-account': {
                url: '<?= Url::to('@web/user-management/blocked-account') ?>',
                notice: '<?= yii::t('user-management', 'js blocked account') ?>',
                username: 'data-username',
                params: {
                    uid: 'data-user-id'
                }
            },
            // 帐号解冻
            '.un-blocked-account': {
                url: '<?= Url::to('@web/user-management/un-blocked-account') ?>',
                notice: '<?= yii::t('user-management', 'js unblocked account') ?>',
                username: 'data-username',
                params: {
                    uid: 'data-user-id'
                }
            },
            // 删除帐号
            '.delete-user': {
                url: '<?= Url::to('@web/user-management/delete-user') ?>',
                notice: '<?= yii::t('user-management', 'js delete user') ?>',
                username: 'data-username',
                params: {
                    uid: 'data-user-id'
                }
            }
        };

        for (var i in handlerList) {
            !function () {
                var tmp = handlerList[i];
                $(i).unbind().click(function () {
                    var me = $(this),
                        notice = tmp.notice.replace(/NAME/, me.attr(tmp.username)),
                        url = tmp.url + '?',
                        params = [];

                    for (var j in tmp.params) {
                        params.push(j + '=' + me.attr(tmp.params[j]));
                    }
                    url += params.join('&');
                    
                    if (confirm(notice)) {
                        $.getJSON(url, function (o) {
                            if (o.code === 0) {
                                window.location.reload();
                            }
                            else {
                                alert(o.message);
                            }
                        });
                    }
                });
            }();
        };

        $('#update-real-name').on('show.bs.modal', function (e) {
            var me = $(this),
                srcTar = $(e.relatedTarget),
                modalTit = me.find('h4'),
                uid = srcTar.attr('data-user-id'),
                subBtn = me.find('.btn-primary'),
                name = me.find('#real-name');

            var title = modalTit.html().replace(/<span>(.*?)<\/span>/, '<span>' + srcTar.attr('data-username') + '</span>');
            modalTit.html(title);

            subBtn.click(function () {
                if (name.val().length == 0 || !/^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[\w])*$/.test(name.val())) {
                    alert('<?= yii::t('user-management', 'u_real_name_illegal') ?>');
                    return false;
                }

                $.getJSON('<?= Url::to('@web/user-management/update-real-name') ?>?uid=' + uid + '&name=' + name.val(), function (o) {
                    if (o.code === 0) {
                        window.location.reload();
                    }
                    else {
                        alert(o.message);
                    }
                })
            });
        });
    });
</script>