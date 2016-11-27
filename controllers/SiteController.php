<?php

namespace app\controllers;

use Yii;
use app\components\Controller;
use app\models\User;
use app\models\forms\LoginForm;
use app\models\forms\LdapLoginForm;
use app\models\forms\PasswordResetRequestForm;
use app\models\forms\ResetPasswordForm;
use yii\web\HttpException;
use yii\base\Exception;
use yii\base\UserException;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;

class SiteController extends Controller
{
    public $layout = 'site';

    /**
     * Render the homepage
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * User login
     */
    public function actionLogin() {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $userDriver = isset(\Yii::$app->params['user_driver']) == true && empty(\Yii::$app->params['user_driver']) == false ? strtolower(\Yii::$app->params['user_driver']) : 'local';

        if($userDriver == 'ldap'){
            if(isset(\Yii::$app->params['ldap']) == false){
                throw new \Exception(yii::t('walle', 'the login dirver configs does not defined', array(
                        'loginType' => \Yii::$app->params['user_driver']
                    )));
            }

            if(is_array(\Yii::$app->params['ldap']) == false){
                throw new \Exception(yii::t('walle', 'the login dirver configs parse error', array(
                        'loginType' => \Yii::$app->params['user_driver']
                    )));
            }

            $model = new LdapLoginForm(\Yii::$app->params['ldap']);
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                return $this->goBack();
            } else {
                return $this->render('login', [
                    'isLdapLigin' => true,
                    'model' => $model,
                ]);
            }
        }elseif($userDriver == 'local'){
            $model = new LoginForm();
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                return $this->goBack();
            } else {
                return $this->render('login', [
                    'isLdapLigin' => false,
                    'model' => $model,
                ]);
            }
        }else{
            throw new \Exception(yii::t('walle', 'login type could not support', array(
                    'loginType' => \Yii::$app->params['user_driver']
                )));
        }
    }

    /**
     * User logout
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * User signup
     */
    public function actionSignup() {
        $userDriver = isset(\Yii::$app->params['user_driver']) == true && empty(\Yii::$app->params['user_driver']) == false ? \Yii::$app->params['user_driver'] : 'local';
        if ($userDriver != 'local') {
            throw new BadRequestHttpException(Yii::t('walle', 'the login type does not provide registration', array(
                    'loginType'=>$userDriver
                )));
        }

        $user = new User(['scenario' => 'signup']);
        if ($user->load(Yii::$app->request->post())) {
            $user->status = User::STATUS_ACTIVE;
            if ($user->save()) {
                Yii::$app->mail->compose('confirmEmail', ['user' => $user])
                    ->setFrom(Yii::$app->mail->messageConfig['from'])
                    ->setTo($user->email)
                    ->setSubject('瓦力平台 - ' . $user->realname)
                    ->send();
                Yii::$app->session->setFlash('user-signed-up');
                return $this->refresh();
            }
        }

        if (Yii::$app->session->hasFlash('user-signed-up')) {
            return $this->render('signedUp');
        }

        return $this->render('signup', [
            'model' => $user,
        ]);
    }

    /**
     * Confirm email
     */
    public function actionConfirmEmail($token)
    {
        $user = User::find()->emailConfirmationToken($token)->one();
        if ($user!==null && $user->removeEmailConfirmationToken(true)) {
            Yii::$app->getUser()->login($user);
            return $this->goHome();
        }

        return $this->render('emailConfirmationFailed');
    }

    /**
     * Request password reset
     */
    public function actionRequestPasswordReset()
    {
        $userDriver = isset(\Yii::$app->params['user_driver']) == true && empty(\Yii::$app->params['user_driver']) == false ? \Yii::$app->params['user_driver'] : 'local';
        if ($userDriver != 'local') {
            throw new BadRequestHttpException(Yii::t('walle', 'the login type does not provide security', array(
                    'loginType'=>$userDriver
                )));
        }

        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Reset password
     */
    public function actionResetPassword($token)
    {
        $userDriver = isset(\Yii::$app->params['user_driver']) == true && empty(\Yii::$app->params['user_driver']) == false ? \Yii::$app->params['user_driver'] : 'local';
        if ($userDriver != 'local') {
            throw new BadRequestHttpException(Yii::t('walle', 'the login type does not provide security', array(
                    'loginType'=>$userDriver
                )));
        }

        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }


    public function actionSearch() {

    }

    public function actionError() {
        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            return '';
        }

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }
        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($exception instanceof \Exception) {
            $message = $exception->getMessage();
        } else {
            $message = Yii::t('yii', 'An internal server error occurred.');
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            static::renderJson([], $code ?: -1, $message);
        } else {
            return $this->render('error', [
                'name' => $name,
                'message' => $message,
                'exception' => $exception,
            ]);
        }
    }
}
