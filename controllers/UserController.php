<?php

namespace app\controllers;

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

}
