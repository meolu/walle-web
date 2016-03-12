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

class Folder extends Ansible {


    /**
     * 初始化宿主机部署工作空间
     *
     * @return bool
     */
    public function initLocalWorkspace($version) {
        // svn
        if ($this->config->repo_type == Project::REPO_SVN) {
            $cmd[] = 'mkdir -p ' . Project::getDeployWorkspace($version);
            $cmd[] = sprintf('mkdir -p %s-svn', rtrim(Project::getDeployWorkspace($version), '/'));
        }
        // git 直接把项目代码拷贝过来，然后更新，取代之前原项目检出，提速
        else {
            $cmd[] = sprintf('cp -rf %s %s ', Project::getDeployFromDir(), Project::getDeployWorkspace($version));
        }
        $command = join(' && ', $cmd);
        return $this->runLocalCommand($command);
    }

    /**
     * 目标机器的版本库初始化
     * 这里会有点特殊化：
     * 1.（git只需要生成版本目录即可）new：好吧，现在跟2一样了，毕竟本地的copy要比rsync要快，到时只需要rsync做增量更新即可
     * 2.svn还需要把线上版本复制到1生成的版本目录中，做增量发布
     *
     * @author wushuiyong
     * @param $log
     * @return bool
     */
    public function initRemoteVersion($version) {
        $cmd[] = sprintf('mkdir -p %s', Project::getReleaseVersionDir($version));
        if ($this->config->repo_type == Project::REPO_SVN) {
            $cmd[] = sprintf('test -d %s && cp -rf %s/* %s/ || echo 1', // 无论如何总得要$?执行成功
                $this->config->release_to, $this->config->release_to, Project::getReleaseVersionDir($version));
        }
        $command = join(' && ', $cmd);

        if (Project::getAnsibleStatus()) {
            // ansible 并发执行远程命令
            return $this->runRemoteCommandByAnsibleShell($command);
        } else {
            // ssh 循环执行远程命令
            return $this->runRemoteCommand($command);
        }

    }

    /**
     * rsync 同步文件
     *
     * @param $remoteHost 远程host，格式：host 、host:port
     * @return bool
     */
    public function syncFiles($remoteHost, $version) {
        $excludes = GlobalHelper::str2arr($this->getConfig()->excludes);

        $command = sprintf('rsync -avzq --rsh="ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o CheckHostIP=false -p %d" %s %s %s@%s:%s',
            $this->getHostPort($remoteHost),
            $this->excludes($excludes),
            escapeshellarg(rtrim(Project::getDeployWorkspace($version), '/') . '/'),
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($this->getHostName($remoteHost)),
            escapeshellarg(Project::getReleaseVersionDir($version)));

        return $this->runLocalCommand($command);
    }

    /**
     * 将多个文件/目录通过ansible传输到指定的多个目标机
     *
     * ansible 不支持 rsync模块, 改用宿主机 tar 打包, ansible 并发传输到目标机临时目录, 目标机解压
     *
     * @param $version
     * @param string $files 相对仓库文件/目录路径, 空格分割
     * @param array $remoteHosts
     */
    public function copyFiles($version, $files = '*', $remoteHosts = []) {

        // 1. 打包
        $excludes = GlobalHelper::str2arr($this->getConfig()->excludes);
        $packagePath = Project::getDeployPackagePath($version);
        $packageCommand = sprintf('cd %s && tar %s --preserve-permissions -czf %s %s',
            escapeshellarg(rtrim(Project::getDeployWorkspace($version), '/') . '/'),
            $this->excludes($excludes),
            $packagePath,
            $files
        );
        $ret = $this->runLocalCommand($packageCommand);
        if (!$ret) {
            return false;
        }

        // 2. 传输文件
        $releasePackage = Project::getReleaseVersionPackage($version);
        $ret = $this->copyFilesByAnsibleCopy($packagePath, $releasePackage);
        if (!$ret) {
            return false;
        }

        // 3. 解压
        $releasePath = Project::getReleaseVersionDir($version);
        $unpackageCommand = sprintf('tar --preserve-permissions --touch --no-same-owner -xzf %s -C %s',
            $releasePackage,
            $releasePath);
        $ret = $this->runRemoteCommandByAnsibleShell($unpackageCommand);

        return $ret;
    }

    /**
     * 打软链
     *
     * @param null $version
     * @return bool
     */
    public function getLinkCommand($version) {
        $user = $this->config->release_user;
        $project = Project::getGitProjectName($this->getConfig()->repo_url);
        $currentTmp = sprintf('%s/%s/current-%s.tmp', rtrim($this->getConfig()->release_library, '/'), $project, $project);
        // 遇到回滚，则使用回滚的版本version
        $linkFrom = Project::getReleaseVersionDir($version);
        $cmd[] = sprintf('ln -sfn %s %s', $linkFrom, $currentTmp);
        $cmd[] = sprintf('chown -h %s %s', $user, $currentTmp);
        $cmd[] = sprintf('mv -fT %s %s', $currentTmp, $this->getConfig()->release_to);

        return join(' && ', $cmd);
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

        if (Project::getAnsibleStatus()) {
            // ansible 并发执行远程命令
            return $this->runRemoteCommandByAnsibleShell($command);
        } else {
            return $this->runRemoteCommand($command);
        }
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
            $excludesRsync .= sprintf(" --exclude=%s ", escapeshellarg(trim($exclude)));
        }


        return trim($excludesRsync);
    }

    /**
     * 收尾做处理工作，如清理本地的部署空间
     *
     * @param $version
     * @return bool|int
     */
    public function cleanUpLocal($version) {
        $cmd[] = 'rm -rf ' . Project::getDeployWorkspace($version);
        if (Project::getAnsibleStatus()) {
            $cmd[] = 'rm -f ' . Project::getDeployPackagePath($version);
        }
        if ($this->config->repo_type == Project::REPO_SVN) {
            $cmd[] = sprintf('rm -rf %s-svn', rtrim(Project::getDeployWorkspace($version), '/'));
        }
        $command = join(' && ', $cmd);
        return $this->runLocalCommand($command);
    }

    /**
     * 删除本地项目空间
     *
     * @param $projectDir
     * @return bool|int
     */
    public function removeLocalProjectWorkspace($projectDir) {
        $cmd[] = "rm -rf " . $projectDir;
        $command = join(' && ', $cmd);
        return $this->runLocalCommand($command);
    }

}

