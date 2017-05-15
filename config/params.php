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
        'weixin' => [//微信消息 企业号
            'host' => isset($_ENV['WALLE_MESSAGE_WEIXIN_HOST']) ? $_ENV['WALLE_MESSAGE_WEIXIN_HOST'] : 'https://qyapi.weixin.qq.com',//接口host
            'corpid' => isset($_ENV['WALLE_MESSAGE_WEIXIN_CORPID']) ? $_ENV['WALLE_MESSAGE_WEIXIN_CORPID'] : 'wx437fe4a3eed11',//企业id
            'corpsecret' => isset($_ENV['WALLE_MESSAGE_WEIXIN_CORPSECRET']) ? $_ENV['WALLE_MESSAGE_WEIXIN_CORPSECRET'] : 'hiahxZ2qhYHl1NLN_rjeK53_lt3dL94o6iwwhvOMLPJI20VuXWHkeaQLNs3',
            'sendUrl' => isset($_ENV['WALLE_MESSAGE_WEIXIN_SEND_URL']) ? $_ENV['WALLE_MESSAGE_WEIXIN_SEND_URL'] : '/cgi-bin/message/send?access_token=',//消息推送url
            'tokenUrl' => isset($_ENV['WALLE_MESSAGE_WEIXIN_TOKEN_URL']) ? $_ENV['WALLE_MESSAGE_WEIXIN_TOKEN_URL'] : '/cgi-bin/gettoken',//token 获取url
            'msgtype' => isset($_ENV['WALLE_MESSAGE_WEIXIN_MSGTYPE']) ? $_ENV['WALLE_MESSAGE_WEIXIN_MSGTYPE'] : 'text',//消息类型
            'touser' => isset($_ENV['WALLE_MESSAGE_WEIXIN_TOUSER']) ? $_ENV['WALLE_MESSAGE_WEIXIN_TOUSER'] : '@all',//标签ID列表
            'totag' => isset($_ENV['WALLE_MESSAGE_WEIXIN_TOTAG']) ? $_ENV['WALLE_MESSAGE_WEIXIN_TOTAG'] : 'wx_send_msg_tag',//标签ID列表
            'toparty' => isset($_ENV['WALLE_MESSAGE_WEIXIN_TOPARTY']) ? $_ENV['WALLE_MESSAGE_WEIXIN_TOPARTY'] : '@all',//     部门ID列表
            'agentid' => isset($_ENV['WALLE_MESSAGE_WEIXIN_AGENT_ID']) ? $_ENV['WALLE_MESSAGE_WEIXIN_AGENT_ID'] : 2,//企业应用ID
            'safe' => isset($_ENV['WALLE_MESSAGE_WEIXIN_SAFE']) ? 1 : 0,//是否加密发送
        ],
    ],
];
