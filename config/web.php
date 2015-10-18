<?php
$config = [
    'id'                  => 'basic',
    'basePath'            => dirname(__DIR__),
    'timeZone'            => 'Asia/Shanghai',
    'extensions'          => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'controllerNamespace' => 'app\controllers',
    'defaultRoute'        => 'task/index',
    'components'          => [
        'db'           => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=127.0.0.1;dbname=walle',
            'username' => 'root',
            'password' => 'whoiam',
            'charset'  => 'utf8',
        ],
        'session'      => [
            'class'        => 'yii\web\DbSession',
            'db'           => 'db',
            'sessionTable' => 'session',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mail'         => [
            'class'            => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport'        => [
                'class'      => 'Swift_SmtpTransport',
                'host'       => 'smtp.exmail.qq.com',
                // 此邮箱为花满树同学为大家提供的测试邮箱，请尽快更换为自己的企业邮箱smtp
                'username'   => 'service@huamanshu.com',
                // 也请不要修改密码哦，浪费大家时间：(
                'password'   => 'K84erUuxg1bHqrfD',
                'port'       => 25,
                'encryption' => 'tls',
            ],
            'messageConfig'    => [
                'charset' => 'UTF-8',
                'from'    => ['admobi@social-touch.com' => '时趣广告 - Walle']
            ],
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'user'         => [
            'identityClass'   => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'i18n'         => [
            'translations' => [
                '*' => [
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                ],
            ],
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'language'            => 'zh-CN',
    'bootstrap'           => [
        'app\components\EventBootstrap',
    ],
    'params'              => require(__DIR__ . '/params.php'),
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class'      => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];
    $config['modules']['gii'] = [
        'class'      => 'yii\gii\Module',
    ];
}

return $config;
