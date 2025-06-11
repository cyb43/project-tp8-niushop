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

namespace addon\shop\app\service\api\goods;

use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsCollect;
use addon\shop\app\model\goods\Stat;
use addon\shop\app\service\core\goods\CoreGoodsActivePriceService;
use addon\shop\app\service\core\goods\CoreGoodsCollectNumService;
use addon\shop\app\service\core\goods\CoreGoodsStatService;
use core\base\BaseApiService;
use core\exception\ApiException;
use core\exception\CommonException;

/**
 *  商品收藏服务层
 */
class GoodsCollectService extends BaseApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new GoodsCollect();
    }

    /**
     * 商品收藏列表
     */
    public function getMemberGoodsCollectList()
    {
        $search_model = $this->model->where([ [ 'member_id', '=', $this->member_id ],['goods.delete_time','=',0] ])
            ->withJoin(['goods'=> [ 'goods_id', 'goods_name', 'goods_cover', 'status', 'member_discount']])
            ->with(['goodsSku' ])
            ->append([ 'goods_cover_thumb_mid' ])
            ->order('create_time desc');
        $list = $this->pageQuery($search_model);

        if (!empty($list['data'])){
            $goods_active_price_service = (new CoreGoodsActivePriceService());
            foreach ($list['data'] as &$v){
                $v[ 'member_discount' ] = $v[ 'goods' ][ 'member_discount' ] ?? '';
                //获取展示活动价格
                $show_price_data = $goods_active_price_service->getActivePrice($v, $this->member_id);
                $v[ 'show_price' ] = $show_price_data[ 'show_price' ];
                $v[ 'show_type' ] = $show_price_data[ 'show_type' ];
            }
        }
        return $list;
    }

    /**
     * 商品添加收藏
     */
    public function addGoodsCollect($data)
    {
        $data[ 'member_id' ] = $this->member_id;
        $info = $this->model->where([
            [ 'member_id', '=', $data[ 'member_id' ] ],
            [ 'goods_id', '=', $data[ 'goods_id' ] ],
        ])->findOrEmpty()->toArray();

        $goods_info = ( new Goods() )->where([
            [ 'goods_id', '=', $data[ 'goods_id' ] ],
        ])->findOrEmpty()->toArray();
        if (empty($goods_info)) throw new ApiException("SHOP_GOODS_NOT_EXIST");

        if (!empty($info)) {
            throw new CommonException('MEMBER_ALREADY_COLLECT');//已收藏
        } else {
            // 添加
            $data[ 'create_time' ] = time();
            $res = $this->model->create($data);

            // 商品收藏统计
            CoreGoodsStatService::addStat([ 'goods_id' => $data[ 'goods_id' ], 'collect_num' => 1 ]);
            ( new CoreGoodsCollectNumService() )->inc([ 'goods_id' => $data[ 'goods_id' ], 'collect_num' => 1 ]);
            return $res->id;
        }
    }

    /**
     * 商品取消收藏
     */
    public function cancelGoodsCollect($data)
    {
        $res = $this->model->where([ [ 'goods_id', 'in', $data[ 'goods_ids' ] ], [ 'member_id', '=', $this->member_id ] ])->delete();
        // 商品收藏统计
        foreach ($data[ 'goods_ids' ] as $value) {
            $goods_info = ( new Goods() )->where([
                [ 'goods_id', '=', $value ]
            ])->findOrEmpty()->toArray();
            if (empty($goods_info)) continue;

            $stat_data = [
                'date' => date('Y-m-d', time()),
                'date_time' => strtotime(date('Y-m-d', time())),
                'goods_id' => $value,
            ];
            $collect_num = ( new Stat() )->where($stat_data)->value('collect_num');
            if ($collect_num > 0) {
                CoreGoodsStatService::addStat([ 'goods_id' => $value, 'collect_num' => -1 ]);
            }
            ( new CoreGoodsCollectNumService() )->inc([ 'goods_id' => $value, 'collect_num' => -1 ]);
        }
        return $res;
    }
}
