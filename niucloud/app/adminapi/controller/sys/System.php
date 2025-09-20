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

namespace app\adminapi\controller\sys;

use app\service\admin\sys\SystemService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 系统信息查询
 * Class System
 * @description 系统信息查询
 * @package app\adminapi\controller\sys
 */
class System extends BaseAdminController
{
    /**
     * 获取当前系统信息
     * @description 获取当前系统信息
     * @return Response
     */
    public function info()
    {
        return success((new SystemService())->getInfo());
    }

    /**
     * 获取当前url配置
     * @description 获取当前url配置
     * @return Response
     */
    public function url()
    {
        return success((new SystemService())->getUrl());
    }

    /**
     * 获取系统环境配置
     * @description 获取系统环境配置
     * @return Response
     */
    public function getSystemInfo()
    {
        return success((new SystemService())->getSystemInfo());
    }

    /**
     * 清理表缓存
     * @description 清理表缓存
     */
    public function schemaCache()
    {
        return success((new SystemService())->schemaCache());
    }

    /**
     * 清理缓存
     * @description 清理缓存
     */
    public function clearCache()
    {
        return success((new SystemService())->clearCache());
    }

    /**
     * 校验消息队列是否正常运行
     * @description 校验消息队列是否正常运行
     * @return Response
     */
    public function checkJob()
    {
        return success(data: (new SystemService())->checkJob());
    }

    /**
     * 校验计划任务是否正常运行
     * @description 校验计划任务是否正常运行
     * @return Response
     */
    public function checkSchedule()
    {
        return success(data: (new SystemService())->checkSchedule());
    }

    /**
     * 环境变量查询
     * @description 环境变量查询
     * @return Response
     */
    public function getEnvInfo()
    {
        return success(['app_debug' => env('app_debug', false)]);
    }

    /**
     * 获取推广二维码
     * @description 获取推广二维码
     * @return Response
     */
    public function getSpreadQrcode()
    {
        $params = $this->request->params([
            ['form_id', ''],
            ['folder',''],
            ['page',''],
            ['params',[]]
        ]);
        return success((new SystemService())->getQrcode($params));
    }

    /**
     * 获取imagick扩展是否开启
     * @return Response
     */
    public function getImagickIsOpen(){
        if (extension_loaded('imagick')) {
                return success(data: true);
        } else {
            return success(data: false);
        }
    }
}
