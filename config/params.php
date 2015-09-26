<?php
/**
 * Application parameters
 */
return [
    'support.email' => 'admin@huamanshu.com',
    'support.name' => 'admin',

    'user.passwordResetTokenExpire' => 3600,
    'user.emailConfirmationTokenExpire' => 43200, // 5 days

    'config.dir' => '/deploy.yml/env',
    'mail-suffix' => [
        '公司邮箱后缀.com', //  限制只有公司同学可注册，可多个
    ]
];
