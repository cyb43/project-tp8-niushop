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

namespace app\service\core\notice;


use app\dict\notice\NoticeDict;
use app\model\addon\Addon;
use app\model\sys\SysNotice;
use core\base\BaseCoreService;
use core\exception\NoticeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;


/**
 * 短信配置服务层
 * Class SmsService
 * @package app\service\admin\message
 */
class CoreNoticeService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new SysNotice();
    }

    public function getList(array $keys = [])
    {
        $list = $this->model->where([ [ 'id', '>', 0 ] ])->select()->toArray();
        if (!empty($list)) {
            $list_key = array_column($list, 'key');
            $list = array_combine($list_key, $list);
        }
        $notice = NoticeDict::getNotice();
        foreach ($notice as $k => $v) {
            if (!empty($keys) && !in_array($v[ 'key' ], $keys)) {
                unset($notice[ $k ]);
                continue;
            }
            if (array_key_exists($k, $list)) {
                $notice[ $k ] = array_merge($v, $list[ $k ]);
            } else {
                $data = [
                    'sms_content' => '',
                    'is_wechat' => 0,
                    'is_weapp' => 0,
                    'is_sms' => 0,
                    'wechat_template_id' => '',
                    'weapp_template_id' => '',
                    'sms_id' => '',
                    'wechat_first' => '',
                    'wechat_remark' => ''
                ];
                $notice[ $k ] = array_merge($v, $data);
            }
        }
        return $notice;
    }

    /**
     * 获取当前站点消息
     * @param array $keys
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAddonList(array $keys = [])
    {
        $list = $this->model->where([ [ 'id', '>', 0 ] ])->select()->toArray();
        if (!empty($list)) {
            $list_key = array_column($list, 'key');
            $list = array_combine($list_key, $list);
        }

        $notice = NoticeDict::getNotice();
        $addon_list = (new Addon())->where([ ['key', 'in', get_install_addons() ]])->column('key,title', 'key');
        $addon_list['app'] = ['key' => 'system', 'title' => '系统'];
        foreach ($addon_list as $addon => &$item) {
            if ($addon =='app') {
                $template_path = str_replace('/', DIRECTORY_SEPARATOR, root_path()."app/dict/notice/notice.php");
            } else {
                $template_path = str_replace('/', DIRECTORY_SEPARATOR, root_path()."addon/{$addon}/app/dict/notice/notice.php");
            }
            if (is_file($template_path)) {
                $notice_list = include $template_path;
                $item['notice'] = [];
                foreach ($notice_list as &$notice_item) {
                    if (!isset($notice[ $notice_item['key'] ])) continue;
                    if (array_key_exists($notice_item['key'], $list)) {
                        $notice_item = array_merge($notice_item, $notice[$notice_item['key']], $list[ $notice_item['key'] ]);
                    } else {
                        $notice_item = array_merge($notice_item, $notice[$notice_item['key']], [
                            'sms_content' => '',
                            'is_wechat' => 0,
                            'is_weapp' => 0,
                            'is_sms' => 0,
                            'wechat_template_id' => '',
                            'weapp_template_id' => '',
                            'sms_id' => '',
                            'wechat_first' => '',
                            'wechat_remark' => '',
                            'audit_status'=>'wait',
                            'sign'=>'',
                        ]);
                    }
                    $item['notice'][] = $notice_item;
                }
            } else {
                $item['notice'] = [];
            }
        }
        ksort($addon_list);
        return array_values($addon_list);
    }

    /**
     * 获取消息内容(可以做缓存)
     * @param string $key
     * @return array
     */
    public function getInfo(string $key)
    {
        if (!array_key_exists($key, NoticeDict::getNotice())) throw new NoticeException('NOTICE_TYPE_NOT_EXIST');
        $info = $this->model->where([ [ 'key', '=', $key ] ])->findOrEmpty()->toArray();
        if (!empty($info)) {
            $notice = array_merge(NoticeDict::getNotice($key), $info);
        } else {
            $data = [
                'sms_content' => '',
                'is_wechat' => 0,
                'is_weapp' => 0,
                'is_sms' => 0,
                'wechat_template_id' => '',
                'weapp_template_id' => '',
                'sms_id' => '',
                'wechat_first' => '',
                'wechat_remark' => ''
            ];
            $notice = array_merge(NoticeDict::getNotice($key), $data);

        }
        return $notice;
    }

    public function find(string $key)
    {
        return $this->model->where([ [ 'key', '=', $key ] ])->findOrEmpty();
    }

    /**
     * 消息公共编辑
     * @param string $key
     * @param array $data
     * @return bool
     */
    public function edit(string $key, array $data)
    {
        $notice = $this->find($key);
        if ($notice->isEmpty()) {
            $notice_template = NoticeDict::getNotice($key);
            $wechat = $notice_template[ 'wechat' ] ?? [];
            $this->model->create(array_merge([
                'key' => $key,
                'sms_content' => $notice_template[ 'sms']['content' ] ?? '',
                'wechat_first' => $data[ 'wechat_first' ] ?? ( $wechat[ 'first' ] ?? '' ),
                'wechat_remark' => $data[ 'wechat_remark' ] ?? ( $wechat[ 'remark' ] ?? '' ),
            ], $data));
        } else {
            $notice->save($data);
        }
        return true;

    }

}
