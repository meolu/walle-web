<?php

namespace app\controllers;

use yii\web\NotFoundHttpException;
use app\components\Controller;
use app\components\GlobalHelper;
use app\models\User;

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
            $this->renderJson([], -1, '上传附件失败');
        }
        if ($_FILES['avatar']['size'] > static::AVATAR_SIZE) {
            $this->renderJson([], -1, '文件过大');
        }
        if (!in_array(strtolower($fileParts['extension']), \Yii::$app->params['user.avatar.extension'])) {
            $this->renderJson([], -1, '上传附件失败，附件格式只支持：' . join(', ', \Yii::$app->params['user.avatar.extension']));
        }
        $tempFile   = $_FILES['avatar']['tmp_name'];
        $baseName   = sprintf('%s-%d.%s', date("YmdHis", time()), rand(10, 99), $fileParts['extension']);
        $newFile    = GlobalHelper::formatAvatar($baseName);
        $targetFile = sprintf("%s/web/%s", rtrim(\Yii::$app->basePath, '/'),  ltrim($newFile, '/'));
        $ret = move_uploaded_file($tempFile, $targetFile);
        if ($ret) {
            $user = User::findOne($this->uid);
            $user->avatar = $baseName;
            $ret = $user->save();
        }

        $this->renderJson(['url' => $newFile], !$ret, $ret ?: '更新头像失败');
    }

    public function actionAudit() {
        $this->validateAdmin();

        $apply = User::getInactiveAdminList();

        return $this->render('audit', [
            'apply' => $apply,
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

        if ($user->role != User::ROLE_ADMIN || $user->is_email_verified != 1
            || $user->status != User::STATUS_INACTIVE) {
            throw new \Exception('只能删除未审核的项目管理员：（');
        }

        if (!$user->delete()) throw new \Exception('删除失败');
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

        if ($user->role != User::ROLE_ADMIN || $user->is_email_verified != 1
            || $user->status != User::STATUS_INACTIVE) {
            throw new \Exception('只能通过未审核的项目管理员：（');
        }
        $user->status = User::STATUS_ACTIVE;
        if (!$user->update()) throw new \Exception('更新失败');
        $this->renderJson([]);
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
            throw new NotFoundHttpException('该用户不存在：）');
        }
    }

}
