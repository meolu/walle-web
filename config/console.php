<?php
// Merged web + local configuration is available in $web
return [
    'id' => 'console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\console',
    'extensions' => $web['extensions'],
    'components' => [
        'db' => $web['components']['db'],
        'mongodb' => [
            'class' => 'yii\mongodb\Connection',
            'dsn'   => 'mongodb://localhost:27017/local',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'example' => [
            'class' => 'console\controllers\ExampleController',
        ],
    ],
    'params' => $web['params'],
];
