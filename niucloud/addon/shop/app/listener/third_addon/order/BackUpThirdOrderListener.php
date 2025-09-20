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

namespace addon\shop\app\listener\third_addon\order;

use addon\shop\app\service\core\third_addon\order\CoreCreateOrderService;
use addon\shop\app\service\core\third_addon\order\CoreSeckillOrderCreateService;
use think\facade\Log;

/**
 * 创建订单
 */
class BackUpThirdOrderListener
{
    public function handle($data)
    {
        $source = $data['source'] ?? '';
        // 合并初始日志：包含来源和数据概要
        Log::write("SHOP-PINTUANBackUpThirdOrder 开始处理订单，来源: {$source}，数据概要: " . json_encode([
                'source_id' => $data['source_id'] ?? 0,
                'order_count' => isset($data['order_data']) ? count($data['order_data']) : 0
            ]));

        try {
            $return = [];
            switch ($source) {
                case 'pintuan':
                    Log::write("SHOP-PINTUANBackUpThirdOrder 开始处理拼团订单");
                    $return = (new CoreCreateOrderService())->pintuanCreateOrder($data);
                    Log::write("SHOP-PINTUANBackUpThirdOrder 拼团订单处理完成");
                    break;
                case 'seckill':
                    $order_model = new CoreSeckillOrderCreateService();
                    return $order_model->create($data);
                default:
                    Log::write("SHOP-PINTUANBackUpThirdOrder 未知订单来源: {$source}");
            }

            Log::write("SHOP-PINTUANBackUpThirdOrder 订单处理流程正常结束，来源: {$source}");
            return $return;
        } catch (\Exception $e) {
            // 合并异常日志：包含消息和堆栈
            Log::error("PINTUAN-BackUpThirdOrder 订单处理异常（来源: {$source}）: {$e->getMessage()}\n堆栈: {$e->getTraceAsString()}");
            throw $e;
        }
    }
}