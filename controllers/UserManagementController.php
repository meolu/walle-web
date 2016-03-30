<?php

namespace app\controllers;

use yii;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use app\components\Controller;
use app\components\GlobalHelper;
use app\models\User;
use app\models\forms\AddUserForm;

class UserManagementController extends Controller {
    
    public function beforeAction($action) {
        parent::beforeAction($action);
        if (!GlobalHelper::isValidAdmin()) {
            throw new \Exception(yii::t('conf', 'you are not active'));
        }
        return true;
    }

    /**
     * 用户管理
     */
    public function actionIndex($page = 1, $size = 10) {
        $data = User::find()->orderBy('id desc');

        $pages = new Pagination(['totalCount' => $data->count(), 'pageSize' => $size]);
        $userList = $data->offset(($page - 1) * $size)->limit($size)->asArray()->all();

        return $this->render('index', [
            'userList' => $userList,
            'pages' => $pages,
        ]);
    }

    /**
     * 设置为管理员
     * @return json
     */
    public function actionUpdateToAdmin($uid) {
        $res = User::updateAll(['role' => 1], "id=$uid");

        $this->renderJson([], $res ? 0 : -1);
    }

    /**
     * 设置为普通用户
     * @return  json 
     */
    public function actionUpdateToUser($uid) {
        $res = User::updateAll(['role' => 10], "id=$uid");

        $this->renderJson([], $res ? 0 : -1);
    }

    /**
     * 帐号冻结
     * @return  json 
     */
    public function actionBlockedAccount($uid) {
        $res = User::updateAll(['status' => -1], "id=$uid");

        $this->renderJson([], $res ? 0 : -1);
    }

    /**
     * 帐号解冻
     * @return  json
     */
    public function actionUnBlockedAccount($uid) {
        $res = User::updateAll(['status' => 10], "id=$uid");

        $this->renderJson([], $res ? 0 : -1);
    }

    /**
     * 删除帐号
     * @return json
     */
    public function actionDeleteUser($uid) {
        $user = User::findOne($uid);
        $res = $user->delete();

        $this->renderJson([], $res ? 0 : -1);
    }

    /**
     * 修改真实姓名
     * @return   json
     */
    public function actionUpdateRealName($name, $uid) {
        $res = User::updateAll(['realname' => $name], "id=$uid");

        $this->renderJson([], $res ? 0 : -1);
    }

    /**
     * 新增用户
     */
    public function actionAddUser() {
        $model = new AddUserForm();

        if ($model->load(Yii::$app->request->post()) ) {
            if ($user = $model->signup()) {
                Yii::$app->mail->compose('accountNotice', ['user' => $user])
                    ->setFrom(Yii::$app->mail->messageConfig['from'])
                    ->setTo($user->email)
                    ->setSubject('瓦力平台 - ' . $user->realname . '帐号已开通')
                    ->send();
                
                return $this->redirect('@web/user-management');
            }
            else {
                throw new \Exception(yii::t('user-management', 'email exists'));
            }
        }
        
        return $this->render('adduser', [
            'model' => $model
        ]);
    }
}