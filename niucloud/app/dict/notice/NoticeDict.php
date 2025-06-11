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

namespace app\dict\notice;

use app\dict\sys\ConfigKeyDict;
use app\service\core\sys\CoreConfigService;
use core\dict\DictLoader;

/**
 * 消息类
 * Class NoticeDict
 * @package app\dict\sys
 */
class NoticeDict
{
    /**
     * 获取消息
     * @return array
     */
    public static function getNotice(string $key = '',$sms_type = '')
    {
        if (empty($sms_type)){
            $sms_type = (new CoreConfigService())->getConfigValue(ConfigKeyDict::SMS)['default'] ?? "";
        }
        $addon_load = new DictLoader('Notice');
        $notice = $addon_load->load(['type' => 'notice']);
        $notice_type = NoticeTypeDict::getType();
        foreach ($notice_type as $k => $v) {
            $var_name = $k . '_notice';
            $$var_name = $addon_load->load(['type' => $k]);
            foreach ($$var_name as &$vv) {
                if (isset($vv['is_need_closure_content']) && $vv['is_need_closure_content'] == 1 && isset($vv['content'])) {
                    $vv['content'] = $vv['content'](['sms_type' => $sms_type]);
                }
            }
        }

        foreach ($notice as $k => $v) {
            $support_type = [];
            foreach ($notice_type as $notice_type_k => $notice_type_v) {
                $var_name = $notice_type_k . '_notice';
                if (array_key_exists($k, $$var_name)) {
                    $notice[$k][$notice_type_k] = $$var_name[$k];
                    $support_type[] = $notice_type_k;
                }
            }
            $notice[$k]['support_type'] = $support_type;
        }
        if (!empty($key)) {
            return $notice[$key] ?? [];
        }
        return $notice;
    }

    /**
     * 获取消息模版包含插件名称
     * @return array
     */
    public static function getNoticeWithAddon(string $key = '',$sms_type = '')
    {
        if (empty($sms_type)) {
            $sms_type = (new CoreConfigService())->getConfigValue(ConfigKeyDict::SMS)['default'] ?? "";
        }
        //获取模版列表
        $addon_load = new DictLoader('Notice');
        $notice_template_list = $addon_load->load(['type' => 'notice', 'with_addon' => 1]);

        //获取针对性的详细内容 例如 注册的 短信通知的信息 / 微信通知的信息
        $notice_type_list = NoticeTypeDict::getType();
        $template_type_desc_list = [];
        foreach ($notice_type_list as $notice_type => $v) {
            $temp = $addon_load->load(['type' => $notice_type, 'with_addon' => 1]);
            if (!isset($template_type_desc_list[$notice_type . '_notice'])) {
                $template_type_desc_list[$notice_type . '_notice'] = [];
            }
            foreach ($temp as &$tmv) {
                foreach ($tmv as &$tmcv) {
                    if (isset($tmcv['is_need_closure_content']) && $tmcv['is_need_closure_content'] == 1 && isset($tmcv['content'])) {
                        $tmcv['content'] = $tmcv['content'](['sms_type' => $sms_type]);
                    }
                }
            }
            $template_type_desc_list[$notice_type . '_notice'] = empty($template_type_desc_list[$notice_type . '_notice']) ? $temp : array_merge($template_type_desc_list[$notice_type . '_notice'], $temp);
        }

        //组合数据
        foreach ($notice_template_list as $addon => $item) {
            $keys = array_keys($item);
            if ($key && !in_array($key, $keys)) {
                unset($notice_template_list[$addon]);
                continue;
            }
            foreach ($item as $template_key => $value) {
                if ($key && $template_key != $key) {
                    unset($notice_template_list[$addon][$template_key]);
                    continue;
                }
                $support_type = [];
                foreach ($notice_type_list as $notice_type => $notice_type_name) {
                    $temp_arr = $template_type_desc_list[$notice_type . "_notice"][$addon] ?? [];
                    $template_keys = array_keys($temp_arr);
                    if (in_array($template_key, $template_keys)) {
                        $notice_template_list[$addon][$template_key][$notice_type] = $template_type_desc_list[$notice_type . "_notice"][$addon][$template_key];
                        $support_type[] = $notice_type;
                    }
                }
                $notice_template_list[$addon][$template_key]['support_type'] = $support_type;
            }
        }
        return $notice_template_list;
    }

}