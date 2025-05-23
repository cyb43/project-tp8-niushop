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

namespace app\service\core\pay;

use app\dict\pay\TransferDict;
use app\dict\sys\ConfigKeyDict;
use app\model\pay\Pay;
use app\model\pay\Transfer;
use app\model\pay\TransferScene;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use core\exception\PayException;
use Exception;
use think\facade\Db;
use think\facade\Log;
use think\Model;
use Throwable;

/**
 * 转账场景服务层
 * Class CoreTransferService
 * @package app\service\core\pay
 */
class CoreTransferSceneService extends BaseCoreService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new TransferScene();

    }

    /**
     * 获取底部导航配置
     * @param string $key
     * @return array
     */
    public function getWechatTransferSceneConfig()
    {
        $info = ( new CoreConfigService() )->getConfigValue(ConfigKeyDict::WECHAT_TRANSFER_SCENE_CONFIG) ?? [];
        return $info;
    }

    /**
     * 设置微信转账场景导航
     * @param array $data
     * @param string $key
     * @return SysConfig|bool|Model
     */
    public function setWechatTransferSceneConfig(array $data)
    {
        return ( new CoreConfigService() )->setConfig(ConfigKeyDict::WECHAT_TRANSFER_SCENE_CONFIG, $data);
    }

    /**
     * 获取转账场景
     * @return void
     */
    public function getWechatTransferScene()
    {
        $list = TransferDict::getWechatTransferScene();

        $config = $this->getWechatTransferSceneConfig();
        //查询业务和场景的对应关系表
        $trade_scene_event_array = event('GetWechatTransferTradeScene', []);
        $trade_scene_column = ( new TransferScene() )->where([ [ 'id', '=', 0 ] ])->column('*', 'type');
        $trade_scene_list = [];
        foreach ($trade_scene_event_array as $trade_scene_item) {
            foreach ($trade_scene_item as $trade_scene_key => $trade_scene_item_item) {
                $trade_scene_select_data = $trade_scene_column[ $trade_scene_key ] ?? $trade_scene_item_item;
                $trade_scene_select_data = array_merge($trade_scene_item_item, $trade_scene_select_data);
                if (!isset($trade_scene_list[ $trade_scene_item_item[ 'scene' ] ])) $trade_scene_list[ $trade_scene_item_item[ 'scene' ] ] = [];
                $trade_scene_list[ $trade_scene_item_item[ 'scene' ] ][ $trade_scene_key ] = $trade_scene_select_data;

            }
        }

        foreach ($list as $key => &$v) {
            $v[ 'scene_id' ] = $config[ $key ] ?? '';
            $trade_scene_data = $trade_scene_list[ $key ] ?? [];
            $v[ 'trade_scene_data' ] = $trade_scene_data;
        }
        //然后根据支持的业务来完善业务场景备注

        return $list;
    }

    /**
     * 通过业务设置转账场景的配置信息
     * @param $data
     * @return void
     */
    public function setTradeScene($type, $data)
    {
        //先判断是否存在
        $trade_scene = $this->model->where([ 'type' => $type ])->findOrEmpty();
        $infos = $data[ 'infos' ];
        $perception = $data[ 'perception' ];
        $scene = $data[ 'scene' ];
        $data = [
            'infos' => $infos,
            'perception' => $perception,
            'scene' => $scene,
            'type' => $type,
        ];
        if ($trade_scene->isEmpty()) {
            $this->model->create($data);
        } else {
            $trade_scene->save($data);
        }
        return true;
    }

    /**
     * 通过业务获取转账场景的配置信息
     * @param $type
     * @return array|mixed
     */
    public function getSceneInfoByType($type)
    {
        $trade_scene_event_array = event('GetWechatTransferTradeScene', []);
        $temp_list = [];
        foreach ($trade_scene_event_array as $trade_scene_item) {
            $temp_list = array_merge($temp_list, $trade_scene_item);
        }
        $trade_scene = $this->model->where([ 'type' => $type ])->findOrEmpty();

        if ($trade_scene->isEmpty()) {
            $value = $temp_list[ $type ] ?? [];
        } else {
            $value = $trade_scene->toArray();
        }
        $config = $this->getWechatTransferSceneConfig();
        $scene_id = $config[ $value[ 'scene' ] ] ?? '';
        $value[ 'scene_id' ] = $scene_id;
        return $value;
    }
}
