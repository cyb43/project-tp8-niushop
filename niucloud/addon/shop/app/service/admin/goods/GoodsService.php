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

use addon\shop\app\dict\active\ActiveDict;
use addon\shop\app\dict\goods\GoodsDict;
use addon\shop\app\dict\order\OrderDict;
use addon\shop\app\model\active\ActiveGoods;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\model\goods\Brand;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\goods\GoodsSpec;
use addon\shop\app\model\goods\Stat;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\admin\marketing\ManjianService;
use addon\shop\app\service\core\goods\CoreGoodsConfigService;
use addon\shop\app\service\core\goods\CoreGoodsLimitBuyService;
use app\model\diy_form\DiyForm;
use app\model\member\Member;
use app\service\admin\addon\AddonService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use core\exception\CommonException;
use think\facade\Db;


/**
 * 商品服务层
 * Class GoodsService
 * @package addon\shop\app\service\admin\goods
 */
class GoodsService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Goods();
    }

    /**
     * 获取商品添加/编辑数据
     * @param array $params
     * @return array
     */
    public function getInit(array $params = [])
    {

        $res = [];

        $addon_service = new AddonService();
        $res[ 'addon_shop_supplier' ] = $addon_service->getInfoByKey('shop_supplier');

        if (!empty($params[ 'goods_id' ])) {
            // 查询商品信息，用于编辑
            $field = 'goods_id,goods_name,sub_title,goods_type,goods_cover,goods_image,goods_video,goods_desc,brand_id,goods_category,label_ids,service_ids,unit,stock,virtual_sale_num,is_limit,limit_type,max_buy,min_buy,status,sort,delivery_type,is_free_shipping,fee_type,delivery_money,delivery_template_id,supplier_id,attr_id,attr_format,member_discount,poster_id,is_gift,form_id';
            $goods_info = $this->model->field($field)->where([ [ 'goods_id', '=', $params[ 'goods_id' ] ] ])->findOrEmpty()->toArray();
            if (!empty($goods_info)) {

                if (!empty($goods_info[ 'goods_category' ])) {
                    $category_service = new CategoryService();
                    foreach ($goods_info[ 'goods_category' ] as $k => $v) {
                        $category = $category_service->getInfo($v);
                        if (empty($category)) {
                            unset($goods_info[ 'goods_category' ][ $k ]);
                        }
                    }
                }

                // 商品品牌，处理数据类型
                if (empty($goods_info[ 'brand_id' ])) {
                    $goods_info[ 'brand_id' ] = '';
                } else {
                    $brand_count = ( new Brand() )->where([ [ 'brand_id', '=', $goods_info[ 'brand_id' ] ] ])->count();
                    if ($brand_count == 0) $goods_info[ 'brand_id' ] = '';
                }

                // 供应商，处理数据类型
                if (empty($goods_info[ 'supplier_id' ])) {
                    $goods_info[ 'supplier_id' ] = '';
                }

                // 标签组
                if (empty($goods_info[ 'label_ids' ])) {
                    $goods_info[ 'label_ids' ] = [];
                } else {
                    $goods_info[ 'label_ids' ] = array_map(function ($item) {
                        return (int) $item;
                    }, $goods_info[ 'label_ids' ]);
                }

                // 商品服务
                if (empty($goods_info[ 'service_ids' ])) {
                    $goods_info[ 'service_ids' ] = [];
                } else {
                    $goods_info[ 'service_ids' ] = array_map(function ($item) {
                        return (int) $item;
                    }, $goods_info[ 'service_ids' ]);
                }

                // 商品参数，处理数据类型
                if (empty($goods_info[ 'attr_id' ])) {
                    $goods_info[ 'attr_id' ] = '';
                }

                // 商品海报id，处理数据类型
                if (empty($goods_info[ 'poster_id' ])) {
                    $goods_info[ 'poster_id' ] = '';
                }

                // 万能表单id，处理数据类型
                if (!empty($goods_info[ 'form_id' ])) {
                    $diy_form_model = new DiyForm();
                    $diy_form_count = $diy_form_model->where([
                        [ 'form_id', '=', $goods_info[ 'form_id' ] ]
                    ])->count();
                    if ($diy_form_count == 0) {
                        $goods_info[ 'form_id' ] = '';
                    }
                } else {
                    $goods_info[ 'form_id' ] = '';
                }

                //  配送方式
                if (empty($goods_info[ 'delivery_type' ])) {
                    $goods_info[ 'delivery_type' ] = [];
                }

                $goods_info[ 'status' ] = (string) $goods_info[ 'status' ];

                // 运费模板，处理数据类型
                if (empty($goods_info[ 'delivery_template_id' ])) {
                    $goods_info[ 'delivery_template_id' ] = '';
                }

                $goods_sku_model = new GoodsSku();

                $sku_field = 'sku_id,sku_name,sku_image,sku_no,goods_id,sku_spec_format,price,market_price,cost_price,stock,weight,volume,is_default';
                $sku_order = 'sku_id asc';
                $goods_info[ 'sku_list' ] = $goods_sku_model->withSearch([ "goods_id" ], [ 'goods_id' => $params[ 'goods_id' ] ])->field($sku_field)->order($sku_order)->select()->toArray();

                $temp_sku_data = array_filter(array_column($goods_info[ 'sku_list' ], 'sku_spec_format'));
                $goods_info[ 'spec_type' ] = 'single';
                if (!empty($temp_sku_data)) {
                    // 多规格
                    $goods_info[ 'spec_type' ] = 'multi';

                    $goods_spec_model = new GoodsSpec();
                    $spec_field = 'spec_id,goods_id,spec_name,spec_values';
                    $spec_order = 'spec_id asc';
                    $goods_info[ 'spec_list' ] = $goods_spec_model->withSearch([ "goods_id" ], [ 'goods_id' => $params[ 'goods_id' ] ])->field($spec_field)->order($spec_order)->select()->toArray();

                }

                // 查询商品参与营销活动的数量
                $goods_info[ 'active_goods_count' ] = $this->getActiveGoodsCount($goods_info[ 'goods_id' ]);

                $res[ 'goods_info' ] = $goods_info;
            }

        }
        $res[ 'default_sort' ] = ( new CoreGoodsConfigService() )->getDefaultSort();

        return $res;
    }

    /**
     * 获取商品列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'goods_id,goods_name,goods_type,goods_cover,stock,sale_num,status,sort,create_time,member_discount,is_gift';
        $order = 'create_time desc';
        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ]
        ];

        if (!empty($where[ 'start_price' ]) && !empty($where[ 'end_price' ])) {
            $money = [ $where[ 'start_price' ], $where[ 'end_price' ] ];
            sort($money);
            $sku_where[] = [ 'goodsSku.price', 'between', $money ];
        } else if (!empty($where[ 'start_price' ])) {
            $sku_where[] = [ 'goodsSku.price', '>=', $where[ 'start_price' ] ];
        } else if (!empty($where[ 'end_price' ])) {
            $sku_where[] = [ 'goodsSku.price', '<=', $where[ 'end_price' ] ];
        }
        if (!empty($where[ 'order' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        }

        $search_model = $this->model->where([ [ 'goods.goods_id', '>', 0 ] ])->withSearch([ "goods_name", "goods_type", "brand_id", "goods_category", "label_ids", 'service_ids', "sale_num", "status" ], $where)
            ->field($field)
            ->withJoin([
                'goodsSku' => [ 'sku_id', 'goods_id', 'price', 'member_price' ]
            ])->where($sku_where)->order($order)->append([ 'goods_type_name', 'goods_edit_path', 'goods_cover_thumb_small' ]);
        $list = $this->pageQuery($search_model);
        $list[ 'data' ] = $this->formatGoodsJoinActive($list[ 'data' ]);
        return $list;
    }

    /**
     * 处理商品参与的活动数据
     * @param $goods_data
     * @return mixed
     */
    private function formatGoodsJoinActive($goods_data)
    {
        $goods_ids = array_column($goods_data, 'goods_id');
        $join_list = event('GetGoodsJoinInfo', [
            'goods_ids' => $goods_ids,
        ]);
        $goods_join = [];
        foreach ($join_list as $item) {
            if (empty($item)) {
                continue;
            }
            foreach ($item as $goods_id => $value) {
                if (!isset($goods_join[ $goods_id ])) {
                    {
                        $goods_join[ $goods_id ] = [];
                    }
                }
                $goods_join[ $goods_id ] = array_merge($goods_join[ $goods_id ], array_values($value));
            }
        }
        if (!empty($goods_join)) {
            foreach ($goods_data as &$item) {
                $item[ 'active' ] = $goods_join[ $item[ 'goods_id' ] ] ?? [];
            }
        }
        return $goods_data;
    }

    /**
     * 获取商品信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'goods_id,goods_name,sub_title,goods_type,goods_cover,goods_image,goods_video,goods_desc,brand_id,goods_category,label_ids,service_ids,unit,stock,sale_num,virtual_sale_num,is_limit,limit_type,max_buy,min_buy,status,sort,delivery_type,is_free_shipping,fee_type,delivery_money,delivery_template_id,supplier_id,create_time,update_time,member_discount,poster_id,form_id';
        $info = $this->model->field($field)->where([ [ 'goods_id', '=', $id ] ])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 添加商品
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        try {
            Db::startTrans();
            $goods_sku_model = new GoodsSku();
            $goods_spec_model = new GoodsSpec();
            $goods_stat_model = new Stat();

            // 商品封面
            if (!empty($data[ 'goods_image' ])) $data[ 'goods_cover' ] = explode(',', $data[ 'goods_image' ])[ 0 ];

            $goods_data = [
                'goods_name' => $data[ 'goods_name' ],
                'sub_title' => $data[ 'sub_title' ],
                'goods_type' => $data[ 'goods_type' ],
                'goods_cover' => $data[ 'goods_cover' ],
                'goods_image' => $data[ 'goods_image' ],
                'goods_video' => $data[ 'goods_video' ],
                'goods_category' => array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'goods_category' ]),
                'goods_desc' => $data[ 'goods_desc' ],
                'brand_id' => $data[ 'brand_id' ],
                'label_ids' => array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'label_ids' ]),
                'service_ids' => array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'service_ids' ]),
                'unit' => $data[ 'unit' ],
                'stock' => $data[ 'stock' ],
                'virtual_sale_num' => $data[ 'virtual_sale_num' ],
                'is_limit' => $data[ 'is_limit' ],
                'limit_type' => $data[ 'limit_type' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'is_gift' => $data[ 'is_gift' ],
                'status' => $data[ 'status' ],
                'sort' => $data[ 'sort' ],
                'attr_id' => $data[ 'attr_id' ],
                'attr_format' => $data[ 'attr_format' ],
                'delivery_type' => $data[ 'delivery_type' ],
                'is_free_shipping' => $data[ 'is_free_shipping' ],
                'fee_type' => $data[ 'fee_type' ],
                'delivery_money' => $data[ 'delivery_money' ],
                'delivery_template_id' => $data[ 'delivery_template_id' ],
                'supplier_id' => $data[ 'supplier_id' ],
                'member_discount' => $data[ 'member_discount' ],
                'poster_id' => $data[ 'poster_id' ],
                'form_id' => $data[ 'form_id' ],
                'create_time' => time()
            ];
            $res = $this->model->create($goods_data);

            $sku_data = [];
            if ($data[ 'spec_type' ] == 'single') {
                if (!empty($data[ 'sku_no' ])) {
                    ( new ConfigService() )->verifySkuNo([ 'sku_no' => $data[ 'sku_no' ] ]);
                }
                // 单规格
                $sku_data = [
                    'sku_name' => '',
                    'sku_image' => $data[ 'goods_cover' ],
                    'sku_no' => $data[ 'sku_no' ],
                    'goods_id' => $res->goods_id,
                    'sku_spec_format' => '', // sku规格格式
                    'price' => $data[ 'price' ],
                    'market_price' => $data[ 'market_price' ],
                    'sale_price' => $data[ 'price' ],
                    'cost_price' => $data[ 'cost_price' ],
                    'stock' => $data[ 'stock' ],
                    'weight' => $data[ 'weight' ],
                    'volume' => $data[ 'volume' ],
                    'is_default' => 1
                ];
                $goods_sku_model->save($sku_data);

            } elseif ($data[ 'spec_type' ] == 'multi') {
                $sku_no = implode(',', array_column($data[ 'goods_sku_data' ] ?? [], 'sku_no'));
                if (!empty($sku_no)) {
                    ( new ConfigService() )->verifySkuNo([ 'sku_no' => $sku_no ]);
                }
                // 多规格数据
                $default_spec_count = 0;
                foreach ($data[ 'goods_sku_data' ] as $k => $v) {
                    $sku_spec_format = [];
                    foreach ($v[ 'sku_spec' ] as $ck => $cv) {
                        $sku_spec_format[] = $cv[ 'spec_value_name' ];
                    }
                    $sku_data[] = [
                        'sku_name' => $v[ 'spec_name' ],
                        'sku_image' => !empty($v[ 'sku_image' ]) ? $v[ 'sku_image' ] : $data[ 'goods_cover' ],
                        'sku_no' => $v[ 'sku_no' ],
                        'goods_id' => $res->goods_id,
                        'sku_spec_format' => implode(',', $sku_spec_format), // sku规格格式
                        'price' => $v[ 'price' ],
                        'market_price' => $v[ 'market_price' ],
                        'sale_price' => $v[ 'price' ],
                        'cost_price' => $v[ 'cost_price' ],
                        'stock' => $v[ 'stock' ],
                        'weight' => $v[ 'weight' ],
                        'volume' => $v[ 'volume' ],
                        'is_default' => $v[ 'is_default' ]
                    ];
                    if ($v[ 'is_default' ] == 1) $default_spec_count++;
                }

                if ($default_spec_count == 0) throw new AdminException('SHOP_GOODS_NOT_HAS_DEFAULT_SPEC');

                $goods_sku_model->insertAll($sku_data);

                // 商品规格值
                $spec_data = [];
                foreach ($data[ 'goods_spec_format' ] as $k => $v) {
                    $spec_values = [];
                    foreach ($v[ 'values' ] as $ck => $cv) {
                        $spec_values[] = $cv[ 'spec_value_name' ];
                    }
                    $spec_data[] = [
                        'goods_id' => $res->goods_id,
                        'spec_name' => $v[ 'spec_name' ],
                        'spec_values' => implode(',', $spec_values)
                    ];
                }
                $goods_spec_model->insertAll($spec_data);

            }

            //添加商品统计表数据
            $goods_stat_data = [
                'date' => date('Y-m-d'),
                'date_time' => strtotime(date('Y-m-d')),
                'goods_id' => $res->goods_id,
            ];
            $goods_stat_model->create($goods_stat_data);

            Db::commit();

            event('AfterGoodsEdit', [
                'goods_id' => $res->goods_id,
                'goods_data' => $goods_data,
                'sku_data' => $sku_data
            ]);

            return $res->goods_id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 商品编辑
     * @param int $goods_id
     * @param array $data
     * @return bool
     */
    public function edit(int $goods_id, array $data)
    {
        try {
            Db::startTrans();

            $goods_sku_model = new GoodsSku();
            $goods_spec_model = new GoodsSpec();
            $order_goods_model = new OrderGoods();

            // 查询商品参与营销活动的数量
            $active_goods_count = $this->getActiveGoodsCount($goods_id);
            if ($data[ 'status' ] == 0) {
                if ($active_goods_count > 0) {
                    throw new AdminException('SHOP_GOODS_PARTICIPATE_IN_ACTIVE_DISABLED_EDIT');
                }
            }

            // 商品封面
            if (!empty($data[ 'goods_image' ])) $data[ 'goods_cover' ] = explode(',', $data[ 'goods_image' ])[ 0 ];

            $goods_data = [
                'goods_name' => $data[ 'goods_name' ],
                'sub_title' => $data[ 'sub_title' ],
                'goods_type' => $data[ 'goods_type' ],
                'goods_cover' => $data[ 'goods_cover' ],
                'goods_image' => $data[ 'goods_image' ],
                'goods_video' => $data[ 'goods_video' ],
                'goods_category' => array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'goods_category' ]),
                'goods_desc' => $data[ 'goods_desc' ],
                'brand_id' => $data[ 'brand_id' ],
                'label_ids' => array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'label_ids' ]),
                'service_ids' => array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'service_ids' ]),
                'unit' => $data[ 'unit' ],
                'stock' => $data[ 'stock' ],
                'virtual_sale_num' => $data[ 'virtual_sale_num' ],
                'is_limit' => $data[ 'is_limit' ],
                'limit_type' => $data[ 'limit_type' ],
                'max_buy' => $data[ 'max_buy' ],
                'min_buy' => $data[ 'min_buy' ],
                'is_gift' => $data[ 'is_gift' ],
                'status' => $data[ 'status' ],
                'sort' => $data[ 'sort' ],
                'attr_id' => $data[ 'attr_id' ],
                'attr_format' => $data[ 'attr_format' ],
                'delivery_type' => $data[ 'delivery_type' ],
                'is_free_shipping' => $data[ 'is_free_shipping' ],
                'fee_type' => $data[ 'fee_type' ],
                'delivery_money' => $data[ 'delivery_money' ],
                'delivery_template_id' => $data[ 'delivery_template_id' ],
                'supplier_id' => $data[ 'supplier_id' ],
                'member_discount' => $data[ 'member_discount' ],
                'poster_id' => $data[ 'poster_id' ],
                'form_id' => $data[ 'form_id' ],
                'update_time' => time()
            ];

            $this->model->where([ [ 'goods_id', '=', $goods_id ] ])->update($goods_data);

            $sku_data = [];
            if ($data[ 'spec_type' ] == 'single') {
                if (!empty($data[ 'sku_no' ])) {
                    $check = [ 'sku_no' => $data[ 'sku_no' ], 'goods_id' => $goods_id ];
                    ( new ConfigService() )->verifySkuNo($check);
                }
                // 单规格
                $sku_data = [
                    'sku_name' => '',
                    'sku_image' => $data[ 'goods_cover' ],
                    'sku_no' => $data[ 'sku_no' ],
                    'goods_id' => $goods_id,
                    'sku_spec_format' => '', // sku规格格式
                    'market_price' => $data[ 'market_price' ],
                    'cost_price' => $data[ 'cost_price' ],
                    'weight' => $data[ 'weight' ],
                    'volume' => $data[ 'volume' ],
                    'stock' => $data[ 'stock' ],
                    'is_default' => 1
                ];

                // 未参与营销活动，则允许修改 原价、销售价
                if ($active_goods_count == 0) {
                    $sku_data[ 'price' ] = $data[ 'price' ];
                    $sku_data[ 'sale_price' ] = $data[ 'price' ];
                }

                $sku_count = $goods_sku_model->where([ [ 'goods_id', '=', $goods_id ] ])->count();
                if ($sku_count > 1) {

                    // 规格项发生变化，删除旧规格，添加新规格重新生成
                    $goods_sku_model->where([ [ 'goods_id', '=', $goods_id ] ])->delete();

                    // 防止存在遗留规格项，删除旧规格
                    $goods_spec_model->where([ [ 'goods_id', '=', $goods_id ] ])->delete();

                    // 新增规格
                    $goods_sku_model->create($sku_data);

                } else {

                    $goods_sku_model->where([ [ 'goods_id', '=', $goods_id ] ])->update($sku_data);

                    // 防止存在遗留规格项，删除旧规格
                    $goods_spec_model->where([ [ 'goods_id', '=', $goods_id ] ])->delete();
                }

            } elseif ($data[ 'spec_type' ] == 'multi') {
                $sku_no = implode(',', array_column($data[ 'goods_sku_data' ] ?? [], 'sku_no'));
                if (!empty($sku_no)) {
                    $check = [ 'sku_no' => $sku_no, 'goods_id' => $goods_id ];
                    ( new ConfigService() )->verifySkuNo($check);
                }
                // 多规格数据
                $first_sku_data = reset($data[ 'goods_sku_data' ]);

                // 商品正在参与营销活动，禁止修改规格
                if ($active_goods_count > 0 && empty($first_sku_data[ 'sku_id' ])) {
                    throw new AdminException('SHOP_GOODS_PARTICIPATE_IN_ACTIVE_DISABLED_EDIT');
                }

                // 检测规格项是否发生变化
                if (!empty($first_sku_data[ 'sku_id' ])) {
                    // 规格项没有变化，修改/新增规格数据

                    $sku_id_arr = [];
                    $default_spec_count = 0;
                    foreach ($data[ 'goods_sku_data' ] as $k => $v) {
                        $sku_spec_format = [];
                        foreach ($v[ 'sku_spec' ] as $ck => $cv) {
                            $sku_spec_format[] = $cv[ 'spec_value_name' ];
                        }
                        $sku_data = [
                            'sku_name' => $v[ 'spec_name' ],
                            'sku_image' => !empty($v[ 'sku_image' ]) ? $v[ 'sku_image' ] : $data[ 'goods_cover' ],
                            'sku_no' => $v[ 'sku_no' ],
                            'goods_id' => $goods_id,
                            'sku_spec_format' => implode(',', $sku_spec_format), // sku规格格式
                            'market_price' => $v[ 'market_price' ],
                            'cost_price' => $v[ 'cost_price' ],
                            'weight' => $v[ 'weight' ],
                            'volume' => $v[ 'volume' ],
                            'stock' => $v[ 'stock' ],
                            'is_default' => $v[ 'is_default' ]
                        ];

                        // 未参与营销活动，则允许修改 原价、销售价
                        if ($active_goods_count == 0) {
                            $sku_data[ 'price' ] = $v[ 'price' ];
                            $sku_data[ 'sale_price' ] = $v[ 'price' ];
                        }

                        if (!empty($v[ 'sku_id' ])) {
                            // 修改规格
                            $sku_id_arr[] = $v[ 'sku_id' ];
                            $goods_sku_model->where([ [ 'sku_id', '=', $v[ 'sku_id' ] ], [ 'goods_id', '=', $goods_id ] ])->update($sku_data);
                        } else {
                            // 新增规格
                            $sku_model = $goods_sku_model->create($sku_data);
                            $sku_id_arr[] = $sku_model->sku_id;
                        }
                        if ($v[ 'is_default' ] == 1) $default_spec_count++;
                    }

                    // 校验默认必须存在默认规格
                    if ($default_spec_count == 0) throw new AdminException('SHOP_GOODS_NOT_HAS_DEFAULT_SPEC');

                    $spec_id_list = $goods_spec_model->withSearch([ "goods_id" ], [ 'goods_id' => $goods_id ])->field('spec_id')->select()->toArray();
                    $spec_id_list = array_column($spec_id_list, 'spec_id');

                    // 商品规格值
                    foreach ($data[ 'goods_spec_format' ] as $k => $v) {
                        $spec_values = [];
                        foreach ($v[ 'values' ] as $ck => $cv) {
                            $spec_values[] = $cv[ 'spec_value_name' ];
                        }
                        $spec_data = [
                            'goods_id' => $goods_id,
                            'spec_name' => $v[ 'spec_name' ],
                            'spec_values' => implode(',', $spec_values)
                        ];
                        if (!empty($v[ 'spec_id' ])) {
                            // 修改规格值
                            $goods_spec_model->where([ [ 'goods_id', '=', $goods_id ], [ 'spec_id', '=', $v[ 'spec_id' ] ] ])->update($spec_data);
                            foreach ($spec_id_list as $ck => $cv) {
                                if ($v[ 'spec_id' ] == $cv) {
                                    unset($spec_id_list[ $ck ]);
                                }
                            }

                        } else {
                            // 添加规格值
                            $goods_spec_model->save($spec_data);
                        }
                    }

                    // 移除不存在的规格项
                    if (!empty($spec_id_list)) {
                        $goods_spec_model->where([ [ 'spec_id', 'in', implode(',', $spec_id_list) ] ])->delete();
                    }

                    // 移除不存在的商品SKU
                    $sku_id_list = $goods_sku_model->withSearch([ "goods_id" ], [ 'goods_id' => $goods_id ])->field('sku_id')->select()->toArray();
                    $sku_id_list = array_column($sku_id_list, 'sku_id');
                    foreach ($sku_id_list as $k => $v) {
                        foreach ($sku_id_arr as $ck => $cv) {
                            if ($v == $cv) {
                                unset($sku_id_list[ $k ]);
                            }
                        }
                    }
                    $sku_id_list = array_values($sku_id_list);

                    if (!empty($sku_id_list)) {

                        // 检测订单是否存在要删除的商品
                        $order_where = [
                            [ 'orderMain.status', 'in', [ OrderDict::NORMAL, OrderDict::WAIT_DELIVERY, OrderDict::WAIT_TAKE ] ], // 排除已完成、已关闭状态
                            [ 'goods_id', '=', $goods_id ],
                            [ 'sku_id', 'in', $sku_id_list ]
                        ];
                        $order_goods_count = $order_goods_model
                            ->withJoin([ 'orderMain' ])
                            ->where($order_where)->count();

                        if ($order_goods_count > 0) {
                            Db::rollback();
                            throw new CommonException('EXIST_ORDER_NOT_DELETE_GOODS');
                        }

                        $goods_sku_model->where([ [ 'sku_id', 'in', implode(',', $sku_id_list) ] ])->delete();
                    }

                } else {

                    // 检测订单是否存在要删除的商品
                    $order_where = [
                        [ 'orderMain.status', 'in', [ 1, 2, 3 ] ], // 排除已完成、已关闭状态
                        [ 'goods_id', '=', $goods_id ]
                    ];
                    $order_goods_count = $order_goods_model
                        ->withJoin([ 'orderMain' ])
                        ->where($order_where)->count();

                    if ($order_goods_count > 0) {
                        Db::rollback();
                        throw new CommonException('EXIST_ORDER_NOT_EDIT_GOODS');
                    }

                    // 规格项发生变化，删除旧规格，添加新规格重新生成
                    $goods_sku_model->where([ [ 'goods_id', '=', $goods_id ] ])->delete();
                    $goods_spec_model->where([ [ 'goods_id', '=', $goods_id ] ])->delete();

                    $default_spec_count = 0;
                    foreach ($data[ 'goods_sku_data' ] as $k => $v) {
                        $sku_spec_format = [];
                        foreach ($v[ 'sku_spec' ] as $ck => $cv) {
                            $sku_spec_format[] = $cv[ 'spec_value_name' ];
                        }
                        $sku_data[] = [
                            'sku_name' => $v[ 'spec_name' ],
                            'sku_image' => !empty($v[ 'sku_image' ]) ? $v[ 'sku_image' ] : $data[ 'goods_cover' ],
                            'sku_no' => $v[ 'sku_no' ],
                            'goods_id' => $goods_id,
                            'sku_spec_format' => implode(',', $sku_spec_format), // sku规格格式
                            'price' => $v[ 'price' ],
                            'sale_price' => $v[ 'price' ],
                            'market_price' => $v[ 'market_price' ],
                            'cost_price' => $v[ 'cost_price' ],
                            'stock' => $v[ 'stock' ],
                            'weight' => $v[ 'weight' ],
                            'volume' => $v[ 'volume' ],
                            'is_default' => $v[ 'is_default' ]
                        ];
                        if ($v[ 'is_default' ] == 1) $default_spec_count++;
                    }

                    // 校验默认必须存在默认规格
                    if ($default_spec_count == 0) throw new AdminException('SHOP_GOODS_NOT_HAS_DEFAULT_SPEC');

                    $goods_sku_model->saveAll($sku_data);

                    // 商品规格值
                    $spec_data = [];
                    foreach ($data[ 'goods_spec_format' ] as $k => $v) {
                        $spec_values = [];
                        foreach ($v[ 'values' ] as $ck => $cv) {
                            $spec_values[] = $cv[ 'spec_value_name' ];
                        }
                        $spec_data[] = [
                            'goods_id' => $goods_id,
                            'spec_name' => $v[ 'spec_name' ],
                            'spec_values' => implode(',', $spec_values)
                        ];
                    }
                    $goods_spec_model->saveAll($spec_data);

                }

            }

            Db::commit();

            event('AfterGoodsEdit', [
                'goods_id' => $goods_id,
                'goods_data' => $goods_data,
                'sku_data' => $sku_data
            ]);
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 删除商品
     * @param $goods_ids
     * @return bool
     */
    public function del($goods_ids)
    {
        // 查询商品参与营销活动的数量
        $active_goods_count = $this->getActiveGoodsCount($goods_ids);
        if ($active_goods_count > 0) {
            throw new AdminException('SHOP_GOODS_PARTICIPATE_IN_ACTIVE_DISABLED_EDIT');
        }
        // 查询商品是否参与礼品卡活动
        $is_connected = event('GoodsIsConnectedCard', [ 'goods_ids' => $goods_ids ]);
        if (!empty($is_connected) && isset($is_connected[ 0 ]) && $is_connected[ 0 ]) {
            throw new AdminException('GOODS_PARTICIPATE_IN_ACTIVE_DISABLED_DELETE');
        }

        // 删除之前下架商品
        $this->model->where([ [ 'goods_id', 'in', $goods_ids ] ])->update([ 'status' => 0 ]);
        $res = $this->model::destroy(function ($query) use ($goods_ids) {
            $query->where([ [ 'goods_id', 'in', $goods_ids ] ]);
        });
        return $res;
    }

    /**
     * 恢复商品
     * @param $goods_ids
     * @return bool
     */
    public function recycle($goods_ids)
    {
        $res = $this->model->restore([ [ 'goods_id', 'in', $goods_ids ] ]);
        return $res;
    }

    /**
     * 获取商品类型
     * @return array|mixed|string
     */
    public function getType()
    {
        return GoodsDict::getType();
    }

    /**
     * 修改商品排序号
     * @param array $data
     * @return Goods|bool
     */
    public function editSort($data)
    {
        return $this->model->where([ [ 'goods_id', '=', $data[ 'goods_id' ] ] ])->update([ 'sort' => $data[ 'sort' ] ]);
    }

    /**
     * 修改商品上下架状态
     * @param $data
     * @return Goods
     */
    public function editStatus($data)
    {
        if ($data[ 'status' ] == 0) {
            // 查询商品参与营销活动的数量
            $active_goods_count = $this->getActiveGoodsCount($data[ 'goods_ids' ]);
            if ($active_goods_count > 0) {
                throw new AdminException('SHOP_GOODS_PARTICIPATE_IN_ACTIVE_DISABLED_EDIT');
            }
        }
        return $this->model->where([ [ 'goods_id', 'in', $data[ 'goods_ids' ] ] ])->update([ 'status' => $data[ 'status' ] ]);
    }

    /**
     * 复制商品
     * @param int $goods_id
     * @return mixed
     */
    public function copy(int $goods_id)
    {
        try {
            Db::startTrans();
            $goods_sku_model = new GoodsSku();
            $goods_spec_model = new GoodsSpec();

            // 查询商品信息
            $field = 'goods_name,sub_title,goods_type,goods_cover,goods_image,goods_video,goods_desc,brand_id,goods_category,label_ids,service_ids,unit,stock,virtual_sale_num,is_limit,limit_type,max_buy,min_buy,status,sort,delivery_type,is_free_shipping,fee_type,delivery_money,delivery_template_id,supplier_id,attr_id,attr_format,virtual_auto_delivery,virtual_receive_type,virtual_verify_type,virtual_indate,poster_id,form_id';

            $goods_data = $this->model->field($field)->where([ [ 'goods_id', '=', $goods_id ] ])->findOrEmpty()->toArray();
            if (empty($goods_data)) {
                throw new AdminException('SHOP_GOODS_NOT_EXIST');
            }

            // 初始化数据
            $goods_data[ 'goods_name' ] .= '_副本';
            $goods_data[ 'sale_num' ] = 0;
            $goods_data[ 'create_time' ] = time();
            $goods_data[ 'sort' ] = 0;
            $goods_data[ 'status' ] = 0;

            // 添加商品
            $res = $this->model->create($goods_data);

            // 查询商品规格信息
            $sku_field = 'sku_id,sku_name,sku_image,sku_no,goods_id,sku_spec_format,price,market_price,cost_price,stock,weight,volume,is_default';

            $sku_order = 'sku_id asc';
            $goods_sku_list = $goods_sku_model->withSearch([ "goods_id" ], [ 'goods_id' => $goods_id ])->field($sku_field)->order($sku_order)->select()->toArray();

            // 添加商品规格
            foreach ($goods_sku_list as $k => $v) {
                unset($goods_sku_list[ $k ][ 'sku_id' ]);
                $goods_sku_list[ $k ][ 'sale_num' ] = 0;
                $goods_sku_list[ $k ][ 'goods_id' ] = $res->goods_id;
            }
            $goods_sku_model->saveAll($goods_sku_list);

            // 查询规格值信息
            $spec_field = 'spec_id,goods_id,spec_name,spec_values';
            $spec_order = 'spec_id asc';
            $spec_list = $goods_spec_model->withSearch([ "goods_id" ], [ 'goods_id' => $goods_id ])->field($spec_field)->order($spec_order)->select()->toArray();

            // 添加规格项/值
            if (!empty($spec_list)) {
                foreach ($spec_list as $k => $v) {
                    unset($spec_list[ $k ][ 'spec_id' ]);
                    $spec_list[ $k ][ 'goods_id' ] = $res->goods_id;
                }
                $goods_spec_model->saveAll($spec_list);
            }

            Db::commit();
            return $res->goods_id;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 查询回收站商品分页列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getRecyclePage(array $where = [])
    {
        $field = 'goods_id,goods_name,goods_type,goods_cover,goods_category,unit,stock,sale_num,virtual_sale_num,status,create_time,update_time';
        $order = 'create_time desc';
        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ]
        ];
        if (!empty($where[ 'order' ])) {
            $order = $where[ 'order' ] . ' ' . $where[ 'sort' ];
        }

        $search_model = $this->model->onlyTrashed()->withSearch([ "goods_id", "goods_name", "goods_type", "goods_category" ], $where)
            ->field($field)->withJoin([
                'goodsSku' => [ 'sku_id', 'sku_name', 'goods_id', 'price', 'stock' ],
            ])->where($sku_where)->order($order)->append([ 'goods_type_name', 'goods_edit_path', 'goods_cover_thumb_small' ]);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取商品选择分页列表
     * @param array $where
     * @return array
     */
    public function getSelectPage(array $where = [])
    {
        $field = 'goods_id, goods_name, goods_type, goods_cover,goods_image, stock,sub_title,goods_desc,is_gift';
        $order = 'sort desc,create_time desc';

        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ]
        ];
        if (!empty($where[ 'start_price' ]) && !empty($where[ 'end_price' ])) {
            $money = [ $where[ 'start_price' ], $where[ 'end_price' ] ];
            sort($money);
            $sku_where[] = [ 'goodsSku.price', 'between', $money ];
        } else if (!empty($where[ 'start_price' ])) {
            $sku_where[] = [ 'goodsSku.price', '>=', $where[ 'start_price' ] ];
        } else if (!empty($where[ 'end_price' ])) {
            $sku_where[] = [ 'goodsSku.price', '<=', $where[ 'end_price' ] ];
        }

        if (isset($where[ 'is_gift' ]) && $where[ 'is_gift' ] == GoodsDict::IS_GIFT) {
            $sku_where[] = [ 'goods.is_gift', 'in', [ GoodsDict::NOT_IS_GIFT, GoodsDict::IS_GIFT ] ];
        } else {
            $sku_where[] = [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ];
        }

        if (!empty($where[ 'keyword' ])) {
            $sku_where[] = [ 'goods_name|sub_title', 'like', '%' . $where[ 'keyword' ] . '%' ];
        }

        // 查询已选的
        if (!empty($where[ 'sku_ids' ])) {
            $goods_sku_model = new GoodsSku();
            $goods_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $where[ 'sku_ids' ] ]
            ])->field('goods_id')->select()->toArray();
            if (!empty($goods_ids)) {
                $goods_ids = array_column($goods_ids, 'goods_id');
            }
        }

        if (!empty($goods_ids) && empty($where[ 'goods_ids' ])) {
            $where[ 'goods_ids' ] = $goods_ids;
        }
        if (!empty($where[ 'goods_type' ])) {
            $sku_where[] = [ 'goods.goods_type', '=', $where[ 'goods_type' ] ];
        }

        if ($where[ 'select_type' ] == 'all') {
            $sku_where[] = [ 'goods.stock', '>', 0 ];
            $sku_where[] = [ 'status', '=', 1 ];
        }
        if ($where[ 'select_type' ] == 'selected') {
            $sku_where[] = [ 'goods.goods_id', 'in', $where[ 'goods_ids' ] ];
        }

        $verify_goods_ids = [];
        $verify_sku_ids = [];
        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_goods_ids' ])) {
            $verify_goods_ids = $this->model->where([
                [ 'goods_id', 'in', $where[ 'verify_goods_ids' ] ],
                [ 'status', '=', 1 ]
            ])->field('goods_id')->select()->toArray();

            if (!empty($verify_goods_ids)) {
                $verify_goods_ids = array_column($verify_goods_ids, 'goods_id');
            }
        }

        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_sku_ids' ])) {
            $goods_sku_model = new GoodsSku();
            $verify_sku_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $where[ 'verify_sku_ids' ] ]
            ])->field('sku_id')->select()->toArray();

            if (!empty($verify_sku_ids)) {
                $verify_sku_ids = array_column($verify_sku_ids, 'sku_id');
            }
        }

        $search_model = $this->model
            ->withSearch([ "goods_category", "goods_type" ], $where)
            ->field($field)
            ->withJoin([
                'goodsSku' => [ 'sku_id', 'sku_name', 'goods_id', 'price', 'stock', 'sku_spec_format' ],
            ])
            ->with([
                'skuList'
            ])
            ->where($sku_where)->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ]);
        $list = $this->pageQuery($search_model);

        $list[ 'verify_goods_ids' ] = $verify_goods_ids;
        $list[ 'verify_sku_ids' ] = $verify_sku_ids;

        return $list;
    }

    /**
     * 获取商品选择分页列表
     * @param array $where
     * @return array
     */
    public function getSelectSku(array $where = [])
    {
        $field = 'goods_id, goods_name, goods_type, goods_cover, stock,is_gift';
        $order = 'sort desc,create_time desc';

        $select_goods_list = [];// 已选商品列表

        if (isset($where[ 'is_gift' ]) && $where[ 'is_gift' ] == GoodsDict::IS_GIFT) {
            $sku_where[] = [ 'goods.is_gift', 'in', [ GoodsDict::NOT_IS_GIFT, GoodsDict::IS_GIFT ] ];
        } else {
            $sku_where[] = [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ];
        }

        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_goods_ids' ])) {
            $verify_goods_ids = $this->model->where([
                [ 'goods_id', 'in', $where[ 'verify_goods_ids' ] ],
                [ 'status', '=', 1 ]
            ])->field('goods_id')->select()->toArray();

            if (!empty($verify_goods_ids)) {
                $verify_goods_ids = array_column($verify_goods_ids, 'goods_id');
            }

            $select_goods_list = $this->model
                ->field($field)
                ->withJoin([
                    'goodsSku' => [ 'sku_id', 'sku_name', 'goods_id', 'price', 'stock', 'sku_spec_format' ],
                ])
                ->with([
                    'skuList'
                ])
                ->where([
                    [ 'goodsSku.is_default', '=', 1 ],
                    [ 'goods.goods_id', 'in', $verify_goods_ids ]
                ])
                ->where($sku_where)
                ->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ])
                ->select()->toArray();
        }

        // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
        if (!empty($where[ 'verify_sku_ids' ])) {
            $goods_sku_model = new GoodsSku();
            $verify_sku_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $where[ 'verify_sku_ids' ] ]
            ])->field('sku_id')->select()->toArray();

            if (!empty($verify_sku_ids)) {
                $verify_sku_ids = array_column($verify_sku_ids, 'sku_id');
            }

            $goods_ids = $goods_sku_model->where([
                [ 'sku_id', 'in', $verify_sku_ids ]
            ])->field('goods_id')->select()->toArray();
            if (!empty($goods_ids)) {
                $goods_ids = array_column($goods_ids, 'goods_id');
                $goods_ids = array_unique($goods_ids);
            }

            $select_goods_list = $this->model
                ->field($field)
                ->withJoin([
                    'goodsSku' => [ 'sku_id', 'sku_name', 'goods_id', 'price', 'stock', 'sku_spec_format' ],
                ])
                ->with([
                    'skuList'
                ])
                ->where([
                    [ 'goodsSku.is_default', '=', 1 ],
                    [ 'goods.goods_id', 'in', $goods_ids ]
                ])
                ->where($sku_where)
                ->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ])
                ->select()->toArray();
        }

        return $select_goods_list;
    }

    /**
     * 获取商品选择分页列表（代客下单专用）
     * @param array $where
     * @return array
     */
    public function getBuyGoodsSelect(array $where = [])
    {
        $field = 'goods_id, goods_name, goods_type, goods_cover,goods_image, stock,sub_title,goods_desc,is_limit,limit_type,max_buy,min_buy,member_discount';
        $order = 'sort desc,create_time desc';

        $sku_where = [
            [ 'goodsSku.is_default', '=', 1 ],
            [ 'goods.stock', '>', 0 ],
            [ 'status', '=', 1 ],
            [ 'goods.is_gift', '=', GoodsDict::NOT_IS_GIFT ]
        ];

        if (!empty($where[ 'keyword' ])) {
            $sku_where[] = [ 'goods_name|sub_title', 'like', '%' . $where[ 'keyword' ] . '%' ];
        }

        $search_model = $this->model
            ->withSearch([ "goods_category", "goods_type" ], $where)
            ->field($field)
            ->withJoin([
                'goodsSku' => [ 'sku_id', 'sku_name', 'goods_id', 'price', 'stock', 'sku_spec_format', 'market_price', 'sale_price', 'member_price' ],
            ])
            ->where($sku_where)->order($order)->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ]);
        $list = $this->pageQuery($search_model);
        if (!empty($where[ 'member_id' ])) {
            $member_info = $this->getMemberInfo($where[ 'member_id' ]);
            foreach ($list[ 'data' ] as $k => &$v) {
                if (!empty($v[ 'goodsSku' ])) {
                    $v[ 'goodsSku' ][ 'member_price' ] = $this->getMemberPrice($member_info, $v[ 'member_discount' ], $v[ 'goodsSku' ][ 'member_price' ], $v[ 'goodsSku' ][ 'price' ]);
                }
                // 限购查询当前会员已购数量
                $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($where[ 'member_id' ], $v[ 'goods_id' ]);
                $v[ 'has_buy' ] = $has_buy;
                // 满减活动
                $manjian_info = ( new ManjianService() )->getManjianInfo([ 'goods_id' => $v[ 'goods_id' ], 'sku_id' => $v[ 'goodsSku' ][ 'sku_id' ], 'member_id' => $where[ 'member_id' ] ]);
                $v[ 'manjian_info' ] = $manjian_info;
            }
        }
        return $list;
    }

    /**
     * 获取已选商品分页列表（代客下单专用）
     * @param array $where
     * @return array
     */
    public function getBuyGoodsSelected(array $where = [])
    {
        $field = 'sku_id, goods_id, sku_name, sku_image, price, stock, member_price, sale_price';
        $goods_sku_model = new GoodsSku();
        $select_goods_list = $goods_sku_model->where([
            [ 'sku_id', 'in', $where[ 'sku_ids' ] ]
        ])->with([ 'goods' ])->field($field)->append([ 'goods_cover_thumb_small', 'goods_cover_thumb_mid' ])->select()->toArray();
        if (!empty($where[ 'member_id' ])) {
            $member_info = $this->getMemberInfo($where[ 'member_id' ]);
            foreach ($select_goods_list as $k => &$v) {
                if (!empty($v[ 'goods' ])) {
                    $v[ 'member_price' ] = $this->getMemberPrice($member_info, $v[ 'goods' ][ 'member_discount' ], $v[ 'member_price' ], $v[ 'price' ]);
                }
                // 限购查询当前会员已购数量
                $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($where[ 'member_id' ], $v[ 'goods_id' ]);
                $v[ 'has_buy' ] = $has_buy;
                // 满减活动
                $manjian_info = ( new ManjianService() )->getManjianInfo([ 'goods_id' => $v[ 'goods_id' ], 'sku_id' => $v[ 'sku_id' ], 'member_id' => $where[ 'member_id' ] ]);
                $v[ 'manjian_info' ] = $manjian_info;
            }
        }
        return $select_goods_list;
    }

    /**
     * 获取商品规格信息，切换规格（代客下单专用）
     * @param array $data
     * @return array
     */
    public function getBuySkuSelect(array $data)
    {

        $field = 'sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, sale_num, is_default,member_price';

        $goods_sku_model = new GoodsSku();

        $info = $goods_sku_model->where([ [ 'sku_id', '=', $data[ 'sku_id' ] ] ])
            ->field($field)
            ->with([
                // 商品主表
                'goods' => function ($query) {
                    $query->withField('goods_id, goods_name, goods_type, sub_title, goods_cover, unit, stock, sale_num + virtual_sale_num as sale_num, status,member_discount,is_discount')
                        ->append([ 'goods_type_name', 'goods_cover_thumb_small', 'goods_cover_thumb_mid', 'goods_cover_thumb_big' ]);
                },
                // 商品规格列表
                'skuList' => function ($query) {
                    $query->field('sku_id, sku_name, sku_image, sku_no, goods_id, sku_spec_format, price, market_price, sale_price, stock, weight, volume, is_default,member_price');
                },
                // 商品规格项/规格值列表
                'goodsSpec' => function ($query) {
                    $query->field('spec_id, goods_id, spec_name, spec_values');
                },
            ])
            ->append([ 'sku_image_thumb_small', 'sku_image_thumb_mid', 'sku_image_thumb_big' ])
            ->findOrEmpty()->toArray();
        if (!empty($info) && !empty($data[ 'member_id' ])) {
            $member_info = $this->getMemberInfo($data[ 'member_id' ]);

            $info[ 'member_price' ] = $this->getMemberPrice($member_info, $info[ 'goods' ][ 'member_discount' ], $info[ 'member_price' ], $info[ 'price' ]);

            $this->getMemberPriceByList($member_info, $info[ 'goods' ][ 'member_discount' ], $info[ 'skuList' ]);
            // 限购查询当前会员已购数量
            $has_buy = ( new CoreGoodsLimitBuyService() )->getGoodsHasBuyNumber($data[ 'member_id' ], $info[ 'goods_id' ]);
            $info[ 'has_buy' ] = $has_buy;
            // 满减活动
            $manjian_info = ( new ManjianService() )->getManjianInfo([ 'goods_id' => $info[ 'goods_id' ], 'sku_id' => $info[ 'sku_id' ], 'member_id' => $data[ 'member_id' ] ]);
            $info[ 'manjian_info' ] = $manjian_info;
        }

        return $info;
    }

    /**
     * 查询商品SKU规格列表
     * @param $params
     * @return array
     */
    public function getSkuList($params)
    {
        $goods_sku_model = new GoodsSku();

        $field = 'sku_id, sku_name, sku_image,sku_no, goods_id, sku_spec_format, price, market_price, sale_price, cost_price, stock, weight, volume,member_price';
        $order = 'sku_id asc';
        $list = $goods_sku_model->where([ [ 'sku_id', '>', 0 ] ])->withSearch([ "goods_id" ], [ 'goods_id' => $params[ 'goods_id' ] ])->with([ 'goods' ])->field($field)->order($order)->select()->toArray();
        return $list;
    }

    /**
     * 商品数统计
     * @return int[]
     * @throws \think\db\exception\DbException
     */
    public function getGoodsCount()
    {
        $data = [
            "sale_goods_num" => 0, //销售
            "warehouse_goods_num" => 0, //仓库
        ];

        $data[ 'sale_goods_num' ] = $this->model->where([ [ 'status', '=', 1 ] ])->count();
        $data[ 'warehouse_goods_num' ] = $this->model->where([ [ 'status', '=', 0 ] ])->count();
        return $data;
    }

    /**
     * 编辑商品规格列表库存
     * @param $params
     * @return array|bool
     */
    public function editGoodsListStock($params)
    {
        try {
            Db::startTrans();

            $goods_info = $this->model->where([
                [ 'goods_id', '=', $params[ 'goods_id' ] ]
            ])->field('goods_type')->findOrEmpty()->toArray();

            if (empty($goods_info)) {
                throw new CommonException('SHOP_GOODS_NOT_EXIST');
            }

            $sku_list = $params[ 'sku_list' ];
            if (!empty($sku_list)) {
                $goods_stock = 0; // 总库存
                $goods_sku_model = new GoodsSku();
                foreach ($sku_list as $k => $v) {
                    $goods_stock += (int) $v[ 'stock' ];

                    $update_data = [
                        'stock' => $v[ 'stock' ],
                    ];

                    $goods_sku_model->where([
                        [ 'goods_id', '=', $params[ 'goods_id' ] ],
                        [ 'sku_id', '=', $v[ 'sku_id' ] ]
                    ])->update($update_data);
                }
                $this->model->where([
                    [ 'goods_id', '=', $params[ 'goods_id' ] ]
                ])->update([
                    'stock' => $goods_stock,
                ]);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 编辑商品规格列表价格
     * @param $params
     * @return array|bool
     */
    public function editGoodsListPrice($params)
    {
        try {
            Db::startTrans();

            $goods_info = $this->model->where([
                [ 'goods_id', '=', $params[ 'goods_id' ] ]
            ])->field('goods_id,goods_type')->findOrEmpty()->toArray();

            if (empty($goods_info)) {
                throw new CommonException('SHOP_GOODS_NOT_EXIST');
            }

            // 查询商品参与营销活动的数量
            $active_goods_count = $this->getActiveGoodsCount($goods_info[ 'goods_id' ]);

            $sku_list = $params[ 'sku_list' ];
            if (!empty($sku_list)) {
                $goods_sku_model = new GoodsSku();
                foreach ($sku_list as $k => $v) {
                    $update_data = [
                        'cost_price' => $v[ 'cost_price' ],
                        'market_price' => $v[ 'market_price' ],
                    ];

                    if ($active_goods_count == 0) {
                        $update_data[ 'price' ] = $v[ 'price' ];
                        $update_data[ 'sale_price' ] = $v[ 'price' ];
                    }

                    $goods_sku_model->where([
                        [ 'goods_id', '=', $params[ 'goods_id' ] ],
                        [ 'sku_id', '=', $v[ 'sku_id' ] ]
                    ])->update($update_data);
                }
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 编辑商品规格列表会员价格
     * @param $params
     * @return array|bool
     */
    public function editGoodsListMemberPrice($params)
    {
        try {
            Db::startTrans();

            $goods_info = $this->model->where([
                [ 'goods_id', '=', $params[ 'goods_id' ] ]
            ])->field('goods_type')->findOrEmpty()->toArray();

            if (empty($goods_info)) {
                throw new CommonException('SHOP_GOODS_NOT_EXIST');
            }

            // 修改商品的会员等级折扣
            $this->model->where([
                [ 'goods_id', '=', $params[ 'goods_id' ] ]
            ])->update([
                'member_discount' => $params[ 'member_discount' ]
            ]);

            $sku_list = $params[ 'sku_list' ];
            if (!empty($sku_list)) {
                $goods_sku_model = new GoodsSku();
                foreach ($sku_list as $k => $v) {
                    $update_data = [
                        'member_price' => json_encode($v[ 'member_price' ]),
                    ];

                    $goods_sku_model->where([
                        [ 'goods_id', '=', $params[ 'goods_id' ] ],
                        [ 'sku_id', '=', $v[ 'sku_id' ] ]
                    ])->update($update_data);
                }
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new CommonException($e->getMessage() . '，Line：' . $e->getLine() . '，File：' . $e->getFile());
        }
    }

    /**
     * 查询商品参与营销活动的数量
     * @param $goods_id
     * @return mixed
     */
    public function getActiveGoodsCount($goods_id)
    {
        // 判断 $goods_id 类型
        if (!is_array($goods_id)) {
            $goods_id = [ $goods_id ];
        }

        $join_list = event('GetGoodsJoinInfo', [
            'goods_ids' => $goods_id,
            'is_get_count' => 1
        ]);
        return array_sum($join_list);
    }

    public function getMemberInfo($member_id)
    {
        $member_model = new Member();
        $member_field = 'member_level';
        $member_info = $member_model->where([
            [ 'member_id', '=', $member_id ]
        ])->field($member_field)
            ->with([
                // 会员等级
                'memberLevelData' => function ($query) {
                    $query->field('level_id, level_name, status, level_benefits, level_gifts');
                },
            ])
            ->findOrEmpty()->toArray();
        return $member_info;
    }

    /**
     * 查询商品的会员价
     * @param $member_info
     * @param string $member_discount 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
     * @param string $member_price 会员价，json格式，指定会员价，数据结构为：{"level_12":"92.00","level_13":"72.00","level_14":"66.00","level_15":"45.00"}
     * @param $price
     * @return int|string
     */
    public function getMemberPrice($member_info, $member_discount, $member_price, $price)
    {
        if (empty($member_discount)) {
            return $price;
        }

        // 未找到会员，排除
        if (empty($member_info)) {
            return $price;
        }

        // 没有会员等级，排除
        if (!empty($member_info) && empty($member_info[ 'member_level' ])) {
            return $price;
        }

        if ($member_discount == 'discount') {
            // 按照会员等级折扣计算

            // 默认按会员享受折扣计算
            if (!empty($member_info[ 'memberLevelData' ][ 'level_benefits' ])
                && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ])
                && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'is_use' ])) {

                $price = number_format($price * $member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'discount' ] / 10, 2, '.', '');
            }

        } elseif ($member_discount == 'fixed_price') {
            // 指定会员价
            if (!empty($member_price)) {
                $member_price = json_decode($member_price, true);
                if (!empty($member_price[ 'level_' . $member_info[ 'member_level' ] ])) {
                    $member_level_price = $member_price[ 'level_' . $member_info[ 'member_level' ] ];
                    $price = number_format($member_level_price, 2, '.', '');
                }
            }
        }

        return $price;

    }

    /**
     * 查询商品的会员价
     * @param $member_info
     * @param string $member_discount 会员等级折扣，不参与：空，会员折扣：discount，指定会员价：fixed_price
     * @param $sku_list
     * @return int
     */
    public function getMemberPriceByList($member_info, $member_discount, &$sku_list)
    {

        // 是否按照原价返回
        $is_default = false;

        if (empty($member_discount)) {
            $is_default = true;
        }

        // 未找到会员，排除
        if (empty($member_info)) {
            $is_default = true;
        }

        // 没有会员等级，排除
        if (!empty($member_info) && empty($member_info[ 'member_level' ])) {
            $is_default = true;
        }

        foreach ($sku_list as $k => &$v) {

            if ($is_default) {
                $v[ 'member_price' ] = $v[ 'price' ];
            } else {
                if ($member_discount == 'discount') {
                    // 按照会员等级折扣计算

                    // 默认按会员享受折扣计算
                    if (!empty($member_info[ 'memberLevelData' ][ 'level_benefits' ])
                        && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ])
                        && !empty($member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'is_use' ])) {
                        $v[ 'member_price' ] = number_format($v[ 'price' ] * $member_info[ 'memberLevelData' ][ 'level_benefits' ][ 'discount' ][ 'discount' ] / 10, 2, '.', '');
                    } else {
                        $v[ 'member_price' ] = $v[ 'price' ];
                    }

                } elseif ($member_discount == 'fixed_price') {
                    // 指定会员价
                    if (!empty($v[ 'member_price' ])) {
                        $member_price = json_decode($v[ 'member_price' ], true); // 会员价，json格式，指定会员价
                        if (!empty($member_price[ 'level_' . $member_info[ 'member_level' ] ])) {
                            $member_level_price = $member_price[ 'level_' . $member_info[ 'member_level' ] ];
                            $v[ 'member_price' ] = number_format($member_level_price, 2, '.', '');
                        } else {
                            $v[ 'member_price' ] = $v[ 'price' ];
                        }
                    }
                }
            }
        }

        return $sku_list;
    }

    /**
     * 批量设置商品
     * @param $data
     * @return mixed
     */
    public function batchSet($data)
    {
        if (empty($data[ 'set_type' ])) throw new AdminException('NOT_GET_SET_TYPE');
        if (empty($data[ 'goods_ids' ])) throw new AdminException('NOT_GET_SHOP_INFO');
        $save_data = $filed_data = $sku_save_data = [];
        switch ($data[ 'set_type' ]) {
            case GoodsDict::LABEL :
                $filed_data[ 'label' ][ 'label_ids' ] = array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'set_value' ][ 'label_ids' ]);
                break;
            case GoodsDict::SERVICE :
                $filed_data[ 'service' ][ 'service_ids' ] = array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'set_value' ][ 'service_ids' ]);
                break;
            case GoodsDict::VIRTUAL_SALE_NUM :
                $filed_data[ 'virtual_sale_num' ][ 'virtual_sale_num' ] = $data[ 'set_value' ][ 'virtual_sale_num' ];
                break;
            case GoodsDict::CATEGORY :
                if (!isset($data[ 'set_value' ][ 'goods_category' ]) || empty($data[ 'set_value' ][ 'goods_category' ])) break;
                $filed_data[ 'category' ][ 'goods_category' ] = array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'set_value' ][ 'goods_category' ]);
                break;
            case GoodsDict::BRAND :
                $filed_data[ 'brand' ][ 'brand_id' ] = $data[ 'set_value' ][ 'brand_id' ];
                break;
            case GoodsDict::POSTER :
                $filed_data[ 'poster' ][ 'poster_id' ] = $data[ 'set_value' ][ 'poster_id' ];
                break;
            case GoodsDict::DIY_FORM :
                $filed_data[ 'diy_form' ][ 'form_id' ] = $data[ 'set_value' ][ 'form_id' ];
                break;
            case GoodsDict::GIFT :
                if (!isset($data[ 'set_value' ][ 'is_gift' ]) || !in_array($data[ 'set_value' ][ 'is_gift' ], [ GoodsDict::IS_GIFT, GoodsDict::NOT_IS_GIFT ])) break;
                $filed_data[ 'gift' ][ 'is_gift' ] = $data[ 'set_value' ][ 'is_gift' ];
                break;
            case GoodsDict::DELIVERY :
                if (!isset($data[ 'set_value' ][ 'delivery_type' ]) || empty($data[ 'set_value' ][ 'delivery_type' ])) break;
                $filed_data[ 'delivery' ][ 'delivery_type' ] = array_map(function ($item) {
                    return (string) $item;
                }, $data[ 'set_value' ][ 'delivery_type' ] ?? []);
                $filed_data[ 'delivery' ][ 'is_free_shipping' ] = $data[ 'set_value' ][ 'is_free_shipping' ] ?? 1;
                $filed_data[ 'delivery' ][ 'fee_type' ] = $data[ 'set_value' ][ 'fee_type' ] ?? 'template';
                $filed_data[ 'delivery' ][ 'delivery_money' ] = $data[ 'set_value' ][ 'delivery_money' ] ?? 0;
                $filed_data[ 'delivery' ][ 'delivery_template_id' ] = $data[ 'set_value' ][ 'delivery_template_id' ] ?? 0;
                break;
            case GoodsDict::STOCK :
                if (!isset($data[ 'set_value' ][ 'stock_type' ]) || empty($data[ 'set_value' ][ 'stock_type' ]) || !isset($data[ 'set_value' ][ 'stock' ]) || $data[ 'set_value' ][ 'stock' ] <= 0) break;

                $sku_list = ( new GoodsSku() )->where([ [ 'goods_id', 'in', $data[ 'goods_ids' ] ] ])->column('sku_id,stock,goods_id');
                $goods_stock_list = ( new Goods() )->where([ [ 'goods_id', 'in', $data[ 'goods_ids' ] ] ])->column('stock,goods_id');
                $sku_save_data = $goods_stock = [];
                foreach ($sku_list as $v) {
//                    $active_goods_count = $this->getActiveGoodsCount($v[ 'goods_id' ]);
//                    if ($active_goods_count > 0) {
//                        continue;
//                    }
                    if (!isset($goods_stock[ $v[ 'goods_id' ] ])) {
                        $goods_stock[ $v[ 'goods_id' ] ] = 0;
                    }
                    if ($data[ 'set_value' ][ 'stock_type' ] == 'inc') {
                        $item_stock = $v[ 'stock' ] + $data[ 'set_value' ][ 'stock' ];
                        $goods_stock[ $v[ 'goods_id' ] ] += $data[ 'set_value' ][ 'stock' ];
                    } else {
                        if ($data[ 'set_value' ][ 'stock' ] > $v[ 'stock' ]) {
                            $goods_stock[ $v[ 'goods_id' ] ] -= $v[ 'stock' ];
                        } else {
                            $goods_stock[ $v[ 'goods_id' ] ] -= $data[ 'set_value' ][ 'stock' ];
                        }
                        $item_stock = $v[ 'stock' ] - $data[ 'set_value' ][ 'stock' ];
                        $item_stock = max($item_stock, 0);
                    }
                    $sku_save_data[] = [ 'sku_id' => $v[ 'sku_id' ], 'stock' => $item_stock ];
                }
                foreach ($goods_stock_list as $k => $v) {
                    if (isset($goods_stock[ $v[ 'goods_id' ] ])) {
                        $save_data[ $k ][ 'goods_id' ] = $v[ 'goods_id' ];
                        $save_data[ $k ][ 'stock' ] = $v[ 'stock' ] + $goods_stock[ $v[ 'goods_id' ] ];
                    }
                }
                break;
        }

        if ($data[ 'set_type' ] == GoodsDict::STOCK) {
            if (!empty($sku_save_data) && !empty($save_data)) {
                Db::startTrans();
                try {
                    $this->model->saveAll($save_data);
                    ( new GoodsSku() )->saveAll($sku_save_data);
                    Db::commit();
                    return true;
                } catch (\Exception $e) {
                    Db::rollback();
                    throw new CommonException($e->getMessage());
                }
            }
        } else {
            if (!empty($filed_data)) {
                $field = array_keys($filed_data)[ 0 ];
                foreach ($data[ 'goods_ids' ] as $k => $v) {
                    $save_data[ $k ] = $filed_data[ $field ];
                    $save_data[ $k ][ 'goods_id' ] = $v;
                }
                $this->model->saveAll($save_data);
            }
        }

        return true;
    }

    /**
     * 获取商品排行榜统计类型
     * @return array
     */
    public function getBatchSetDict()
    {
        $list = GoodsDict::getBatchSetDict();
        return $list;
    }

    /**
     * 分类调整时使用（分类登记变化）   数据较多时需优化 Job
     * @return void
     */
    public function batchUpdateCategory($category_id, $old_pid, $new_pid)
    {
        $goods_category = [];

        $category_goods_list = ( new Goods() )->where([
            [ 'goods_id', '>', 0 ]
        ])->withSearch([ 'goods_category' ], [
            'goods_category' => $category_id
        ])->select()->toArray();
        if ($old_pid != 0 && $new_pid == 0) {

            foreach ($category_goods_list as $item) {

                $goods_category = array_map(function ($value) {
                    return (string) $value;
                }, $goods_category);
                ( new Goods() )->where([
                    [ 'goods_id', '=', $item[ 'goods_id' ] ]
                ])->update([
                    'goods_category' => array_values($goods_category)
                ]);

            }
        }

        foreach ($category_goods_list as $item) {
            //将无二级分类的顶级分类调整为二级分类
            $item_category = $item[ 'goods_category' ];
            if ($old_pid == 0 && $new_pid != 0) {
                $item_category[] = $new_pid;
            } else
                if ($old_pid != 0 && $new_pid == 0) { //将二级分类调整为顶级分类
                    foreach ($item_category as $k => $value) {
                        if ($value == $old_pid) {
                            unset($item_category[ $k ]);
                        }
                    }
                }
            $item_category = array_map(function ($value) {
                return (string) $value;
            }, $item_category);
            ( new Goods() )->where([
                [ 'goods_id', '=', $item[ 'goods_id' ] ]
            ])->update([
                'goods_category' => array_values($item_category)
            ]);
        }
    }

}
