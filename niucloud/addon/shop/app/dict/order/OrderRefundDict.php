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

namespace addon\shop\app\dict\order;


/**
 * 订单退款相关枚举类
 */
class OrderRefundDict
{
    /***************************************************** 退款相关状态 ****************************************************/
    // 买家发起售后申请,等待商家处理
    const BUYER_APPLY_WAIT_STORE = 1;
    //商家同意售后申请，等待买家处理
    const STORE_AGREE_REFUND_GOODS_APPLY_WAIT_BUYER = 2;
    //商家不同意售后申请，等待买家处理
    const STORE_REFUSE_REFUND_GOODS_APPLY_WAIT_BUYER = 3;
    //买家已退货，等待商家确认收货
    const BUYER_REFUND_GOODS_WAIT_STORE = 4;
    //商家拒绝收货，等待买家处理
    const STORE_REFUSE_TAKE_REFUND_GOODS_WAIT_BUYER = 5;

    //商家已同意售后申请，等待商家转账
    const STORE_AGREE_REFUND_WAIT_TRANSFER = 6;
    //商家转账中
    const STORE_REFUND_TRANSFERING = 7;
    //售后成功
    const FINISH = 8;
    //售后关闭
    const CLOSE = -1;

    //商家主动关闭售后
    const SHOP_ACTIVE_CLOSE_REFUND = -3;
    //部分退款
    const PARTIAL_REFUND = -2;

    const ONLY_REFUND = 1;//仅退款
    const RETURN_AND_REFUND_GOODS = 2;//退货退款



    const APPLY = 1;//退货退款
    const ACTIVE_REFUND = 2;
    const APPLY_ACTION = 'apply';//申请退款
    const EDIT_APPLY_ACTION = 'edit_apply';//修改退款
    const DELIVERY_ACTION = 'delivery';
    const EDIT_DELIVERY_ACTION = 'edit_delivery';
    const AGREE_AUDIT_APPLY_ACTION = 'agree_audit_apply';//买家申请退款
    const REFUSE_AUDIT_APPLY_ACTION = 'refuse_audit_apply';//买家修改退款
    const AGREE_AUDIT_REFUND_GOODS_ACTION = 'agree_audit_refund_goods';//买家退货
    const REFUSE_AUDIT_REFUND_GOODS_ACTION = 'refuse_audit_refund_goods';//买家修改退货
    const ACTIVE_REFUND_ACTION = 'active_refund';//卖家同意退款
    const FINISH_ACTION = 'finish';//卖家拒绝退款
    const CLOSE_ACTION = 'close';//卖家同意退货
    const SHOP_ACTIVE_REFUND_ACTION = 'shop_active_refund';//商家主动退款

    /**
     * 获取售后状态
     * @param string $status
     * @return array|array[]
     */
    public static function getRefundStatus(string $status = '')
    {
        $data = [
            self::BUYER_APPLY_WAIT_STORE => get_lang('dict_shop_order_refund_status.buyer_apply_wait_store'),
            self::STORE_AGREE_REFUND_GOODS_APPLY_WAIT_BUYER => get_lang('dict_shop_order_refund_status.store_agree_refund_goods_apply_wait_buyer'),
            self::STORE_REFUSE_REFUND_GOODS_APPLY_WAIT_BUYER => get_lang('dict_shop_order_refund_status.store_refuse_refund_goods_apply_wait_buyer'),
            self::BUYER_REFUND_GOODS_WAIT_STORE => get_lang('dict_shop_order_refund_status.buyer_refund_goods_wait_store'),
            self::STORE_REFUSE_TAKE_REFUND_GOODS_WAIT_BUYER => get_lang('dict_shop_order_refund_status.store_refuse_take_refund_goods_wait_buyer'),
            self::STORE_AGREE_REFUND_WAIT_TRANSFER => get_lang('dict_shop_order_refund_status.store_agree_refund_wait_transfer'),
            self::STORE_REFUND_TRANSFERING => get_lang('dict_shop_order_refund_status.store_refund_transfering'),
            self::FINISH => get_lang('dict_shop_order_refund_status.finish'),
            self::CLOSE => get_lang('dict_shop_order_refund_status.close'),
            self::SHOP_ACTIVE_CLOSE_REFUND => get_lang('dict_shop_order_refund_status.shop_active_close_refund'),
        ];
        if ($status == '') {
            return $data;
        }
        return $data[$status] ?? [];
    }//卖家拒绝退货

    public static function getRefundType(string $type = '')
    {
        $data = [
            self::ONLY_REFUND => get_lang('dict_shop_order_refund_type.only_refund'),
            self::RETURN_AND_REFUND_GOODS => get_lang('dict_shop_order_refund_type.return_and_refund_goods'),
        ];
        if (!$type) {
            return $data;
        }
        return $data[$type] ?? [];
    }//卖家主动退款

    /**
     * 退款来源方式
     * @param string $type
     * @return array|mixed
     */
    public static function getSource(string $type = '')
    {
        $data = [
            self::APPLY => get_lang('dict_shop_order_refund_source.apply'),
            self::ACTIVE_REFUND => get_lang('dict_shop_order_refund_source.active_refund'),
        ];
        if (!$type) {
            return $data;
        }
        return $data[$type] ?? [];
    }//退款完成

    /**
     * 拒绝理由
     * @return array
     */
    public static function getRefundReason()
    {
        return [
            get_lang('dict_shop_order_refund_reason.delivery_timeout'),//未按约定时间发货
            get_lang('dict_shop_order_refund_reason.buy_more_or_dislike'),//拍错/多拍/不喜欢
            get_lang('dict_shop_order_refund_reason.negotiation_completed'),//协商一致退款
            get_lang('dict_shop_order_refund_reason.other'),//其他
        ];
    }//关闭退款

    /**
     * 退款操作类型
     * @param string $type
     * @return array|mixed|string
     */
    public static function getActionType(string $type = '')
    {
        $data = [
            self::APPLY_ACTION => get_lang('dict_shop_order_refund_action.apply'),
            self::EDIT_APPLY_ACTION  => get_lang('dict_shop_order_refund_action.edit_apply'),
            self::CLOSE_ACTION => get_lang('dict_shop_order_refund_action.close'),
            self::DELIVERY_ACTION => get_lang('dict_shop_order_refund_action.delivery'),
            self::EDIT_DELIVERY_ACTION => get_lang('dict_shop_order_refund_action.edit_delivery'),
            self::AGREE_AUDIT_APPLY_ACTION => get_lang('dict_shop_order_refund_action.agree_audit_apply'),
            self::REFUSE_AUDIT_APPLY_ACTION => get_lang('dict_shop_order_refund_action.refuse_audit_apply_action'),
            self::AGREE_AUDIT_REFUND_GOODS_ACTION => get_lang('dict_shop_order_refund_action.agree_audit_refund_goods'),
            self::REFUSE_AUDIT_REFUND_GOODS_ACTION => get_lang('dict_shop_order_refund_action.refuse_audit_refund_goods'),
            self::FINISH_ACTION => get_lang('dict_shop_order_refund_action.finish'),
            self::ACTIVE_REFUND_ACTION => get_lang('dict_shop_order_refund_action.active_refund'),
            self::SHOP_ACTIVE_REFUND_ACTION => get_lang('dict_shop_order_refund_action.shop_active_refund'),
        ];
        if (!$type) {
            return $data;
        }
        return $data[$type] ?? '';
    }
}
