<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------
namespace core\dict;

class SowCommunityGift extends BaseDict
{
    /**
     * 获取种草赠送规则字典
     * @param array $data
     * @return array|mixed
     */
    public function load(array $data = [])
    {
        $addons = $this->getLocalAddons();
        foreach ($addons as $v) {
            $addon_change_type_file = $this->getAddonDictPath($v) . "sow_community" . DIRECTORY_SEPARATOR . "package_gift.php";
            if (is_file($addon_change_type_file)) {
                $account_change_type_files[] = $addon_change_type_file;
            }
        }
        $account_change_type_datas = $this->loadFiles($account_change_type_files);
        $account_change_type_array = [];
        foreach ($account_change_type_datas as $account_change_type_data) {
            $account_change_type_array = empty($account_change_type_array) ? $account_change_type_data : array_merge2($account_change_type_array, $account_change_type_data);
        }
        foreach ($account_change_type_array as $key => &$value) {
            $value[ 'key' ] = $key;
        }
        usort($account_change_type_array, function($list_one, $list_two) {
            return $list_one[ 'sort' ] <=> $list_two[ 'sort' ];
        });
        return $account_change_type_array;

    }
}
