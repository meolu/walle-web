<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 一  7/20 15:52:01 2015
 *
 * @File Name: components/Controller.php
 * @Description:
 * *****************************************************************/

namespace app\components;

use app\models\User;
use yii;
use yii\helpers\Url;

class GlobalHelper {

    /**
     * 获取参数（post/get）的值, 优先级：post > get > default
     *
     * @param string $name 参数名字
     * @param mixed  $default 默认值
     * @return mixed
     */
    public static function sendMail($user, $default = null) {
        $params = Yii::$app->params;
        return Yii::$app->mail->compose()
            ->setFrom(Yii::$app->mail->messageConfig['from'])
            ->setTo($user->email)
            ->setSubject('Complete registration with ' . Yii::$app->name)
            ->send();
    }

    /**
     * 字符串转换成数组
     *
     * @param $string
     * @param $delimiter
     * @return array
     */
    public static function str2arr($string, $delimiter = PHP_EOL) {

        $items = explode($delimiter, $string);

        foreach ($items as $key => &$item) {

            // 查找 # 的位置
            $pos = strpos($item, '#');

            if ($pos === 0) {
                // # 开头, 整行注释
                unset($items[$key]);
                // 直接到下一个
                continue;
            }

            if ($pos > 0) {
                // # 在中间, 后面一段注释
                $item = substr($item, 0, $pos);
            }

            $item = trim($item);
            if (empty($item)) {
                unset($items[$key]);
            }

        }

        return $items;
    }

    /**
     * 转换成utf8
     * @param $text
     * @return string
     */
    public static function convert2Utf8($text) {
        $encoding = mb_detect_encoding($text, mb_detect_order(), false);
        if ($encoding == "UTF-8") {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        $out = iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8//IGNORE", $text);

        return $out;
    }

    /**
     * @param $pic
     * @return string
     */
    public static function formatAvatar($pic) {
        return rtrim(Url::to('@web' . User::AVATAR_ROOT), '/') . '/' . $pic;
    }

    /**
     * 当前登录是否为管理员（已激活）
     *
     * @return bool
     */
    public static function isValidAdmin() {
        return \Yii::$app->user
            && \Yii::$app->user->identity->role == User::ROLE_ADMIN
            && \Yii::$app->user->identity->is_email_verified == User::MAIL_ACTIVE
            && \Yii::$app->user->identity->status == User::STATUS_ADMIN_ACTIVE;
    }

}
