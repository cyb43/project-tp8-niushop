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

namespace app\dict\sys;

/**
 * 云服务的字典
 */
class CloudDict
{
    const APPLET_UPLOADING = 0;

    const APPLET_UPLOAD_SUCCESS = 1;

    const APPLET_AUDITING = 2;

    const APPLET_PUBLISHED = 3;

    const APPLET_UPLOAD_FAIL = -1;

    const APPLET_AUDIT_FAIL = -2;

    const APPLET_AUDIT_UNDO = -3;

    public static function getAppletUploadStatus($status) {
        $status_list = [
            self::APPLET_UPLOADING => get_lang('dict_cloud_applet.uploading'),
            self::APPLET_UPLOAD_SUCCESS => get_lang('dict_cloud_applet.upload_success'),
            self::APPLET_UPLOAD_FAIL => get_lang('dict_cloud_applet.upload_fail'),
            self::APPLET_AUDITING => get_lang('dict_cloud_applet.auditing'),
            self::APPLET_AUDIT_FAIL => get_lang('dict_cloud_applet.audit_fail'),
            self::APPLET_PUBLISHED => get_lang('dict_cloud_applet.published'),
            self::APPLET_AUDIT_UNDO => get_lang('dict_cloud_applet.undo')
        ];
        return $status_list[$status] ?? '';
    }
}
