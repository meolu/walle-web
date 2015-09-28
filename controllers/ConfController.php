<?php

namespace app\controllers;

use yii;
use yii\data\Pagination;
use app\components\Controller;
use app\models\Conf;
use app\models\User;

class ConfController extends Controller
{

    /**
     * 配置项目列表
     *
     */
    public function actionIndex() {
        $conf = Conf::find();
        $kw = \Yii::$app->request->post('kw');
        if ($kw) {
            $conf->where(['like', "name", $kw]);
        }
        $conf = $conf->asArray()->all();
        return $this->render('index', [
            'list' => $conf,
        ]);
    }

    /**
     * 配置项目
     *
     * @param $projectId
     * @return string
     * @throws \Exception
     */
    public function actionEdit($projectId = null) {
        if (\Yii::$app->user->identity->role != User::ROLE_ADMIN) throw new \Exception('非管理员不能操作：（');
        $conf = $projectId ? Conf::findOne($projectId) : new Conf();
        if (\Yii::$app->request->getIsPost() && $conf->load(Yii::$app->request->post())) {
            $conf->user_id = \Yii::$app->user->id;
            if ($conf->save()) {
                $this->redirect('/conf/');
            }
        }

        if ($projectId && !$conf) throw new \Exception('找不到项目配置');
        return $this->render('edit', [
            'conf' => $conf,
        ]);
    }

    /**
     * 删除配置
     *
     * @return string
     * @throws \Exception
     */
    public function actionDelete() {
        $confId = $this->getParam('confId');
        $conf = Conf::findOne($confId);
        if (!$conf) {
            throw new \Exception('项目不存在：）');
        }
        if ($conf->user_id != \Yii::$app->user->id) {
            throw new \Exception('不可以操作其它人的项目：）');
        }
        if ($conf->delete()) throw new \Exception('删除失败');
        $this->renderJson([]);

    }
}
