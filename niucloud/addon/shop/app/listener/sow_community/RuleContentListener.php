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

namespace addon\shop\app\listener\sow_community;


/**
 * 获取赠送内容事件
 */
class RuleContentListener
{
    public function handle($params = [])
    {
        $data = [];
        if ($params[ 'key' ] == 'coupon') {
            $count = array_sum(array_column($params[ 'value' ], 'num'));
            $data[ 'label' ] = '优惠券'; // 标题
            $data[ 'info' ] = '优惠券*' . $count; // 大概信息
            $data[ 'detail' ] = []; // 详细信息
            foreach ($params[ 'value' ] as $k => $v) {
                $data[ 'detail' ][] = $v[ 'num' ] . '张 ' . $v[ 'title' ];
            }
        }
        return $data;
    }
}
