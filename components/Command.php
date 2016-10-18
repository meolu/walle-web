<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:42:32 2015
 *
 * @File Name: command/Command.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\models\Record;

class Command {

    protected $taskId;

    protected static $LOGDIR = '';

    /**
     * Handler to the current Log File.
     * @var mixed
     */
    protected static $logFile = null;

    /**
     * Config
     * @var \walle\config\Config
     */
    protected $config;

    /**
     * 命令运行返回值：0失败，1成功
     * @var int
     */
    protected $status = 1;

    /**
     * 每次执行的命令
     * @var string
     */
    protected $command = '';

    /**
     * 每次执行的开始时间
     * @var string
     */
    protected $startTime = '';

    /**
     * 每次执行的时长，单位毫秒
     * @var int
     */
    protected $duration = 0;

    protected $log = null;

    /**
     * 加载配置
     *
     * @param $config
     * @return $this
     * @throws \Exception
     */
    public function __construct($config) {
        if ($config) {
            $this->config = $config;
        } else {
            throw new \Exception(\yii::t('walle', 'unknown config'));
        }
    }

    /**
     * 执行本地宿主机命令
     *
     * @param $command
     * @return bool|int true 成功，false 失败
     */
    final public function runLocalCommand($command) {
        $command = trim($command);
        $this->log('---------------------------------');
        $this->log('---- Executing: $ ' . $command);

        $status = 1;
        $log = '';
        $this->startTime = time();

        $executeStartMs = $this->getMs();
        exec($command . ' 2>&1', $log, $status);
        $this->duration = $this->getMs() - $executeStartMs;

        // 执行过的命令
        $this->command = $command;
        // 执行的状态
        $this->status = !$status;
        // 操作日志
        $log = implode(PHP_EOL, $log);
        $this->log = trim($log);

        $this->log($log);
        $this->log('---------------------------------');

        // log save to mysql
        Record::saveRecord($this, $this->taskId, Task::$PROCESS_STAGE);
        return $this->status;
    }

    /**
     * 执行远程目标机器命令
     *
     * @param string  $command
     * @param integer $delay 每台机器延迟执行post_release任务间隔, 不推荐使用, 仅当业务无法平滑重启时使用
     * @return bool
     */
    final public function runRemoteCommand($command, $delay = 0) {
        $this->log = '';
        $needTTY = '-T';

        foreach (GlobalHelper::str2arr($this->getConfig()->hosts) as $remoteHost) {

            $localCommand = sprintf('ssh %s -p %d -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o CheckHostIP=false %s@%s %s',
                $needTTY,
                $this->getHostPort($remoteHost),
                escapeshellarg($this->getConfig()->release_user),
                escapeshellarg($this->getHostName($remoteHost)),
                escapeshellarg($command)
            );

            if ($delay > 0) {
                // 每台机器延迟执行post_release任务间隔, 不推荐使用, 仅当业务无法平滑重启时使用
                static::log(sprintf('Sleep: %d s', $delay));
                sleep($delay);
            }

            static::log('Run remote command ' . $command);

            $log = $this->log;
            $this->status = $this->runLocalCommand($localCommand);

            $this->log = $log . (($log ? PHP_EOL : '') . $remoteHost . ' : ' . $this->log);
            if (!$this->status) {
                return false;
            }

        }

        return true;
    }

    /**
     * 加载配置
     *
     * @param $config
     * @return $this
     * @throws \Exception
     */
    public function setConfig($config) {
        if ($config) {
            $this->config = $config;
        } else {
            throw new \Exception(\yii::t('walle', 'unknown config'));
        }
        return $this;
    }

    /**
     * 获取配置
     * @return \walle\config\Config
     */
    protected function getConfig() {
        return $this->config;
    }

    public static function log($message) {
        if (empty(\Yii::$app->params['log.dir'])) return;

        $logDir = \Yii::$app->params['log.dir'];
        if (!file_exists($logDir)) return;

        $logFile = realpath($logDir) . '/walle-' . date('Ymd') . '.log';
        if (self::$logFile === null) {
            self::$logFile = fopen($logFile, 'a');
        }

        $message = date('Y-m-d H:i:s -- ') . $message;
        fwrite(self::$logFile, $message . PHP_EOL);
    }

    /**
     * 获取执行command
     *
     * @author wushuiyong
     * @return string
     */
    public function getExeCommand() {
        return $this->command;
    }

    /**
     * 获取执行执行时间
     *
     * @author wushuiyong
     * @return string
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * 获取开始执行时间
     *
     * @author wushuiyong
     * @return string
     */
    public function getStartTime() {
        return $this->startTime;
    }

    /**
     * 获取执行log
     *
     * @author wushuiyong
     * @return string
     */
    public function getExeLog() {
        return $this->log;
    }

    /**
     * 获取执行log
     *
     * @author wushuiyong
     * @return string
     */
    public function getExeStatus() {
        return $this->status;
    }

    /**
     * 获取耗时毫秒数
     *
     * @return int
     */
    public static function getMs() {
        return intval(microtime(true) * 1000);
    }

    /**
     * 获取目标机器的ip或别名
     *
     * @param $host
     * @return mixed
     */
    protected function getHostName($host) {
        list($hostName,) = explode(':', $host);
        return $hostName;
    }

    /**
     * 获取目标机器的ssh端口
     *
     * @param $host
     * @param int $default
     * @return int
     */
    protected function getHostPort($host, $default = 22) {
        $hostInfo = explode(':', $host);
        return !empty($hostInfo[1]) ? $hostInfo[1] : $default;
    }

}
