<?php

namespace app\listener\notice;

use app\dict\notice\NoticeTypeDict;
use app\service\core\member\CoreMemberService;
use app\service\core\notice\CoreNoticeLogService;
use core\exception\NoticeException;
use core\template\TemplateLoader;
use think\facade\Log;

class Weapp
{

    public function handle(array $data)
    {
        $template = $data['template'];//模板
        $vars = $data['vars'];//模板变量
        $key = $data['key'];
        $to = $data['to'];//发送对象主题

        Log::write("小程序消息发送" . json_encode($data));
        //完全信任消息的设置, 不再依赖support_type
        if ($template['is_weapp']) {
            $member_id = $to['member_id'] ?? 0;
            if ($member_id > 0) {//查询openid
                $info = (new CoreMemberService())->getInfoByMemberId($member_id);
                $openid = $info['weapp_openid'] ?? '';
                $nickname = $info['nickname'] ?? '';
            }
            Log::write("小程序消息发送  member_id:{$member_id}  openid:{$openid}");
            if (!empty($openid)) {
                $weapp_template_id = $template['weapp_template_id'];
                $weapp = $template['weapp'];
                $weapp_content = $weapp['content'];
                $weapp_data = [];
                foreach ($weapp_content as $k => $v) {
                    $search_content = $v[1];
                    foreach ($vars as $item_k => $item_v) {
                        $search_content = str_replace('{' . $item_k . '}', $item_v, $search_content);
                    }
                    $weapp_data[$v[2]]['value'] = $search_content;
                }
                $url = $vars['__weapp_page'] ?? '';
                $log_data = array(
                    'key' => $key,
                    'notice_type' => NoticeTypeDict::WEAPP,
                    'uid' => $data['uid'] ?? 0,
                    'member_id' => $member_id,
                    'nickname' => $nickname ?? '',
                    'receiver' => $openid,
                    'params' => $data,
                    'content' => $weapp
                );

                Log::write("小程序消息发送参数" . json_encode($log_data));
                try {
                    (new TemplateLoader(NoticeTypeDict::WEAPP))->send(
                        [
                            'template_id' => $weapp_template_id,
                            'data' => $weapp_data,
                            'openid' => $openid,
                            'page' => $url,
                        ]);
                    (new CoreNoticeLogService())->add($log_data);
                } catch (NoticeException $e) {
                    $log_data['result'] = $e->getMessage();
                    (new CoreNoticeLogService())->add($log_data);
                    //这儿决定要不要抛出
                    if (!$template['async']) {
                        throw new NoticeException($e->getMessage());
                    }
                }
            }
        }
        return true;
    }

}