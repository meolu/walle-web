<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:21:23 2015
 *
 * @File Name: command/Folder.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use app\models\Project;

class Folder extends Command {


    /**
     * 初始化宿主机部署工作空间
     *
     * @return bool
     */
    public function initLocalWorkspace($version) {
        $command = 'mkdir -p ' . Project::getDeployWorkspace($version);
        return $this->runLocalCommand($command);
    }

    /**
     * 目标机器的版本库初始化
     *
     * @author wushuiyong
     * @param $log
     * @return bool
     */
    public function initRemoteVersion($version) {
        $command = sprintf('mkdir -p %s', Project::getReleaseVersionDir($version));
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

        $command = sprintf('rsync -avz --rsh="ssh -p 22" %s %s %s%s:%s',
            $this->excludes($excludes),
            rtrim(Project::getDeployWorkspace($version), '/') . '/',
            ($this->getConfig()->release_user ? $this->getConfig()->release_user . '@' : ''),
            $remoteHost,
            Project::getReleaseVersionDir($version));

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
        $project = Project::getGitProjectName($this->getConfig()->git_url);
        $currentTmp = sprintf('%s/%s/current-%s.tmp', rtrim($this->getConfig()->release_library, '/'), $project, $project);
        // 遇到回滚，则使用回滚的版本version
        $linkFrom = Project::getReleaseVersionDir($version);
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

    /**
     * rsync时，要排除的文件
     *
     * @param array $excludes
     * @return string
     */
    protected function excludes($excludes) {
        $excludesRsync = '';
        foreach ($excludes as $exclude) {
            $excludesRsync .= sprintf(" --exclude=%s", escapeshellarg(trim($exclude)));
        }


        return trim($excludesRsync);
    }

    /**
     * 收尾做处理工作，如清理本地的部署空间
     *
     * @param $version
     * @return bool|int
     */
    public function cleanUp($version) {
        $command = "rm -rf " . Project::getDeployWorkspace($version);

        return $this->runLocalCommand($command);
    }
}

