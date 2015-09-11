<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 一  7/27 22:23:54 2015
 *
 * @File Name: SubscribeController.php
 * @Description:
 * *****************************************************************/
namespace app\controllers;

use app\components\Controller;
use app\components\LoggerDetail;
use app\models\LogDetail;

class SubscribeController extends Controller {

    public function actionIndex() {
        $logDetails = LogDetail::find()->asArray()->all();
        foreach ($logDetails as $logDetail) {
            $detail = LoggerDetail::formatChildDetail($logDetail);
        }
        return $this->render('index', [
            'detail' => $detail,
        ]);
    }
}
