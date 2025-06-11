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

namespace addon\shop\app\dict\active;

class DiscountDict
{
    //活动状态
    const NOT_ACTIVE = 'not_active';//活动未开始
    const ACTIVE = 'active';//活动进行中
    const END = 'end';//活动已结束
    const CLOSE = 'close';//活动已关闭

    //商品是否参与
    const YES = 1;
    const NO = 0;



    /**
     * 状态
     * @param $status
     * @return array|mixed|string
     */
    public static function getStatus($status = '')
    {
        $list = [
            self::NOT_ACTIVE => get_lang('dict_shop_active_status.not_active'),
            self::ACTIVE => get_lang('dict_shop_active_status.active'),
            self::END => get_lang('dict_shop_active_status.end'),
            self::CLOSE => get_lang('dict_shop_active_status.close'),
        ];
        if ($status == '') return $list;
        return $list[ $status ] ?? '';
    }



}
