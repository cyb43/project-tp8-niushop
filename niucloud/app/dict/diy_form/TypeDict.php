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

namespace app\dict\diy_form;

use app\dict\sys\AppTypeDict;
use core\dict\DictLoader;

/**
 * 万能表单类型
 */
class TypeDict
{

    /**
     * 获取万能表单类型
     * @param array $params
     * @return array|null
     */
    public static function getType($params = [])
    {
        $system_pages = [
            //自定义表单
            'DIY_FORM' => [
                'title' => get_lang('dict_diy_form.type_diy_form'),
                'preview' => 'static/resource/images/diy_form/diy_from_preview.jpg', // 预览图
                'sort' => 10001,
                'addon' => ''
            ],
            //个人资料
            'DIY_FORM_MEMBER_INFO' => [
                'title' => get_lang('dict_diy_form.type_diy_form_member_info'),
                'preview' => 'static/resource/images/diy_form/diy_form_member_info_preview.jpg', // 预览图
                'sort' => 10002,
                'addon' => '',
                'app_type' => [AppTypeDict::ADMIN],
                "show_submit" => true, // 是否显示表单提交组件
            ],
            //签到报名登记 todo 靠后完善
//            'SIGN_REGISTRATION' => [
//                'title' => get_lang('dict_diy_form.type_sign_registration'),
//                'preview' => 'static/resource/images/diy_form/diy_from_preview.png',
//                'sort' => 10002,
//                'addon' => ''
//            ],
            //留言建议 todo 靠后完善
//            'LEAVE_MESSAGE_SUGGESTION' => [
//                'title' => get_lang('dict_diy_form.type_leave_message_suggestion'),
//                'preview' => 'static/resource/images/diy_form/diy_from_preview.png',
//                'sort' => 10003,
//                'addon' => ''
//            ],
            //核销凭证 todo 靠后完善
//            'WRITE_OFF_VOUCHER' => [
//                'title' => get_lang('dict_diy_form.type_write_off_voucher'),
//                'preview' => 'static/resource/images/diy_form/diy_from_preview.png',
//                'sort' => 10004,
//                'addon' => ''
//            ],
        ];

        $data = ( new DictLoader("DiyFormType") )->load($system_pages);

        if (!empty($params) && !empty($params[ 'type' ])) {
            return $data[ $params[ 'type' ] ];
        }

        return $data;

    }

}
