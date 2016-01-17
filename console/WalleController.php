<?php
/* *****************************************************************
* @Author: wushuiyong
* @Created Time : 日  1/17 09:22:10 2016
*
* @File Name: WalleController.php
* @Description: walle脚手架
* *****************************************************************/

namespace app\console;
 
use yii;
use yii\console\Controller;
use yii\helpers\Console;

class WalleController extends Controller {

    public $writablePaths = [
        '@app/runtime',
        '@app/web/assets',
    ];

    public $executablePaths = [
        '@app/yii',
    ];

    public function actionIndex() {
        echo sprintf("walle-web %s (built: %s)\nCopyright (c) 2015-2016 The walle-web Group.\nGet Help from wushuiyong@huamanshu.com. Enjoy It.\n",
            Yii::$app->params['version'], Yii::$app->params['buildTime']);
    }
    /**
     * init walle 初始化项目
     *
     * @throws yii\console\Exception
     */
    public function actionSetup() {
        $this->runAction('create-dir', ['interactive' => $this->interactive]);
        $this->runAction('set-writable', ['interactive' => $this->interactive]);
        $this->runAction('set-executable', ['interactive' => $this->interactive]);
        \Yii::$app->runAction('migrate/up', ['interactive' => $this->interactive]);
    }

    public function actionCreateDir() {
        $mkdirPaths = [
            yii::$app->params['log.dir']
        ];
        $this->mkdir($mkdirPaths);
    }

    public function actionSetWritable() {
        $this->writablePaths[] = yii::$app->params['log.dir'];
        $this->setWritable($this->writablePaths);
    }

    public function actionSetExecutable() {
        $this->setExecutable($this->executablePaths);
    }

    public function mkdir($paths) {
        foreach ($paths as $path) {
            $path = Yii::getAlias($path);
            Console::output("mkdiring dir: {$path}");
            @mkdir($path, 0755);
        }
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
