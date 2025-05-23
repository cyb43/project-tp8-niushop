<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\dict\sys;

/**
 * 备份记录字典
 */
class BackupDict
{
    const STATUS_READY = 'ready'; //准备执行

    const STATUS_COMPLETE = 'complete'; // 完成

    const STATUS_FAIL = 'fail'; // 失败

    public static function getStatus($status)
    {
        $status_list = [
            self::STATUS_READY => get_lang('dict_backup.ready'),
            self::STATUS_COMPLETE => get_lang('dict_backup.complete'),
            self::STATUS_FAIL => get_lang('dict_backup.fail'),
        ];
        return $status_list[ $status ] ?? '';
    }
}
