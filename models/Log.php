<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class Log extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function collectionName() {
        return 'log';
    }

    public function attributes() {
        return ['_id', 'name', 'trace',];
    }
}