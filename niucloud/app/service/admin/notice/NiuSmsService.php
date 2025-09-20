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

namespace app\service\admin\notice;

use app\dict\notice\NoticeDict;
use app\dict\notice\NoticeTypeDict;
use app\dict\sys\SmsDict;
use app\model\addon\Addon;
use app\model\sys\NiuSmsTemplate;
use app\service\core\notice\CoreNiuSmsService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use core\exception\ApiException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 消息管理服务层
 */
class NiuSmsService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->template_model = new NiuSmsTemplate();
        $this->niu_service = (new CoreNiuSmsService());
    }

    public function enableNiuSms($enable)
    {
        if ($enable == 1) {
            $config = $this->niu_service->getNiuLoginConfig(true);
            if (empty($config) || !isset($config[SmsDict::NIUSMS]) || empty($config[SmsDict::NIUSMS]['username']) || empty($config[SmsDict::NIUSMS]['password']) || empty($config[SmsDict::NIUSMS]['signature'])) {
                throw new AdminException("NIU_SMS_ENABLE_FAILED");
            }
            $this->niu_service->setNiuLoginConfig(['default' => SmsDict::NIUSMS]);
        } else {
            $this->niu_service->setNiuLoginConfig(['default' => '']);
        }
    }

    /**
     * 获取当前登录的牛云短信账号
     * @return array
     */
    public function getConfig()
    {
        $login_config = $this->niu_service->getNiuLoginConfig(true);
        return [
            'is_login' => empty($login_config[SmsDict::NIUSMS]) ? 0 : 1,
            'username' => $login_config[SmsDict::NIUSMS]['username'] ?? '',
            'is_enable' => (($login_config['default'] ?? '') == SmsDict::NIUSMS) ? 1 : 0,
        ];
    }

    /**
     * 获取套餐列表
     * @param $params
     * @return mixed
     */
    public function packageList($params)
    {
        $res = $this->niu_service->packageList($params);
        return $res;
    }

    /**
     * 发送动态码
     * @param $params
     * @return mixed
     */
    public function sendMobileCode($params)
    {
        $res = $this->niu_service->sendMobileCode($params);
        return $res;
    }

    /**
     * 发送动态码
     * @return mixed
     */
    public function captcha()
    {
        $res = $this->niu_service->captcha();
        return $res;
    }

    /**
     * 注册牛云短信账号
     * @param $data
     * @return mixed
     */
    public function registerAccount($data)
    {
        if (!empty($data['imgUrl']) && strstr($data['imgUrl'], 'http') === false) {
            $data['imgUrl'] = request()->domain() . "/" . $data['imgUrl'];
        } else {
            $data['imgUrl'] = $data['imgUrl'] ?? '';
        }
        $res = $this->niu_service->registerAccount($data);
        return $res;
    }

    /**
     * 登录牛云短信账号
     * @param $params
     * @return mixed
     */
    public function loginAccount($params)
    {
        $account_info = $this->niu_service->loginAccount($params);
        if ($account_info) {
            (new CoreNiuSmsService())->setNiuLoginConfig($params, true);
        }
        return $account_info;
    }

    /**
     * 编辑牛云短信账号信息（暂时只有 手机号和默认签名）
     * @param $username
     * @param $params
     * @return mixed
     */
    public function editAccount($username, $params)
    {
        $res = $this->niu_service->editAccount($username, $params);
        $this->niu_service->setNiuLoginConfig($params);
        return $res;
    }

    /**
     * 获取牛云短信账号信息
     * @param $username
     * @return mixed
     */
    public function accountInfo($username)
    {
        $res = $this->niu_service->accountInfo($username);
        return $res;
    }

    /**
     * 重置转牛云短信账号密码
     * @param $username
     * @param $params
     * @return array
     */
    public function resetPassword($username, $params)
    {
        $account_info = $this->accountInfo($username);
        $mobile_arr = explode(",", $account_info['mobiles']);
        if (!in_array($params['mobile'], $mobile_arr)) {
            throw new ApiException('ACCOUNT_BIND_MOBILE_ERROR');
        }
        $res = $this->niu_service->resetPassword($username, $params);
        $this->niu_service->setNiuLoginConfig(['username' => $username, 'password' => $res['newPassword']]);
        return [
            'password' => $res['newPassword'],
        ];
    }

    /**
     * 获取牛云短信账号发送短信列表
     * @param $username
     * @param $params
     * @return array
     */
    public function accountSendList($username, $params)
    {
        $res = $this->niu_service->accountSendList($username, $params);
        $return = $this->formatListPaginate($res['page']);
        $return['data'] = $res['records'];
        return $return;
    }

    /**
     * 获取签名列表
     * @param $username
     * @return array
     */
    public function getSignList($username)
    {
        $res = $this->niu_service->signList($username);
        $return = $this->formatListPaginate($res['page']);
        $return['data'] = $res['signatures'];
        $config = $this->niu_service->getNiuLoginConfig();
        foreach ($return['data'] as &$item) {
            $item['auditResultName'] = NoticeTypeDict::getSignAuditType($item['auditResult']);
            $item['createTime'] = date('Y-m-d H:i:s', ($item['createTime'] / 1000));
            $item['is_default'] = ($config['signature'] == $item['sign']) ? 1 : 0;
        }
        return $return;
    }

    /**
     * 获取签名信息
     * @param $username
     * @param $signature
     * @return mixed
     */
    public function signInfo($username, $signature)
    {
        return $this->niu_service->signInfo($username, $signature);
    }

    /**
     * 获取创建签名初始化的配置信息
     * @return array
     */
    public function signCreateConfig()
    {
        return [
            'sign_source_list' => NoticeTypeDict::getSignSource(),
            'sign_type_list' => NoticeTypeDict::getSignType(),
            'sign_default_list' => NoticeTypeDict::getSignDefault()
        ];
    }

    /**
     * 签名创建
     * @param $username
     * @param $params
     */
    public function signCreate($username, $params)
    {
        if (!empty($params['imgUrl']) && strstr($params['imgUrl'], 'http') === false) {
            $params['imgUrl'] = request()->domain() . '/' . $params['imgUrl'];
        } else {
            $params['imgUrl'] = $params['imgUrl'] ?? '';
        }
        $res = $this->niu_service->signCreate($username, $params);
        if (!empty($res['failList'])) {
            throw new AdminException($res['failList'][0]['msg']);
        }
    }

    /**
     * 签名创建
     * @param $username
     * @param $params
     * @return array|mixed
     * @throws \Exception
     */
    public function signDelete($username, $params)
    {
        $config = $this->niu_service->getNiuLoginConfig();
        $params['password'] = $config['password'];
        $fail_list = $this->niu_service->signDelete($username, $params);
        if (in_array($config['signature'], $params['signatures']) && !in_array($config['signature'], $fail_list)) {
            $this->editAccount($username, ['signature' => '']);
        }
        return $fail_list;
    }

    /**
     * 拉取模版状态
     * @param $sms_type
     * @param $username
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function syncTemplateList($sms_type, $username)
    {
        $template_list = $this->getTemplateList($sms_type, $username);
        $repeat_name_arr = [];
        $is_repeat = 0;
        foreach ($template_list as $item) {
            $repeat_name_arr[$item['name']][] = $item['addon'];
            if (count($repeat_name_arr[$item['name']]) > 1) {
                $is_repeat = 1;
            }
        }
        if ($is_repeat == 1) {
            foreach ($repeat_name_arr as $name => $values) {
                if (count($values) == 1) {
                    unset($repeat_name_arr[$name]);
                }
            }
            return ['repeat_list' => $repeat_name_arr];
        } else {
            $repeat_name_arr = [];
        }
        $this->execsync($sms_type, $username, $template_list);
        return ['repeat_list' => $repeat_name_arr];
    }

    private function execsync($sms_type, $username, $template_list, $page = 1)
    {
        $name_template_list = array_column($template_list, null, 'name');
        $limit = 100;
        $api_template_data = $this->niu_service->templateList($username, ['size' => $limit]);

        $total = $api_template_data['page']['total'];
        $templates = $api_template_data['templates'];
        $insert = [];
        foreach ($templates as $template) {
            $tem_id = $template['temId'];

            //拉取回来的模版在项目中未配置
            if (!isset($name_template_list[$template['temName']])) {
                continue;
            }
            if (!empty($template['extend'])) {
                $template_key = $template['extend']['template_key'] ?? "";
            } else {
                $template_key = $name_template_list[$template['temName']]['key'];
            }
            $model_info = $this->template_model->where([
                ['sms_type', '=', $sms_type],
                ['username', '=', $username],
                ['template_key', '=', $template_key]
            ])->findOrEmpty();
            $data = [
                'sms_type' => $sms_type,
                'username' => $username,
                'template_key' => $template_key,
                'template_content' => $template['temContent'],
                'param_json' => $template['paramJson'],
                'template_type' => $template['temType'],
                'audit_status' => $template['auditResult'],
                'audit_msg' => $template['auditMsg'],
                'template_id' => $tem_id,
                'report_info' => $template,
                'create_time' => $template['createTime'] / 1000,
                'update_time' => time(),
            ];
            if ($model_info->isEmpty()) {
                $insert[] = $data;
                continue;
            }
            $this->template_model->where('id', $model_info->id)->update($data);
        }
        if (!empty($insert)) {
            $this->template_model->insertAll($insert);
        }
        if ($total > $limit * $page) {
            $this->execsync($sms_type, $username, $page + 1);
        }
    }

    /**
     * 获取模版列表
     * @param $sms_type
     * @param $username
     * @param $template_key
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getTemplateList($sms_type, $username, $template_key = '')
    {
        $config = $this->niu_service->getNiuLoginConfig();
        if (empty($config) || $config['username'] != $username) {
            throw new AdminException('ACCOUNT_ERROR_RELOGIN');
        }
        $list = NoticeDict::getNoticeWithAddon($template_key, SmsDict::NIUSMS);
        $list = $this->formatTemplateList($list);


        $searchKeys = array_column($list, 'key');

        $report_list = $this->template_model->whereIn('template_key', $searchKeys)->where([
            ['sms_type', '=', $sms_type],
            ['username', '=', $username],
        ])->select()->toArray();
        $report_list = array_column($report_list, null, 'template_key');

        foreach ($list as &$item) {
            $template_key = $item['key'];
            $report_info = $report_list[$template_key] ?? [];

            $item['template_id'] = $report_info['template_id'] ?? 0;
            $item['template_type_name'] = !empty($report_info['template_type']) ? NoticeTypeDict::getTemplateType($report_info['template_type']) : "";
            $item['sms_type'] = $report_info['sms_type'] ?? $sms_type;
            $item['username'] = $report_info['username'] ?? $username;

            $params_json = array_keys($report_info['param_json'] ?? []);
            sort($params_json);
            $variable = array_keys($item['variable'] ?? []);
            sort($variable);

            $audit_status = $report_info['audit_status'] ?? NoticeTypeDict::TEMPLATE_AUDIT_STATUS_NOT_REPORT;
            $error_status = '';
            if (!empty($report_info) && $variable != $params_json) {
                if (empty($params_json)) {
                    $error_status = NoticeTypeDict::TEMPLATE_NEED_PULL;
                } else {
                    $error_status = $audit_status == NoticeTypeDict::TEMPLATE_AUDIT_STATUS_PASS
                        ? NoticeTypeDict::TEMPLATE_AUDIT_STATUS_NEED_AGAIN_REPORT
                        : NoticeTypeDict::TEMPLATE_AUDIT_STATUS_NEED_EDIT;
                }
            }

            $item['audit_info'] = [
                'audit_msg' => $report_info['audit_msg'] ?? '',
                'audit_status' => $audit_status,
                'audit_status_name' => NoticeTypeDict::getTemplateAuditStatus($audit_status),
                'error_status' => $error_status,
                'error_status_name' => !empty($error_status) ? NoticeTypeDict::getTemplateAuditStatus($error_status) : ""
            ];
        }
        return $list;
    }

    public function checkTemplateAudit($template_key, $template_id)
    {
        $config = $this->niu_service->getNiuLoginConfig(true);
        if (empty($config)) {
            throw new AdminException('SMS_TYPE_NOT_OPEN');
        }
        //当前使用短信功能提供商非牛云短信无需校验模版ID是否正常
        if (isset($config['default']) && $config['default'] != SmsDict::NIUSMS) {
            return true;
        }
        $config = $config[SmsDict::NIUSMS];
        if (empty($config)) {
            throw new AdminException('TEMPLATE_ERROR');
        }
        $template_info = $this->template_model->where([
            ['sms_type', '=', SmsDict::NIUSMS],
            ['template_id', '=', $template_id],
            ['username', '=', $config['username']],
            ['template_key', '=', $template_key],
        ])->findOrEmpty()->toArray();
        $params_json = array_keys($template_info['param_json'] ?? []);
        sort($params_json);

        $template_config = $this->getTemplateList(SmsDict::NIUSMS, $config['username'], $template_key)[0];

        $variable = array_keys($template_config['variable'] ?? []);
        sort($variable);

        if (!empty($template_info) && $variable != $params_json) {
            throw new AdminException('TEMPLATE_USE_ERROR');
        }

        if (empty($template_info) || $template_info['audit_status'] != NoticeTypeDict::TEMPLATE_AUDIT_STATUS_PASS) {
            throw new AdminException('TEMPLATE_ERROR');
        }
        return $template_info;
    }

    /**
     * 格式化模版列表返回数据
     * @param $list
     * @return array
     */
    private function formatTemplateList($list)
    {
        $return = [];
        $addon_arr = array_keys($list);
        $addon_list = (new Addon())->whereIn('key', $addon_arr)->field('key,title')->select()->toArray();
        $addon_list = array_column($addon_list, 'title', 'key');
        foreach ($list as $addon => $item) {
            foreach ($item as $value) {
                $temp = $value;
//                $temp['addon'] = $addon;
                $temp['addon'] = $addon_list[$addon] ?? '系统';
                $str = $temp['sms']['content'] ?? "";
                if (empty($str)) {
                    continue;
                }
                preg_match_all('/\{(.*?)\}/', $str, $matches);
                $result = $matches[1];
                foreach ($temp['variable'] as $k => $v) {
                    if (!in_array($k, $result)) {
                        unset($temp['variable'][$k]);
                    }
                }
                $return[] = $temp;
            }
        }
        return $return;
    }

    /**
     * 获取模版信息
     * @param $sms_type
     * @param $username
     * @param $template_key
     * @return array
     */
    public function templateInfo($sms_type, $username, $template_key)
    {
        $template_info = $this->template_model->where([
            ['username', '=', $username],
            ['sms_type', '=', $sms_type],
            ['template_key', '=', $template_key],
        ])->findOrEmpty();
        if ($template_info->isEmpty()) {
            throw new AdminException('TEMPLATE_NOT_REPORT');
        }
        $res = $this->niu_service->templateInfo($username, $template_info->template_id);
        $template_info->audit_status = $res['auditResult'] ?? $template_info->audit_status;
        $template_info->save();
        return $template_info->toArray();
    }

    /**
     * 获取模版创建使用的初始化配置信息
     * @return array
     */
    public function templateCreateConfig()
    {
        return [
            'template_params_type_list' => NoticeTypeDict::getApiParamsType(),
            'template_type_list' => NoticeTypeDict::getTemplateType(),
            'template_status_list' => NoticeTypeDict::getTemplateAuditStatus()
        ];
    }

    /**
     * 报备/编辑模版
     * @param $sms_type
     * @param $username
     * @param $params
     * @return mixed
     */
    public function templateUpsert($sms_type, $username, $params)
    {
        //niusms
        $template_key = $params['template_key'];
        $template_info = $this->getTemplateList($sms_type, $username, $template_key)[0];
        if (empty($template_info['sms']['content'])) {
            throw new AdminException('TEMPLATE_NOT_SMS_CONTENT');
        }
        $model_info = $this->template_model->where('template_key', $template_key)->findOrEmpty();
        if (!$model_info->isEmpty() && $model_info->audit_status == NoticeTypeDict::TEMPLATE_AUDIT_STATUS_PASS) {
            throw new AdminException('TEMPLATE_IS_PASS');
        }
        $config = $this->niu_service->getNiuLoginConfig();
        $data = [
            'temName' => $template_info['name'],
            'temType' => $params['template_type'],
            'temContent' => $template_info['sms']['content'],
            'paramJson' => $params['params_json'],
            'extend' => ['template_key' => $template_key],
            'signature' => $config['signature'],
            'temId' => $params['template_id'],
        ];
        $res = $this->niu_service->templateCreate($username, $data);
        $tem_id = $res['temId'] ?? 0;
        if ($model_info->isEmpty()) {
            $this->template_model->create([
                'sms_type' => $sms_type,
                'username' => $username,
                'template_key' => $template_key,
                'audit_status' => NoticeTypeDict::TEMPLATE_AUDIT_STATUS_WAIT,
                'template_id' => $tem_id,
                'report_info' => $res ?? [],
                'create_time' => time(),
                'update_time' => time(),
            ]);
        } else {
            $model_info->audit_status = NoticeTypeDict::TEMPLATE_AUDIT_STATUS_WAIT;
            $model_info->template_id = $tem_id;
            $model_info->report_info = $res ?? [];
            $model_info->update_time = time();
            $model_info->save();
        }
        return $res ?? [];
    }

    /**
     * 清除模版报备信息
     * @param $username
     * @param $template_id
     * @return void
     * @throws \Exception
     */
    public function templateDelete($username, $template_id)
    {
        $config = $this->niu_service->getNiuLoginConfig();
        $params['password'] = $config['password'];
        $params['template_id'] = $template_id;
        $this->niu_service->templateDelete($username, $params);
        $this->template_model->where('template_id', $template_id)->delete();
    }

    /**
     * 格式化列表接口分页器
     * @param $data
     * @return array
     */
    private function formatListPaginate($data)
    {
        return [
            'total' => $data['total'],
            'per_page' => $data['size'],
            'current_page' => $data['currentPage'],
            'last_page' => $data['totalPage'],
        ];
    }

    /**
     * 获取订单列表
     * @param $username
     * @param $params
     * @return mixed
     */
    public function orderList($username, $params)
    {
        $res = $this->niu_service->orderList($username, $params);
        return $res;
    }

    /**
     * 创建订单
     * @param $username
     * @param $package_id
     * @return mixed
     */
    public function createOrder($username, $package_id)
    {
        $res = $this->niu_service->orderCreate($username, ['package_id' => $package_id]);
        return $res;
    }

    /**
     * 计算订单
     * @param $username
     * @param $package_id
     * @return mixed
     */
    public function calculate($username, $package_id)
    {
        $res = $this->niu_service->calculate($username, ['package_id' => $package_id]);
        return $res;
    }

    /**
     * 获取支付使用信息
     * @param $username
     * @param $params
     * @return mixed
     */
    public function getPayInfo($username, $params)
    {
        $res = $this->niu_service->orderPayInfo($username, $params);
        return $res;
    }

    /**
     * 获取订单信息
     * @param $username
     * @param $out_trade_no
     * @return mixed
     */
    public function orderInfo($username, $out_trade_no)
    {
        $res = $this->niu_service->orderInfo($username, $out_trade_no);
        return $res;
    }

    /**
     * 获取订单状态
     * @param $username
     * @param $out_trade_no
     * @return mixed
     */
    public function orderStatus($username, $out_trade_no)
    {
        $res = $this->niu_service->orderStatus($username, $out_trade_no);
        return $res;
    }

}
