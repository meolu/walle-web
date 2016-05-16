<?php
/**
 * Created by PhpStorm.
 * User: wushuiyong
 * Date: 15/8/14
 * Time: 下午5:41
 */

namespace app\components;

use Yii;
use yii\base\BootstrapInterface;

class EventBootstrap implements BootstrapInterface
{
    public function bootstrap($app) {
        $this->event();
    }

    public function event() {
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function ($event) {
            $aid = $event->action->id;
            $cid = $event->action->controller->id;
            if (Yii::$app->user->id) return true;
            if ($cid == 'site') {
                return true;
            }
            if (!Yii::$app->request->getIsAjax()) {
                Yii::$app->response->redirect('site/login');
                Yii::$app->end();
            }
            $event->isValid = false;
            throw new \Exception(yii::t('w', 'need login'));
        });
    }

}
