<?php
declare ( strict_types = 1 );

namespace app\listener\poster;

use app\dict\pay\PayDict;
use app\model\member\Member;
use app\model\pay\Pay;
use app\service\core\pay\CorePayChannelService;
use app\service\core\sys\CoreSysConfigService;

/**
 * 找朋友帮忙付海报数据
 */
class FriendspayPoster
{
    /**
     * 找朋友帮忙付海报
     * @param $data
     * @return array
     */
    public function handle($data)
    {
        $type = $data[ 'type' ];
        if ($type == 'friendspay') {
            // 找朋友帮忙付 海报模板数据

            $channel = $data[ 'channel' ];
            $param = $data[ 'param' ];
            $member_id = $param[ 'member_id' ] ?? 0;
            $trade_id = $param[ 'id' ] ?? 0;//交易id
            $trade_type = $param[ 'type' ] ?? '';//交易类型
            $mode = $param[ 'mode' ] ?? '';

            $url_data = [
                [ 'key' => 'id', 'value' => $trade_id ],
                [ 'key' => 'type', 'value' => $trade_type ]
            ];

            if ($mode == 'preview') {
                // 预览模式
                $return_data = [
                    'nickname' => '会员昵称的帮付',
                    'headimg' => 'static/resource/images/default_headimg.png',
                    'friendspay_message' => get_lang('dict_pay_config.pay_leave_message'),
                    'friendspay_money' => '￥369.00',
                    'url' => [
                        'url' => ( new CoreSysConfigService() )->getSceneDomain()[ 'wap_url' ],
                        'page' => 'app/pages/friendspay/money',
                        'data' => $url_data,
                    ],
                ];
                return $return_data;
            }

            $pay_info = ( new Pay() )->field('money')->where([
                [ 'trade_id', '=', $trade_id ],
                [ 'trade_type', '=', $trade_type ],
                [ 'status', '<>', PayDict::STATUS_CANCEL ],///不查询已取消的单据
            ])->findOrEmpty()->toArray();

            if (empty($pay_info)) return [];

            $member_info = [];

            if ($member_id > 0) {
                //查询会员信息
                $member_info = ( new Member() )->where([ [ 'member_id', '=', $member_id ] ])->findOrEmpty();

                if (!empty($member_info)) {
                    if (empty($member_info[ 'headimg' ])) {
                        $member_info[ 'headimg' ] = 'static/resource/images/default_headimg.png';
                    }
                }
            }

            $pay_config = [];
            $pay_type_list = ( new CorePayChannelService() )->getAllowPayTypeByChannel($channel, $trade_type);
            if (!empty($pay_type_list) && !empty($pay_type_list[ PayDict::FRIENDSPAY ])) {
                $pay_config = $pay_type_list[ PayDict::FRIENDSPAY ][ 'config' ];
            }

            $return_data = [
                'friendspay_message' => $pay_config[ 'pay_leave_message' ] ?? get_lang('dict_pay_config.pay_leave_message'),
                'friendspay_money' => '￥' . $pay_info[ 'money' ],
                'url' => [
                    'url' => ( new CoreSysConfigService() )->getSceneDomain()[ 'wap_url' ],
                    'page' => 'app/pages/friendspay/money',
                    'data' => $url_data,
                ],
            ];

            if (!empty($member_info)) {
                $return_data[ 'nickname' ] = mb_strlen($member_info[ 'nickname' ]) > 12 ? mb_substr($member_info[ 'nickname' ], 0, 12, 'utf-8') . '...的帮付' : $member_info[ 'nickname' ] . '的帮付';
                $return_data[ 'headimg' ] = $member_info[ 'headimg' ];
            }
            return $return_data;
        }
    }
}
