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

namespace app\service\core\weapp;

use core\base\BaseCoreService;
use core\exception\CommonException;
use core\exception\WechatException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\MiniApp\Application;


/**
 * easywechat主体提供
 * Class CoreWeappService
 * @package app\service\core\wechat
 */
class CoreWeappService extends BaseCoreService
{
    /**
     * 获取小程序的handle
     * @return Application
     * @throws InvalidArgumentException
     */
    public static function app()
    {
        $core_weapp_service = new CoreWeappConfigService();
        $weapp_config = $core_weapp_service->getWeappConfig();

        if (empty($weapp_config[ 'app_id' ]) || empty($weapp_config[ 'app_secret' ])) throw new WechatException('WEAPP_NOT_EXIST');//公众号未配置

        $config = array(
            'app_id' => $weapp_config[ 'app_id' ],
            'secret' => $weapp_config[ 'app_secret' ],
            'token' => $weapp_config[ 'token' ],
            'aes_key' => $weapp_config[ 'encryption_type' ] == 'not_encrypt' ? '' : $weapp_config[ 'encoding_aes_key' ],// 明文模式请勿填写 EncodingAESKey
            'http' => [
                'throw' => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
                'retry' => true, // 使用默认重试配置
            ],
        );
        return new Application($config);
    }


    /**
     * 微信小程序实例接口调用
     * @return \EasyWeChat\Kernel\HttpClient\AccessTokenAwareClient
     * @throws InvalidArgumentException
     */
    public static function appApiClient()
    {
        return self::app()->getClient();
    }

    /**
     * 生成小程序码
     * @param $page
     * @param $data
     * @param $filepath
     * @param int $width
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function qrcode($page, $data, $filepath, $width = 430)
    {
        $scene = [];
        foreach ($data as $v) {
            $scene[] = $v[ 'key' ] . '-' . $v[ 'value' ];
        }
        $response = self::appApiClient()->postJson('/wxa/getwxacodeunlimit', [
            'scene' => implode('&', $scene),
            'page' => $page,
            'width' => $width,
            'check_path' => false,
//            'env_version' => 'trial' // 要打开的小程序版本。正式版为"release"，体验版为"trial"，开发版为"develop"
        ]);
        if ($response->isFailed()) {
            // 出错了，处理异常
            throw new CommonException('微信小程序码生成失败：errcode:' . $response[ 'errcode' ] . 'errmsg:' . $response[ 'errmsg' ]);
        }
        $response->saveAs($filepath);
        return $filepath;
    }

    /**
     * 获取小程序体验码
     * @return void
     */
    public function getWeappPreviewImage()
    {
        $app = self::appApiClient();
        $response = $app->get('/wxa/get_qrcode');
        if ($response->isFailed()) {
            // 出错了，处理异常
            throw new CommonException('WECHAT_MINI_PROGRAM_CODE_GENERATION_FAILED');
        }
        $dir = public_path() . "qrcode/";
        mkdirs_or_notexist($dir);
        $filepath = $dir . time() . '.png';
        file_put_contents($filepath, $response->getContent());
        return $filepath;
    }
}
