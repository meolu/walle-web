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

    /**
     * 获取版本管理句柄
     *
     * @param $conf
     * @return Git|Svn
     * @throws \Exception
     */
    public static function getRevision($conf) {
        switch ($conf->repo_type) {
            case Project::REPO_GIT:
                return new Git($conf);
            case Project::REPO_SVN:
                return new Svn($conf);
            default:
                throw new \Exception(\yii::t('walle', 'unknown scm'));
                break;
        }
    }
}