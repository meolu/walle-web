<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use app\util\CurlUtil;

class Message extends Model
{
    /**
     * token url
     * @return string
     * @throws \Exception
     */
    public function getWeiXinTokenUrl()
    {
        $configs = self::getConfigs('weixin');
        return $configs['host'] . $configs['tokenUrl'];
    }

    /**
     * send message url
     * @param $accessToken
     * @return string
     * @throws \Exception
     */
    public function getWeiXinSendUrl($accessToken)
    {
        $configs = self::getConfigs('weixin');
        return $configs['host'] . $configs['sendUrl'] . $accessToken;
    }

    /**
     * 获取微信企业号token
     * @return mixed
     * @throws \Exception
     */
    public function getWeiXinToken()
    {
        $configs = self::getConfigs('weixin');
        $corpid = ArrayHelper::getValue($configs,'corpid');
        $corpsecret = ArrayHelper::getValue($configs,'corpsecret');
        $params = array(
            'corpid' => $corpid,
            'corpsecret' => $corpsecret
        );
        $url = self::getWeiXinTokenUrl();
        $response = CurlUtil::request($url, $params, CurlUtil::METHOD_GET);
        if (!$response) {
            throw new \Exception(yii::t('message', 'unknown token'));
        }
        return json_decode($response, true);
    }

    /**
     * 消息配置
     * @param string $type
     * @return mixed
     * @throws \Exception
     */
    public function getConfigs($type = '')
    {
        if (!$type) {
            throw new \Exception(yii::t('message', 'unknown configs'));
        }
        return Yii::$app->params['message-release'][$type];
    }

    /**
     * 生成微信消息推送数据
     * @param string $content
     * @return array
     * @throws \Exception
     */
    public function getWeiXinMsgData($content = '')
    {
        if (!$content) {
            throw new \Exception(yii::t('message', 'empty content'));
        }
        $configs = self::getConfigs('weixin');
        if (!$configs) {
            throw new \Exception(yii::t('message', 'unknown configs'));
        }
        $msgtype = ArrayHelper::getValue($configs, 'msgtype');
        if (!$msgtype) {
            throw new \Exception(yii::t('message', 'unknown msgtype'));
        }
        $agentid = ArrayHelper::getValue($configs, 'agentid');
        if (!$agentid) {
            throw new \Exception(yii::t('message', 'unknown agentid'));
        }
        $safe = ArrayHelper::getValue($configs, 'safe');
        $params = array(
            'touser' => $configs['touser'],
            'toparty' => $configs['toparty'],
            'totag' => $configs['totag'],
            'msgtype' => $msgtype,
            'agentid' => $agentid,
            'text' => array(
                'content' => $content,
            ),
            'safe' => $safe,
        );
        return $params;
    }

    /**
     * 推送微信消息
     * @param $message
     * @return bool
     * @throws \Exception
     */
    public function  sendWeiXinMessage($message)
    {
        if (!$message) {
            throw new \Exception(yii::t('message', 'empty content'));
        }
        $weiXinToken = self::getWeiXinToken();
        $params = self::getWeiXinMsgData($message);
        $accessToken = ArrayHelper::getValue($weiXinToken, 'access_token');
        $url = self::getWeiXinSendUrl($accessToken);
        $response =  CurlUtil::request($url, json_encode($params));
        $responseRet = json_decode($response, true);
        $errCode = ArrayHelper::getValue($responseRet, 'errcode');
        if ($errCode != 0) {
            throw new \Exception(yii::t('message', 'send fail'));
        }
        return true;
    }

    /**
     * 新发版消息
     * @param $taskObj
     * @return bool
     * @throws \Exception
     */
    public static function releaseWeiXinMessage($taskObj)
    {
        if (!$taskObj) {
            throw new \Exception(yii::t('message', 'task obj empty'));
        }
        $userInfo = User::find()
            ->select(['username'])
            ->where(['id' => $taskObj->user_id,])
            ->asArray()->one();

        $username_info = explode('@', $userInfo['username']);
        $project_name = $taskObj->project->name;
        $commit_id = $taskObj->commit_id;
        $updated_at = $taskObj->updated_at;
        $branch = $taskObj->branch;
        $emoji = '/:ok';//表情
        if (strpos($branch, 'hotfix') !== false) {//紧急上线
            $emoji = "/:li";
        }

        $message_str = $emoji . " Release:\n"
            . " p:" . $project_name . "\n"
            . " u:" . $username_info[0] . "\n"
            . " r:" . $commit_id . "\n"
            . " b:" . $branch . "\n"
            . " t: " . $updated_at;
        return self::sendWeiXinMessage($message_str);
    }

    /**
     * 回滚消息
     * @param $rollBackTaskObj 回滚任务
     * @param $lastTaskObj 回滚前任务
     * @return bool
     * @throws \Exception
     */
    public static function rollBackWeiXinMessage($rollBackTaskObj, $lastTaskObj)
    {
        if (!$rollBackTaskObj) {
            throw new \Exception(yii::t('message', 'roll back task obj empty'));
        }
        if (!$lastTaskObj) {
            throw new \Exception(yii::t('message', 'last task obj empty'));
        }
        $rollBackUserInfo = User::find()
            ->select(['username'])
            ->where(['id' => $rollBackTaskObj->user_id,])
            ->asArray()->one();
        $username_info = explode('@', $rollBackUserInfo['username']);
        $project_name = $rollBackTaskObj->project->name;
        $commit_id = $rollBackTaskObj->commit_id;
        $updated_at = $rollBackTaskObj->updated_at;
        $branch = $rollBackTaskObj->branch;
        $last_commit_id = $lastTaskObj->commit_id;
        $last_branch = $lastTaskObj->branch;

        $message_str = "/:,@@ RollBack:\n"
            . " p:" . $project_name . "\n"
            . " u:" . $username_info[0] . "\n"
            . " r:" . $commit_id . "\n"
            . " b:" . $branch . "\n"
            . " t: " . $updated_at
            . " Before Version: \n"
            . " r: " . $last_commit_id
            . " b: " . $last_branch;

        return self::sendWeiXinMessage($message_str);
    }
} 
