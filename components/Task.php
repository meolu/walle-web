<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:21:23 2015
 *
 * @File Name: command/Sync.php
 * @Description:
 * *****************************************************************/
namespace app\components;


use app\models\Conf;

class Task extends Command {

    /**
     * 初始化部署目录
     *
     * @return bool
     */
    public function preDeploy() {
        $tasks = GlobalHelper::str2arr($this->getConfig()->pre_deploy);
        if (empty($tasks)) return true;

        $cmd = [];
        $workspace = trim(rtrim($this->getConfig()->deploy_from, '/'));
        $pattern = [
            '#{WORKSPACE}#',
        ];
        $replace = [
            $workspace,
        ];

        foreach ($tasks as $task) {
            $cmd[] = preg_replace($pattern, $replace, $task);
        }
        $command = join(' && ', $cmd);
        return $this->runLocalCommand($command);
    }

    /**
     * release时任务
     *
     * @return bool
     */
    public function postRelease($version) {
        $tasks = GlobalHelper::str2arr($this->getConfig()->post_release);
        if (empty($tasks)) return true;

        $cmd = [];
        $workspace = trim(rtrim($this->getConfig()->release_to, '/'));
        $version   = Conf::getReleaseVersionDir($version);
        $pattern = [
            '#{WORKSPACE}#',
            '#{VERSION}#',
        ];
        $replace = [
            $workspace,
            $version,
        ];
        foreach ($tasks as $task) {
            $cmd[] = preg_replace($pattern, $replace, $task);
        }
        $command = join(' && ', $cmd);
        return $this->runLocalCommand($command);
    }

}

