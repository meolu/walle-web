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
     * Ansible 调用SSH的附加参数
     *
     * @var string
     */
    public $ansibleSshArgs = 'ANSIBLE_SSH_ARGS=\'-o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o CheckHostIP=false\'';

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

        $command = sprintf('%s ansible %s -u %s -m ping -i %s -f %d -T %d -vvv',
            $this->ansibleSshArgs,
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($command);
    }

    /**
     * 根据传入的 remote hosts 数组 生成 ansible 命令需要的 hosts 参数
     *
     * @param array $remoteHosts 不能在remoteHosts中传入 :端口
     * @return string
     */
    protected function _getAnsibleHosts($remoteHosts = [])
    {

        if ($remoteHosts) {
            // ansible 多个主机通过 : 间隔
            $ansibleHosts = implode(':', $remoteHosts);
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

        $localCommand = sprintf('%s ansible %s -u %s -m raw -a %s -i %s -f %d -T %d',
            $this->ansibleSshArgs,
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

        $localCommand = sprintf('%s ansible %s -u %s -m command -a %s -i %s -f %d -T %d',
            $this->ansibleSshArgs,
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

        $localCommand = sprintf('%s ansible %s -u %s -m shell -a %s -i %s -f %d -T %d',
            $this->ansibleSshArgs,
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

        $localCommand = sprintf('%s ansible %s -u %s -m script -a %s -i %s -f %d -T %d',
            $this->ansibleSshArgs,
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($shellFile),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($localCommand);
    }

    /**
     * 通过 ansible 复制文件模块, 并发传输文件
     *
     * @param string $src 宿主机文件路径
     * @param string $dest 目标机文件路径
     * @param array $hosts 可选的主机列表
     * @return bool|int
     */
    public function copyFilesByAnsibleCopy($src, $dest, $hosts = []) {

        $ansibleHosts = $this->_getAnsibleHosts($hosts);

        $localCommand = sprintf('%s ansible %s -u %s -m copy -a %s -i %s -f %d -T %d',
            $this->ansibleSshArgs,
            escapeshellarg($ansibleHosts),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg('src=' . $src . ' dest=' . $dest),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork,
            $this->ansibleTimeout);

        return $this->runLocalCommand($localCommand);
    }

}
