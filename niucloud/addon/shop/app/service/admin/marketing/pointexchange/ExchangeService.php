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

namespace addon\shop\app\service\admin\marketing\pointexchange;


use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\active\ExchangeDict;
use addon\shop\app\model\active\Active;
use addon\shop\app\model\exchange\Exchange;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\service\core\marketing\CoreActiveService;
use core\exception\CommonException;
use core\exception\AdminException;
use core\base\BaseAdminService;
use think\db\Query;
use think\facade\Db;
use addon\shop\app\model\active\ActiveGoods;


/**
 * 积分商城服务层
 * Class StoreService
 * @package addon\shop\app\service\admin\delivery
 */
class ExchangeService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Exchange();
    }

    /**
     * 获取积分商城列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'total_exchange_num,stock,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $order = 'id desc';
        $search_model = $this->model->where([ [ 'id', '>', 0 ] ])->withSearch([ 'names', 'status', 'create_time' ], $where)->append([ 'type_name', 'status_name', 'goods_cover_thumb_small' ])->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取积分商城分页列表（用于弹框选择）
     * @param array $where
     * @return array
     */
    public function getSelectPage(array $where = [])
    {
        $verify_ids=[];
        // 检测id集合是否存在，移除不存在的id，纠正数据准确性
        if (!empty($where[ 'verify_ids' ])) {
            $verify_ids = $this->model->where([
                [ 'id', 'in', $where[ 'verify_ids' ] ]
            ])->field('id')->select()->toArray();

            if (!empty($verify_ids)) {
                $verify_ids = array_column($verify_ids, 'id');
            }
        }

        $field = 'total_exchange_num,stock,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $order = 'id desc';
        $search_model = $this->model->where([ [ 'status', '=', ExchangeDict::UP ] ])->withSearch([ 'names', 'status', 'create_time' ], $where)->append([ 'type_name', 'status_name', 'goods_cover_thumb_small' ])->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        $list[ 'verify_ids' ] = $verify_ids;
        return $list;
    }

    /**
     * 获取积分商城详情
     * @param int $id
     * @return array
     */
    public function getDetail(int $id)
    {
        $field = 'stock,total_exchange_num,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $info = $this->model->where([ [ 'id', '=', $id ] ])->append([ 'type_name' ])->field($field)->findOrEmpty()->toArray();
        switch ($info[ 'type' ]) {
            case ExchangeDict::GOODS:
                $goods_id = 0;
                foreach ($info[ 'product_detail' ] as $k => $v) {
                    if (!empty($v[ 'goods_id' ])) {
                        $goods_id = $v[ 'goods_id' ];
                    }
                }
                $info[ 'goods_list' ] = ( new GoodsSku() )->where([ [ 'goods_id', '=', $goods_id ] ])->field('sku_spec_format,sku_id,sku_name,sku_image,sku_no,price as goods_price,stock as goods_stock')->select()->toArray();
                $info[ 'goods_info' ] = ( new Goods() )->where([ [ 'goods_id', '=', $goods_id ] ])->field('goods_id,goods_name,goods_image,goods_cover')->findOrEmpty()->toArray();
                $info[ 'goods_info' ][ 'spec_type' ] = empty($info[ 'goods_list' ][ 0 ][ 'sku_spec_format' ]) ? 'single' : 'multi';
                $info[ 'goods_info' ][ 'goods_price' ] = $info[ 'goods_list' ][ 0 ][ 'goods_price' ];
                foreach ($info[ 'goods_list' ] as &$item) {
                    $product_detail_key = array_search($item[ 'sku_id' ], array_column($info[ 'product_detail' ], 'sku_id'));
                    if ($product_detail_key !== false) {
                        $item[ 'is_enabled' ] = 1;
                        $item = array_merge($item, $info[ 'product_detail' ][ $product_detail_key ]);
                    } else {
                        $item[ 'is_enabled' ] = 0;
                    }
                }
                break;
        }
        return $info;
    }

    /**
     * 获取积分商城详情
     * @param $where
     * @return mixed
     */
    public function getInfo($where)
    {
        $field = 'stock,total_exchange_num,id,type,names,title,image,status,product_detail,point,price,limit_num,content,sort,total_point_num,total_price_num,total_order_num,total_member_num,update_time,create_time';
        $info = $this->model->withSearch([ 'ids', 'names', 'status', 'create_time', 'product_detail', 'sku_id', 'goods_id' ], $where)->append([ 'type_name' ])->field($field)->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 添加积分商城
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $product_detail = json_decode($data[ 'product_detail' ], true);
        $this->verifyData($data);
        $check_condition = [
            [ 'active_goods.active_class', '=', ActiveDict::EXCHANGE ],
            [ 'active_goods.goods_id', 'in', $product_detail[ 0 ][ 'goods_id' ] ],
        ];
        $goods_where = [];
        $active_goods_model = new ActiveGoods();
        $count = $active_goods_model->where($check_condition)
            ->withJoin([
                'active' => function(Query $query) use ($goods_where) {
                    $query->where($goods_where);
                },
            ], 'inner')
            ->count();
        if ($count > 0) throw new AdminException('EXCHANGE_GOODS_ACTIVITY_EXISTING');
        Db::startTrans();
        try {
            $create_data = [
                'names' => $data[ 'names' ] ?? '',
                'type' => $data[ 'type' ] ?? '',
                'title' => $data[ 'title' ] ?? '',
                'image' => $data[ 'image' ] ?? '',
                'product_detail' => $data[ 'product_detail' ] ?? '',
                'point' => $data[ 'point' ] ?? 0,
                'price' => $data[ 'price' ] ?? 0,
                'stock' => $data[ 'stock' ] ?? 0,
                'limit_num' => $data[ 'limit_num' ] ?? '',
                'content' => $data[ 'content' ] ?? '',
                'create_time' => time(),
                'update_time' => time(),
                'status' => ExchangeDict::UP,
            ];
            $res = $this->model->create($create_data);
            $active_goods[] = [
                'goods_id' => $product_detail[ 0 ][ 'goods_id' ],
                'active_goods_type' => ActiveDict::GOODS_SINGLE,
                'active_class' => ActiveDict::EXCHANGE,
                'active_goods_value' => json_encode($product_detail),
                'active_goods_status' => ActiveDict::ACTIVE,
                'active_goods_price' => $create_data[ 'price' ],
                'active_goods_point' => $create_data[ 'point' ],
            ];
            $data[ 'active_goods' ] = $product_detail;
            $data[ 'active_status' ] = ActiveDict::ACTIVE;
            $data[ 'active_type' ] = ActiveDict::GOODS;
            $data[ 'active_goods_type' ] = ActiveDict::GOODS_SINGLE;
            $data[ 'active_class' ] = ActiveDict::EXCHANGE;
            $data[ 'active_name' ] = $create_data[ 'names' ] ?? '';
            $data[ 'active_desc' ] = $create_data[ 'content' ] ?? '';
            $data[ 'start_time' ] = time();
            $data[ 'end_time' ] = 0;
            $data[ 'active_goods' ] = $active_goods;
            $active_id = ( new CoreActiveService() )->add($data);
            Db::commit();
            return $res->id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }


    /**
     * 验证积分商城 数据保存
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function verifyData(&$data)
    {
        if (empty(ExchangeDict::getType($data[ 'type' ]))) throw new AdminException('EXCHANGE_GOODS_CONFIRM_TYPE');
        $product_detail = json_decode($data[ 'product_detail' ], true);
        if ($data[ 'type' ] == 'goods') {
            if (empty($product_detail)) throw new AdminException('EXCHANGE_GOODS_NOT_EMPTY');
            $data[ 'stock' ] = 0;
            foreach ($product_detail as &$item) {
                if ($item[ 'point' ] <= 0) throw new AdminException('EXCHANGE_GOODS_POINT_GREATER_THAN_ZERO');
                if ($item[ 'stock' ] < 0) throw new AdminException('EXCHANGE_GOODS_STOCK_GREATER_THAN_ZERO');
                if (empty($item[ 'price' ])) $item[ 'price' ] = 0;
                $data[ 'stock' ] += $item[ 'stock' ];
            }
            array_multisort(array_column($product_detail, "point"), SORT_ASC, $product_detail);
            $data[ 'point' ] = $product_detail[ 0 ][ 'point' ];
            $data[ 'price' ] = $product_detail[ 0 ][ 'price' ];
            $data[ 'limit_num' ] = $product_detail[ 0 ][ 'limit_num' ];
            $data[ 'product_detail' ] = json_encode($product_detail);
        }
    }

    /**
     * 编辑积分商城
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $info = $this->model->where([ [ 'id', '=', $id ] ])->findOrEmpty();
        if ($info->isEmpty()) throw new AdminException('EXCHANGE_DATA_NOT_FOUND');
//        $product_detail = json_decode($data[ 'product_detail' ], true);
        $this->verifyData($data);
        Db::startTrans();
        try {
            $save_data = [
                'names' => $data[ 'names' ] ?? '',
                'type' => $data[ 'type' ] ?? '',
                'title' => $data[ 'title' ] ?? '',
                'image' => $data[ 'image' ] ?? '',
                'product_detail' => $data[ 'product_detail' ] ?? '',
                'point' => $data[ 'point' ] ?? 0,
                'price' => $data[ 'price' ] ?? 0,
                'stock' => $data[ 'stock' ] ?? 0,
                'limit_num' => $data[ 'limit_num' ] ?? '',
                'content' => $data[ 'content' ] ?? '',
                'update_time' => time(),
            ];
            $this->model->where([ [ 'id', '=', $id ] ])->update($save_data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 修改积分商品上下架状态
     * @param $data
     * @return Goods
     */
    public function editStatus($data, $id)
    {
        if ($data[ 'status' ] == 1) {
            $data_info = $this->getInfo([ 'ids' => $id ]);
            if (!empty($data_info))
                $goods_info = ( new Goods() )->where([ [ 'goods_id', '=', $data_info[ 'product_detail' ][ 0 ][ 'goods_id' ] ] ])->field('status')->findOrEmpty()->toArray();
            if ($goods_info[ 'status' ] == 0) throw new AdminException('SHOP_GOODS_DELISTED');
        }
        return $this->model->where([ [ 'id', '=', $id ] ])->update([ 'status' => $data[ 'status' ] ]);
    }


    /**
     * 修改排序号
     * @param $data
     * @return mixed
     */
    public function modifySort($data, $id = 0)
    {
        return $this->model->where([ [ 'id', '=', $id ] ])->update([ 'sort' => $data[ 'sort' ] ]);
    }


    /**
     * 删除积分商城
     * @param int $active_id
     * @return bool
     */
    public function del(int $id)
    {
        $info = $this->model->where([ [ 'id', '=', $id ] ])->findOrEmpty();
        if ($info->isEmpty()) throw new AdminException('EXCHANGE_DATA_NOT_FOUND');
        Db::startTrans();
        try {
            $goods_id = 0;
            foreach ($info[ 'product_detail' ] as $k => $v) {
                if (!empty($v[ 'goods_id' ])) {
                    $goods_id = $v[ 'goods_id' ];
                }
            }
            $active_info = ( new ActiveGoods() )->where([
                [ 'goods_id', 'in', $goods_id ],
                [ 'active_class', '=', ActiveDict::EXCHANGE ],
            ])->field('active_id')->findOrEmpty()->toArray();
            if (!empty($active_info)) ( new CoreActiveService() )->del($active_info[ 'active_id' ], 1);
            $this->model->where([ [ 'id', '=', $id ] ])->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 批量删除积分商城
     * @param array $ids
     * @return bool
     */
    public function batchDelete($ids)
    {
        $point_exchange_list = $this->model->where([ [ 'id', 'in', $ids ] ])->select()->toArray();
        foreach ($point_exchange_list as $value){
            if ($value[ 'status' ] == ExchangeDict::UP) throw new AdminException('EXIST_NOT_DOWN_ACTIVE');
        }

        $goods_ids = [];
        foreach ($point_exchange_list as $value){
            foreach ($value[ 'product_detail' ] as $v) {
                if (!empty($v[ 'goods_id' ])) {
                    $goods_ids[] = $v[ 'goods_id' ];
                }
            }
        }
        $goods_ids = array_unique($goods_ids);

        $active_ids = ( new ActiveGoods() )->where([
            [ 'goods_id', 'in', $goods_ids ],
            [ 'active_class', '=', ActiveDict::EXCHANGE ],
        ])->column('active_id');
        Db::startTrans();
        try {
            $this->model->where([ [ 'id', 'in', $ids ] ])->delete();
            (new Active())->where([ [ 'active_id', 'in', $active_ids ], [ 'active_class', '=', ActiveDict::EXCHANGE ] ])->delete();
            (new ActiveGoods())->where([ [ 'active_id', 'in', $active_ids ], [ 'active_class', '=', ActiveDict::EXCHANGE ] ])->delete();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 批量下架积分商城
     * @param array $ids
     * @return bool
     */
    public function batchDown($ids)
    {
        $this->model->where([ [ 'id', 'in', $ids ], [ 'status', '=', ExchangeDict::UP] ])->update( [ 'status' => ExchangeDict::DOWN] );

        return true;
    }

    /**
     * 批量上架积分商城
     * @param array $ids
     * @return bool
     */
    public function batchUp($ids)
    {
        $this->model->where([ [ 'id', 'in', $ids ], [ 'status', '=', ExchangeDict::DOWN] ])->update( [ 'status' => ExchangeDict::UP] );

        return true;
    }


    /**
     * 商品编辑业务
     * @param $exchange_goods_info
     * @param string $status
     * @param array $sku_list
     * @return bool
     */
    public function afterGoodsEdit($exchange_goods_info, $status = '', $sku_list = [])
    {
        Db::startTrans();
        try {
            if ($status != '' && $status == '0') $save_data[ 'status' ] = $status;
            if (!empty($sku_list)) {
                foreach ($exchange_goods_info[ 'product_detail' ] as &$item) {
                    $item[ 'stock' ] = $item[ 'stock' ] > $sku_list[ $item[ 'sku_id' ] ][ 'stock' ] ? $sku_list[ $item[ 'sku_id' ] ][ 'stock' ] : $item[ 'stock' ];
                }
                $save_data[ 'type' ] = $exchange_goods_info[ 'type' ];
                $save_data[ 'product_detail' ] = json_encode($exchange_goods_info[ 'product_detail' ]);
                $this->verifyData($save_data);
            }
            if (isset($save_data)) $this->model->where([ [ 'id', '=', $exchange_goods_info[ 'id' ] ] ])->update($save_data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }

    }


}
