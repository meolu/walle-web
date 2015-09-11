<?php
namespace app\models\behaviors;

use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior as BaseTimestampBehavior;

class TimestampBehavior extends BaseTimestampBehavior
{
    public $attributes = [
        ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
    ];

    public function init()
    {
        parent::init();
        $this->value = new Expression('NOW()');
    }
}
