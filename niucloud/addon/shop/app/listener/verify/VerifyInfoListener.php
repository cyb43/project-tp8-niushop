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

namespace addon\shop\app\listener\verify;


use addon\shop\app\model\order\OrderGoods;

/**
 * 核销详情
 */
class VerifyInfoListener
{
    /**
     * @param array $params
     * @return array|void
     */
    public function handle($params = [])
    {
        $info = [];
        if ($params[ 'type' ] == 'shopVirtualGoods') {
            $order_goods_id = $params[ 'relate_tag' ];
            $order_goods_info = ( new OrderGoods() )->where([ [ 'order_goods_id', '=', $order_goods_id ], [ 'is_verify', '=', 1 ] ])->findOrEmpty()->toArray();
            if ($order_goods_info) {
                $info = [
                    [
                        'name' => '核销次数/总次数',
                        'value' => $order_goods_info[ 'verify_count' ] . '/' . $order_goods_info[ 'num' ]
                    ]
                ];
            }
        } else if ($params[ 'type' ] == 'shopPickUpOrder') {
            // todo 自提订单核销 后续做了拆分处理再添加逻辑
            $info = [
                [
                    'name' => '核销次数/总次数',
                    'value' => '1/1'
                ]
            ];
        }
        return $info;
    }
}
