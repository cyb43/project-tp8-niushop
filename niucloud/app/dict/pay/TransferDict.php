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

namespace app\dict\pay;

class TransferDict
{

    public const WECHAT = 'wechatpay';//微信零钱

    public const ALIPAY = 'alipay';//支付宝支付  (默认收款码)

    public const OFFLINE = 'offline';//线下转账

    public const BANK = 'bank';//银行卡转账


    public const WECHAT_CODE = 'wechat_code';//微信收款码(线下转账)

    //转账状态

    public const SUCCESS = 'success';//转账成功
    public const DEALING = 'dealing';//处理中

    public const WAIT = 'wait';//待转账

    public const WAIT_USER = 'wait_user';//等待用户确认
    public const WAIT_USER_ING = 'wait_user_ing';//用户确认.转账中

    public const FAIL_ING = 'fail_ing';//转账撤销中
    public const FAIL = 'fail';//失败


    public static function getPayTypeByTransfer(string $type = '')
    {
        $list = array(
            self::WECHAT => PayDict::WECHATPAY,
            self::ALIPAY => PayDict::ALIPAY,
        );
        if (empty($type))
            return $list;
        return $list[$type];

    }

    /**
     * 转账类型
     * @return array
     */
    public static function getTransferType(array $types = [], $is_all = true)
    {
        $list = [
            self::WECHAT => [
                'name' => get_lang('dict_transfer.type_wechat'),
                'key' => self::WECHAT,
                'is_online' => true
            ],//微信
            self::WECHAT_CODE => [
                'name' => get_lang('dict_transfer.type_wechat_code'),
                'key' => self::WECHAT_CODE,
                'is_online' => false
            ],//微信收款码(线下转账)
            self::ALIPAY => [
                'name' => get_lang('dict_transfer.type_ali'),
                'key' => self::ALIPAY,
                'is_online' => false
            ],//支付宝
            self::BANK => [
                'name' => get_lang('dict_transfer.type_bank'),
                'key' => self::BANK,
                'is_online' => false
            ],//银行卡
        ];
        if ($is_all) {
            $list[self::OFFLINE] = [
                'name' => get_lang('dict_transfer.type_offline'),
                'key' => self::OFFLINE,
                'is_online' => false
            ];
        }
        if (!empty($types)) {
            foreach ($list as $k => $v) {
                if (!in_array($k, $types)) {
                    unset($list[$k]);
                }
            }
        }
        return $list;
    }

    /**
     * 获取状态
     * @return array
     */
    public static function getStatus()
    {

        return [
            self::WAIT => get_lang('dict_transfer.status_wait'),
            self::DEALING => get_lang('dict_transfer.status_dealing'),
            self::WAIT_USER => get_lang('dict_transfer.status_wait_user'),
            self::WAIT_USER_ING => get_lang('dict_transfer.wait_user_ing'),
            self::SUCCESS => get_lang('dict_transfer.status_success'),
            self::FAIL => get_lang('dict_transfer.status_fail'),


            self::FAIL_ING => get_lang('dict_transfer.status_fail_ing'),
        ];
    }


    public const XJYX = 'xjyx';

    public const QYPF = 'qypf';
    public const YJBC = 'yjbc';
    public const CGHK = 'cghk';
    public const ESHS = 'eshs';
    public const GYBZ = 'gybz';
    public const XZBT = 'xzbt';
    public const BXLP = 'bxlp';

    /**
     * 获取微信转账场景
     * @return array[]
     */
    public static function getWechatTransferScene(){
        return [
            self::YJBC =>  [
                'name' => '佣金报酬',
                'user_recv_perception' => [
                    '劳务报酬',
                    '报销款',
                    '企业补贴',
                    '开工利是'
                ],
                'transfer_scene_report_infos' => [
                    '岗位类型',
                    '报酬说明'
                ]
            ],
            self::XJYX =>  [
                'name' => '现金营销',
                'user_recv_perception' => [
                    '活动奖励',
                    '现金奖励',
                ],
                'transfer_scene_report_infos' => [
                    '活动名称',
                    '奖励说明'
                ]
            ],
            self::QYPF =>  [
                'name' => '企业赔付',
                'user_recv_perception' => [
                    '退款',
                    '商家赔付',
                ],
                'transfer_scene_report_infos' => [
                    '赔付原因',
                ]
            ],

            self::CGHK =>  [
                'name' => '采购货款',
                'user_recv_perception' => [
                    '货款',
                ],
                'transfer_scene_report_infos' => [
                    '采购商品名称',
                ]
            ],
            self::ESHS =>  [
                'name' => '二手回收',
                'user_recv_perception' => [
                    '二手回收货款',
                ],
                'transfer_scene_report_infos' => [
                    '回收商品名称',
                ]
            ],
            self::GYBZ =>  [
                'name' => '公益补助',
                'user_recv_perception' => [
                    '公益补助金',
                ],
                'transfer_scene_report_infos' => [
                    '公益活动名称',
                    '公益活动备案编号'
                ]
            ],
            self::XZBT =>  [
                'name' => '行政补贴',
                'user_recv_perception' => [
                    '行政补贴',
                    '行政奖励'
                ],
                'transfer_scene_report_infos' => [
                    '补贴类型',
                ]
            ],
            self::BXLP =>  [
                'name' => '保险理赔',
                'user_recv_perception' => [
                    '保险理赔款',
                ],
                'transfer_scene_report_infos' => [
                    '保险产品备案编号',
                    '保险名称',
                    '保险操作单号'
                ]
            ],
        ];
    }
}