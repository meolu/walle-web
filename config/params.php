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
    'message-release' => [ //推送发布提醒消息
        'weixin' => [
            'host' => 'https://qyapi.weixin.qq.com',//接口host
            'sendUrl' => '/cgi-bin/message/send?access_token=',//消息推送url
            'tokenUrl' => '/cgi-bin/gettoken',//token 获取url
            'msgtype' => 'text',//消息类型
            //以上配置谨慎修改

            //以下配置可按自己业务修改
            'isOpen'=> true,//是否开启
            'corpid' => 'wx68b034ed',//企业id
            'corpsecret' => 'hiahxZ2qhYHl1NLN_rjeK53_lQZFoe6iwwhvOMLPJI20VuXWHkeaQLNs3',
            'touser' => '@all',//标签ID列表
            'totag' => 'wx_send_msg_tag',//标签ID列表
            'toparty' => '@all',//     部门ID列表
            'agentid' =>  2,//企业应用ID
            'safe' => 0,//是否加密发送 0否 1是
        ],
    ],
];
