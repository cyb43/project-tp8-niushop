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

namespace addon\shop\app\dict\delivery;

class ElectronicSheetDict
{

    /**
     * 获取邮费支付方式类型
     * @param $type
     * @return array|mixed|string
     */
    public static function getPayType($type = '')
    {
        $type_list = [
            1 => get_lang('dict_shop_delivery_electronic_sheet.cash_payment'),
            2 => get_lang('dict_shop_delivery_electronic_sheet.freight_collect'),
            3 => get_lang('dict_shop_delivery_electronic_sheet.monthly_statement')
        ];
        if ($type == '') return $type_list;
        return $type_list[ $type ] ?? '';
    }


    /**
     * 快递100 支付方式类型
     * @param $type
     * @return string|string[]
     */
    public static function getKd100PayType($type = '')
    {
        $type_list = [
            1 =>"SHIPPER",
            2 =>"CONSIGNEE",
            3 =>"THIRDPARTY"
        ];
        if ($type == '') return $type_list;
        return $type_list[ $type ] ?? '';
    }

}
