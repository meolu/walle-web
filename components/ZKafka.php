<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 六  7/18 23:02:36 2015
 *
 * @File Name: ZKafka.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\models\KafkaOffset;

class ZKafka {
    
    const KAFKA_OFFSET = 'kafka_offset';

    // kafka的偏移量
    const KAFKA_ERROR_OFFSET   = 'kafka_error_offset';

    const KAFKA_WARNING_OFFSET = 'kafka_warning_offset';

    const KAFKA_NOTICE_OFFSET  = 'kafka_notice_offset';

    private static $_kafka;

    public static function produce($topic, $message) {
        return static::getInstance()->produce($topic, $message);
    }

    public static function consume($topic, $begin, $limit) {
        return static::getInstance()->consume($topic, $begin, $limit);
    }

    public static function getInstance() {
        if (is_object(static::$_kafka)) {
            return static::$_kafka;
        }
        return static::$_kafka = new \Kafka("localhost:9092");
    }

    public static function setOffset($topic, $offset) {
        $kafkaOffset = KafkaOffset::findOne(['name' => $topic]);
        if ($kafkaOffset) {
            $kafkaOffset->offset = $offset;
            return $kafkaOffset->save();
        } else {
            $kafkaOffset = new KafkaOffset();
            $kafkaOffset->name = $topic;
            $kafkaOffset->offset = $offset;
            return $kafkaOffset->save();
        }

    }

    public static function getOffset($topic) {
        $kafkaOffset = KafkaOffset::findOne(['name' => $topic]);
        return $kafkaOffset ? $kafkaOffset->offset : 0;
    }
}
