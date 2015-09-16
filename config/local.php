<?php
// Uncomment to enable debug mode. Recommended for development.
define('YII_DEBUG', false);

// Uncomment to enable dev environment. Recommended for development
define('YII_ENV', 'prod');
function d($var) {
    var_dump($var);
}
function dd($var) {
    die(var_dump($var));
}

return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'PdXWDAfV5-gPJJWRar5sEN71DN0JcDRV',
        ],
    ],
];
