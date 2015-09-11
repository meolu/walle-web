<?php

namespace app\models;

use yii\mongodb\ActiveRecord;

class KafkaOffset extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function collectionName() {
        return 'kafkfa_offset';
    }
    
	public function attributes() {
	    return ['_id', 'name', 'offset',];
	}
}