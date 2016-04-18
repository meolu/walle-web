<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('user', 'profile title');
use \app\components\GlobalHelper;
use app\models\User;
use yii\helpers\Url;

?>
<div class="tab-content no-border padding-24">
    <div class="row">
        <div class="col-xs-12 col-sm-3 center">
            <div>
                <span class="profile-picture">
                    <img src="<?= GlobalHelper::formatAvatar($user->avatar) ?>" id="avatar" class="editable img-responsive editable-click editable-empty"/>
                    <input type="hidden" name="_csrf" value="<?= \Yii::$app->request->getCsrfToken(); ?>">
                </span>

                <div class="space-4"></div>

                <div class="width-80 label label-info label-xlg arrowed-in arrowed-in-right">
                    <div class="inline position-relative">
                        <a href="javascript:;" class="user-title-label dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-circle light-green middle"></i>
                            &nbsp;
                            <span class="white"><?= $user->realname ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="space-6"></div>

            <!-- 这个地方准备写一个有意思的东西，专为工程师荣耀而准备
            <div class="hr hr12 dotted"></div>

            <div class="clearfix">
                <div class="grid2">
                    <span class="bigger-175 blue">25</span>
                    <br>
                    Followers
                </div>

                <div class="grid2">
                    <span class="bigger-175 blue">12</span>

                    <br>
                    Following
                </div>
            </div>

            <div class="hr hr16 dotted"></div>
            <-->
        </div><!-- /span -->

        <div class="col-xs-12 col-sm-9">
            <div class="profile-user-info">
                <div class="profile-info-row">
                    <div class="profile-info-name"> <?= $user->realname ?> </div>

                    <div class="profile-info-value">
                        <span> <?= $user->email ?> </span>
                    </div>
                </div>


                <div class="profile-info-row">
                    <div class="profile-info-name"> <?= yii::t('user', 'level') ?> </div>

                    <div class="profile-info-value">
                        <span>
                            <?= \Yii::t('w', 'user_role_' . $user->role) ?>
                            <?= $user->role == User::ROLE_ADMIN && $user->status != \app\models\User::STATUS_ADMIN_ACTIVE ? yii::t('user', 'un-auth') : '' ?>
                        </span>
                    </div>
                </div>

                <div class="profile-info-row">
                    <div class="profile-info-name"> <?= yii::t('user', 'register time') ?> </div>

                    <div class="profile-info-value">
                        <span> <?= $user->created_at ?> </span>
                    </div>
                </div>
            </div>

            <div class="hr hr-8 dotted"></div>
        </div><!-- /span -->
    </div><!-- /row-fluid -->

    <div class="space-20"></div>
</div>
<script src="<?= Url::to('@web/dist/js/x-editable/bootstrap-editable.min.js') ?>"></script>
<script src="<?= Url::to('@web/dist/js/x-editable/ace-editable.min.js') ?>"></script>
<script src="<?= Url::to('@web/dist/js/jquery.gritter.min.js') ?>"></script>
<script>
    jQuery(function($) {

        //editables on first profile page
        $.fn.editable.defaults.mode = 'inline';
        $.fn.editableform.loading = "<div class='editableform-loading'><i class='light-blue icon-2x icon-spinner icon-spin'></i></div>";
        $.fn.editableform.buttons = '<button type="submit" class="btn btn-info editable-submit"><i class="icon-ok icon-white"></i></button>'+
        '<button type="button" class="btn editable-cancel"><i class="icon-remove"></i></button>';


        // *** editable avatar *** //
        try {//ie8 throws some harmless exception, so let's catch it

            //it seems that editable plugin calls appendChild, and as Image doesn't have it, it causes errors on IE at unpredicted points
            //so let's have a fake appendChild for it!
            if( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase()) ) Image.prototype.appendChild = function(el){}

            var last_gritter
            $('#avatar').editable({
                type: 'image',
                name: 'avatar',
                value: null,
                image: {
                    //specify ace file input plugin's options here
                    btn_choose: 'Change Avatar',
                    droppable: true,

                    //and a few extra ones here
                    name: 'avatar',//put the field name here as well, will be used inside the custom plugin
                    max_size: <?= \app\controllers\UserController::AVATAR_SIZE ?>,//~200Kb
                    on_error : function(code) {//on_error function will be called when the selected file has a problem
                        if(last_gritter) $.gritter.remove(last_gritter);
                        if(code == 1) {//file format error
                            last_gritter = $.gritter.add({
                                title: 'File is not an image!',
                                text: 'Please choose a jpg|gif|png image!',
                                class_name: 'gritter-error gritter-center'
                            });
                        } else if(code == 2) {//file size rror
                            last_gritter = $.gritter.add({
                                title: 'File too big!',
                                text: 'Image size should not exceed 100Kb!',
                                class_name: 'gritter-error gritter-center'
                            });
                        }
                        else {//other error
                        }
                    },
                    on_success : function() {
                        $.gritter.removeAll();
                    }
                },
                url: function(params) {
                    // ***UPDATE AVATAR HERE*** //
                    //You can replace the contents of this function with examples/profile-avatar-update.js for actual upload


                    //please modify submit_url accordingly
                    var deferred;


//if value is empty, means no valid files were selected
//but it may still be submitted by the plugin, because "" (empty string) is different from previous non-empty value whatever it was
//so we return just here to prevent problems
                    var value = $('#avatar').next().find('input[type=hidden]:eq(0)').val();
                    if(!value || value.length == 0) {
                        deferred = new $.Deferred
                        deferred.resolve();
                        return deferred.promise();
                    }

                    var $form = $('#avatar').next().find('.editableform:eq(0)')
                    var file_input = $form.find('input[type=file]:eq(0)');

//user iframe for older browsers that don't support file upload via FormData & Ajax
                    if( !("FormData" in window) ) {
                        deferred = new $.Deferred

                        var iframe_id = 'temporary-iframe-'+(new Date()).getTime()+'-'+(parseInt(Math.random()*1000));
                        $form.after('<iframe id="'+iframe_id+'" name="'+iframe_id+'" frameborder="0" width="0" height="0" src="about:blank" style="position:absolute;z-index:-1;"></iframe>');
                        $form.append('<input type="hidden" name="temporary-iframe-id" value="'+iframe_id+'" />');
                        $form.next().data('deferrer' , deferred);//save the deferred object to the iframe
                        $form.attr({'method' : 'POST', 'enctype' : 'multipart/form-data',
                            'target':iframe_id, 'action':submit_url});

                        $form.get(0).submit();

                        //if we don't receive the response after 60 seconds, declare it as failed!
                        setTimeout(function(){
                            var iframe = document.getElementById(iframe_id);
                            if(iframe != null) {
                                iframe.src = "about:blank";
                                $(iframe).remove();

                                deferred.reject({'status':'fail','message':'Timeout!'});
                            }
                        } , 60000);
                    }
                    else {
                        var fd = null;
                        try {
                            fd = new FormData($form.get(0));
                        } catch(e) {
                            //IE10 throws "SCRIPT5: Access is denied" exception,
                            //so we need to add the key/value pairs one by one
                            fd = new FormData();
                            $.each($form.serializeArray(), function(index, item) {
                                fd.append(item.name, item.value);
                            });
                            //and then add files because files are not included in serializeArray()'s result
                            $form.find('input[type=file]').each(function(){
                                if(this.files.length > 0) fd.append(this.getAttribute('name'), this.files[0]);
                            });
                        }

                        //if file has been drag&dropped , append it to FormData
                        if(file_input.data('ace_input_method') == 'drop') {
                            var files = file_input.data('ace_input_files');
                            if(files && files.length > 0) {
                                fd.append(file_input.attr('name'), files[0]);
                            }
                        }

                        fd.append('_csrf', '<?= \Yii::$app->request->getCsrfToken(); ?>');

                        deferred = $.ajax({
                            url: '<?= Url::to('@web/user/avatar/') ?>',
                            type: 'POST',
                            processData: false,
                            contentType: false,
                            dataType: 'json',
                            data: fd,
                            xhr: function() {
                                var req = $.ajaxSettings.xhr();
                                return req;
                            },
                            beforeSend : function() {
                                //bar.css('width', '0%').parent().attr('data-percent', '0%');
                            },
                            success : function() {
                                //bar.css('width', '100%').parent().attr('data-percent', '100%');
                            }
                        })
                    }



                    deferred.done(function(res){
                        if(res.code == 0) {
                            $('#avatar').get(0).src = res.data.url;
                            $('#avatar').show();
                            $('#avatar').next().find('div').first().remove()
                        }
                        else alert(res.msg);
                    }).fail(function(res){
                        alert("Failure");
                    });


                    return deferred.promise();
                },

                success: function(response, newValue) {
                }
            })
        }catch(e) {}


    });



</script>
