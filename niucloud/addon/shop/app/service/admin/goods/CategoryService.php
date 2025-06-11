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

use addon\shop\app\model\goods\Category;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\service\core\goods\CoreGoodsCategoryService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use core\exception\CommonException;


/**
 * 商品分类服务层
 * Class CategoryService
 * @package addon\shop\app\service\admin\goods
 */
class CategoryService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Category();
    }

    /**
     * 获取商品分级查询分类列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'category_id,category_name,image,level,pid,category_full_name,is_show,sort';
        $order = 'create_time desc';

        $search_model = $this->model->where([ [ 'category_id', '>', 0 ] ])->withSearch([ "category_name" ], $where)->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取商品分类列表
     * @param array $where
     * @param string $field
     * @return array
     */
    public function getList(array $where = [], $field = 'category_id,category_name,image,level,pid,category_full_name,is_show,sort')
    {
        $order = 'sort desc,create_time desc';
        return $this->model->where([ [ 'category_id', '>', 0 ] ])->withSearch([ "category_name", 'level' ], $where)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 查询商品分类树结构
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getTree()
    {
        return ( new CoreGoodsCategoryService() )->getTree([ [ 'category_id', '>', 0 ] ]);
    }

    /**
     * 获取商品分类信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'category_id,category_name,image,level,pid,category_full_name,is_show,sort';
        $info = $this->model->field($field)->where([ [ 'category_id', '=', $id ] ])->findOrEmpty()->toArray();
        if (!empty($info)) {
            $info[ 'child_count' ] = 0;
            if ($info[ 'level' ] == 1) {
                $info[ 'child_count' ] = $this->model->where([ [ 'pid', '=', $info[ 'category_id' ] ] ])->count();
            }
        }
        return $info;
    }

    /**
     * 添加商品分类
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data[ 'category_full_name' ] = $data[ 'category_name' ];
        $data[ 'level' ] = 1;

        $condition = [
            [ 'category_name', '=', $data[ 'category_name' ] ]
        ];
        if ($data[ 'pid' ] > 0) {
            $condition[] = [ 'pid', '=', $data[ 'pid' ] ];
        } else {
            $condition[] = [ 'level', '=', 1 ];
        }

        $categoryInfo = $this->model->where($condition)->findOrEmpty()->toArray();
        if ($categoryInfo) {
            throw new AdminException('分类已存在，请检查');
        }
        if ($data[ 'pid' ] > 0) {
            $info = $this->model->field("category_id, category_name")->where([ [ 'category_id', '=', $data[ 'pid' ] ] ])->findOrEmpty();
            if ($info->isEmpty()) throw new CommonException('SHOP_GOODS_CATEGORY_NOT_EXIST');
            $data[ 'category_full_name' ] = $info->category_name . '/' . $data[ 'category_name' ];
            $data[ 'level' ] = 2;
        }

        $data[ 'create_time' ] = time();
        $res = $this->model->create($data);
        return $res->category_id;
    }

    /**
     * 商品分类编辑
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $category_info = $this->getInfo($id);
        if ($category_info && $category_info[ 'category_id' ] != $id) {
            throw new AdminException('分类已存在，请检查');
        }
        if ($category_info[ 'level' ] == 1 && $category_info[ 'pid' ] != $data[ 'pid' ] && $category_info[ 'child_count' ] > 0) {
            throw new CommonException('SHOP_GOODS_CATEGORY_EXIST_CHILD');
        }

        $data[ 'category_full_name' ] = $data[ 'category_name' ];
        $data[ 'level' ] = 1;
        if ($data[ 'pid' ] > 0) {
            $info = $this->model->field("category_id, category_name")->where([ [ 'category_id', '=', $data[ 'pid' ] ] ])->findOrEmpty();
            if ($info->isEmpty()) throw new CommonException('SHOP_GOODS_CATEGORY_NOT_EXIST');
            $data[ 'category_full_name' ] = $info->category_name . '/' . $data[ 'category_name' ];
            $data[ 'level' ] = 2;
        }
        $data[ 'update_time' ] = time();
        try {
            (new GoodsService())->batchUpdateCategory($id,$category_info['pid'],$data['pid']);
            $this->model->where([ [ 'category_id', '=', $id ] ])->update($data);
            return true;
        }catch (AdminException $e) {
            throw new AdminException($e->getMessage());
        }

    }

    /**
     * 删除商品分类
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        $count = ( new Goods() )->withSearch([ 'goods_category' ], [ 'goods_category' => $id ])->count();
        if ($count) {
            throw new AdminException('SHOP_GOODS_CATEGORY_EXIST_GOODS');
        }

        $field = 'category_id,level,pid';
        $info = $this->model->field($field)->where([ [ 'category_id', '=', $id ] ])->findOrEmpty()->toArray();
        if (!empty($info[ 'level' ] == 1)) {
            // 删除子级分类
            $this->model->where([ [ 'pid', '=', $id ] ])->delete();
        }
        $res = $this->model->where([ [ 'category_id', '=', $id ] ])->delete();
        return $res;
    }

    /**
     * 拖拽商品分类
     * @param $id
     * @param $data
     */
    public function updateCategory($data)
    {
        foreach ($data[ 'category_sort_array' ] as $key => $val) {
            $info = $this->model->field("category_id, category_name")->where([ [ 'category_id', '=', $val[ 'category_id' ] ] ])->findOrEmpty();
            if (!$info->isEmpty()) {
                $data[ 'update_time' ] = time();
                $this->model->where([ [ 'category_id', '=', $val[ 'category_id' ] ] ])->update([ 'sort' => $val[ 'sort' ] ]);
            }
        }
        return true;
    }

    /**
     * 设置商品分类配置
     * @param array $params
     * @return array
     */
    public function setGoodsCategoryConfig(array $params = [])
    {
        return ( new CoreGoodsCategoryService() )->setGoodsCategoryConfig($params);
    }

    /**
     * 获取商品分类配置
     * @return array
     */
    public function getGoodsCategoryConfig()
    {
        return ( new CoreGoodsCategoryService() )->getGoodsCategoryConfig();
    }

    /**
     * 获取商品分类树结构供弹框调用
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getTreeComponents()
    {
        return ( new CoreGoodsCategoryService() )->getTree([
            [ 'is_show', '=', 1 ],
        ], 'category_id,category_name,image,level,pid');
    }

}
