<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 一  7/20 15:52:01 2015
 *
 * @File Name: components/Controller.php
 * @Description:
 * *****************************************************************/

namespace app\components;

use yii;

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
            ->setFrom([$params['support.email'] => $params['support.name']])
            ->setTo($user->email)
            ->setSubject('Complete registration with ' . Yii::$app->name)
            ->send();
    }
}

