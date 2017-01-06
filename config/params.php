<?php
/**
 * 亲，为方便大家，已经把必须修改为自己配置的选项已经带上*****了
 * 此配置为测试配置，如果你不想消息泄露，请尽快修改为自己的邮箱smtp
 */

return [
    'user.passwordResetTokenExpire'     => 3600,
    'user.emailConfirmationTokenExpire' => 43200, // 5 days有效

    // 头像图片后缀
    'user.avatar.extension'             => [
        'jpg',
        'png',
        'jpeg',
    ],

    // *******操作日志目录*******
    'log.dir'                           => isset($_ENV['WALLE_LOG_PATH']) ? $_ENV['WALLE_LOG_PATH'] : '/tmp/walle/',
    // *******Ansible Hosts 主机列表目录*******
    'ansible_hosts.dir'                 => isset($_ENV['WALLE_ANSIBLE_HOSTS_DIR']) ? $_ENV['WALLE_ANSIBLE_HOSTS_DIR'] : realpath(__DIR__ . '/../runtime') . '/ansible_hosts/',
    // *******指定公司邮箱后缀*******
    'mail-suffix'                       => [
        '*', # 支持多个
    ],
    'user_driver'                       => 'local',
    'ldap'                              => [
        'host'           => '127.0.0.1',
        'port'           => 389,
        'username'       => 'cn=root,dc=example,dc=com',
        'password'       => 'password',
        'accountBase'    => 'dc=example,dc=com',
        'accountPattern' => '(&(objectClass=inetOrgPerson)(cn=${username}))',
        'identity'       => 'uid',
        'attributesMap'  => [
            'uid'       => 'username',
            'mail'      => 'email',
            'givenName' => 'realname',
        ],
        'ssl'            => false,
    ],
];
