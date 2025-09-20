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

namespace addon\shop\app\service\admin\goods;

use addon\shop\app\model\goods\Brand;
use core\base\BaseAdminService;
use core\exception\AdminException;


/**
 * 商品品牌服务层
 * Class BrandService
 * @package addon\shop\app\service\admin\goods
 */
class BrandService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Brand();
    }

    /**
     * 获取商品品牌列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'brand_id,brand_name,logo,color_json,desc,sort,create_time';
        $order = 'brand_id desc';
        if (!empty($where[ 'order' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        }

        $search_model = $this->model->where([ [ 'brand_id', '>', 0 ] ])->withSearch([ "brand_name" ], $where)->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取商品品牌列表
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getList(array $where = [], $field = 'brand_id,brand_name,logo,color_json,desc,sort,create_time')
    {
        $order = 'create_time desc';
        return $this->model->where([ [ 'brand_id', '>', 0 ] ])->withSearch([ "brand_name" ], $where)->field($field)->limit(10)->order($order)->select()->toArray();
    }

    /**
     * 获取商品品牌信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'brand_id,brand_name,logo,color_json,desc,sort,create_time';
        $info = $this->model->field($field)->where([ [ 'brand_id', '=', $id ] ])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 添加商品品牌
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data[ 'create_time' ] = time();
        $brandInfo = $this->model->where([ [ 'brand_name', '=', $data[ 'brand_name' ] ] ])->findOrEmpty()->toArray();
        if ($brandInfo) {
            throw new AdminException('品牌已存在，请检查');
        }
        $res = $this->model->create($data);
        return $res->brand_id;

    }

    /**
     * 商品品牌编辑
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $data[ 'update_time' ] = time();
        $brandInfo = $this->model->where([ [ 'brand_name', '=', $data[ 'brand_name' ] ] ])->findOrEmpty()->toArray();
        if ($brandInfo && $brandInfo[ 'brand_id' ] != $id) {
            throw new AdminException('品牌已存在，请检查');
        }
        $this->model->where([ [ 'brand_id', '=', $id ] ])->update($data);
        return true;
    }

    /**
     * 删除商品品牌
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        $model = $this->model->where([ [ 'brand_id', '=', $id ] ])->find();
        $res = $model->delete();
        return $res;
    }

    /**
     * 修改排序
     * @param $data
     * @return Brand
     */
    public function modifySort($data)
    {
        return $this->model->where([
            [ 'brand_id', '=', $data[ 'brand_id' ] ],
        ])->update([ 'sort' => $data[ 'sort' ] ]);
    }

}
