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
use app\components\Command;

class WalleController extends Controller {

    public $writablePaths = [
        '@app/runtime',
        '@app/web/assets',
    ];

    public $executablePaths = [
        '@app/yii',
    ];

    /**
     * checkout the current version 查看版本
     */
    public function actionIndex() {
        printf("\n\033[32mwalle-web %s (built: %s)\033[0m\nCopyright (c) 2015-2016 The walle-web Group.\nGet Help from team@walle-web.io. Enjoy It.\n\n",
            Yii::$app->params['version'], Yii::$app->params['buildTime']);
    }

    /**
     * upgrade walle 更新walle版本
     *
     * @throws yii\console\Exception
     */
    public function actionUpgrade() {
        $commander = new Command(['console']);
        // stash save local change 暂存本地修改
        echo 'stash save local change 暂存本地修改: git stash save ...', PHP_EOL;
        $commander->runLocalCommand('/usr/bin/env git stash save');
        // pull code 更新代码
        echo 'pull code 更新代码: git pull ...', PHP_EOL;
        $commander->runLocalCommand('/usr/bin/env git pull --rebase');
        // stash pop local change 弹出暂存本地修改
        echo 'stash pop local change 弹出暂存本地修改: git stash pop ...', PHP_EOL;
        $commander->runLocalCommand('/usr/bin/env git stash pop');
        // init walle 初始化项目
        $this->runAction('setup', ['interactive' => $this->interactive]);
        // checkout the current version查看最新版本
        echo "\033[32m\n--------------------------------------------------------", PHP_EOL;
        echo "Congratulations To Upgrade. Your Walle Current Version:\033[0m", PHP_EOL;
        $this->runAction('index', ['interactive' => $this->interactive]);
    }

    /**
     * init walle 初始化项目
     *
     * @throws yii\console\Exception
     */
    public function actionSetup() {
        // create dir 创建目录
        echo 'create dir 创建目录...', PHP_EOL;
        $this->createDir();
        // set writable 设置可写权限
        echo 'set writable 设置可写权限...', PHP_EOL;
        $this->setWritable();
        // set executable 设置可执行权限
        echo 'set executable 设置可执行权限...', PHP_EOL;
        $this->setExecutable();
        // update database 更新数据库
        echo 'update database 更新数据库: yii migrate/up ...', PHP_EOL;
        \Yii::$app->runAction('migrate/up', ['interactive' => $this->interactive]);
    }

    /**
     * create dir 创建目录
     */
    protected function createDir() {
        $mkdirPaths = [
            yii::$app->params['log.dir'],
            yii::$app->params['ansible_hosts.dir'],
            '@app/vendor/bower/jquery/dist',
        ];
        foreach ($mkdirPaths as $path) {
            $path = Yii::getAlias($path);
            Console::output("mkdiring dir: {$path}");
            @mkdir($path, 0755, true);
        }
    }

    /**
     * set writable 设置可写权限
     */
    protected function setWritable() {
        $this->writablePaths[] = yii::$app->params['log.dir'];
        $this->writablePaths[] = yii::$app->params['ansible_hosts.dir'];
        foreach ($this->writablePaths as $writable) {
            $writable = Yii::getAlias($writable);
            Console::output("Setting writable: {$writable}");
            @chmod($writable, 0777);
        }
    }

    /**
     * set executable 设置可执行权限
     */
    protected function setExecutable() {
        foreach ($this->executablePaths as $executable) {
            $executable = Yii::getAlias($executable);
            Console::output("Setting executable: {$executable}");
            @chmod($executable, 0755);
        }
    }

}
