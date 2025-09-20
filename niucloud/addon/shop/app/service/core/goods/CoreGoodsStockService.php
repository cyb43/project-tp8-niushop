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

namespace addon\shop\app\service\core\goods;

use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use core\base\BaseCoreService;
use think\facade\Log;

/**
 * 商品库存服务层
 */
class CoreGoodsStockService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Goods();
    }

    /**
     * 增加库存
     * @param $data
     * @return void
     */
    public function inc($data)
    {
        $this->model->where([['goods_id', '=', $data['goods_id']]])->inc('stock', $data['num'])->update();
        (new GoodsSku())->where([['sku_id', '=', $data['sku_id']]])->inc('stock', $data['num'])->update();
        return true;
    }

    public function batchUpdateStock($data)
    {
        if (!isset($data['goods']) || !isset($data['sku'])) {
            return false;
        }
        try {
            $this->model->saveAll($data['goods']);
            (new GoodsSku())->saveAll($data['sku']);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 减少库存
     * @param $data
     * @return void
     */
    public function dec($data)
    {
        $this->model->where([['goods_id', '=', $data['goods_id']]])->dec('stock', $data['num'])->update();
        (new GoodsSku())->where([['sku_id', '=', $data['sku_id']]])->dec('stock', $data['num'])->update();
        return true;
    }

}
