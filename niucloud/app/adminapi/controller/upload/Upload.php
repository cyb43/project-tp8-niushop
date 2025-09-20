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

namespace app\adminapi\controller\upload;

use app\service\admin\upload\UploadConfigService;
use app\service\admin\upload\UploadService;
use core\base\BaseAdminController;
use think\Response;

/**
 * 上传管理
 * Class Upload
 * @description 上传管理
 * @package app\adminapi\controller\upload
 */
class Upload extends BaseAdminController
{
    /**
     * 图片上传
     * @description 图片上传
     * @return Response
     */
    public function image()
    {

        $data = $this->request->params([
            ['file', 'file'],
            ['cate_id', 0],
            ['is_attachment', 1]
        ]);
        $upload_service = new UploadService();
        return success($upload_service->image($data['file'], $data['cate_id'], boolval($data['is_attachment'])));
    }

    /**
     * 视频上传
     * @description 视频上传
     * @return Response
     */
    public function video()
    {
        $data = $this->request->params([
            ['file', 'file'],
            ['cate_id', 0]
        ]);
        $upload_service = new UploadService();
        return success($upload_service->video($data['file'], $data['cate_id']));
    }

    /**
     * 视频上传
     * @description 音频上传
     * @return Response
     */
    public function audio()
    {
        $data = $this->request->params([
            ['file', 'file'],
            ['cate_id', 0]
        ]);
        $upload_service = new UploadService();
        return success($upload_service->audio($data['file'], $data['cate_id']));
    }


    /**
     * 文件上传(默认不上云)
     * @description 文件上传
     * @return Response
     */
    public function document($type)
    {
        $data = $this->request->params([
            ['file', 'file'],
        ]);
        $upload_service = new UploadService();
        return success($upload_service->document($data['file'], $type));
    }

    /**
     * 上传配置
     * @description 上传配置
     * @return Response
     */
    public function setUploadConfig()
    {
        $data = $this->request->params(
            [
                ['image_size', 0],
                ['video_size', 0],
                ['image_ext', ''],
                ['video_ext', ''],
            ]
        );
        (new UploadConfigService())->setUploadConfig($data);
        return success('SET_SUCCESS');
    }

    /**
     * 获取上传配置
     * @description 获取上传配置
     * @return Response
     */
    public function getUploadConfig()
    {
        return success((new UploadConfigService())->getUploadConfig());
    }

}
