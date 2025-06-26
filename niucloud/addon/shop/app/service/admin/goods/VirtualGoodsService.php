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
use addon\shop\app\model\active\ActiveGoods;
use addon\shop\app\model\discount\DiscountGoods;
use addon\shop\app\model\goods\Brand;
use addon\shop\app\model\goods\Goods;
use addon\shop\app\model\goods\GoodsSku;
use addon\shop\app\model\goods\GoodsSpec;
use addon\shop\app\model\goods\Stat;
use addon\shop\app\model\order\OrderGoods;
use addon\shop\app\service\core\goods\CoreGoodsConfigService;
use app\model\diy_form\DiyForm;
use app\service\admin\addon\AddonService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use core\exception\CommonException;
use think\facade\Db;


/**
 * 虚拟商品服务层
 * Class VirtualGoodsService
 * @package addon\shop\app\service\admin\goods
 */
class VirtualGoodsService extends BaseAdminService
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
            $field = 'goods_id,goods_name,sub_title,goods_type,goods_cover,goods_image,goods_video,goods_desc,brand_id,goods_category,label_ids,service_ids,unit,stock,virtual_sale_num,is_limit,limit_type,max_buy,min_buy,status,sort,supplier_id,attr_ids,attr_format,virtual_auto_delivery,virtual_receive_type,virtual_verify_type,virtual_indate,member_discount,poster_id,is_gift,form_id';
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
                    $goods_info[ 'label_ids' ] = array_map(function($item) { return (int) $item; }, $goods_info[ 'label_ids' ]);
                }

                // 商品服务
                if (empty($goods_info[ 'service_ids' ])) {
                    $goods_info[ 'service_ids' ] = [];
                } else {
                    $goods_info[ 'service_ids' ] = array_map(function($item) { return (int) $item; }, $goods_info[ 'service_ids' ]);
                }

                // 商品参数，处理数据类型
                if (empty($goods_info[ 'attr_ids' ])) {
                    $goods_info['attr_ids'] = [];
                } else {
                    $goods_info['attr_ids'] = array_map(function ($item) {
                        return (int)$item;
                    }, $goods_info['attr_ids']);
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

                $goods_info[ 'status' ] = (string) $goods_info[ 'status' ];

                $goods_sku_model = new GoodsSku();

                $sku_field = 'sku_id,sku_name,sku_image,sku_no,goods_id,sku_spec_format,price,market_price,cost_price,stock,is_default';
                $sku_order = 'sku_id asc';
                $goods_info[ 'sku_list' ] = $goods_sku_model->withSearch([ "goods_id" ], [ 'goods_id' => $params[ 'goods_id' ] ])->field($sku_field)->order($sku_order)->select()->toArray();

                $goods_info[ 'spec_type' ] = 'single';
                if (count($goods_info[ 'sku_list' ]) > 1) {
                    // 多规格
                    $goods_info[ 'spec_type' ] = 'multi';

                    $goods_spec_model = new GoodsSpec();
                    $spec_field = 'spec_id,goods_id,spec_name,spec_values';
                    $spec_order = 'spec_id asc';
                    $goods_info[ 'spec_list' ] = $goods_spec_model->withSearch([ "goods_id" ], [ 'goods_id' => $params[ 'goods_id' ] ])->field($spec_field)->order($spec_order)->select()->toArray();

                }

                // 存在订单的虚拟商品（收货方式为 到店核销），则无法编辑收发货设置
                if ($goods_info[ 'virtual_receive_type' ] == 'verify') {
                    $order_goods_model = new OrderGoods();

                    // 检测订单是否存在
                    $order_where = [
                        [ 'orderMain.status', 'in', [ 1, 2, 3 ] ], // 排除已完成、已关闭状态
                        [ 'goods_id', '=', $goods_info[ 'goods_id' ] ],
                    ];
                    $goods_info[ 'order_goods_count' ] = $order_goods_model
                        ->withJoin([ 'orderMain' ])
                        ->where($order_where)->count();
                } else {
                    $goods_info[ 'order_goods_count' ] = 0;
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
                'goods_category' => array_map(function($item) { return (string) $item; }, $data[ 'goods_category' ]),
                'goods_desc' => $data[ 'goods_desc' ],
                'brand_id' => $data[ 'brand_id' ],
                'label_ids' => array_map(function($item) { return (string) $item; }, $data[ 'label_ids' ]),
                'service_ids' => array_map(function($item) { return (string) $item; }, $data[ 'service_ids' ]),
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
                'attr_ids' => $data[ 'attr_ids' ],
                'attr_format' => $data[ 'attr_format' ],
                'supplier_id' => $data[ 'supplier_id' ],
                'virtual_auto_delivery' => $data[ 'virtual_auto_delivery' ] ?? 0,
                'virtual_receive_type' => $data[ 'virtual_receive_type' ] ?? 'artificial',
                'virtual_verify_type' => $data[ 'virtual_verify_type' ] ?? 0,
                'virtual_indate' => $data[ 'virtual_indate' ] ?? 0,
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
                        'is_default' => $v[ 'is_default' ]
                    ];
                    if ($v[ 'is_default' ] == 1) $default_spec_count++;
                }

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
                        'goods_id' => $res->goods_id,
                        'spec_name' => $v[ 'spec_name' ],
                        'spec_values' => implode(',', $spec_values)
                    ];
                }
                $goods_spec_model->saveAll($spec_data);

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
                'goods_category' => array_map(function($item) { return (string) $item; }, $data[ 'goods_category' ]),
                'goods_desc' => $data[ 'goods_desc' ],
                'brand_id' => $data[ 'brand_id' ],
                'label_ids' => array_map(function($item) { return (string) $item; }, $data[ 'label_ids' ]),
                'service_ids' => array_map(function($item) { return (string) $item; }, $data[ 'service_ids' ]),
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
                'attr_ids' => $data[ 'attr_ids' ],
                'attr_format' => $data[ 'attr_format' ],
                'supplier_id' => $data[ 'supplier_id' ],
                'virtual_auto_delivery' => $data[ 'virtual_auto_delivery' ] ?? 0,
                'virtual_receive_type' => $data[ 'virtual_receive_type' ] ?? 'artificial',
                'virtual_verify_type' => $data[ 'virtual_verify_type' ] ?? 0,
                'virtual_indate' => $data[ 'virtual_indate' ] ?? 0,
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
                    'is_default' => 1
                ];

                // 未参与营销活动，则允许修改 原价、销售价、库存
                if ($active_goods_count == 0) {
                    $sku_data[ 'price' ] = $data[ 'price' ];
                    $sku_data[ 'sale_price' ] = $data[ 'price' ];
                    $sku_data[ 'stock' ] = $data[ 'stock' ];
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
                            'is_default' => $v[ 'is_default' ]
                        ];

                        // 未参与营销活动，则允许修改 原价、销售价
                        if ($active_goods_count == 0) {
                            $sku_data[ 'price' ] = $v[ 'price' ];
                            $sku_data[ 'sale_price' ] = $v[ 'price' ];
                            $sku_data[ 'stock' ] = $v[ 'stock' ];
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
                            [ 'orderMain.status', 'in', [ 1, 2, 3 ] ], // 排除已完成、已关闭状态
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
                            'market_price' => $v[ 'market_price' ],
                            'sale_price' => $v[ 'price' ],
                            'cost_price' => $v[ 'cost_price' ],
                            'stock' => $v[ 'stock' ],
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
     * 查询商品参与营销活动的数量
     * @param $goods_id
     * @return mixed
     */
    public function getActiveGoodsCount($goods_id)
    {
        $active_goods_model = new ActiveGoods();
        $discount_goods_model = new DiscountGoods();
        $field = 'active_goods_id,active_id';

        $condition = [];

        // 判断 $goods_id 类型
        if (is_array($goods_id)) {
            $condition[] = [ 'goods_id', 'in', $goods_id ];
        } else {
            $condition[] = [ 'goods_id', '=', $goods_id ];
        }

        // 活动商品条件
        $active_goods_count = $active_goods_model->where(array_merge($condition, [
            [ 'active_goods_status', '=', 'active' ],
            [ 'active_goods_type', 'in', [ ActiveDict::GOODS_SINGLE, ActiveDict::GOODS_INDEPENDENT ] ]
        ]))->field($field)->with([
            'active' => function($query) {
                $query->withField('active_id,active_name, active_desc, start_time, end_time');
            }
        ])->count();

        // 折扣商品条件
        $discount_goods_count = $discount_goods_model->where(array_merge($condition, [
            [ 'status', '=', 'active' ]
        ]))->field('discount_goods_id')->with([
            'discount' => function($query) {
                $query->withField('discount_id');
            }
        ])->count();

        return $active_goods_count + $discount_goods_count;
    }

}
