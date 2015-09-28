<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:21:23 2015
 *
 * @File Name: command/Folder.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\models\Conf;

class Folder extends Command {


    /**
     * 初始化部署目录
     *
     * @return bool
     */
    public function initDirector() {
        $command = sprintf('mkdir -p %s',
            rtrim($this->getConfig()->deploy_from, '/'));
        return $this->runLocalCommand($command);
    }

    /**
     * 目录、权限检查
     *
     * @author wushuiyong
     * @param $log
     * @return bool
     */
    public function folderAndPermission($version) {
        $command = sprintf('mkdir -p %s', Conf::getReleaseVersionDir($version));
        return $this->runRemoteCommand($command);

    }

    /**
     * rsync 同步文件
     * 后续ssh -p参数的端口可以加入可配置，可能会出现非22端口
     *
     * @param $remoteHost 远程host，格式：host 、host:port
     * @return bool
     */
    public function syncFiles($remoteHost, $version) {
        $excludes = GlobalHelper::str2arr($this->getConfig()->excludes);
        $command = 'rsync -avz ' . '--rsh="ssh '
            . '-p 22 ' . '" '
            . $this->excludes($excludes) . ' '
            . rtrim(Conf::getDeployFromDir(), '/') . '/ '
            . ($this->getConfig()->release_user ? $this->getConfig()->release_user . '@' : '')
            . $remoteHost . ':' . Conf::getReleaseVersionDir($version);

        return $this->runLocalCommand($command);
    }

    /**
     * 打软链
     *
     * @param null $version
     * @return bool
     */
    public function link($version) {
        $user = $this->getConfig()->release_user;
        $project = Conf::getGitProjectName($this->getConfig()->git_url);
        $currentTmp = sprintf('%s/%s/current-%s.tmp', rtrim($this->getConfig()->release_library, '/'), $project, $project);
        // 遇到回滚，则使用回滚的版本version
        $linkFrom = Conf::getReleaseVersionDir($version);
        $cmd[] = sprintf('ln -sfn %s %s', $linkFrom, $currentTmp);
        $cmd[] = sprintf('chown -h %s %s', $user, $currentTmp);
        $cmd[] = sprintf('mv -fT %s %s', $currentTmp, $this->getConfig()->release_to);
        $command = join(' && ', $cmd);

        return $this->runRemoteCommand($command);
    }

    /**
     * 获取文件的MD5
     *
     * @param $file
     * @return bool
     */
    public function getFileMd5($file) {
        $cmd[] = "test -f /usr/bin/md5sum && md5sum {$file}";
        $command = join(' && ', $cmd);

        return $this->runRemoteCommand($command);
    }


}

