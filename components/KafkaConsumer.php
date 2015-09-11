<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 六  7/18 23:08:29 2015
 *
 * @File Name: KafkaConsumer.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use yii\console\Controller;
use app\components\ZKafka;

class KafkaConsumer {

    public static function process($topic, $offset, $limit = 20) {
        $message = ZKafka::consume($topic, $offset, $limit);
        return $message;
    }

}
