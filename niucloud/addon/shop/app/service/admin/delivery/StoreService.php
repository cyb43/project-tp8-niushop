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

namespace addon\shop\app\service\admin\delivery;

use addon\shop\app\dict\delivery\DeliveryDict;
use addon\shop\app\model\delivery\Store;
use app\service\admin\sys\AreaService;
use app\service\core\sys\CoreAreaService;
use core\base\BaseAdminService;


/**
 * 自提门店服务层
 * Class StoreService
 * @package addon\shop\app\service\admin\delivery
 */
class StoreService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Store();
    }

    /**
     * 获取自提门店列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $field = 'time_week,trade_time_json,time_interval,store_id,store_name,store_desc,store_logo,store_mobile,province_id,city_id,district_id,address,full_address,longitude,latitude,trade_time,create_time,update_time';
        $order = 'create_time desc';

        $search_model = $this->model->where([ [ 'store_id', '>', 0 ] ])->withSearch([ "store_name", "create_time" ], $where)->field($field)->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取自提门店信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'time_week,trade_time_json,time_interval,store_id,store_name,store_desc,store_logo,store_mobile,province_id,city_id,district_id,address,full_address,longitude,latitude,trade_time,create_time,update_time';
        $info = $this->model->field($field)->where([ [ 'store_id', '=', $id ] ])->findOrEmpty()->toArray();
        $info[ 'province_name' ] = ( new AreaService() )->getAreaName($info[ 'province_id' ]);
        $info[ 'city_name' ] = ( new AreaService() )->getAreaName($info[ 'city_id' ]);
        $info[ 'district_name' ] = ( new AreaService() )->getAreaName($info[ 'district_id' ]);
        if (empty($info[ 'time_interval' ])) $info[ 'time_interval' ] = DeliveryDict::TIME_INTERVAL_30;
        return $info;
    }

    public function getInitInfo()
    {
        return [
            'week_list' => DeliveryDict::getWeekList(),
            'time_interval_list' => DeliveryDict::getTimeIntervalList()
        ];
    }

    /**
     * 添加自提门店
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data[ 'create_time' ] = time();
        $data[ 'update_time' ] = time();

        $data[ 'province_id' ] = ( new AreaService() )->getAreaId($data[ 'province_name' ], 1);
        $data[ 'city_id' ] = ( new AreaService() )->getAreaId($data[ 'city_name' ], 2);
        $data[ 'district_id' ] = ( new AreaService() )->getAreaId($data[ 'district_name' ], 3);
        if ($data[ 'full_address' ] == '') $data[ 'full_address' ] = ( new CoreAreaService() )->getFullAddress($data[ 'province_id' ], $data[ 'city_id' ], $data[ 'district_id' ], $data[ 'address' ], '');

        $res = $this->model->create($data);
        return $res->store_id;
    }

    /**
     * 自提门店编辑
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data, $address)
    {
        $data[ 'update_time' ] = time();
        $data[ 'province_id' ] = ( new AreaService() )->getAreaId($address[ 'province_name' ], 1);
        $data[ 'city_id' ] = ( new AreaService() )->getAreaId($address[ 'city_name' ], 2);
        $data[ 'district_id' ] = ( new AreaService() )->getAreaId($address[ 'district_name' ], 3);
        if ($data[ 'full_address' ] == '') $data[ 'full_address' ] = ( new CoreAreaService() )->getFullAddress($data[ 'province_id' ], $data[ 'city_id' ], $data[ 'district_id' ], $data[ 'address' ], '');

        $this->model->where([ [ 'store_id', '=', $id ] ])->update($data);
        return true;
    }

    /**
     * 删除自提门店
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        $res = $this->model->where([ [ 'store_id', '=', $id ] ])->delete();
        return $res;
    }

    /**
     * 获取自提门店列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        $field = 'store_id,store_name,store_desc,store_logo,store_mobile,province_id,city_id,district_id,address,full_address,longitude,latitude,trade_time,create_time,update_time';
        $order = 'create_time desc';

        $list = $this->model->where([ [ 'store_id', '>', 0 ] ])->withSearch([ "store_name", "create_time" ], $where)->field($field)->order($order)->select()->toArray();
        return $list;
    }

}
