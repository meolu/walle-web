<?php

namespace app\models;

use Yii;

/**
 * This is the model class for collection "kafka_offset".
 *
 * @property \MongoId|string $_id
 */
class KafkaOffset extends \yii\mongodb\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['local', 'kafka_offset'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id', 'name', 'offset',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
        ];
    }
}
