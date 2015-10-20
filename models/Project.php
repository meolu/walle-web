<?php

namespace app\models;

use app\components\GlobalHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "conf".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property integer $level
 * @property integer $status
 * @property string $version
 * @property integer $created_at
 * @property string $deploy_from
 * @property string $excludes
 * @property string $release_user
 * @property string $release_to
 * @property string $release_library
 * @property string $hosts
 * @property string $pre_deploy
 * @property string $post_deploy
 * @property string $post_release
 * @property string $repo_mode
 * @property string $repo_type
 * @property integer $audit
 */
class Project extends \yii\db\ActiveRecord
{

    // 有效状态
    const STATUS_VALID = 1;

    // 测试环境
    const LEVEL_TEST  = 1;

    // 仿真环境
    const LEVEL_SIMU  = 2;

    // 线上环境
    const LEVEL_PROD  = 3;

    const AUDIT_YES = 1;

    const AUDIT_NO = 2;

    const REPO_BRANCH = 'branch';

    const REPO_TAG = 'tag';

    const REPO_GIT = 'git';

    const REPO_SVN = 'svn';

    public static $CONF;

    public static $LEVEL = [
        self::LEVEL_TEST => 'test',
        self::LEVEL_SIMU => 'simu',
        self::LEVEL_PROD => 'prod',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'repo_url', 'name', 'level', 'deploy_from', 'release_user', 'release_to', 'release_library', 'hosts'], 'required'],
            [['user_id', 'level', 'status', 'audit'], 'integer'],
            [['excludes', 'hosts', 'pre_deploy', 'post_deploy', 'pre_release', 'post_release'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'repo_password'], 'string', 'max' => 100],
            [['version'], 'string', 'max' => 20],
            ['repo_type', 'default', 'value' => self::REPO_GIT],
            [['deploy_from', 'release_to', 'release_library', 'repo_url'], 'string', 'max' => 200],
            [['release_user', 'repo_mode', 'repo_username'], 'string', 'max' => 50],
            [['repo_type'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'user_id'         => 'User ID',
            'name'            => '项目名字',
            'level'           => '环境级别',
            'status'          => 'Status',
            'version'         => 'Version',
            'created_at'      => 'Created At',
            'deploy_from'     => '检出仓库',
            'excludes'        => '排除文件列表',
            'release_user'    => '目标机器部署代码用户',
            'release_to'      => '代码的webroot',
            'release_library' => '发布版本库',
            'hosts'           => '目标机器',
            'pre_deploy'      => '宿主机代码检出前置任务',
            'post_deploy'     => '宿主机同步前置任务',
            'pre_release'     => '目标机更新版本前置任务',
            'post_release'    => '目标机更新版本后置任务',
            'repo_url'        => 'git/svn地址',
            'repo_username'   => 'svn用户名',
            'repo_password'   => 'svn密码',
            'repo_mode'       => '分支/tag',
            'audit'           => '任务需要审核？',
        ];
    }

    /**
     * 获取当前进程的项目配置
     *
     * @param $id
     * @return string|\yii\db\ActiveQuery
     */
    public static function getConf($id = null) {
        if (empty(static::$CONF)) {
            static::$CONF = static::findOne($id);
        }
        return static::$CONF;
    }

    /**
     * 根据git地址获取项目名字
     *
     * @param $gitUrl
     * @return mixed
     */
    public static function getGitProjectName($gitUrl) {
        if (preg_match('#.*/(.*?)\.git#', $gitUrl, $match)) {
            return $match[1];
        }

        return basename($gitUrl);;
    }

    /**
     * 拼接宿主机的部署隔离工作空间
     * {deploy_from}/{env}/{project}-YYmmdd-HHiiss
     *
     * @return string
     */
    public static function getDeployWorkspace($version) {
        $from    = static::$CONF->deploy_from;
        $env     = isset(static::$LEVEL[static::$CONF->level]) ? static::$LEVEL[static::$CONF->level] : 'unknow';
        $project = static::getGitProjectName(static::$CONF->repo_url);

        return sprintf("%s/%s/%s-%s", rtrim($from, '/'), rtrim($env, '/'), $project, $version);
    }

    /**
     * 拼接宿主机的仓库目录
     * {deploy_from}/{env}/{project}
     *
     * @return string
     */
    public static function getDeployFromDir() {
        $from    = static::$CONF->deploy_from;
        $env     = isset(static::$LEVEL[static::$CONF->level]) ? static::$LEVEL[static::$CONF->level] : 'unknow';
        $project = static::getGitProjectName(static::$CONF->repo_url);

        return sprintf("%s/%s/%s", rtrim($from, '/'), rtrim($env, '/'), $project);
    }

    /**
     * 拼接目标机要发布的目录
     * {release_library}/{project}/{version}
     *
     * @param $version
     * @return string
     */
    public static function getReleaseVersionDir($version = null) {
        $version = $version ?: static::$CONF->link_id;

        return sprintf('%s/%s/%s', rtrim(static::$CONF->release_library, '/'),
            static::getGitProjectName(static::$CONF->repo_url), $version);
    }

    /**
     * 获取当前进程配置的目标机器host列表
     */
    public static function getHosts() {
        return GlobalHelper::str2arr(static::$CONF->hosts);
    }

    /**
     * 添加数据保存事件afterSave
     *
     * @author wushuiyong
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        // 插入一条管理员关系
        if ($insert) {
            Group::addGroupUser($this->attributes['id'], [$this->attributes['user_id']], Group::TYPE_ADMIN);
        }
    }

    /**
     * 添加数据删除事件afterDelete
     *
     * @author wushuiyong
     */
    public function afterDelete() {
        parent::afterDelete();
        // 删除所有该项目的关系
        Group::deleteAll(['project_id' => $this->attributes['id']]);
        // 删除本地目录

    }
}
