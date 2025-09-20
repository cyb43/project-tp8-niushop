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

namespace app\service\core\upload;

use core\exception\UploadFileException;
use Exception;

/**
 * 上传服务层
 * Class CoreFetchService
 * @package app\service\core\file
 */
class CoreFetchService extends CoreFileService
{

    public function __construct($is_attachment = false)
    {
        parent::__construct($is_attachment);
    }

    /**
     * 图片上传
     * @param string $url
     * @param string $file_dir
     * @param string $storage_type
     * @return array
     * @throws Exception
     */
    public function image(string $url, string $file_dir, $storage_type = '')
    {
        if(empty($url)) throw new UploadFileException('OSS_FILE_URL_NOT_EXIST');
        $this->upload_driver = $this->driver($storage_type);
        [$ext, $link] = explode('.', strrev($url));
        $ext = empty($ext) ? strrev($ext) : 'jpg';
        $file_path = $this->upload_driver->createFileName($link, $ext);

        $dir = $this->root_path .'/'.  $file_dir.'/'.$file_path;
        $result = $this->upload_driver->fetch($url, $dir);

        //读取上传附件的信息用于后续得校验和数据写入
        if($result){
            return [
                'url' => $this->upload_driver->getUrl($dir)
            ];
        }
        else{
            throw new UploadFileException($result);
        }

    }



}