<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:21:23 2015
 *
 * @File Name: command/Sync.php
 * @Description:
 * *****************************************************************/
namespace app\components;


use app\models\Project;

class Task extends Command {

    /**
     * pre-deploy部署代码前置触发任务
     * 在部署代码之前的准备工作，如git的一些前置检查、vendor的安装（更新）
     *
     * @return bool
     */
    public function preDeploy($version) {
        $tasks = GlobalHelper::str2arr($this->getConfig()->pre_deploy);
        if (empty($tasks)) return true;

        $cmd = [];
        $workspace = rtrim(Project::getDeployWorkspace($version), '/');
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
    public function postDeploy($version) {
        $tasks = GlobalHelper::str2arr($this->getConfig()->post_deploy);
        if (empty($tasks)) return true;

        $cmd = [];
        $workspace = rtrim(Project::getDeployWorkspace($version), '/');
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
     * 获取远程服务器要操作的任务命令
     *
     * @param $task    string
     * @param $version string
     * @return string string
     */
    public static function getRemoteTaskCommand($task, $version) {
        $tasks = GlobalHelper::str2arr($task);
        if (empty($tasks)) return '';

        $cmd = [];
        $workspace = rtrim(Project::getDeployWorkspace($version), '/');
        $version   = Project::getReleaseVersionDir($version);
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
        return join(' && ', $cmd);
    }

    /**
     * 执行远程服务器任务集合
     * 对于目标机器更多的时候是一台机器完成一组命令，而不是每条命令逐台机器执行
     *
     * @param $tasks
     * @return mixed
     */
    public function runRemoteTaskCommandPackage($tasks) {
        $task = join(' && ', $tasks);
        return $this->runRemoteCommand($task);
    }

}

