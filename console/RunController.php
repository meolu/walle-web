<?php
 
namespace app\console;
 
use yii;
use yii\console\Controller;
use app\components\ZKafka;
use app\components\KafkaConsumer;
use app\components\KafkaProducer;
 
/**
 * Test controller
 */
class RunController extends Controller {

    public function actionProduce() {
//        $ret = KafkaProducer::error('social.touch', '找不到数据');
        $ret = KafkaProducer::warning('social.touch', '只能切回去不能切回来');
//        $ret = KafkaProducer::notice('social.touch', '记录下数据');
        echo "\n==========================\n";
        dd($ret);
    }

    public function actionConsume() {
        return KafkaConsumer::process(KafkaProducer::TOPIC_ERROR, 10);
    }

    public function actionMongo() {
        $offset = ZKafka::getOffset(ZKafka::KAFKA_ERROR_OFFSET);
        $colError = Yii::$app->mongodb->getCollection(KafkaProducer::LOG);
        while (true) {
            echo 'offset:', $offset, PHP_EOL;
            $logs = KafkaConsumer::process(KafkaProducer::TOPIC_ERROR, $offset, 10);
            if (!$logs) {
                echo 'empty. sleep for a while...', PHP_EOL;
                sleep(5);
                continue;
            }
            foreach ($logs as $log) {
                $log = json_decode($log, true);
                if (empty($log)) continue;
                $ret = $colError->insert($log);
                echo $ret ? 'done' : 'error', PHP_EOL;
            }
        }
        
    }



    public function actionLog($level = KafkaProducer::LEVEL_ERROR) {
        if (!in_array($level, [KafkaProducer::LEVEL_ERROR, KafkaProducer::LEVEL_WARNING, KafkaProducer::LEVEL_NOTICE])) {
            throw new \Exception('暂无配置此错误级别：' . $level);
        }
        $limit = 10;
        $offsetName = 'kafka_' . $level . '_offset';
        $offset = ZKafka::getOffset($offsetName);
        $colError = Yii::$app->mongodb->getCollection(KafkaProducer::LOG);
        while (true) {
            echo 'offset:', $offset, PHP_EOL;
            $logs = KafkaConsumer::process($level, $offset, $limit);
            if (!$logs) {
                echo 'empty. sleep for a while...', PHP_EOL;
                sleep(5);
                continue;
            }
            foreach ($logs as $log) {
                $log = json_decode($log, true);
                if (empty($log)) continue;
                $ret = $colError->insert($log);
                echo $ret ? 'done' : 'error', PHP_EOL;
                if ($ret) {
                    ZKafka::setOffset($offsetName, ++$offset);
                }
            }
        }

    }


    public function actionSetoffset() {
        $offset = ZKafka::getOffset(ZKafka::KAFKA_ERROR_OFFSET);
        d($offset);
        $ret = ZKafka::setOffset(ZKafka::KAFKA_ERROR_OFFSET, --$offset);
        d($ret);
        $offset = ZKafka::getOffset(ZKafka::KAFKA_ERROR_OFFSET);
        d($offset);
    }

    public function actionCost() {
        $ret = Zkafka::produce('adOffline', 'planId|1|xxxdasflkd');
        dd($ret);
    }
}
