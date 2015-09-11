<?php

namespace app\models;

use Yii;

/**
 * This is the model class for collection "log_detail".
 *
 * @property \MongoId|string $_id
 */
class LogDetail extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['local', 'log_detail'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id', 'name',
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

    public static function formatChildDetail($detail, $name = null) {
        $name = $name ? $name . '.' . $detail['name'] : $detail['name'];
        $list[$name] = [
            'name'    => $detail['name'],
            'error'   => $detail['error'],
            'warning' => $detail['warning'],
            'notice'  => $detail['notice'],
        ];
        if (!empty($detail['child'])) {
            foreach ($detail['child'] as $child) {
                $list = array_merge($list, static::formatChildDetail($child, $name));
            }
        }
        return $list;
    }

    public static function incrCount() {

    }
}
