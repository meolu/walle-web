<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 六  7/18 22:47:22 2015
 *
 * @File Name: KafkaProducer.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\components\ZKafka;

class KafkaProducer {

    const KAFKA_OFFSET = 'kafka_offset';

    const LOG   = 'log';

    // log topic
    const TOPIC_ERROR   = 'error';

    const TOPIC_WARNING = 'warning';

    const TOPIC_NOTICE  = 'notice';

    // log level
    const LEVEL_ERROR   = 'error';

    const LEVEL_WARNING = 'warning';

    const LEVEL_NOTICE  = 'notice';

    // kafka offset
    const KAFKA_ERROR_OFFSET   = 'kafka_error_offset';

    const KAFKA_WARNING_OFFSET = 'kafka_warning_offset';

    const KAFKA_NOTICE_OFFSET  = 'kafka_notice_offset';

    public static function error($name, $log) {
        $trace = debug_backtrace(false);
        foreach ($trace as &$line) {
            unset($line['args']);
        }
        return static::write(static::TOPIC_ERROR, static::LEVEL_ERROR, $name, $log, $trace);
    }

    public static function warning($name, $log) {
        $trace = debug_backtrace(false);
        foreach ($trace as &$line) {
            unset($line['args']);
        }
        return static::write(static::TOPIC_WARNING, static::LEVEL_WARNING, $name, $log, $trace);
    }

    public static function notice($name, $log) {
        $trace = debug_backtrace(false);
        foreach ($trace as &$line) {
            unset($line['args']);
        }
        return static::write(static::TOPIC_NOTICE, static::LEVEL_NOTICE, $name, $log, $trace);
    }

    public static function write($topic, $level, $name, $log, $trace) {
        $trace = static::formatTrace($trace);
        $message = json_encode([
            'name'    => $name,
            'log'     => $log,
            'level'   => $level,
            'trace'   => json_encode($trace),
            'request' => $_SERVER,
            'time'    => date("Y-m-d H:i:s", time()),
        ], JSON_UNESCAPED_UNICODE);
        d($topic);d($message);
        return ZKafka::produce($topic, $message);
    }

    public static function formatTrace($trace) {
        $format = [];
        foreach ($trace as $line) {
            $format[] = sprintf("%s %s",
                empty($line['file']) ? '' : $line['file'] . ':' .$line['line'],
                empty($line['class']) ? '' : stripslashes($line['class']) . $line['type'] . $line['function']);

        }
        return $format;
    }

}
