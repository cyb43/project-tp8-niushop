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

namespace addon\shop\app\adminapi\controller\goods;

use core\base\BaseAdminController;
use addon\shop\app\service\admin\goods\BrandService;


/**
 * 商品品牌控制器
 * @description 商品品牌
 * Class Brand
 * @package addon\shop\app\adminapi\controller\goods
 */
class Brand extends BaseAdminController
{
   /**
    * 获取商品品牌分页列表
    * @description 查看列表-分页
    * @return \think\Response
    */
    public function pages(){
        $data = $this->request->params([
             [ "brand_name","" ],
             [ 'order', '' ],
             [ 'sort', '' ]
        ]);
        return success((new BrandService())->getPage($data));
    }

    /**
     * 获取商品品牌列表
     * @description 查看商品列表-全部
     * @return \think\Response
     */
    public function lists(){
        $data = $this->request->params([
            ["brand_name",""]
        ]);
        return success((new BrandService())->getList($data));
    }

    /**
     * 商品品牌详情
     * @description 查看详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id){
        return success((new BrandService())->getInfo($id));
    }

    /**
     * 添加商品品牌
     * @description 添加商品品牌
     * @return \think\Response
     */
    public function add(){
        $data = $this->request->params([
             ["brand_name",""],
             ["logo",""],
             ["desc",""],
             ["sort",0],
             ["color_json",""],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Brand.add');
        $id = (new BrandService())->add($data);
        return success('ADD_SUCCESS', ['id' => $id]);
    }

    /**
     * 商品品牌编辑
     * @description 编辑商品品牌
     * @param $id  商品品牌id
     * @return \think\Response
     */
    public function edit($id){
        $data = $this->request->params([
             ["brand_name",""],
             ["logo",""],
             ["desc",""],
             ["sort",0],
             ["color_json",""],
        ]);
        $this->validate($data, 'addon\shop\app\validate\goods\Brand.edit');
        (new BrandService())->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 商品品牌删除
     * @description 删除商品品牌
     * @param $id  商品品牌id
     * @return \think\Response
     */
    public function del(int $id){
        (new BrandService())->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 修改排序
     * @description 编辑品牌排序
     * @return \think\Response
     */
    public function modifySort()
    {
        $data = $this->request->params([
            [ 'brand_id', '' ],
            [ 'sort', '' ],
        ]);
        ( new BrandService() )->modifySort($data);
        return success('SUCCESS');
    }

}
