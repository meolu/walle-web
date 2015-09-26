<?php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'controllerNamespace' => 'app\controllers',
    'defaultRoute' => 'walle/index',
    'components' => [
        'db' => [
            'class'     => 'yii\db\Connection',
            'dsn'       => 'mysql:host=127.0.0.1;dbname=test',
            'username'  => 'username',
            'password'  => 'password',
            'charset'   => 'utf8',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'ip or host',               # smtp 发件地址
                'username' => 'admin@huamanshu.com',  # smtp 发件用户名
                'password' => 'password',             # smtp 发件人的密码
                'port' => 25,                         # smtp 端口
                'encryption' => 'tls',                # smtp 协议
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['admin@humanshu.com' => '花满树出品']
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules'=>[
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],
    ],
    'language' => 'zh-CN',
    'bootstrap' => [
        'app\components\EventBootstrap',
    ],
    'params' => require(__DIR__.'/params.php'),
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];
    $config['modules']['gii'] =  [
        'class' => 'yii\gii\Module',
        'generators' => [
            'mongoDbModel' => [
                'class' => 'yii\mongodb\gii\model\Generator'
            ],
        ],
    ];
}

return $config;
