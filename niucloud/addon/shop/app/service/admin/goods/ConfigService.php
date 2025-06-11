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

use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\service\core\goods\CoreGoodsConfigService;
use app\model\sys\SysConfig;
use core\base\BaseAdminService;
use core\exception\AdminException;
use think\facade\Db;


/**
 * 商品分类服务层
 * Class CategoryService
 * @package addon\shop\app\service\admin\goods
 */
class ConfigService extends BaseAdminService
{
    public $core_goods_config_service;

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysConfig();
        $this->core_goods_config_service = new CoreGoodsConfigService();
    }

    /**
     * 获取商品搜索配置
     * @return array
     */
    public function getSearchConfig()
    {
        return $this->core_goods_config_service->getSearchConfig();
    }

    /**
     * 设置商品搜索配置
     * @param $data
     * @return SysConfig|bool|\think\Model
     */
    public function setSearchConfig($data)
    {
        return $this->core_goods_config_service->setSearchConfig($data);
    }

    /**
     * 获取商品编码唯一值配置
     * @return array
     */
    public function getUniqueConfig()
    {
        return $this->core_goods_config_service->getUniqueConfig();
    }

    /**
     * 设置商品编码唯一值配置
     * @param $data
     * @return SysConfig|bool|\think\Model
     */
    public function setUniqueConfig($data)
    {
        return $this->core_goods_config_service->setUniqueConfig($data);
    }

    /**
     * 验证商品编码是否重复
     * @param $params
     */
    public function verifySkuNo($params)
    {
        $config_info = $this->core_goods_config_service->getUniqueConfig();
        if ($config_info['is_enable'] == 0) {
            return true;
        }

        if (empty($params['sku_no'])) {
            throw new AdminException("缺少参数sku_no");
        }

        $sku_no_arr = explode(',', $params['sku_no']);
        foreach ($sku_no_arr as $k=>$sku_no) {
            if (empty($sku_no)){
                unset($sku_no_arr[$k]);
            }
        }
        if (empty($sku_no_arr)){
            return true;
        }
        //判断传参中的sku_no是否有重复的
        if (count($sku_no_arr) > 1) {
            $counts = array_count_values($sku_no_arr);
            $duplicates = array_filter($counts, function ($count) {
                return $count > 1;
            });
            // 获取所有重复的值
            $duplicateValues = array_keys($duplicates);
            if (!empty($duplicateValues)) {
                throw new AdminException("商品编码[{$duplicateValues[0]}]已存在");
            }
        }
        $sql_arr = [];
        foreach ($sku_no_arr as $sku_no) {
            $sql_arr[] = "FIND_IN_SET('{$sku_no}', sku_no)";
        }
        $condition = [
            ['', 'exp', Db::raw(join(' or ', $sql_arr))],
        ];
        if (!empty($params['goods_id'])) {
            $condition[] = ['goods_id', '<>', $params['goods_id']];
        }
        $goods_sku_model = new GoodsSku();
        $info = $goods_sku_model->where($condition)->find();
        if (!empty($info)) {
            $exist_sku_no_arr = array_intersect($sku_no_arr, explode(',', $info['sku_no']));
            $exist_sku_no_arr = array_values($exist_sku_no_arr);
            throw new AdminException("商品编码[{$exist_sku_no_arr[0]}]已存在");
        }
        return true;
    }

    /**
     * 获取商品排序配置
     * @return array|int[]|mixed
     */
    public function getSortConfig()
    {
        return $this->core_goods_config_service->getSortConfig();
    }

    /**
     * 设置商品排序配置
     * @param $data
     * @return \addon\shop\app\service\core\goods\SysConfig|bool|\think\Model
     */
    public function setSortConfig($data)
    {
        return $this->core_goods_config_service->setSortConfig($data);
    }

}
