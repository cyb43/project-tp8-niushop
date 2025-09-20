<?php

namespace addon\shop\app\listener\seckill;

use addon\shop\app\service\admin\goods\OfferGoodsService;
use addon\shop\app\service\core\order\CoreSeckillOrderCreateService;
use think\facade\Log;

class SeckillOrderPushListener
{

    public function handle($params)
    {
        Log::write("秒杀推送订单");
        Log::write($params);
        try{
            if(!empty($params['source']) && $params['source'] == 'shop'){
                //创建商城订单
                $order_model = new CoreSeckillOrderCreateService();
                $data = [
                    'order_from'=>$params['order_from'] ?? '',
                    'member_id'=>$params['member_id'],
                    'extend_data'=>[
                        'relate_id'=>$params['seckill_id'],
                        'relate_order_id'=>$params['id'],
                        'relate_source'=>'seckill'
                    ],
                    'delivery'=>[
                        'delivery_type'=>'express',
                    ],
                    'take_address'=>[
                        'taker_name' => $params[ 'taker_name' ],
                        'taker_mobile' => $params[ 'taker_mobile' ],
                        'taker_province' => $params[ 'taker_province' ],
                        'taker_city' => $params[ 'taker_city' ],
                        'taker_district' => $params[ 'taker_district' ],
                        'taker_address' => $params[ 'taker_address' ],
                        'taker_full_address' => $params[ 'taker_full_address' ],
                        'taker_longitude' => $params[ 'taker_longitude' ] ?? '',
                        'taker_latitude' => $params[ 'taker_latitude' ] ?? '',
                    ],
                    'sku_data'=>[
                        [
                            'sku_id'=>$params['source_sku_id'],
                            'num'=>$params['num'],
                            'seckill_price'=>$params['goods_price']
                        ]
                    ],
                    'pay'=>$params['pay']
                ];
                return $order_model->create($data);
            }
        }catch (\Exception $e){
            Log::write("订单创建出错".$e->getMessage().'行号:'.$e->getLine());
            return false;
        }
    }

}