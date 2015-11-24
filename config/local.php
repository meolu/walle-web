<?php
// Uncomment to enable debug mode. Recommended for development.
define('YII_DEBUG', false);

// Uncomment to enable dev environment. Recommended for development
define('YII_ENV', 'prod');

// zh_CN.UTF-8 => 中文,  en_US.UTF-8 => English
setlocale(LC_ALL, 'zh_CN.UTF-8');
putenv('LC_ALL=zh_CN.UTF-8');

return [
    'components' => [
    ],
];
