<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 日  8/ 2 10:43:15 2015
 *
 * @File Name: command/Git.php
 * @Description:
 * *****************************************************************/
namespace app\components;


use app\models\Conf;

class Git extends Command {

    public function updateRepo($branch = 'master') {
        $destination = Conf::getDeployFromDir();
        // 存在git目录，直接pull
        if (file_exists($destination)) {
            $cmd[] = sprintf('cd %s ', $destination);
            $cmd[] = sprintf('/usr/bin/env git fetch --all');
            $cmd[] = sprintf('/usr/bin/env git reset --hard origin/%s', $branch); //$this->getConfig()->branch
            $cmd[] = sprintf('/usr/bin/env git checkout %s', $branch);
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
        // 不存在，则先checkout
        else {
            $parentDir = dirname($destination);
            $baseName = basename($destination);
            $cmd[] = sprintf('mkdir -p %s ', $parentDir);
            $cmd[] = sprintf('cd %s ', $parentDir);
            $cmd[] = sprintf('/usr/bin/env git clone %s %s', $this->getConfig()->git_url, $baseName);
            $cmd[] = sprintf('cd %s', $destination);
            $cmd[] = sprintf('/usr/bin/env git checkout %s', $branch); //$this->getConfig()->getScm('branch')
            $command = join(' && ', $cmd);
            return $this->runLocalCommand($command);
        }
    }

    /**
     * 回滚到指定commit版本
     *
     * @param string $commit
     * @return bool
     */
    public function rollback($commit) {
        $destination = Conf::getDeployFromDir();
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = sprintf('/usr/bin/env git reset %s', $commit);
        $cmd[] = '/usr/bin/env git checkout .';
        $command = join(' && ', $cmd);

        return $this->runLocalCommand($command);
    }

    /**
     * 获取分支列表
     *
     * @return array
     */
    public function getBranchList() {
        // 先更新
        $this->updateRepo();
        $destination = Conf::getDeployFromDir();
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = '/usr/bin/env git branch -a --list';
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);

        $history = [];
        if (!$result) return $history;

        $list = explode("\n", $this->getExeLog());
        foreach ($list as &$item) {
            $item = trim($item);
            $remotePrefix = 'remotes/origin/';
            if (substr($item, 0, strlen($remotePrefix)) == $remotePrefix) {
                $item = substr($item, strlen($remotePrefix));
            }
            $history[] = [
                'id'      => $item,
                'message' => $item,
            ];
        }
        return $history;
    }

    /**
     * 获取提交历史
     *
     * @return array
     */
    public function getCommitList($branch = 'master', $count = 20) {
        // 先更新
        $this->updateRepo($branch);
        $destination = Conf::getDeployFromDir();
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = '/usr/bin/env git log -' . $count . ' --pretty="%h - %an %s" ';
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);

        $history = [];
        if (!$result) return $history;

        // 总有一些同学没有团队协作意识，不设置好编码：(
        $log = GlobalHelper::convert2Utf8($this->getExeLog());
        $list = explode("\n", $log);
        foreach ($list as $item) {
            $commitId = substr($item, 0, strpos($item, '-') - 1);
            $history[] = [
                'id' => $commitId,
                'message'  => $item,
            ];
        }
        return $history;
    }

    /**
     * 获取tag记录
     *
     * @return array
     */
    public function getTagList($count = 20) {
        // 先更新
        $this->updateRepo();
        $destination = Conf::getDeployFromDir();
        $cmd[] = sprintf('cd %s ', $destination);
        $cmd[] = '/usr/bin/env git tag -l ';
        $command = join(' && ', $cmd);
        $result = $this->runLocalCommand($command);

        $history = [];
        if (!$result) return $history;

        $list = explode("\n", $this->getExeLog());
        foreach ($list as $item) {
            $history[] = [
                'id' => $item,
                'message'  => $item,
            ];
        }
        return $history;
    }

}
