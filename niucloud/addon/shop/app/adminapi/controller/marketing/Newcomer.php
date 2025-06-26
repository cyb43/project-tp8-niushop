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

namespace addon\shop\app\adminapi\controller\marketing;

use addon\shop\app\service\admin\marketing\NewcomerService;
use core\base\BaseAdminController;


/**
 * 新人专享控制器
 * Class Newcomer
 * @package addon\shop\app\adminapi\controller\marketing
 */
class Newcomer extends BaseAdminController
{

    /**
     * 新人专享设置
     * @description 设置新人专享配置
     * @return \think\Response
     */
    public function setConfig()
    {
        $data = $this->request->params([
            [ "active_status", '' ], //是否启用 active:开启 close:关闭
            [ "active_desc", '' ],
            [ "goods_data", '{}' ],
            [ "validity_type", 'day' ], //有效期类型，day：x天有效，date：指定过期日期
            [ "validity_day", 3 ], //有效天数
            [ "validity_time", 0 ], //有效时间
            [ "participation_way", 'never_order' ], //参与方式，never_order：从未下过单的会员，assign_time_order：指定时间内未下过单会员，assign_time_register：指定时间内注册的会员
            [ "appoint_time", 0 ], //指定时间
            [ "limit_num", 1 ], //限购数量
            [ "banner_list", [] ], //广告图
        ]);
        ( new NewcomerService() )->setConfig($data);
        return success('SET_SUCCESS');
    }

    /**
     * 获取新增专享配置
     * @description 获取新增专享配置
     * @return \think\Response
     */
    public function getConfig()
    {
        return success(( new NewcomerService() )->getConfig());
    }

    /**
     * 商品选择分页列表（按照单商品）
     * @description 商品选择分页列表（按照单商品）
     * @return \think\Response
     */
    public function select()
    {
        $data = $this->request->params([
            [ 'keyword', '' ], // 搜索关键词
            [ "goods_category", "" ], // 商品分类
            [ "goods_type", "" ], // 商品分类
            [ "select_type", "all" ], // 商品分类
            [ 'goods_ids', [] ], // 已选商品id集合
            [ 'sku_ids', [] ], // 已选商品规格id集合
            [ 'verify_goods_ids', [] ], // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
            [ 'verify_sku_ids', [] ] // 检测商品规格id集合是否存在，移除不存在的商品规格id，纠正数据准确性
        ]);

        return success(( new NewcomerService() )->getSelectPage($data));
    }

    /**
     * 已选商品列表
     * @description 获取已选商品列表
     * @return \think\Response
     */
    public function selectGoodsSku()
    {
        $data = $this->request->params([
            [ 'keyword', '' ], // 搜索关键词
            [ "goods_category", "" ], // 商品分类
            [ "goods_type", "" ], // 商品分类
            [ 'goods_ids', '' ], // 已选商品id集合
            [ 'verify_goods_ids', [] ], // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
            [ 'verify_sku_ids', [] ] // 检测商品规格id集合是否存在，移除不存在的商品规格id，纠正数据准确性
        ]);

        return success(( new NewcomerService() )->getSelectSku($data));
    }

}
