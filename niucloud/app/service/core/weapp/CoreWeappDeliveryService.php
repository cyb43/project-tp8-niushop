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

namespace app\service\core\weapp;

use app\dict\common\ChannelDict;
use app\dict\pay\PayDict;
use app\model\sys\SysConfig;
use app\service\admin\pay\PayChannelService;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use think\facade\Log;
use think\Model;

/**
 * 小程序发货信息管理服务
 * Class CoreWeappDeliveryService
 * @package app\service\core\weapp
 */
class CoreWeappDeliveryService extends BaseCoreService
{

    /**
     * 查询小程序是否已开通发货信息管理服务
     * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html#七、查询小程序是否已开通发货信息管理服务
     * 返回结果：{"errcode":0,"errmsg":"ok","is_trade_managed":true}
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function isTradeManaged()
    {
        $app = CoreWeappService::app();
        $account = $app->getAccount();
        $appid = $account->getAppId();
        return $app->getClient()->postJson('wxa/sec/order/is_trade_managed', [ 'appid' => $appid ])->toArray();
    }

    /**
     * 发货信息录入接口
     * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html#一、发货信息录入接口
     * 用户交易后，默认资金将会进入冻结状态，开发者在发货后，需要在小程序平台录入相关发货信息，平台会将发货信息以消息的形式推送给购买的微信用户。
     * 如果你已经录入发货信息，在用户尚未确认收货的情况下可以通过该接口修改发货信息，但一个支付单只能更新一次发货信息，请谨慎操作。
     * 如暂时没有完成相关API的对接开发工作，你也可以登陆小程序的后台，通过发货信息管理页面手动录入发货信息。
     * 注意事项
     * 1、根据指定的订单单号类型，采用不同参数给指定订单上传物流信息：
     *      (1). 商户侧单号形式（枚举值1），通过下单商户号和商户侧单号确定一笔订单
     *      (2). 微信支付单号形式（枚举值2），通过微信支付单号确定一笔订单
     * 2、发货模式根据具体发货情况选择：
     *      (1). 统一发货（枚举值1），一笔订单统一发货，只有一个物流单号。
     *      (2). 分拆发货（枚举值2），一笔订单分拆发货，包括多个物流单号。
     * 3、物流公司编码，参见获取运力 id 列表get_delivery_list。
     *      https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/industry/express/business/express_search.html#%E8%8E%B7%E5%8F%96%E8%BF%90%E5%8A%9Bid%E5%88%97%E8%A1%A8get-delivery-list
     * 4、上传时间，用于标识请求的先后顺序，如果要更新物流信息，上传时间必须比之前的请求更新，请按照 RFC 3339 格式填写。
     * 5、分拆发货仅支持使用物流快递发货，一笔支付单最多分拆成 10 个包裹。
     * 6、以下情况将视为重新发货，每笔支付单仅有一次重新发货机会。
     *      (1). 对已完成发货的支付单再次调用该 API。
     *      (2). 使用该 API 修改发货模式或物流模式。
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function uploadShippingInfo(array $params = [])
    {
        try {

            $pay_service = new PayChannelService();
            $pay_config = $pay_service->getInfo([
                'type' => PayDict::WECHATPAY,
                'channel' => ChannelDict::WEAPP
            ]);

            $mch_id = '';
            if (!empty($pay_config)) {
                $mch_id = $pay_config[ 'config' ][ 'mch_id' ];
            }

            $data = [
                'order_key' => [
                    'order_number_type' => 1, // 订单单号类型，用于确认需要上传详情的订单。枚举值1，使用下单商户号和商户侧单号；枚举值2，使用微信支付单号。
                    'mchid' => $mch_id, // 支付下单商户的商户号，由微信支付生成并下发。
                    'out_trade_no' => $params[ 'out_trade_no' ] // 商户系统内部订单号，只能是数字、大小写字母`_-*`且在同一个商户号下唯一
                ],
                'logistics_type' => $params[ 'logistics_type' ], // 物流模式，发货方式枚举值：1、实体物流配送采用快递公司进行实体物流配送形式 2、同城配送 3、虚拟商品，虚拟商品，例如话费充值，点卡等，无实体配送形式 4、用户自提
                'delivery_mode' => $params[ 'delivery_mode' ], // 发货模式，发货模式枚举值：1、UNIFIED_DELIVERY（统一发货）2、SPLIT_DELIVERY（分拆发货） 示例值: UNIFIED_DELIVERY
                // 同城配送没有物流信息，只能传一个订单
                'shipping_list' => $params[ 'shipping_list' ], // 物流信息列表，发货物流单列表，支持统一发货（单个物流单）和分拆发货（多个物流单）两种模式，多重性: [1, 10]
                'upload_time' => date("c", time()), // 上传时间，用于标识请求的先后顺序 示例值: `2022-12-15T13:29:35.120+08:00`
                'payer' => [
                    'openid' => $params[ 'weapp_openid' ] // 用户标识，用户在小程序appid下的唯一标识。 下单前需获取到用户的Openid 示例值: oUpF8uMuAJO_M2pxb1Q9zNjWeS6o 字符字节限制: [1, 128]
                ],
                'is_all_delivered' => $params[ 'is_all_delivered' ] // 分拆发货模式时必填，用于标识分拆发货模式下是否已全部发货完成，只有全部发货完成的情况下才会向用户推送发货完成通知。示例值: true/false
            ];

            Log::write('发货信息录入接口，参数打印：' . json_encode($data));

            //微信订单录入有时差 延时3秒执行发货通知
            sleep(3);

            $api = CoreWeappService::appApiClient();
            $result = $api->postJson('wxa/sec/order/upload_shipping_info', $data)->toArray();

            Log::write('发货信息录入接口，返回结果：' . json_encode($result));

            return $result;
        } catch (\Exception $e) {
            Log::write('uploadShippingInfo，报错：' . $e->getMessage() . "，File：" . $e->getFile() . "，line：" . $e->getLine());
            return $e->getMessage() . "，File：" . $e->getFile() . "，line：" . $e->getLine();
        }
    }

    /**
     * 确认收货提醒接口
     * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html#五、确认收货提醒接口
     * 如你已经从你的快递物流服务方获知到用户已经签收相关商品，可以通过该接口提醒用户及时确认收货，以提高资金结算效率，每个订单仅可调用一次。
     * 注意事项
     * 1、通过交易单号或商户号+商户单号来指定订单。
     * 2、只有物流类型为物流快递时才能进行提醒。
     * 3、签收时间由商户传入，在给用户发送提醒消息时会显示签收时间，签收时间必须在发货时间之后。
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function notifyConfirmReceive(array $params = [])
    {
        try {

            $pay_service = new PayChannelService();
            $pay_config = $pay_service->getInfo([
                'type' => PayDict::WECHATPAY,
                'channel' => ChannelDict::WEAPP
            ]);

            $mch_id = '';
            if (!empty($pay_config)) {
                $mch_id = $pay_config[ 'config' ][ 'mch_id' ];
            }

            $data = [
                'merchant_id' => $mch_id, // 支付下单商户的商户号，由微信支付生成并下发
                'merchant_trade_no' => $params[ 'out_trade_no' ], // 商户系统内部订单号，只能是数字、大小写字母_-*且在同一个商户号下唯一
                'received_time' => time() // 快递签收时间，时间戳形式
            ];

            Log::write('确认收货提醒接口，参数打印：' . json_encode($data));

            //微信订单录入有时差 延时3秒执行发货通知
            sleep(3);

            $api = CoreWeappService::appApiClient();

            $result = $api->postJson('wxa/sec/order/notify_confirm_receive', $data)->toArray();

            Log::write('确认收货提醒接口，返回结果：' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage() . "，File：" . $e->getFile() . "，line：" . $e->getLine();
        }
    }

    /**
     * 消息跳转路径设置接口
     * 如你已经在小程序内接入平台提供的确认收货组件，可以通过该接口设置发货消息及确认收货消息的跳转动作，用户点击发货消息时会直接进入你的小程序订单列表页面或详情页面进行确认收货，进一步优化用户体验。
     * 注意事项
     * 1、如设置为空路径或小程序中不存在的路径，将仍然跳转平台默认的确认收货页面，不会进入你的小程序。
     * 2、平台会在路径后面增加支付单的 transaction_id、merchant_id、merchant_trade_no 作为query参数，如果存在二级商户号则还会再增加 sub_merchant_id 参数,开发者可以在小程序中通过onLaunch等方式获取。
     * 3、如你需要在path中携带自定义的query参数，请注意与上面的参数进行区分
     * @param string $type
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setMsgJumpPath($type)
    {
        $config_data = $this->getConfig($type);
        if (true || empty($config_data) || ( !empty($config_data[ 'value' ]) && empty($config_data[ 'value' ][ 'path' ]) )) {
            try {
                $path = 'app/pages/weapp/order_shipping';
                $api = CoreWeappService::appApiClient();
                $result = $api->postJson('wxa/sec/order/set_msg_jump_path', [ 'path' => $path ]);
                if (isset($result[ 'errcode' ]) && $result[ 'errcode' ] == 0) {
                    $data = [
                        'path' => $path
                    ];
                    $this->setConfig($type, $data);
                }

                return $result;
            } catch (\Exception $e) {
            }
        }
        return [
            "errcode" => -1,
            "errmsg" => "系统繁忙"
        ];
    }

    /**
     * 获取物流公司，运力id列表
     * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/industry/express/business/express_open_msg.html#_4-4获取运力id列表get-delivery-list
     * 数据返回格式：{"errcode":0,"delivery_list":[{"delivery_id":"007EX","delivery_name":"俄顺达"},{"delivery_id":"138SD","delivery_name":"泰国138快递"}],"count":2}
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getDeliveryList()
    {
        $api = CoreWeappService::appApiClient();
        return $api->postJson('cgi-bin/express/delivery/open_msg/get_delivery_list', [ 'request' => 'post' ])->toArray();
    }

    /**
     * todo 暂无使用
     * 查询订单发货状态
     * 你可以通过交易单号或商户号+商户单号来查询该支付单的发货状态。
     * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html#三、查询订单发货状态
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getOrder(array $params = [])
    {
        $api = CoreWeappService::appApiClient();
        return $api->postJson('wxa/sec/order/get_order', $params)->toArray();
    }

    /**
     * todo 暂无使用
     * 查询订单列表
     * 你可以通过支付时间、支付者openid或订单状态来查询订单列表。
     * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/order-shipping/order-shipping.html#四、查询订单列表
     * @param array $params
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getOrderList(array $params = [])
    {
        $api = CoreWeappService::appApiClient();
        return $api->postJson('wxa/sec/order/get_order_list', $params)->toArray();
    }

    /**
     * 获取配置信息
     * @param string $type
     * @return array
     */
    public function getConfig($type)
    {
        $config_service = new CoreConfigService();
        $res = $config_service->getConfig('WEAPP_ORDER_SHIPPING_CONFIG_' . $type);
        return $res;
    }

    /**
     * 设置配置
     * @param string $type
     * @param array $value
     * @return SysConfig|bool|Model
     */
    public function setConfig($type, array $value)
    {
        $config_service = new CoreConfigService();
        $res = $config_service->setConfig('WEAPP_ORDER_SHIPPING_CONFIG_' . $type, $value);
        return $res;
    }

}
