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

namespace addon\shop\app\service\core\delivery;

use addon\shop\app\dict\delivery\DeliveryDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\model\delivery\ElectronicSheet;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\order\Order;
use addon\shop\app\model\order\OrderDelivery;
use addon\shop\app\model\shop_address\ShopAddress;
use addon\shop\app\service\core\delivery\electronic_sheet\ElectronicSheetSearchLoader;
use app\model\sys\SysArea;
use app\model\sys\SysConfig;
use app\service\core\sys\CoreConfigService as ConfigService;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\facade\Cache;
use think\Model;

/**
 * 电子面单相关接口
 */
class CoreElectronicSheetService extends BaseCoreService
{
    /**
     * 设置电子面单配置
     * @param $data
     * @return SysConfig|bool|Model
     */
    public function setElectronicSheetConfig($data)
    {
        return ( new ConfigService() )->setConfig( 'ELECTRONIC_SHEET_CONFIG', $data);
    }

    /**
     * 获取电子面单设置
     * @return array|mixed
     */
    public function getElectronicSheetConfig()
    {
        $info = ( new ConfigService() )->getConfig('ELECTRONIC_SHEET_CONFIG');
        if (empty($info)) {
            $info = [];
            $info[ 'value' ] = [
                'interface_type' => 'kdbird', // 接口类型，kdbird：快递鸟，后期支持扩展
                'kdniao_id' => '',
                'kdniao_api_key' => '',
                'server_port1' => '8000',
                'server_port2' => '18000',
                'https_port' => '8443'
            ];
        }
        return $info[ 'value' ];
    }

    /**
     * 添加电子面单模板
     * @param $data
     * @return bool
     */
    public function add( $data)
    {
        ( new ElectronicSheet() )->create($data);
        return true;
    }

    /**
     * 批量添加电子面单模板
     * @param $data
     * @return bool
     */
    public function addAll($data)
    {
        ( new ElectronicSheet() )->insertAll($data);
        return true;
    }

    /**
     * 获取电子面单信息
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getInfo($condition = [], $field = 'id,template_name,express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,status,exp_type,print_style,is_default,interface_data,temp_id,child_temp_id,back_temp_id')
    {
        $info = ( new ElectronicSheet() )->field($field)->where($condition)->withJoin([
            'company' => [ 'company_name', 'express_no_electronic_sheet' ,'kd100_express_no_electronic_sheet']
        ])->append(['kdbird_config', 'kd100_config'])->findOrEmpty()
            ->toArray();
        return $info;
    }

    /**
     * 打印电子面单
     * @param $params
     * @return mixed
     */
    public function printElectronicSheet($params)
    {
        $es_config = $this->getElectronicSheetConfig();

        // 查询订单信息
        $order_model = new Order();
        $field = 'order_id, order_no, member_id, taker_name, taker_mobile, taker_province, taker_city, taker_district, taker_address,delivery_money';
        $order_list = $order_model->where([
            [ 'order_id', 'in', $params[ 'order_id' ] ],
            [ 'delivery_type', '=', DeliveryDict::EXPRESS ], // 物流配送
            [ 'status', 'in', [ OrderDict::WAIT_DELIVERY, OrderDict::WAIT_TAKE ] ], // 待发货、待收货状态下
        ])->field($field)
            ->with(
                [
                    'order_goods' => function($query) {
                        $query->field('order_goods_id,order_id, sku_id, goods_name, sku_name, num,status,delivery_id');
                    }
                ])->select()->toArray();

        if (empty($order_list)) {
            throw new CommonException('SHOP_ORDER_NOT_FOUND');
        }

        $sys_area_model = new SysArea();

        //  查询商家地址库（默认）
        $shop_address_model = new ShopAddress();
        $default_shop_address = $shop_address_model->where([
            [ 'is_delivery_address', '=', 1 ],
            [ 'is_default_delivery', '=', 1 ]
        ])->field('contact_name,mobile,province_id,city_id,district_id,address')->findOrEmpty()->toArray();

        if (empty($default_shop_address)) {
            throw new CommonException('SHOP_ADDRESS_DEFAULT_EMPTY');
        }

        $sender_province_name = $sys_area_model->where([ [ 'id', '=', $default_shop_address[ 'province_id' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
        $sender_city_name = $sys_area_model->where([ [ 'id', '=', $default_shop_address[ 'city_id' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
        $sender_exp_area_name = $sys_area_model->where([ [ 'id', '=', $default_shop_address[ 'district_id' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';

        $goods_sku_model = new GoodsSku();

        // 替换商品名称中的特殊字符 '   "   #    &    +    <   >   %   \
        $search = array( "'", '"', '&', '+', '<', '>', '%', "\\", '#', "and" );

        $result = [];

        // 打印方式，single：单订单，multiple：多订单

        if ($params[ 'print_type' ] == 'single') {
            // 单订单打印，按照包裹拆分打印

            foreach ($params[ 'list' ] as $k => $v) {
                // 查询电子面单模板
                $es_template_info = $this->getInfo([
                    [ 'id', '=', $v[ 'electronic_sheet_id' ] ],
                    [ 'status', '=', 1 ]
                ], 'express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,exp_type,print_style,interface_data,temp_id,child_temp_id,back_temp_id');

                if (empty($es_template_info)) {
                    // 电子面单模板不存在
                    continue;
                }

                if ($es_template_info[ 'print_style' ] == '0') {
                    $es_template_info[ 'print_style' ] = '';
                }

                foreach ($order_list as $ck => $cv) {

                    $key = $es_template_info[ 'company' ][ 'express_no_electronic_sheet' ] . '_' . $cv[ 'order_no' ]
                        . '_' . $es_config[ 'kdniao_id' ] . '_' . $es_template_info[ 'print_style' ]
                        . '_' . $v[ 'delivery_id' ] . '_' . $k;

                    $cache = Cache::get($key);
                    if (!empty($cache)) {
                        $cache[ 'delivery_id' ] = $v[ 'delivery_id' ];
                        $result[] = $cache;
                        continue;
                    }

                    $commodity = []; // 商品信息
                    $weight_total = 0; // 包裹总重量kg
                    $volume_total = 0; // 包裹总体积 m³
                    $remark = []; // 备注
                    foreach ($cv[ 'order_goods' ] as $og_k => $og_v) {
                        // 排除退款订单项
                        if ($og_v[ 'status' ] != 1) {
                            continue;
                        }

                        // 排除 无需物流的订单项
                        if (empty($og_v[ 'delivery_id' ])) {
                            continue;
                        }

                        // 排除不是一个包裹
                        if ($og_v[ 'delivery_id' ] != $v[ 'delivery_id' ]) {
                            continue;
                        }

                        $order_goods_delivery = ( new OrderDelivery() )
                            ->where([
                                [ 'order_id', '=', $cv[ 'order_id' ] ],
                                [ 'id', '=', $og_v[ 'delivery_id' ] ],
                                [ 'delivery_type', '=', 'express' ],
                                [ 'sub_delivery_type', '=', 'express' ]
                            ])
                            ->field('id, order_id, delivery_type, sub_delivery_type,express_company_id, express_number')
                            ->findOrEmpty()->toArray();

                        // 只打印 物流配送的订单项
                        if (!empty($order_goods_delivery)) {
                            $commodity[] = [
                                'GoodsName' => str_replace($search, '', mb_substr($og_v[ 'goods_name' ] . ' ' . $og_v[ 'sku_name' ], 0, 50, 'UTF-8')),
                                'GoodsQuantity' => $og_v[ 'num' ]
                            ];
//                            $remark[] = str_replace($search, '', mb_substr($og_v[ 'goods_name' ] . ' ' . $og_v[ 'sku_name' ], 0, 50, 'UTF-8'));
                            $goods_sku_info = $goods_sku_model->field('weight,volume')->where([ [ 'sku_id', '=', $og_v[ 'sku_id' ] ] ])->findOrEmpty()->toArray();
                            if (!empty($goods_sku_info)) {
                                $weight_total += number_format($goods_sku_info[ 'weight' ], 3);
                                $volume_total += number_format($goods_sku_info[ 'volume' ], 3);
                            }
                        }

                    }

                    if (empty($commodity)) {
                        $result[] = [
                            'success' => false,
                            'result_code' => -1,
                            'reason' => '该订单不支持打印电子面单',
                            'order_no' => $cv[ 'order_no' ]
                        ];
                        continue;
                    }

                    $receiver_province_name = $sys_area_model->where([ [ 'id', '=', $cv[ 'taker_province' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
                    $receiver_city_name = $sys_area_model->where([ [ 'id', '=', $cv[ 'taker_city' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
                    $receiver_exp_area_name = $sys_area_model->where([ [ 'id', '=', $cv[ 'taker_district' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';

                    $api_data = [
                        'cache_key' => $key,
//                        'ShipperCode' => $es_template_info[ 'company' ][ 'express_no_electronic_sheet' ], // 快递公司编码
//                        'CustomerName' => $es_template_info[ 'customer_name' ], // 电子面单账号，申请：https://www.yuque.com/kdnjishuzhichi/rg4owd
//                        'CustomerPwd' => $es_template_info[ 'customer_pwd' ], // 电子面单密码，电子面单账号对照表：https://www.yuque.com/kdnjishuzhichi/dfcrg1/hrfw43
//                        'SendSite' => $es_template_info[ 'send_site' ],
//                        'SendStaff' => $es_template_info[ 'send_staff' ],
//                        'MonthCode' => $es_template_info[ 'month_code' ], // 月结账号
//                        'PayType' => $es_template_info[ 'pay_type' ], // 运费支付方式（1：现付，2：到付，3：月结）
//                        'ExpType' => $es_template_info[ 'exp_type' ], // 快递业务类型，https://www.yuque.com/kdnjishuzhichi/dfcrg1/hgx758hom5p6wz0l
//                        'TemplateSize' => $es_template_info[ 'print_style' ], // 模板规格，https://www.yuque.com/kdnjishuzhichi/dfcrg1/vpptucr1q5ahcxa7#iZvLV
//                        'IsNotice' => $es_template_info[ 'is_notice' ], // 是否通知快递员上门揽件跨越速运，京东快运必填

                        'es_template_info'=>$es_template_info,
                        'OrderCode' => $cv[ 'order_no' ] . $k, // 订单编号，多包裹防重复，要增加下标
                        // 发件人信息
                        'Sender' => [
                            'Name' => $default_shop_address[ 'contact_name' ], // 发件人
                            'Mobile' => $default_shop_address[ 'mobile' ], // 手机
                            'ProvinceName' => $sender_province_name, // 发件省
                            'CityName' => $sender_city_name, // 发件市
                            'ExpAreaName' => $sender_exp_area_name, // 发件区
                            'Address' => $default_shop_address[ 'address' ], // 发件人详细地址
                            'PostCode' => '000000' // 发件地邮编邮政/EMS 必填可填000000
                        ],
                        // 收件人信息
                        'Receiver' => [
                            'Name' => $cv[ 'taker_name' ], // 收件人
                            'Mobile' => $cv[ 'taker_mobile' ], // 电话
                            'ProvinceName' => $receiver_province_name, // 收件省
                            'CityName' => $receiver_city_name, // 收件市
                            'ExpAreaName' => $receiver_exp_area_name, // 收件区
                            'Address' => mb_substr($cv[ 'taker_address' ], 0, 100, 'UTF-8'), // 收件人详细地址
                            'PostCode' => '000000' // 收件地邮编邮政/EMS 必填可填000000
                        ],
                        'Quantity' => count($cv[ 'order_goods' ]) > 50 ? 50 : count($cv[ 'order_goods' ]), // 包裹数  (至少填1,最多50)
                        // 商品信息
                        'Commodity' => $commodity,
                        'Weight' => $weight_total, // 包裹总重量kg，京东、快运类必填
                        'Volume' => $volume_total, // 包裹总体积 m³，京东、快运类必填
                        'Cost' => $cv[ 'delivery_money' ], // 快递运费
                        'Remark' => '', // '<br>' . mb_substr(implode('<br>', $remark), 0, 80, 'UTF-8'), // 备注
                        'IsReturnPrintTemplate' => 1, // 是否返回电子面单模板：（不填默认为0）0：不需要，1：需要
                    ];

                    $class = new ElectronicSheetSearchLoader();
                    $temp_result = $class->electronicSheet($api_data);
                    $temp_result[ 'order_no' ] = $cv[ 'order_no' ]; // 记录打印的 订单号
                    $temp_result[ 'delivery_id' ] = $v[ 'delivery_id' ]; // 记录打印的 包裹id

                    $result[] = $temp_result;
                }

            }

            return $result;

        } elseif ($params[ 'print_type' ] == 'multiple') {
            // 多订单打印，一个订单一个电子面单
            // 查询电子面单模板
            if (!empty($params[ 'electronic_sheet_id' ])) {
                $es_template_info = $this->getInfo([
                    [ 'id', '=', $params[ 'electronic_sheet_id' ] ],
                    [ 'status', '=', 1 ]
                ], 'express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,exp_type,print_style,interface_data,temp_id,child_temp_id,back_temp_id');
            } else {
                $es_template_info = $this->getInfo([
                    [ 'is_default', '=', 1 ],
                    [ 'status', '=', 1 ]
                ], 'express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,exp_type,print_style,interface_data,temp_id,child_temp_id,back_temp_id');
            }

            if (empty($es_template_info)) {
                throw new CommonException('SHOP_ELECTRONIC_SHEET_TEMPLATE_FOUND');
            }

            if ($es_template_info[ 'print_style' ] == '0') {
                $es_template_info[ 'print_style' ] = '';
            }

            // 查询指定订单项
            if (!empty($params[ 'order_goods_ids' ])) {
                foreach ($order_list as $k => $v) {
                    if ($v[ 'order_id' ] == $params[ 'order_id' ]) {
                        foreach ($v[ 'order_goods' ] as $ck => $cv) {
                            if (!in_array($cv[ 'order_goods_id' ], $params[ 'order_goods_ids' ])) {
                                unset($order_list[ $k ][ 'order_goods' ][ $ck ]);
                            }
                        }
                        $order_list[ $k ][ 'order_goods' ] = array_values($order_list[ $k ][ 'order_goods' ]);
                    }
                }
            }

            foreach ($order_list as $k => $v) {

                $order_goods_delivery_count = ( new OrderDelivery() )->where([
                    [ 'order_id', '=', $v[ 'order_id' ] ],
                    [ 'delivery_type', '=', 'express' ],
                    [ 'sub_delivery_type', '=', 'express' ]
                ])->count();

                if ($order_goods_delivery_count > 0) {
                    $order_goods_delivery_count--;
                }

                $key = $es_template_info[ 'company' ][ 'express_no_electronic_sheet' ] . '_' . $v[ 'order_no' ]
                    . '_' . $es_config[ 'kdniao_id' ]
                    . '_' . $es_template_info[ 'print_style' ] . '_' . $order_goods_delivery_count;

                $cache = Cache::get($key);
                if (!empty($cache)) {
                    $result[] = $cache;
                    continue;
                }

                $commodity = []; // 商品信息
                $weight_total = 0; // 包裹总重量kg
                $volume_total = 0; // 包裹总体积 m³
                $remark = []; // 备注
                foreach ($v[ 'order_goods' ] as $ck => $cv) {

                    // 排除退款订单项
                    if ($cv[ 'status' ] != 1) {
                        continue;
                    }

                    // 排除 无需物流的订单项
                    if (empty($cv[ 'delivery_id' ])) {
                        continue;
                    }

                    $order_goods_delivery = ( new OrderDelivery() )
                        ->where([
                            [ 'order_id', '=', $v[ 'order_id' ] ],
                            [ 'id', '=', $cv[ 'delivery_id' ] ],
                            [ 'delivery_type', '=', 'express' ],
                            [ 'sub_delivery_type', '=', 'express' ]
                        ])
                        ->field('id, order_id, delivery_type, sub_delivery_type,express_company_id, express_number')
                        ->findOrEmpty()->toArray();

                    // 只打印 物流配送的订单项
                    if (!empty($order_goods_delivery)) {
                        $commodity[] = [
                            'GoodsName' => str_replace($search, '', mb_substr($cv[ 'goods_name' ] . ' ' . $cv[ 'sku_name' ], 0, 50, 'UTF-8')),
                            'GoodsQuantity' => $cv[ 'num' ]
                        ];
//                        $remark[] = str_replace($search, '', mb_substr($cv[ 'goods_name' ] . ' ' . $cv[ 'sku_name' ], 0, 50, 'UTF-8'));
                        $goods_sku_info = $goods_sku_model->field('weight,volume')->where([ [ 'sku_id', '=', $cv[ 'sku_id' ] ] ])->findOrEmpty()->toArray();
                        if (!empty($goods_sku_info)) {
                            $weight_total += number_format($goods_sku_info[ 'weight' ], 3);
                            $volume_total += number_format($goods_sku_info[ 'volume' ], 3);
                        }
                    }
                }

                if (empty($commodity)) {
                    $result[] = [
                        'success' => false,
                        'result_code' => -1,
                        'reason' => '该订单不支持打印电子面单',
                        'order_no' => $v[ 'order_no' ]
                    ];
                    continue;
                }

                $receiver_province_name = $sys_area_model->where([ [ 'id', '=', $v[ 'taker_province' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
                $receiver_city_name = $sys_area_model->where([ [ 'id', '=', $v[ 'taker_city' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
                $receiver_exp_area_name = $sys_area_model->where([ [ 'id', '=', $v[ 'taker_district' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';

                $api_data = [
                    'cache_key' => $key,
//                  'ShipperCode' => $es_template_info[ 'company' ][ 'express_no_electronic_sheet' ], // 快递公司编码
//                  'CustomerName' => $es_template_info[ 'customer_name' ], // 电子面单账号，申请：https://www.yuque.com/kdnjishuzhichi/rg4owd
//                  'CustomerPwd' => $es_template_info[ 'customer_pwd' ], // 电子面单密码，电子面单账号对照表：https://www.yuque.com/kdnjishuzhichi/dfcrg1/hrfw43
//                  'SendSite' => $es_template_info[ 'send_site' ],
//                  'SendStaff' => $es_template_info[ 'send_staff' ],
//                  'MonthCode' => $es_template_info[ 'month_code' ], // 月结账号
                    'es_template_info'=> $es_template_info,
                    'OrderCode' => $v[ 'order_no' ] . $order_goods_delivery_count, // 订单编号，多包裹防重复，要增加下标

                    'PayType' => $es_template_info[ 'pay_type' ], // 运费支付方式（1：现付，2：到付，3：月结）
                    'ExpType' => $es_template_info[ 'exp_type' ], // 快递业务类型，https://www.yuque.com/kdnjishuzhichi/dfcrg1/hgx758hom5p6wz0l

                    // 发件人信息
                    'Sender' => [
                        'Name' => $default_shop_address[ 'contact_name' ], // 发件人
                        'Mobile' => $default_shop_address[ 'mobile' ], // 手机
                        'ProvinceName' => $sender_province_name, // 发件省
                        'CityName' => $sender_city_name, // 发件市
                        'ExpAreaName' => $sender_exp_area_name, // 发件区
                        'Address' => $default_shop_address[ 'address' ], // 发件人详细地址
                        'PostCode' => '000000' // 发件地邮编邮政/EMS 必填可填000000
                    ],

                    // 收件人信息
                    'Receiver' => [
                        'Name' => $v[ 'taker_name' ], // 收件人
                        'Mobile' => $v[ 'taker_mobile' ], // 电话
                        'ProvinceName' => $receiver_province_name, // 收件省
                        'CityName' => $receiver_city_name, // 收件市
                        'ExpAreaName' => $receiver_exp_area_name, // 收件区
                        'Address' => mb_substr($v[ 'taker_address' ], 0, 100, 'UTF-8'), // 收件人详细地址
                        'PostCode' => '000000' // 收件地邮编邮政/EMS 必填可填000000
                    ],

                    'Quantity' => count($v[ 'order_goods' ]) > 50 ? 50 : count($v[ 'order_goods' ]), // 包裹数  (至少填1,最多50)

                    // 商品信息
                    'Commodity' => $commodity,
                    'Weight' => $weight_total, // 包裹总重量kg，京东、快运类必填
                    'Volume' => $volume_total, // 包裹总体积 m³，京东、快运类必填
                    'Cost' => $v[ 'delivery_money' ], // 快递运费
                    'Remark' => '',// '<br>' . mb_substr(implode('<br>', $remark), 0, 80, 'UTF-8'), // 备注

                    'IsReturnPrintTemplate' => 1, // 是否返回电子面单模板：（不填默认为0）0：不需要，1：需要

                    'TemplateSize' => $es_template_info[ 'print_style' ], // 模板规格，https://www.yuque.com/kdnjishuzhichi/dfcrg1/vpptucr1q5ahcxa7#iZvLV
                    'IsNotice' => $es_template_info[ 'is_notice' ] // 是否通知快递员上门揽件跨越速运，京东快运必填
                ];

                $class = new ElectronicSheetSearchLoader();

                $temp_result = $class->electronicSheet($api_data);
                $temp_result[ 'order_no' ] = $v[ 'order_no' ]; // 记录打印的 订单号
                $result[] = $temp_result;
            }
            return $result;
        }
    }

    /**
     * 发货的同时打印电子面单
     * @param $params
     * @return array|void
     */
    public function printElectronicSheetByDelivery($params)
    {
        $es_config = $this->getElectronicSheetConfig();
        // 查询订单信息
        $order_model = new Order();
        $field = 'order_id, order_no, member_id, taker_name, taker_mobile, taker_province, taker_city, taker_district, taker_address,delivery_money';
        $order_info = $order_model->where([
            [ 'order_id', '=', $params[ 'order_id' ] ],
            [ 'delivery_type', '=', DeliveryDict::EXPRESS ], // 物流配送
            [ 'status', 'in', [ OrderDict::WAIT_DELIVERY, OrderDict::WAIT_TAKE ] ], // 待发货、待收货状态下
        ])->field($field)->findOrEmpty()->toArray();

        if (empty($order_info)) {
            throw new CommonException('SHOP_ORDER_NOT_FOUND');
        }

        // 发货的同时打印电子面单
        if (!empty($params[ 'order_goods_data' ])) {
            $order_info[ 'order_goods' ] = $params[ 'order_goods_data' ];
        }

        $sys_area_model = new SysArea();

        //  查询商家地址库（默认）
        $shop_address_model = new ShopAddress();
        $default_shop_address = $shop_address_model->where([
            [ 'is_delivery_address', '=', 1 ],
            [ 'is_default_delivery', '=', 1 ]
        ])->field('contact_name,mobile,province_id,city_id,district_id,address')->findOrEmpty()->toArray();

        if (empty($default_shop_address)) {
            throw new CommonException('SHOP_ADDRESS_DEFAULT_EMPTY');
        }

        $sender_province_name = $sys_area_model->where([ [ 'id', '=', $default_shop_address[ 'province_id' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
        $sender_city_name = $sys_area_model->where([ [ 'id', '=', $default_shop_address[ 'city_id' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
        $sender_exp_area_name = $sys_area_model->where([ [ 'id', '=', $default_shop_address[ 'district_id' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';

        $goods_sku_model = new GoodsSku();

        // 替换商品名称中的特殊字符 '   "   #    &    +    <   >   %   \
        $search = array( "'", '"', '&', '+', '<', '>', '%', "\\", '#', "and" );

        // 查询电子面单模板
        if (!empty($params[ 'electronic_sheet_id' ])) {
            $es_template_info = $this->getInfo([
                [ 'id', '=', $params[ 'electronic_sheet_id' ] ],
                [ 'status', '=', 1 ]
            ], 'express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,exp_type,print_style,exp_type_name,interface_data,temp_id,child_temp_id,back_temp_id');
        } else {
            $es_template_info = $this->getInfo([
                [ 'is_default', '=', 1 ],
                [ 'status', '=', 1 ]
            ], 'express_company_id,customer_name,customer_pwd,send_site,send_staff,month_code,pay_type,is_notice,exp_type,print_style,exp_type_name,interface_data,temp_id,child_temp_id,back_temp_id');
        }

        if (empty($es_template_info)) {
            throw new CommonException('SHOP_ELECTRONIC_SHEET_TEMPLATE_FOUND');
        }

        if ($es_template_info[ 'print_style' ] == '0') {
            $es_template_info[ 'print_style' ] = '';
        }

        $order_goods_delivery_count = ( new OrderDelivery() )
            ->where([
                [ 'order_id', '=', $order_info[ 'order_id' ] ],
                [ 'delivery_type', '=', 'express' ],
                [ 'sub_delivery_type', '=', 'express' ]
            ])
            ->count();

        $key = $es_template_info[ 'company' ][ 'express_no_electronic_sheet' ] . '_' . $order_info[ 'order_no' ]
            . '_' . $es_config[ 'kdniao_id' ] . '_' . $es_template_info[ 'print_style' ]
            . '_' . $order_goods_delivery_count;

        $cache = Cache::get($key);
        if (!empty($cache)) {
            return $cache;
        }

        $commodity = []; // 商品信息
        $weight_total = 0; // 包裹总重量kg
        $volume_total = 0; // 包裹总体积 m³
        $remark = []; // 备注
        foreach ($order_info[ 'order_goods' ] as $ck => $cv) {

            $commodity[] = [
                'GoodsName' => str_replace($search, '', mb_substr($cv[ 'goods_name' ] . ' ' . $cv[ 'sku_name' ], 0, 50, 'UTF-8')),
                'GoodsQuantity' => $cv[ 'num' ]
            ];
//            $remark[] = str_replace($search, '', mb_substr($cv[ 'goods_name' ] . ' ' . $cv[ 'sku_name' ], 0, 50, 'UTF-8'));
            $goods_sku_info = $goods_sku_model->field('weight,volume')->where([ [ 'sku_id', '=', $cv[ 'sku_id' ] ] ])->findOrEmpty()->toArray();
            if (!empty($goods_sku_info)) {
                $weight_total += number_format($goods_sku_info[ 'weight' ], 3);
                $volume_total += number_format($goods_sku_info[ 'volume' ], 3);
            }
        }

        if (empty($commodity)) {
            $result = [
                'success' => false,
                'result_code' => -1,
                'reason' => '该订单不支持打印电子面单',
                'order_no' => $order_info[ 'order_no' ]
            ];
            return $result;
        }

        $receiver_province_name = $sys_area_model->where([ [ 'id', '=', $order_info[ 'taker_province' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
        $receiver_city_name = $sys_area_model->where([ [ 'id', '=', $order_info[ 'taker_city' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';
        $receiver_exp_area_name = $sys_area_model->where([ [ 'id', '=', $order_info[ 'taker_district' ] ] ])->field('name')->findOrEmpty()->toArray()[ 'name' ] ?? '';


//        $ShipperCode = $es_config['interface_type'] == 'kdbird' ? $es_template_info[ 'company' ][ 'express_no_electronic_sheet' ] : $es_template_info[ 'company' ][ 'kd100_express_no_electronic_sheet' ];
        $api_data = [
            'cache_key' => $key,
//            'ShipperCode' => $ShipperCode, // 快递公司编码
//            'CustomerName' => $es_template_info[ 'customer_name' ], // 电子面单账号，申请：https://www.yuque.com/kdnjishuzhichi/rg4owd
//            'CustomerPwd' => $es_template_info[ 'customer_pwd' ], // 电子面单密码，电子面单账号对照表：https://www.yuque.com/kdnjishuzhichi/dfcrg1/hrfw43
//            'SendSite' => $es_template_info[ 'send_site' ],
//            'SendStaff' => $es_template_info[ 'send_staff' ],
//            'MonthCode' => $es_template_info[ 'month_code' ], // 月结账号
            'es_template_info'=>$es_template_info,
            'OrderCode' => $order_info[ 'order_no' ] . $order_goods_delivery_count, // 订单编号，多包裹防重复，要增加下标

            'PayType' => $es_template_info[ 'pay_type' ], // 运费支付方式（1：现付，2：到付，3：月结）
            'ExpType' => $es_template_info[ 'exp_type' ], // 快递业务类型，https://www.yuque.com/kdnjishuzhichi/dfcrg1/hgx758hom5p6wz0l

            // 发件人信息
            'Sender' => [
                'Name' => $default_shop_address[ 'contact_name' ], // 发件人
                'Mobile' => $default_shop_address[ 'mobile' ], // 手机
                'ProvinceName' => $sender_province_name, // 发件省
                'CityName' => $sender_city_name, // 发件市
                'ExpAreaName' => $sender_exp_area_name, // 发件区
                'Address' => $default_shop_address[ 'address' ], // 发件人详细地址
                'PostCode' => '000000' // 发件地邮编邮政/EMS 必填可填000000
            ],

            // 收件人信息
            'Receiver' => [
                'Name' => $order_info[ 'taker_name' ], // 收件人
                'Mobile' => $order_info[ 'taker_mobile' ], // 电话
                'ProvinceName' => $receiver_province_name, // 收件省
                'CityName' => $receiver_city_name, // 收件市
                'ExpAreaName' => $receiver_exp_area_name, // 收件区
                'Address' => mb_substr($order_info[ 'taker_address' ], 0, 100, 'UTF-8'), // 收件人详细地址
                'PostCode' => '000000' // 收件地邮编邮政/EMS 必填可填000000
            ],

            'Quantity' => count($order_info[ 'order_goods' ]) > 50 ? 50 : count($order_info[ 'order_goods' ]), // 包裹数  (至少填1,最多50)

            // 商品信息
            'Commodity' => $commodity,
            'Weight' => $weight_total, // 包裹总重量kg，京东、快运类必填
            'Volume' => $volume_total, // 包裹总体积 m³，京东、快运类必填
            'Cost' => $order_info[ 'delivery_money' ], // 快递运费
            'Remark' => '', // '<br>' . mb_substr(implode('<br>', $remark), 0, 80, 'UTF-8'), // 备注

            'IsReturnPrintTemplate' => 1, // 是否返回电子面单模板：（不填默认为0）0：不需要，1：需要

            'TemplateSize' => $es_template_info[ 'print_style' ], // 模板规格，https://www.yuque.com/kdnjishuzhichi/dfcrg1/vpptucr1q5ahcxa7#iZvLV
            'IsNotice' => $es_template_info[ 'is_notice' ] // 是否通知快递员上门揽件跨越速运，京东快运必填
        ];

        $class = new ElectronicSheetSearchLoader();
        $result = $class->electronicSheet($api_data);
        $result[ 'order_no' ] = $order_info[ 'order_no' ]; // 记录打印的 订单号

        return $result;
    }

}
