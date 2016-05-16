<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 五  7/31 22:21:23 2015
 *
 * @File Name: command/Folder.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use Yii;
use app\models\Project;
use app\models\Task as TaskModel;

class Folder extends Ansible {

    /**
     * 初始化宿主机部署工作空间
     *
     * @param TaskModel $task
     * @return bool|int
     */
    public function initLocalWorkspace(TaskModel $task) {

        $version = $task->link_id;
        $branch = $task->branch;

        if ($this->config->repo_type == Project::REPO_SVN) {
            // svn cp 过来指定分支的目录, 然后 svn up 到指定版本
            $cmd[] = sprintf('cp -rf %s %s ', Project::getSvnDeployBranchFromDir($branch), Project::getDeployWorkspace($version));
        } else {
            // git cp 仓库, 然后 checkout 切换分支, up 到指定版本
            $cmd[] = sprintf('cp -rf %s %s ', Project::getDeployFromDir(), Project::getDeployWorkspace($version));
        }
        $command = join(' && ', $cmd);
        return $this->runLocalCommand($command);
    }

    /**
     * 目标机器的版本库初始化
     * git 和 svn 没有任何区别, 只初始空目录
     *
     * @author wushuiyong
     * @param $log
     * @return bool
     */
    public function initRemoteVersion($version) {

        $command = sprintf('mkdir -p %s', Project::getReleaseVersionDir($version));

        if (Project::getAnsibleStatus()) {
            // ansible 并发执行远程命令
            return $this->runRemoteCommandByAnsibleShell($command);
        } else {
            // ssh 循环执行远程命令
            return $this->runRemoteCommand($command);
        }

    }

    /**
     * 将多个文件/目录通过tar + scp传输到指定的多个目标机
     *
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    public function scpCopyFiles(Project $project, TaskModel $task) {

        // 1. 宿主机 tar 打包
        $this->_packageFiles($project, $task);

        // 2. 传输 tar.gz 文件
        foreach (Project::getHosts() as $remoteHost) {
            // 循环 scp 传输
            $this->_copyPackageToServer($remoteHost, $project, $task);
        }

        // 3. 目标机 tar 解压
        $this->_unpackageFiles($project, $task);

        return true;
    }

    /**
     * 将多个文件/目录通过tar + ansible传输到指定的多个目标机
     *
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    public function ansibleCopyFiles(Project $project, TaskModel $task) {

        // 1. 宿主机 tar 打包
        $this->_packageFiles($project, $task);

        // 2. 传输 tar.gz 文件
        $this->_copyPackageToServerByAnsible($project, $task);

        // 3. 目标机 tar 解压
        $this->_unpackageFilesByAnsible($project, $task);

        return true;
    }

    /**
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    protected function _packageFiles(Project $project, TaskModel $task) {

        $version = $task->link_id;
        $files = $task->getCommandFiles();
        $excludes = GlobalHelper::str2arr($project->excludes) ;
        $packagePath = Project::getDeployPackagePath($version);
        $packageCommand = sprintf('cd %s && tar -p %s -cz -f %s %s',
            escapeshellarg(rtrim(Project::getDeployWorkspace($version), '/') . '/'),
            $this->excludes($excludes),
            escapeshellarg($packagePath),
            $files
        );
        $ret = $this->runLocalCommand($packageCommand);
        if (!$ret) {
            throw new \Exception(yii::t('walle', 'package error'));
        }

        return true;
    }

    /**
     * @param $remoteHost
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    protected function _copyPackageToServer($remoteHost, Project $project, TaskModel $task) {

        $version = $task->link_id;
        $packagePath = Project::getDeployPackagePath($version);

        $releasePackage = Project::getReleaseVersionPackage($version);

        $scpCommand = sprintf('scp -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o CheckHostIP=false -P %d %s %s@%s:%s',
            $this->getHostPort($remoteHost),
            $packagePath,
            escapeshellarg($this->getConfig()->release_user),
            escapeshellarg($this->getHostName($remoteHost)),
            $releasePackage);

        $ret = $this->runLocalCommand($scpCommand);

        if (!$ret) {
            throw new \Exception(yii::t('walle', 'rsync error'));
        }

        return true;
    }

    /**
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    protected function _copyPackageToServerByAnsible(Project $project, TaskModel $task) {

        $version = $task->link_id;
        $packagePath = Project::getDeployPackagePath($version);

        $releasePackage = Project::getReleaseVersionPackage($version);
        $ret = $this->copyFilesByAnsibleCopy($packagePath, $releasePackage);
        if (!$ret) {
            throw new \Exception(yii::t('walle', 'rsync error'));
        }

        return true;
    }

    /**
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    protected function _unpackageFiles(Project $project, TaskModel $task) {

        $version = $task->link_id;
        $releasePackage = Project::getReleaseVersionPackage($version);

        $webrootPath = Project::getTargetWorkspace();
        $releasePath = Project::getReleaseVersionDir($version);

        $cmd = [];

        if ($task->file_transmission_mode == TaskModel::FILE_TRANSMISSION_MODE_PART) {
            // 增量传输时, 在解压数据包之前, 需要把目标机当前版本复制一份到release目录
            $cmd[] = sprintf('cp -arf %s/. %s', $webrootPath, $releasePath);
        }

        $cmd[] = sprintf('cd %1$s && tar --no-same-owner -pm -C %1$s -xz -f %2$s',
            $releasePath,
            $releasePackage
        );

        $command = join(' && ', $cmd);

        $ret = $this->runRemoteCommand($command);

        if (!$ret) {
            throw new \Exception(yii::t('walle', 'unpackage error'));
        }

        return true;
    }

    /**
     * @param Project $project
     * @param TaskModel $task
     * @return bool
     * @throws \Exception
     */
    protected function _unpackageFilesByAnsible(Project $project, TaskModel $task) {

        $version = $task->link_id;
        $releasePackage = Project::getReleaseVersionPackage($version);

        $webrootPath = Project::getTargetWorkspace();
        $releasePath = Project::getReleaseVersionDir($version);

        $cmd = [];

        if ($task->file_transmission_mode == TaskModel::FILE_TRANSMISSION_MODE_PART) {
            // 增量传输时, 在解压数据包之前, 需要把目标机当前版本复制一份到release目录
            $cmd[] = sprintf('cp -arf %s/. %s', $webrootPath, $releasePath);
        }

        $cmd[] = sprintf('cd %1$s && tar --no-same-owner -pm -C %1$s -xz -f %2$s',
            $releasePath,
            $releasePackage
        );

        $command = join(' && ', $cmd);

        $ret = $this->runRemoteCommandByAnsibleShell($command);

        if (!$ret) {
            throw new \Exception(yii::t('walle', 'unpackage error'));
        }

        return true;
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

        $excludesCmd = '';

        // 无论是否填写排除.git和.svn, 这两个目录都不会发布
        array_push($excludes, '.git');
        array_push($excludes, '.svn');

        $excludes = array_unique($excludes);
        foreach ($excludes as $exclude) {
            $excludesCmd .= sprintf("--exclude=%s ", escapeshellarg(trim($exclude)));
        }

        return trim($excludesCmd);
    }

    /**
     * 收尾做处理工作，如清理本地的部署空间
     *
     * @param $version
     * @return bool|int
     */
    public function cleanUpLocal($version) {
        $cmd[] = 'rm -rf ' . Project::getDeployWorkspace($version);
        $cmd[] = sprintf('rm -f %s/*.tar.gz', dirname(Project::getDeployPackagePath($version)));
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

