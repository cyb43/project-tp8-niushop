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

namespace addon\shop\app\listener\printer;

use addon\shop\app\model\order\Order;
use app\model\pay\Pay;
use app\model\sys\SysPrinterTemplate;
use app\service\core\printer\CorePrinterService;
use app\service\admin\sys\ConfigService;


/**
 * 商品订单小票打印内容
 * Class PrinterContentListener
 * @package addon\shop\app\listener\order
 */
class PrinterContentListener
{

    /**
     * @param $params
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function handle($params)
    {
        $key = 'shopGoodsOrder';
        if (!empty($params) && !empty($params[ 'type' ]) && $params[ 'type' ] != $key) return [];

        // 根据自身业务查询符合条件的打印机列表
        $printer_service = new CorePrinterService();
        $printer_list = $printer_service->getList([
            [ 'status', '=', 1 ],
            [ 'template_type', 'like', [ '%shopGoodsOrder%' ], 'or' ],
            [ 'trigger', 'like', [ '%shopGoodsOrder_trigger_pay_after%', '%shopGoodsOrder_trigger_take_delivery%', '%shopGoodsOrder_trigger_manual%' ], 'or' ],
        ], 'printer_id,brand,printer_name,printer_code,printer_key,open_id,apikey,value');

        if (empty($printer_list)) {
            return [
                'code' => -1,
                'message' => '未找到小票打印机'
            ];
        }

        // 查询订单信息
        $order_model = new Order();
        $field = 'order_id, order_no, order_from, out_trade_no, status, member_id,taker_name, taker_mobile, goods_money, delivery_money, discount_money, order_money, create_time, pay_time, finish_time, delivery_type, take_store_id, taker_name, taker_mobile, taker_full_address, member_remark, shop_remark';
        $order_info = $order_model->where([ [ 'order_id', '=', $params[ 'business' ][ 'order_id' ] ] ])->field($field)
            ->with(
                [
                    'order_goods' => function($query) {
                        $query->field('order_goods_id, order_id, goods_id, sku_id, goods_name, sku_name, price, num, goods_money, discount_money, status, order_goods_money, original_price')->append([ 'status_name' ]);
                    },
                    'member' => function($query) {
                        $query->field('member_id,nickname,mobile,balance,point');
                    }
                ])->append([ 'order_from_name', 'status_name', 'delivery_type_name' ])->findOrEmpty()->toArray();

        if (empty($order_info)) {
            return [
                'code' => -1,
                'message' => '未找到订单信息'
            ];
        }

        if ($order_info[ 'out_trade_no' ]) {
            $order_info[ 'pay' ] = ( new Pay() )->where([ [ 'out_trade_no', '=', $order_info[ 'out_trade_no' ] ] ])
                ->field('out_trade_no, type, pay_time')->append([ 'type_name' ])->findOrEmpty()->toArray();
        }

        $res_data = [];

        $printer_template_model = new SysPrinterTemplate();

        // 拼装打印内容
        foreach ($printer_list as $k => $v) {

            if (!empty($v[ 'value' ] [ $params[ 'type' ] ]) && !empty($v[ 'value' ] [ $params[ 'type' ] ] [ 'trigger_' . $params[ 'trigger' ] ])) {
                $info = $v[ 'value' ] [ $params[ 'type' ] ] [ 'trigger_' . $params[ 'trigger' ] ];

                // 过滤不符合条件的模板
                if (in_array($order_info[ 'delivery_type' ], $info[ 'print_delivery_type' ]) == false) {
                    continue;
                }

                // 查询小票模板内容
                $template_info = $printer_template_model->field('value')->where([
                    [ 'template_id', "=", $info[ 'template_id' ] ],
                    [ 'template_type', '=', $params[ 'type' ] ]
                ])->findOrEmpty()->toArray();

                if (empty($template_info) && empty($template_info[ 'value' ])) {
                    continue;
                }

                $template_info_value = $template_info[ 'value' ];

                $content = "<MN>" . $info[ 'print_num' ] . "</MN>";

                // 获取 票头信息内容
                $content .= $this->getTicketHeadContent($template_info_value[ 'head_info' ]);

                // 获取 订单信息内容
                $content .= $this->getOrderInfoContent($template_info_value[ 'order_info' ], $order_info);

                // 获取 商品信息内容
                $content .= $this->getGoodsInfoContent($template_info_value[ 'goods_info' ], $order_info);

                // 获取 买家/收货信息内容
                $content .= $this->getMemberInfoContent($template_info_value[ 'buyer_info' ], $order_info);

                // 获取 底部信息内容
                $content .= $this->getBottomInfoContent($template_info_value[ 'bottom_info' ]);

                $item = [
                    'printer_info' => $v,
                    'origin_id' => $order_info[ 'order_no' ], // 内部订单号(32位以内)
                    'content' => $content
                ];

                $res_data[] = $item;

            }

        }

        return [
            'code' => 0,
            'message' => '',
            'data' => $res_data
        ];
    }

    /**
     * 获取票头信息内容
     * @param $data
     * @return string
     */
    private function getTicketHeadContent($data)
    {

        $content = '';

        // 小票名称
        if ($data[ 'ticket_name' ][ 'status' ] == 1 && !empty($data[ 'ticket_name' ][ 'value' ])) {

            // 文字大小
            if ($data[ 'ticket_name' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'ticket_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= "<center>";
            $content .= $data[ 'ticket_name' ][ 'value' ];
            $content .= "</center>";

            if ($data[ 'ticket_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'ticket_name' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= str_repeat('.', 32);
        }

        $site_info = ( new ConfigService() )->getWebSite();

        // 商城名称
        if ($data[ 'shop_name' ][ 'status' ] == 1 && !empty($site_info[ 'site_name' ])) {

            // 文字大小
            if ($data[ 'shop_name' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'shop_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= "<center>";
            $content .= $site_info[ 'site_name' ];
            $content .= "</center>";

            if ($data[ 'shop_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'shop_name' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= str_repeat('.', 32);
        }

        return $content;
    }

    /**
     * 获取订单信息内容
     * @param $data
     * @param $order_info
     * @return string
     */
    private function getOrderInfoContent($data, $order_info)
    {
        $content = '';

        // 订单基础信息
        if ($data[ 'order_item' ][ 'status' ] == 1 && !empty($data[ 'order_item' ][ 'value' ])) {

            // 订单编号
            if (in_array('order_no', $data[ 'order_item' ][ 'value' ])) {
                $content .= '订单编号：' . $order_info[ 'order_no' ] . "\n";
            }

            // 订单来源
            if (in_array('order_from', $data[ 'order_item' ][ 'value' ])) {
                $content .= '订单来源：' . $order_info[ 'order_from_name' ] . "\n";
            }

            // 订单状态
            if (in_array('order_status', $data[ 'order_item' ][ 'value' ])) {
                $content .= '订单状态：' . $order_info[ 'status_name' ][ 'name' ] . "\n";
            }

            // 支付方式
            if (in_array('pay_type', $data[ 'order_item' ][ 'value' ])) {
                $content .= '支付方式：' . $order_info[ 'pay' ][ 'type_name' ] . "\n";
            }

            // 配送方式
            if (in_array('delivery_type', $data[ 'order_item' ][ 'value' ])) {
                $content .= '配送方式：' . $order_info[ 'delivery_type_name' ] . "\n";
            }

            // 下单时间
            if (in_array('create_time', $data[ 'order_item' ][ 'value' ])) {
                $content .= '下单时间：' . $order_info[ 'create_time' ] . "\n";
            }

            // 付款时间
            if (in_array('pay_time', $data[ 'order_item' ][ 'value' ])) {
                $content .= '付款时间：' . $order_info[ 'pay_time' ] . "\n";
            }

            // 商品总额
            if (in_array('goods_money', $data[ 'order_item' ][ 'value' ]) && $order_info[ 'goods_money' ] > 0) {
                $content .= '商品总额：￥' . $order_info[ 'goods_money' ] . "\n";
            }

            // 优惠金额
            if (in_array('discount_money', $data[ 'order_item' ][ 'value' ]) && $order_info[ 'discount_money' ] > 0) {
                $content .= '优惠金额：￥' . $order_info[ 'discount_money' ] . "\n";
            }

            // 配送金额
            if (in_array('delivery_money', $data[ 'order_item' ][ 'value' ]) && $order_info[ 'delivery_money' ] > 0) {
                $content .= '配送金额：￥' . $order_info[ 'delivery_money' ] . "\n";
            }

        }

        // 实付金额
        if ($data[ 'order_money' ][ 'status' ] == 1) {

            // 文字大小
            if ($data[ 'order_money' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'order_money' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= '实付金额：￥' . $order_info[ 'order_money' ];

            if ($data[ 'order_money' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'order_money' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "\n";
        }

        // 商家备注
        if ($data[ 'shop_remark' ][ 'status' ] == 1 && !empty($order_info[ 'shop_remark' ])) {

            // 文字大小
            if ($data[ 'shop_remark' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'shop_remark' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= '商家备注：' . $order_info[ 'shop_remark' ] . "\n";

            if ($data[ 'shop_remark' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'shop_remark' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }
        }

        if ($data[ 'order_item' ][ 'status' ] == 1 || $data[ 'order_money' ][ 'status' ] == 1 || $data[ 'shop_remark' ][ 'status' ] == 1) {
            $content .= str_repeat('.', 32);
        }

        return $content;
    }

    /**
     * 获取 商品信息内容
     * @param $data
     * @param $order_info
     * @return string
     */
    private function getGoodsInfoContent($data, $order_info)
    {
        $content = '';

        $content .= "<table>";
        $content .= "<tr>";

        $content .= "<td>商品名称</td>";
        $content .= "<td></td>";
        $content .= "<td>数量</td>";

        if ($data[ 'goods_price' ][ 'status' ] == 1) {
            $content .= "<td>金额</td>";
        }

        $content .= "</tr>";
        $content .= "</table>\n";

        $content .= "<table>";
        foreach ($order_info[ 'order_goods' ] as $goods_k => $goods_v) {

            $content .= "<tr>";

            $content .= "<td>";

            // 文字大小
            if ($data[ 'goods_name' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'goods_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= $goods_v[ 'goods_name' ] . ' ' . $goods_v[ 'sku_name' ];

            // 文字加粗
            if ($data[ 'goods_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'goods_name' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "</td>";

            $content .= "<td></td>";
            $content .= "<td>x" . $goods_v[ 'num' ] . "</td>";

            if ($data[ 'goods_price' ][ 'status' ] == 1) {
                $content .= "<td>";

                // 文字大小
                if ($data[ 'goods_price' ][ 'fontSize' ] == 'big') {
                    $content .= "<FH2>";
                }

                // 文字加粗
                if ($data[ 'goods_price' ][ 'fontWeight' ] == 'bold') {
                    $content .= "<FB>";
                }

                $content .= "￥" . $goods_v[ 'price' ];

                // 文字加粗
                if ($data[ 'goods_price' ][ 'fontWeight' ] == 'bold') {
                    $content .= "</FB>";
                }

                // 文字大小
                if ($data[ 'goods_price' ][ 'fontSize' ] == 'big') {
                    $content .= "</FH2>";
                }

                $content .= "</td>";
            }
            $content .= "</tr>";

        }

        $content .= "</table>\n";
        $content .= str_repeat('.', 32);
        return $content;
    }

    /**
     * 获取 买家/收货信息内容
     * @param $data
     * @param $order_info
     * @return string
     */
    private function getMemberInfoContent($data, $order_info)
    {
        $content = '';

        // 会员基础信息
        if ($data[ 'member_basic_info' ][ 'status' ] == 1) {

            // 买家昵称
            if (in_array('nickname', $data[ 'member_basic_info' ][ 'value' ])) {
                $content .= '会员昵称：' . $order_info[ 'member' ][ 'nickname' ] . "\n";
            }

            // 账户余额
            if (in_array('account_balance', $data[ 'member_basic_info' ][ 'value' ])) {
                $content .= '账户余额：￥' . $order_info[ 'member' ][ 'balance' ] . "\n";
            }

            // 账户积分
            if (in_array('account_point', $data[ 'member_basic_info' ][ 'value' ])) {
                $content .= '账户积分：' . $order_info[ 'member' ][ 'point' ] . "\n";
            }

        }

        // 买家手机号
        if ($data[ 'buyer_mobile' ][ 'status' ] == 1 && !empty($order_info[ 'member' ][ 'mobile' ])) {

            // 文字大小
            if ($data[ 'buyer_mobile' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'buyer_mobile' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            if ($data[ 'buyer_mobile' ][ 'value' ] == 'yes') {
                $content .= '会员手机号：' . substr($order_info[ 'member' ][ 'mobile' ], 0, 3) . '****' . substr($order_info[ 'member' ][ 'mobile' ], 6 + 1);
            } else {
                $content .= '会员手机号：' . $order_info[ 'member' ][ 'mobile' ];
            }

            if ($data[ 'buyer_mobile' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'buyer_mobile' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "\n";

            $content .= str_repeat('.', 32);
        }

        // 收货人姓名
        if ($data[ 'taker_name' ][ 'status' ] == 1 && !empty($order_info[ 'taker_name' ])) {

            // 文字大小
            if ($data[ 'taker_name' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'taker_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= '收货人：' . $order_info[ 'taker_name' ];

            if ($data[ 'taker_name' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'taker_name' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "\n";

        }

        // 收货人手机号
        if ($data[ 'taker_mobile' ][ 'status' ] == 1) {

            // 文字大小
            if ($data[ 'taker_mobile' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'taker_mobile' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            if ($data[ 'taker_mobile' ][ 'value' ] == 'yes') {
                $content .= '联系方式：' . substr($order_info[ 'taker_mobile' ], 0, 3) . '****' . substr($order_info[ 'taker_mobile' ], 6 + 1);
            } else {
                $content .= '联系方式：' . $order_info[ 'taker_mobile' ];
            }

            if ($data[ 'taker_mobile' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'taker_mobile' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "\n";

        }

        // 收货人地址
        if ($data[ 'taker_full_address' ][ 'status' ] == 1) {

            // 文字大小
            if ($data[ 'taker_full_address' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'taker_full_address' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= '收货地址：' . $order_info[ 'taker_full_address' ];

            if ($data[ 'taker_full_address' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'taker_full_address' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "\n";

        }

        // 买家备注
        if ($data[ 'member_remark' ][ 'status' ] == 1 && !empty($order_info[ 'member_remark' ])) {

            // 文字大小
            if ($data[ 'member_remark' ][ 'fontSize' ] == 'big') {
                $content .= "<FH2>";
            }

            // 文字加粗
            if ($data[ 'member_remark' ][ 'fontWeight' ] == 'bold') {
                $content .= "<FB>";
            }

            $content .= '买家备注：' . $order_info[ 'member_remark' ];

            if ($data[ 'member_remark' ][ 'fontWeight' ] == 'bold') {
                $content .= "</FB>";
            }

            // 文字大小
            if ($data[ 'member_remark' ][ 'fontSize' ] == 'big') {
                $content .= "</FH2>";
            }

            $content .= "\n";
        }

        if ($data[ 'member_basic_info' ][ 'status' ] == 1 || $data[ 'buyer_mobile' ][ 'status' ] == 1 ||
            $data[ 'taker_name' ][ 'status' ] == 1 || $data[ 'taker_mobile' ][ 'status' ] == 1 ||
            $data[ 'taker_full_address' ][ 'status' ] == 1 || $data[ 'member_remark' ][ 'status' ] == 1) {
            $content .= "\n";
        }

        return $content;
    }

    /**
     * 获取 底部信息内容
     * @param $data
     * @return string
     */
    private function getBottomInfoContent($data)
    {
        $content = '';

        // 二维码
        if ($data[ 'qrcode' ][ 'status' ] == 1) {
            $content .= "<QR>" . $data[ 'qrcode' ][ 'value' ] . "</QR>\n\n";
        }

        // 小票打印时间
        if ($data[ 'ticket_print_time' ][ 'status' ] == 1) {
            $content .= '<center>打印时间：' . date('Y-m-d H:i:s', time()) . "</center>\n";
        }

        // 底部信息
        if ($data[ 'bottom_remark' ][ 'status' ] == 1) {
            $content .= "<center>" . $data[ 'bottom_remark' ][ 'value' ] . "</center>\n";
        }

        return $content;
    }

}