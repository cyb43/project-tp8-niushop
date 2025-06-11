<?php

namespace addon\shop\app\upgrade\v645;

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\model\active\Active;
use addon\shop\app\model\discount\Discount;
use addon\shop\app\model\discount\DiscountGoods;
use think\facade\Db;

class Upgrade
{

    public function handle()
    {
        $this->handleDiscountData();
    }

    /**
     * 处理限时折扣数据
     */
    private function handleDiscountData()
    {
        $data = ( new Active() )->where([ [ 'active_class', '=', ActiveDict::DISCOUNT ] ])->with([ 'activeGoods' ])->select()->toArray();
        try {
            if (!empty($data)) {
                $discount_data = $discount_goods_data = [];
                foreach ($data as $value) {
                    $discount_data[] = [
                        'discount_id' => $value[ 'active_id' ],
                        'name' => $value[ 'active_name' ],
                        'remark' => $value[ 'active_desc' ],
                        'start_time' => $this->getFormatTime($value[ 'start_time' ]),
                        'end_time' => $this->getFormatTime($value[ 'end_time' ]),
                        'status' => $value[ 'active_status' ],
                        'create_time' => $this->getFormatTime($value[ 'create_time' ]),
                        'update_time' => $this->getFormatTime($value[ 'update_time' ]),
                    ];
                    if (!empty($value[ 'activeGoods' ])) {
                        foreach ($value[ 'activeGoods' ] as $v) {
                            $active_goods_value = json_decode($v[ 'active_goods_value' ], true);
                            if (!empty($active_goods_value)) {
                                foreach ($active_goods_value as $item) {
                                    $discount_goods_data[] = [
                                        'discount_id' => $v[ 'active_id' ],
                                        'goods_id' => $v[ 'goods_id' ],
                                        'sku_id' => $item[ 'sku_id' ],
                                        'status' => $v[ 'active_goods_status' ],
                                        'type' => $item[ 'discount_type' ],
                                        'rate' => $item[ 'discount_rate' ],
                                        'reduce_money' => $item[ 'reduce_money' ],
                                        'discount_price' => $item[ 'discount_price' ],
                                        'is_enabled' => $item[ 'is_enabled' ] ?? 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            Db::startTrans();
            if (!empty($discount_data) && !empty($discount_goods_data)) {
                ( new Discount() )->where([ [ 'discount_id', '>', 0 ] ])->delete();
                ( new DiscountGoods() )->where([ [ 'discount_goods_id', '>', 0 ] ])->delete();
                ( new Discount() )->insertAll($discount_data);
                ( new DiscountGoods() )->insertAll($discount_goods_data);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

    public function getFormatTime($time)
    {
        if (is_numeric($time) && ( strlen($time) == 10 || strlen($time) == 13 )) {
            return (int) $time;
        }

        $timestamp = strtotime($time);

        return $timestamp !== false ? $timestamp : 0;
    }

}
