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

namespace app\service\api\sys;

use app\model\sys\SysArea;
use app\service\admin\sys\ConfigService;
use core\base\BaseApiService;
use core\exception\ApiException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 地区服务层
 * Class AreaService
 * @package app\service\admin\sys
 */
class AreaService extends BaseApiService
{
    public static $cache_tag_name = 'area_cache';

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysArea();
    }

    /**
     * 获取地区信息
     * @param int $pid //上级pid
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getListByPid(int $pid = 0)
    {

        $cache_name = self::$cache_tag_name . '_api_pid_' . $pid;
        return cache_remember(
            $cache_name,
            function() use ($pid) {
                return $this->model->where([ [ 'pid', '=', $pid ] ])->field('id, name')->select()->toArray();
            },
            [ self::$cache_tag_name ]
        );
    }

    /**
     * 查询地区树列表
     * @param int $level //层级1,2,3
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAreaTree(int $level = 3)
    {
        $cache_name = self::$cache_tag_name . '_api_tree_' . $level;
        return cache_remember(
            $cache_name,
            function() use ($level) {
                $list = $this->model->where([ [ 'level', '<=', $level ] ])->field('id, pid, name')->select()->toArray();
                return list_to_tree($list);
            },
            [ self::$cache_tag_name ]
        );
    }

    public function getAreaByAreaCode($id)
    {
        $cache_name = self::$cache_tag_name . '_api_area_' . $id;
        return cache_remember(
            $cache_name,
            function() use ($id) {
                $level = [ 1 => 'province', 2 => 'city', 3 => 'district' ];
                $tree = [];
                $area = $this->model->where([ [ 'id', '=', $id ] ])->field('id,level,pid,name')->findOrEmpty();

                if (!$area->isEmpty()) {
                    $tree[ $level[ $area[ 'level' ] ] ] = $area->toArray();

                    while ($area[ 'level' ] > 1) {
                        $area = $this->model->where([ [ 'id', '=', $area[ 'pid' ] ] ])->field('id,level,pid,name')->findOrEmpty();
                        $tree[ $level[ $area[ 'level' ] ] ] = $area->toArray();
                    }
                }
                return $tree;
            },
            [ self::$cache_tag_name ]
        );
    }

    /**
     * 通过经纬度查询地址
     * @param $params
     * @return array
     */
    public function getAddressByLatlng($params)
    {
        $url = 'https://apis.map.qq.com/ws/geocoder/v1/';
        $map = ( new ConfigService() )->getMap();

        $get_data = array(
            'key' => $map[ 'key' ],
            'location' => $params[ 'latlng' ],
            'get_poi' => 0, // 是否返回周边POI列表：1.返回；0不返回(默认)
        );

        $url = $url . '?' . http_build_query($get_data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $res = curl_exec($curl);
        $res = json_decode($res, true);
        if ($res) {
            curl_close($curl);

            if ($res[ 'status' ] == 0) {
                $return_array = $res[ 'result' ][ 'address_component' ] ?? []; // 地址部件，address不满足需求时可自行拼接
                $address_reference = $res[ 'result' ][ 'address_reference' ] ?? [];
                $address = $return_array[ 'street_number' ] ?? ''; // 门牌，可能为空字串
                if (empty($address)) {
                    $address = $return_array[ 'street' ] ?? ''; // 道路，可能为空字串
                }

                $town = $address_reference[ 'town' ] ?? [];
                $landmark_l1 = $address_reference[ 'landmark_l1' ] ?? [];
                $landmark_l2 = $address_reference[ 'landmark_l2' ] ?? [];

                $community = '';
                if (!empty($landmark_l2[ 'title' ])) {
                    $community = $landmark_l2[ 'title' ]; // 二级地标，较一级地标更为精确，规模更小
                } elseif (!empty($landmark_l1[ 'title' ])) {
                    $community = $landmark_l1[ 'title' ]; // 一级地标，可识别性较强、规模较大的地点、小区等
                } elseif (!empty($town[ 'title' ])) {
                    $community = $town[ 'title' ]; // 乡镇/街道（四级行政区划）
                }

                $address_data = array(
                    'province' => $return_array[ 'province' ] ?? '', // 省
                    'city' => $return_array[ 'city' ] ?? '', // 市
                    'district' => $return_array[ 'district' ] ?? '', // 区
                    'community' => $community,
                    'address' => $address,
                    'full_address' => $res[ 'result' ][ 'address' ] ?? '',
                    'formatted_addresses' => $res[ 'result' ][ 'formatted_addresses' ] ?? []
                );

                $province = '';
                if ($address_data[ 'province' ]) {
                    $province = str_replace('省', '', $address_data[ 'province' ]);
                    $province = str_replace('市', '', $province);
                }

                $city = $address_data[ 'city' ] ?? '';
                $district = $address_data[ 'district' ] ?? '';

                $province_info = $this->model->where([ [ 'name', 'like', '%' . $province . '%' ], [ 'level', '=', 1 ] ])->field('id,name')->select()->toArray()[ 0 ] ?? [];

                $province_id = 0;
                $province_name = $address_data[ 'province' ];

                $city_id = 0;
                $city_name = $address_data[ 'city' ];

                $district_id = 0;
                $district_name = $address_data[ 'district' ];

                if (!empty($province_info)) {
                    $province_id = $province_info[ 'id' ];
                    $province_name = $province_info[ 'name' ];
                }

                if ($province_id > 0) {
                    $city_info = $this->model->where([ [ 'name', 'like', '%' . $city . '%' ], [ 'level', '=', 2 ], [ 'pid', '=', $province_id ] ])->field('id,name')->select()->toArray()[ 0 ] ?? [];

                    if (!empty($city_info)) {
                        $city_id = $city_info[ 'id' ];
                        $city_name = $city_info[ 'name' ];
                    }
                }

                if ($city_id > 0 && $province_id > 0) {
                    $district_info = $this->model->where([ [ 'name', 'like', '%' . $district . '%' ], [ 'level', '=', 3 ], [ 'pid', '=', $city_id ] ])->field('id,name')->select()->toArray()[ 0 ] ?? [];

                    if (!empty($district_info)) {
                        $district_id = $district_info[ 'id' ];
                        $district_name = $district_info[ 'name' ];
                    }
                }

                return [
                    'province_id' => $province_id,
                    'province' => $province_name,

                    'city_id' => $city_id,
                    'city' => $city_name,

                    'district_id' => $district_id,
                    'district' => $district_name,

                    'community' => $address_data[ 'community' ],

                    'full_address' => $address_data[ 'full_address' ],
                    'formatted_addresses' => $address_data[ 'formatted_addresses' ]
                ];
            } else {
                throw new ApiException('请检查地图配置：'.$res[ 'message' ]);
            }

        } else {
            $error = curl_errno($curl);
            curl_close($curl);
            throw new ApiException($error);
        }
    }

}
