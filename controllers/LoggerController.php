<?php

namespace app\controllers;
use app\components\KafkaProducer;
use app\models\Log;
use app\components\Controller;

class LoggerController extends Controller {

    public $enableCsrfValidation = false;

    public function actionSearch() {
        $rows = [];
        $kw = self::getParam('kw');
        if ($kw) {
            $query = new \yii\mongodb\Query();
            $rows  = $query->select([])
                ->from(KafkaProducer::LOG)
                ->where(['LIKE', 'name', $kw])
                ->all();
        }
        return $this->render('index', [
            'rows' => $rows,
        ]);
    }



}
