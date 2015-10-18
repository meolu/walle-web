<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 日 10/18 15:41:42 2015
 *
 * @File Name: components/Repo.php
 * @Description:
 * *****************************************************************/
namespace app\components;

use \app\models\Project;

class Repo extends Command {

    public static function getRevision($type = Project::REPO_GIT) {
        switch ($type) {
            case Project::REPO_GIT:
                return new Git();
            case Project::REPO_SVN:
                return new Svn();
            default:
                throw new \Exception('未知的版本管理');
                break;
        }
    }
}