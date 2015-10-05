<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:42:32 2015
 *
 * @File Name: command/Command.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\models\Conf;

abstract class Command {

    protected static $LOGDIR = '';
    /**
     * Handler to the current Log File.
     * @var mixed
     */
    protected static $logFile = null;


    /**
     * Enables or Disables Logging
     * @var boolean
     */
    private static $logEnabled = true;

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

    protected $command = '';

    protected $log = null;


    final protected function runLocalCommand($command) {
        $command = trim($command);
        file_put_contents('/tmp/cmd', $command.PHP_EOL.PHP_EOL, 8);
        $this->log('---------------------------------');
        $this->log('---- Executing: $ ' . $command);

        $status = 1;
        $log = '';

        exec($command . ' 2>&1', $log, $status);
        // 执行过的命令
        $this->command = $command;
        // 执行的状态
        $this->status = !$status;
        // 操作日志
        $log = implode(PHP_EOL, $log);
        $this->log = trim($log);

        $this->log($log);
        $this->log('---------------------------------');

        return $this->status;
    }

    final protected function runRemoteCommand($command) {
        $this->log = '';
        $needs_tty = ''; #($this->getConfig()->general('ssh_needs_tty', false) ? '-t' : '');

        foreach (GlobalHelper::str2arr($this->getConfig()->hosts) as $remoteHost) {
            $remoteHost = trim($remoteHost);
            $localCommand = 'ssh ' . $needs_tty . ' -p 22 '
                . '-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no '
//            . $this->getConfig()->getConnectTimeoutOption()
                . ($this->getConfig()->release_user ? $this->getConfig()->release_user . '@' : '')
                . $remoteHost;
            $remoteCommand = str_replace('"', '\"', trim($command));
            $localCommand .= ' "sh -c \"' . $remoteCommand . '\"" ';
            static::log('Run remote command ' . $remoteCommand);

            $log = $this->log;
            $this->status = $this->runLocalCommand($localCommand);

            $this->log = $log . (($log ? PHP_EOL : '') . $remoteHost . ' : ' . $this->log);
            if (!$this->status) return false;
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
            throw new \Exception('未知的配置');
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
        $logFile = realpath($logDir) . '/log-' . date('Ymd') . '.log';

        if (!is_writable($logFile)) return;

        if (self::$logFile === null) {
            self::$logFile = fopen($logFile, 'w');
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


}
