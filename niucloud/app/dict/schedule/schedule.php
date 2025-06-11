<?php

return [
    [
        'key' => 'order_close',
        'name' => '未支付订单自动关闭',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => '',
        'function' => ''
    ],
    [
        'key' => 'auto_clear_schedule_log',
        'name' => '定时清理计划任务日志表',
        'desc' => '',
        'time' => [
            'type' => 'day',
            'day' => 1,
            'hour' => 1,
            'min' => 1
        ],
        'class' => 'app\job\schedule\AutoClearScheduleLog',
        'function' => ''
    ],
    [
        'key' => 'transfer_check_finish',
        'name' => '检验在线转账是否处理完毕',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 5
        ],
        'class' => 'app\job\transfer\schedule\CheckFinish',
        'function' => ''
    ],
    [
        'key' => 'auto_clear_upgrade_records',
        'name' => '定时清理升级/备份记录表',
        'desc' => '',
        'time' => [
            'type' => 'day',
            'day' => 1,
            'hour' => 1,
            'min' => 1
        ],
        'class' => 'app\job\upgrade\AutoClearUpgradeRecords',
        'function' => ''
    ],[
        'key' => 'auto_clear_user_log',
        'name' => '定时清理用户操作日志',
        'desc' => '',
        'time' => [
            'type' => 'day',
            'day' => 1,
            'hour' => 1,
            'min' => 1
        ],
        'class' => 'app\job\sys\ClearUserLog',
        'function' => ''
    ],
];
