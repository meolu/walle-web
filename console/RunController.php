<?php
 
namespace app\console;
 
use yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * run controller
 */
class RunController extends Controller {

    public $writablePaths = [
        '@app/runtime',
        '@app/web/assets',
    ];

    public $executablePaths = [
        '@app/yii',
    ];

    /**
     * 初始化项目
     *
     * @throws yii\console\Exception
     */
    public function actionSetup() {
        $this->runAction('set-writable', ['interactive' => $this->interactive]);
        $this->runAction('set-executable', ['interactive' => $this->interactive]);
        \Yii::$app->runAction('migrate/up', ['interactive' => $this->interactive]);
    }

    public function actionSetWritable() {
        $this->setWritable($this->writablePaths);
    }

    public function actionSetExecutable() {
        $this->setExecutable($this->executablePaths);
    }

    public function setWritable($paths) {
        foreach ($paths as $writable) {
            $writable = Yii::getAlias($writable);
            Console::output("Setting writable: {$writable}");
            @chmod($writable, 0777);
        }
    }

    public function setExecutable($paths) {
        foreach ($paths as $executable) {
            $executable = Yii::getAlias($executable);
            Console::output("Setting executable: {$executable}");
            @chmod($executable, 0755);
        }
    }

}
