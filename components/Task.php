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
     * pre-deploy部署代码前置触发任务
     * 在部署代码之前的准备工作，如git的一些前置检查、vendor的安装（更新）
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
     * post-deploy部署代码后置触发任务
     * git代码检出之后，可能做一些调整处理，如vendor拷贝，配置环境适配（mv config-test.php config.php）
     *
     * @return bool
     */
    public function postDeploy() {
        $tasks = GlobalHelper::str2arr($this->getConfig()->post_deploy);
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
     * 同步代码之后触发任务
     * 所有目标机器都部署完毕之后，做一些清理工作，如删除缓存、重启服务（nginx、php、task）
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

