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
 * 升级记录字典
 * Class UpgradeRecordsDict
 * @package app\dict\sys
 *
 */
class UpgradeDict
{
    const STATUS_READY = 'ready'; //准备执行

    const STATUS_COMPLETE = 'complete'; // 完成

    const STATUS_FAIL = 'fail'; // 失败

    const STATUS_CANCEL = 'cancel';

    public static function getStatus($status)
    {
        $status_list = [
            self::STATUS_READY => get_lang('dict_upgrade.ready'),
            self::STATUS_COMPLETE => get_lang('dict_upgrade.complete'),
            self::STATUS_FAIL => get_lang('dict_upgrade.fail'),
            self::STATUS_CANCEL => get_lang('dict_upgrade.cancel'),
        ];
        return $status_list[ $status ] ?? '';
    }
}
