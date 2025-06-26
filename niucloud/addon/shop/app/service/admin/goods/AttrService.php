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

use addon\shop\app\model\goods\Attr;
use core\base\BaseAdminService;


/**
 * 商品参数服务层
 * Class AttrService
 * @package addon\shop\app\service\admin\goods
 */
class AttrService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Attr();
    }

    /**
     * 获取商品参数分页列表
     * @param array $where
     * @param string $field
     * @param string $order
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getPage(array $where = [], $field = 'attr_id,attr_name,sort')
    {
        $order = 'attr_id desc';
        if (!empty($where[ 'order' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        }
        $search_model = $this->model->where([ [ 'attr_id', ">", 0 ] ])->withSearch([ "attr_id_arr", "attr_id", "attr_name" ], $where)->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取商品参数列表
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public function getList(array $where = [], $field = 'attr_id,attr_name,attr_value_format,sort')
    {
        $order = 'sort desc,attr_id desc';
        if (!empty($where[ 'order' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        }
        return $this->model->where([ [ 'attr_id', ">", 0 ] ])->withSearch([ "attr_id_arr", "attr_id", "attr_name" ], $where)->field($field)->order($order)->select()->toArray();
    }

    /**
     * 获取商品参数信息
     * @param int $id
     * @param string $field
     * @return mixed
     */
    public function getInfo(int $id, $field = 'attr_id,attr_name,attr_value_format,sort')
    {
        $info = $this->model->field($field)->where([ [ 'attr_id', "=", $id ] ])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 添加商品参数
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $res = $this->model->create($data);
        return $res->attr_id;
    }

    /**
     * 商品参数编辑
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $this->model->where([ [ 'attr_id', '=', $id ] ])->update($data);
        return true;
    }

    /**
     * 删除商品参数
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        $model = $this->model->where([ [ 'attr_id', '=', $id ] ])->find();
        $res = $model->delete();
        return $res;
    }

    /**
     * 修改商品参数排序号
     * @param $data
     * @return mixed
     */
    public function modifySort($data)
    {
        return $this->model->where([
            [ 'attr_id', '=', $data[ 'attr_id' ] ],
        ])->update([ 'sort' => $data[ 'sort' ] ]);
    }

    /**
     * 修改商品参数名称
     * @param $data
     * @return mixed
     */
    public function modifyAttrName($data)
    {
        return $this->model->where([
            [ 'attr_id', '=', $data[ 'attr_id' ] ],
        ])->update([ 'attr_name' => $data[ 'attr_name' ] ]);
    }

    /**
     * 修改商品参数值
     * @param $data
     * @return mixed
     */
    public function modifyAttrValueFormat($data)
    {
        return $this->model->where([
            [ 'attr_id', '=', $data[ 'attr_id' ] ],
        ])->update([ 'attr_value_format' => $data[ 'attr_value_format' ] ]);
    }

}
