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

namespace app\adminapi\controller\wechat;

use app\service\admin\wechat\WechatMediaService;
use core\base\BaseAdminController;

/**
 * 微信公众号素材管理
 */
class Media extends BaseAdminController
{
    public function lists()
    {
        $data = $this->request->params([
            ['type', ''],
        ]);
        return success((new WechatMediaService())->getMediaPage($data));
    }

    /**
     * 上传图片素材
     * @description 上传图片素材
     * @return \think\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function image() {
        $data = $this->request->params([
            ['file', 'file'],
        ]);
        return success((new WechatMediaService())->addImageMedia($data));
    }

    /**
     * 上传视频素材
     * @description 上传视频素材
     * @return \think\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function video() {
        $data = $this->request->params([
            ['file', 'file'],
        ]);
        return success((new WechatMediaService())->addVideoMedia($data));
    }

    /**
     * 同步草稿箱
     * @description 同步草稿箱
     * @return \think\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function syncNews() {
        return success((new WechatMediaService())->syncNewsMedia());
    }
}
