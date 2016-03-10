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
     * Ansible Fork
     * @var int
     */
    public $ansibleFork = 10;

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

        $command = sprintf('ansible %s -m ping -i %s -f %d',
            escapeshellarg($ansibleHosts),
            escapeshellarg(Project::getAnsibleHostsFile()),
            $this->ansibleFork);

        return $this->runLocalCommand($command);
    }

    /**
     * 根据传入的 hosts 数组 生成 ansible 命令需要的 hosts 桉树
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

}
