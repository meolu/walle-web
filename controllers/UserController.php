<?php

namespace app\controllers;

use yii;
use yii\web\NotFoundHttpException;
use app\components\Controller;
use app\components\GlobalHelper;
use app\models\User;
use app\models\forms\UserResetPasswordForm;
use app\models\forms\AddUserForm;
use yii\data\Pagination;

class UserController extends Controller {

    // 头像大小限制200k
    const AVATAR_SIZE = 200000;

    public function actionIndex() {
        $user = User::findOne($this->uid);
        return $this->render('index', [
            'user' => $user,
        ]);
    }

    public function actionAvatar() {
        $fileParts = pathinfo($_FILES['avatar']['name']);
        if ($_FILES['avatar']['error']) {
            $this->renderJson([], self::FAIL, yii::t('user', 'upload failed'));
        }
        if ($_FILES['avatar']['size'] > static::AVATAR_SIZE) {
            $this->renderJson([], self::FAIL, yii::t('user', 'attached\'s size too large'));
        }
        if (!in_array(strtolower($fileParts['extension']), \Yii::$app->params['user.avatar.extension'])) {
            $this->renderJson([], self::FAIL, yii::t('user', 'type not allow', [
                'types' => join(', ', \Yii::$app->params['user.avatar.extension'])
            ]));
        }
        $tempFile   = $_FILES['avatar']['tmp_name'];
        $baseName   = sprintf('%s-%d.%s', date("YmdHis", time()), rand(10, 99), $fileParts['extension']);
        $newFile    = User::AVATAR_ROOT . $baseName;
        $urlFile    = GlobalHelper::formatAvatar($baseName);
        $targetFile = sprintf("%s/web/%s", rtrim(\Yii::$app->basePath, '/'),  ltrim($newFile, '/'));
        $ret = move_uploaded_file($tempFile, $targetFile);
        if ($ret) {
            $user = User::findOne($this->uid);
            $user->avatar = $baseName;
            $ret = $user->save();
        }

        $this->renderJson(['url' => $urlFile], !$ret, $ret ?: yii::t('user', 'update avatar failed'));
    }

    public function actionAudit() {
        $this->validateAdmin();

        $apply = User::getInactiveAdminList();

        return $this->render('audit', [
            'apply' => $apply,
        ]);
    }


    /**
     * 用户管理
     */
    public function actionList($page = 1, $size = 10) {
        $userList = User::find()->orderBy('id desc');
        $kw = \Yii::$app->request->post('kw');
        if ($kw) {
            $userList->andFilterWhere(['like', "username", $kw])
                ->orFilterWhere(['like', "email", $kw]);
        }
        $pages = new Pagination(['totalCount' => $userList->count(), 'pageSize' => $size]);
        $userList = $userList->offset(($page - 1) * $size)->limit($size)->asArray()->all();

        return $this->render('list', [
            'userList' => $userList,
            'pages' => $pages,
        ]);
    }

    /**
     * 删除项目管理员
     *
     * @return string
     * @throws \Exception
     */
    public function actionDeleteAdmin($id) {
        $this->validateAdmin();
        $user = $this->findModel($id);

        if (!$user->delete()) throw new \Exception(yii::t('w', 'delete failed'));
        $this->renderJson([]);
    }

    /**
     * 项目审核管理员审核通过
     *
     * @return string
     * @throws \Exception
     */
    public function actionActiveAdmin($id) {
        $this->validateAdmin();
        $user = $this->findModel($id);

        $user->status = User::STATUS_ADMIN_ACTIVE;
        if (!$user->update()) throw new \Exception(yii::t('w', 'update failed'));
        $this->renderJson([]);
    }


    /**
     * 用户重置密码
     */
    public function actionResetPassword()
    {
        $user = new UserResetPasswordForm($this->uid);

        if ($user->load(Yii::$app->request->post()) && $user->validate() && $user->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');
            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $user,
        ]);
    }

    /**
     * 简化
     *
     * @param integer $id
     * @return Notification the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(yii::t('user', 'user not exists'));
        }
    }

    /**
     * 设置为管理员
     *
     * @return json
     */
    public function actionToAdmin($uid) {
        $this->validateAdmin();
        if ($uid) {
            User::updateAll(['role' => User::ROLE_ADMIN], ['id' => $uid]);
        }

        $this->renderJson([], self::SUCCESS);
    }

    /**
     * 设置为普通用户
     *
     * @return  json
     */
    public function actionToDev($uid) {
        $this->validateAdmin();
        if ($uid) {
            User::updateAll(['role' => User::ROLE_DEV], ['id' => $uid]);
        }

        $this->renderJson([], self::SUCCESS);
    }

    /**
     * 帐号冻结
     *
     * @return  json
     */
    public function actionBan($uid) {
        $this->validateAdmin();
        if ($uid) {
            User::updateAll(['status' => User::STATUS_INVALID], ['id' => $uid]);
        }

        $this->renderJson([], self::SUCCESS);
    }

    /**
     * 帐号解冻
     *
     * @return  json
     */
    public function actionUnBan($uid) {
        $this->validateAdmin();
        if ($uid) {
            User::updateAll(['status' => User::STATUS_ACTIVE], ['id' => $uid]);
        }

        $this->renderJson([], self::SUCCESS);
    }

    /**
     * 删除帐号
     *
     * @return json
     */
    public function actionDelete($uid) {
        $this->validateAdmin();
        $user = User::findOne($uid);
        if ($user) {
            $user->delete();
        }

        $this->renderJson([], self::SUCCESS);
    }

    /**
     * 修改真实姓名
     *
     * @return   json
     */
    public function actionRename($realName, $uid) {
        $this->validateAdmin();
        if ($realName && $uid) {
            $res = User::updateAll(['realname' => $realName], ['id' => $uid]);
            $this->renderJson([], $res ? self::SUCCESS : self::FAIL, $res ? '' : Yii::t('w', 'update failed'));
        }
        $this->renderJson([], self::FAIL, Yii::t('w', 'update failed'));
    }

    /**
     * 新增用户
     */
    public function actionAdd() {
        $this->validateAdmin();
        $model = new AddUserForm();

        if ($model->load(Yii::$app->request->post()) ) {
            if ($user = $model->signup()) {
                Yii::$app->mail->compose('accountNotice', ['user' => $user])
                    ->setFrom(Yii::$app->mail->messageConfig['from'])
                    ->setTo($user->email)
                    ->setSubject('帐号已开通')
                    ->send();
                return $this->redirect('@web/user/list');
            }else {
                throw new \Exception(yii::t('user', 'email exists'));//修改这里添加用户有问题
            }
        }
        return $this->render('add', [
            'model' => $model
        ]);
    }

    /**
     * 邮件重发
     */
    public function actionRetryEmail($uid) {
        $this->validateAdmin();
        if ($uid) {
            $user = User::findOne($uid);
                Yii::$app->mail->compose('accountNotice', ['user' => $user])
                    ->setFrom(Yii::$app->mail->messageConfig['from'])
                    ->setTo($user->email)
                    ->setSubject('帐号已开通')
                    ->send();
                return $this->redirect('@web/user/list');
        }

        $this->renderJson([], self::SUCCESS);
	
	}


}
