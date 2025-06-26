<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author=>Niucloud Team
// +----------------------------------------------------------------------
namespace addon\shop\app\service\core\delivery\third_delivery;


use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderCancelRequest;
use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderCreateByFeeOrderNoRequest;
use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderCreateRequest;
use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderFeeRequest;
use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderInfoRequest;
use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderTransporterPositionH5Request;
use addon\shop\app\service\core\delivery\third_delivery\sdk\Dada\OrderTransporterPositionRequest;
use think\facade\Log;

class Dada extends BaseThirdDelivery
{
    public $app_secret;//App_secret
    public $app_key;//App_key
    public $shop_id;//商户ID
    public $shop_store_no;//门店编号
    public $config;

    public function initialize(array $config = [])
    {
        $this->app_secret = $config['app_secret'];
        $this->app_key = $config['app_key'];
        $this->shop_id = $config['shop_id'];
        $this->shop_store_no = $config['shop_store_no'];
        $this->config = $config;
    }

    /**
     * ADMINAPI 发货使用
     * @param $data
     * @return array
     */
    public function createOrder($data)
    {
        $order_data = $data['order_data'];
        $order_goods_data = $data['order_goods_data'];
        $order_no = $order_data->order_no;
//        return [//调试数据
//            'delivery_price' => 100,//$res['fee'],
//            'third_order_no' => $order_no,//$res['fee'],
//        ];
        $request = new OrderCreateRequest();
        $product_list = [];
        $weight = $num = 0;
        foreach ($order_goods_data as $sku_info) {
            $product_list[] = [
                'count' => $sku_info['num'],
                'sku_name' => $sku_info['goods_name'] . $sku_info['sku_name'],
                'src_product_no' => $sku_info['sku_id'],
                'unit' => $sku_info['goods']['unit'],
            ];
            $weight += $sku_info['sku']['weight'] * $sku_info['num'];
            $num += $sku_info['num'];
        }
        // 创建请求对象并链式设置参数
        $request = $request->setBaseParams($this->config)
            ->setCallback($order_no)
            ->setShopNo($this->shop_store_no)
            ->setOriginId($order_no)
            ->setReceiverName($order_data->taker_name)
            ->setReceiverPhone($order_data->taker_phone)
            ->setReceiverAddress($order_data->taker_address)
            ->setReceiverLat($order_data->taker_latitude)
            ->setReceiverLng($order_data->taker_longitude)
            ->setCargoNum($num)
            ->setCargoWeight(max($weight, 1))
            ->setCargoPrice($order_data->order_money)
            ->setProductList($product_list);
        try {
            $res = $request->sendRequest();
            return [
                'delivery_price' => $res['fee'],
                'third_order_no' => $order_no,
            ];

        } catch (\Exception $e) {
            Log::write('DADA' . $e->getMessage());
            return [
                'delivery_price' => 0,
                'third_order_no' => $order_no,
            ];

        }

    }

    /**
     * API使用
     * @param $order_data
     * @return array
     */
    public function calculate($order_data)
    {
        $order_no = $order_data->order_key;
        $goods_data = $order_data->goods_data;
        $member_address = $order_data->delivery['take_address'];
        $request = new OrderFeeRequest();
        $product_list = [];
        $weight = 0;
        foreach ($goods_data as $sku_id => $sku_info) {
            $product_list[] = [
                'count' => $sku_info['num'],
                'sku_name' => $sku_info['goods']['goods_name'] . $sku_info['sku_name'],
                'src_product_no' => $sku_id,
                'unit' => $sku_info['goods']['unit'],
            ];
            $weight += $sku_info['weight'] * $sku_info['num'];
        }
        // 创建请求对象并链式设置参数
        $request = $request->setBaseParams($this->config)
            ->setShopNo($this->shop_store_no)
            ->setOriginId($order_no)
            ->setCargoPrice($order_data->basic['goods_money'])
            ->setReceiverName($member_address['name'])
            ->setReceiverPhone($member_address['mobile'])
            ->setReceiverAddress($member_address['full_address'])
            ->setReceiverLat($member_address['lat'])
            ->setReceiverLng($member_address['lng'])
            ->setCargoNum(count($goods_data))
            ->setCargoWeight(max($weight, 1))
            ->setCallback($order_no)
            ->setProductList($product_list);
        try {
            $res = $request->sendRequest();
            return [
                'delivery_price' => $res['fee'],
                'third_order_no' => $order_no,
            ];
        } catch (\Exception $exception) {
            Log::write('DADA' . $exception->getMessage());
            return [
                'delivery_price' => 0,
                'third_order_no' => $order_no,
            ];
        }
    }

    /**
     *
     * @param $delivery_third_order_no `计算运费接口返回的  配送方单号 queryFeeMoney.response.delivery_third_order_no`
     * @return string[]
     */
    public function createOrderAfterCalculate($delivery_third_order_no)
    {
        return (new OrderCreateByFeeOrderNoRequest())->setDeliveryNo($delivery_third_order_no)->sendRequest();
    }

    /**
     * @param $order_no `订单号`
     * @return string[]
     */
    public function queryOrderInfo($data)
    {
//        return [//模拟返回的数据
//            'order_no' => $data,
//            'status' => 1,
//            'status_name' => '待接单',
//            'transporter_name' => '配送员1',
//            'transporter_phone' => '15235353636',
//            'transporter_lng' => '121.531522',
//            'transporter_lat' => '31.252839',
//            'accept_time' => '2025-06-22 12:00:00',
//            'finish_time' => '2025-06-22 12:00:00',
//            'order_finish_code' => '9527',
//        ];
        $order_no = $data['order_no'];
        try {
            $res = (new OrderInfoRequest())->setOrderId($order_no)->sendRequest();
            return [
                'order_no' => $res['orderId'],
                'status' => $res['statusCode'],
                'status_name' => $res['statusMsg'],
                'transporter_name' => $res['transporterName'],
                'transporter_phone' => $res['transporterPhone'],
                'transporter_lng' => $res['transporterLng'],
                'transporter_lat' => $res['transporterLat'],
                'accept_time' => $res['acceptTime'],
                'finish_time' => $res['finishTime'],
                'order_finish_code' => $res['orderFinishCode'],
            ];
        } catch (\Exception $exception) {
            Log::write('DADA' . $exception->getMessage());
            return [//模拟返回的数据
                'order_no' => $order_no,
                'status' => 1,
                'status_name' => '待接单',
                'transporter_name' => '未知',
                'transporter_phone' => '未知',
                'transporter_lng' => '',
                'transporter_lat' => '',
                'accept_time' => '',
                'finish_time' => '',
                'order_finish_code' => '',
            ];
        }

    }

    /**
     * @param $order_no `订单号`
     * @param $cancel_reason_id `取消原因类型ID`
     * @param $cancel_reason `取消原因`
     * @return false|string[]
     */
    public function closeOrder($data)
    {
        try {
            return (new OrderCancelRequest())
                ->setOrderId($data['order_no'])
                ->setCancelReasonId($data['cancel_reason_id'])
                ->setCancelReason($data['cancel_reason'])
                ->sendRequest();
        } catch (\Exception $exception) {
            Log::write('DADA' . $exception->getMessage());
            return false;
        }

    }

    public function GetTransporterPosition($data)
    {
        //调试数据
//        return [
//            'transporter_name' => '配送员1',
//            'transporter_phone' => '15235353636',
//            'transporter_lng' => '121.531522',
//            'transporter_lat' => '31.252839',
//        ];
        try {
            if ($data['is_h5']) {
                $res = (new OrderTransporterPositionH5Request())
                    ->setOrderId($data['order_no'])
                    ->sendRequest();
                $h5_url = $res['trackUrl'];
            }
            $res = (new OrderTransporterPositionRequest())
                ->setOrderIds([$data['order_no']])
                ->sendRequest();
            return [
                'transporter_name' => $res['transporterName'],
                'transporter_phone' => $res['transporterPhone'],
                'transporter_lng' => $res['transporterLng'],
                'transporter_lat' => $res['transporterLat'],
                'h5_url' => $h5_url ?? "",
            ];
        } catch (\Exception $exception) {
            Log::write('DADA' . $exception->getMessage());
            return [
                'transporter_name' => '',
                'transporter_phone' => '',
                'transporter_lng' => '',
                'transporter_lat' => '',
            ];
        }

    }

    public function callback($data)
    {
        $callback = [
            'order_no' => '112233',//自主追加
            "signature" => "2b3aa825562bcea9471fe860b3f8a8fa",
            "client_id" => "1357061741749469184",
            "order_id" => "Cu16538768403935339353",//订单号
            "order_status" => 100,//订单状态(待接单＝1,待取货＝2,骑士到店=100,配送中＝3,已完成＝4,已取消＝5, 已追加待接单=8,妥投异常之物品返回中=9, 妥投异常之物品返回完成=10, 售后取件单送达门店=6, 创建达达运单失败=1000）
            "cancel_reason" => "",
            "cancel_from" => 0,
            "dm_id" => 170900,
            "dm_name" => "张三",
            "dm_mobile" => "18500000000",
            "update_time" => 1653876860,
            "finish_code" => "1234"
        ];
        $status = $data['order_status'];//订单配送状态
    }
}