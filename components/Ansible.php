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

class Ansible extends Command
{
    /**
     * Ansible 并发数
     *
     * @var int
     */
    public $ansibleFork = 10;

    /**
     * Ansible 超时时间
     *
     * @var int
     */
    public $ansibleTimeout = 600;

    /**
     * 测试 ansible 命令是否可用
     *
     * @return bool|int
     */
    public function test()
    {

        $command = 'ansible --version';

        return $this->runLocalCommand($command);
    }

    /**
     * ping 测试 ansible 连接远程主机是否正确
     *
     * @param array $hosts
     */
    public function ping($hosts = [])
    {

        $ansibleHosts = $this->_getAnsibleHosts($hosts);

        $command = sprintf('ansible %s -u %s -m ping -i %s -f %d -T %d',
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($command);
    }

    /**
     * 根据传入的 hosts 数组 生成 ansible 命令需要的 hosts 参数
     *
     * @param array $hosts
     * @return string
     */
    protected function _getAnsibleHosts($hosts = [])
    {

        if ($hosts) {
            $ansibleHosts = implode(':', $hosts);
        } else {
            $ansibleHosts = 'all';
        }

        return $ansibleHosts;
    }

    /**
     * 通过 ansible 并发执行目标机器命令
     * RAW 模块, 不推荐使用, 仅用于首次安装 python 等场景
     *
     * @param string $command
     * @param array $hosts
     * @return bool|int
     */
    public function runRemoteCommandByAnsibleRaw($remoteCommand, $hosts = [])
    {

        $ansibleHosts = $this->_getAnsibleHosts($hosts);

        $localCommand = sprintf('ansible %s -u %s -m raw -a %s -i %s -f %d -T %d',
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($remoteCommand),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($localCommand);
    }

    /**
     * 通过 ansible 并发执行目标机器命令
     * Command 模块, 无法取得返回值, 不支持管道符, 命令中含有 $HOME, "<", ">", "|", and "&" 会返回失败
     *
     * @param string $command
     * @param array $hosts
     * @return bool|int
     */
    public function runRemoteCommandByAnsibleCommand($remoteCommand, $hosts = [])
    {

        $ansibleHosts = $this->_getAnsibleHosts($hosts);

        $localCommand = sprintf('ansible %s -u %s -m command -a %s -i %s -f %d -T %d',
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($remoteCommand),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($localCommand);
    }

    /**
     * 通过 ansible 并发执行目标机器命令
     * Shell 模块, 推荐使用
     *
     * @param string $command
     * @param array $hosts
     * @return bool|int
     */
    public function runRemoteCommandByAnsibleShell($remoteCommand, $hosts = [])
    {

        $ansibleHosts = $this->_getAnsibleHosts($hosts);

        $localCommand = sprintf('ansible %s -u %s -m shell -a %s -i %s -f %d -T %d',
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($remoteCommand),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($localCommand);
    }

    /**
     * 通过 ansible 并发执行目标机器命令
     * script 模块, 将宿主机的 .sh 文件推送到目标机上, 再执行这个文件
     *
     * @param string $command
     * @param array $hosts
     * @return bool|int
     */
    public function runRemoteCommandByAnsibleScript($shellFile, $hosts = [])
    {

        $ansibleHosts = $this->_getAnsibleHosts($hosts);

        $localCommand = sprintf('ansible %s -u %s -m script -a %s -i %s -f %d -T %d',
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($shellFile),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($localCommand);
    }

}
