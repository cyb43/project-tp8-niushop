<?php

namespace app\service\core\notice;

use app\dict\sys\ConfigKeyDict;
use app\dict\sys\SmsDict;
use app\service\core\http\HttpHelper;
use app\service\core\sys\CoreConfigService;
use core\base\BaseAdminService;

class CoreNiuSmsService extends BaseAdminService
{
    /*************************牛云短信处理****************************/
    private const PACKAGE_LIST_URL = '/niusms/packages';
    private const SEND_CODE_URL = '/niusms/send';

    /***子账号相关***/
    private const SEND_CAPTCHA_URL = '/niusms/captcha';
    private const LOGIN_ACCOUNT_URL = '/niusms/account/login/%s';
    private const ACCOUNT_REGISTER_URL = '/niusms/account/register';
    private const ACCOUNT_INFO_URL = '/niusms/account/info/%s';
    private const ACCOUNT_EDIT_URL = '/niusms/account/edit/%s';
    private const ACCOUNT_SEND_LIST_URL = '/niusms/account/send_list/%s';
    private const RESET_PASSWORD_URL = '/niusms/account/reset/password/%s';

    /**签名相关**/
    private const SIGN_LIST_URL = '/niusms/sign/%s/list';
    private const SIGN_INFO_URL = '/niusms/sign/%s/info';
    private const SIGN_ADD_URL = '/niusms/sign/%s/add';

    /**模版相关**/
    private const TEMPLATE_LIST_URL = '/niusms/template/%s/list';
    private const TEMPLATE_INFO_URL = '/niusms/template/%s/info';
    private const TEMPLATE_ADD_URL = '/niusms/template/%s/add';

    /*****订单相关*****/
    private const ORDER_LIST_URL = '/niusms/order/list/%s';
    private const ORDER_CREATE_URL = '/niusms/order/create/%s';
    private const ORDER_NOTIFY_URL = "/niusms/pay/notify";
    private const ORDER_CALCULATE_URL = '/niusms/order/calculate/%s';
    private const ORDER_PAY_URL = '/niusms/pay/info/%s';
    private const ORDER_INFO_URL = '/niusms/order/info/%s/%s';
    private const ORDER_STATUS_URL = '/niusms/order/status/%s/%s';

    private $niushop_url_prefix = null;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        $niushop_url_prefix = env('NIU_SHOP_PREFIX', 'https://api.niucloud.com/openapi');
        if (empty($niushop_url_prefix)) {
            throw new \Exception('URL_NOT_FOUND');
        } else {
            $this->niushop_url_prefix = $niushop_url_prefix;
        }
    }

    /**
     * 设置账号缓存
     * @param $params
     * @return \app\model\sys\SysConfig|bool|\think\Model
     */
    public function setNiuLoginConfig($params, $is_login = false)
    {
        $config = $this->getNiuLoginConfig(true);
        $config['default'] = $params['default'] ?? ($config['default'] ?? "");
        $config[SmsDict::NIUSMS] = [
            'username' => $params['username'] ?? $config[SmsDict::NIUSMS]['username'] ?? "",
            'password' => $params['password'] ?? $config[SmsDict::NIUSMS]['password'] ?? "",
            'signature' => $is_login ? "" : ($params['signature'] ?? $config[SmsDict::NIUSMS]['signature'] ?? ""),//登录清空默认签名重新选择
        ];
        return (new CoreConfigService())->setConfig(ConfigKeyDict::SMS, $config);
    }

    public function getNiuLoginConfig($is_all = false)
    {
        $config = (new CoreConfigService())->getConfigValue(ConfigKeyDict::SMS);
        if ($is_all) {
            return $config;
        }
        return $config[SmsDict::NIUSMS] ?? [];
    }

    /**
     * 发验证短信
     * @param $params
     * @return mixed
     */
    public function packageList($params)
    {
        $url = $this->niushop_url_prefix . self::PACKAGE_LIST_URL;
        $res = (new HttpHelper())->get($url, $params + $this->getPageParam());
        return $res;
    }

    /**
     * 发验证短信
     * @param $params
     * @return mixed
     */
    public function sendMobileCode($params)
    {
        $url = $this->niushop_url_prefix . self::SEND_CODE_URL;
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }

    /**
     * 发验证短信
     * @return mixed
     */
    public function captcha()
    {
        $url = $this->niushop_url_prefix . self::SEND_CAPTCHA_URL;
        $res = (new HttpHelper())->get($url);
        return $res;
    }

    /**
     * 注册牛云短信子账号
     * @param $params
     * @return mixed
     */
    public function registerAccount($params)
    {
        $res = (new HttpHelper())->post($this->niushop_url_prefix . self::ACCOUNT_REGISTER_URL, $params);
        return $res;
    }

    /**
     * 登录牛云短信子账号
     * @param $params
     * @return mixed
     */
    public function loginAccount($params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::LOGIN_ACCOUNT_URL, $params['username']);
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }

    /**
     * 获取牛云短信子账号信息
     * @param $username
     * @return mixed
     */
    public function accountInfo($username)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ACCOUNT_INFO_URL, $username);
        $res = (new HttpHelper())->get($url);
        $config = $this->getNiuLoginConfig();
        if ($config['username'] == $res['username']) {
            $res['signature'] = $config['signature'] ?? "";
        }
        return $res;
    }

    /**
     * 获取牛云短信子账号信息
     * @param $username
     * @param $params
     * @return mixed
     */
    public function accountSendList($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ACCOUNT_SEND_LIST_URL, $username);
        $res = (new HttpHelper())->get($url, $this->getPageParam() + $params);
        return $res;
    }

    /**
     * 更新账号信息  （手机号 默认签名）
     * @param $username
     * @param $params
     * @return mixed
     */
    public function editAccount($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ACCOUNT_EDIT_URL, $username);
        $res = (new HttpHelper())->put($url, $params);
        return $res;
    }

    /**
     * 重置密码
     * @param $username
     * @param $params
     * @return mixed
     */
    public function resetPassword($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::RESET_PASSWORD_URL, $username);
        $res = (new HttpHelper())->put($url, $params);
        return $res;
    }


    /**********************签名处理*********************/

    /**
     * 获取签名列表
     * @param $username
     * @return mixed
     */
    public function signList($username)
    {
        $url = $this->niushop_url_prefix . sprintf(self::SIGN_LIST_URL, $username);
        $res = (new HttpHelper())->get($url, $this->getPageParam());
        return $res;
    }

    /**
     * 获取签名信息
     * @param $username
     * @param $signature
     * @return mixed
     */
    public function signInfo($username, $signature)
    {
        $url = $this->niushop_url_prefix . sprintf(self::SIGN_INFO_URL, $username);
        $res = (new HttpHelper())->get($url, ['signature' => $signature]);
        return $res;
    }

    /**
     * 报备签名
     * @param $username
     * @param $params
     * @return mixed
     */
    public function signCreate($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::SIGN_ADD_URL, $username);
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }

    /**
     * 报备签名-删除
     * @param $username
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function signDelete($username, $params)
    {
        $time = time();
        $request['tKey'] = $time;
        $request['password'] = md5(md5($params['password']) . $time);
        $request['username'] = $username;
        $request['remark'] = $params['remark'] ?? '';
        $request['signatureList'] = $params['signatures'];
        $url = "https://api-shss.zthysms.com/sms/v1/sign/delete";
        $res = (new HttpHelper())->httpRequest('POST', $url, $request);
        if ($res['code'] != 200) {
            throw new \Exception($res['msg']);
        }
        return $res['failList'] ?? [];

    }



    /**********************模版处理*********************/
    /**
     * 获取模版列表
     * @param $username
     * @param $params
     * @return mixed
     */
    public function templateList($username, $params)
    {
        $params = array_merge($params, $this->getPageParam());
        $params['limit'] = $params['limit'] ?? 100;
        $url = $this->niushop_url_prefix . sprintf(self::TEMPLATE_LIST_URL, $username);
        $res = (new HttpHelper())->get($url, $params);
        return $res;
    }

    /**
     * 获取模版详情
     * @param $username
     * @param $tem_id
     * @return mixed
     */
    public function templateInfo($username, $tem_id)
    {
        $url = $this->niushop_url_prefix . sprintf(self::TEMPLATE_INFO_URL, $username);
        $res = (new HttpHelper())->get($url, [
            'tem_id' => $tem_id
        ]);
        return $res;
    }

    /**
     * 获取签名列表
     * @param $username
     * @param $params
     * @return mixed
     */
    public function templateCreate($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::TEMPLATE_ADD_URL, $username);
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }

    /**
     * 删除模版
     * @param $username
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function templateDelete($username, $params)
    {
        $time = time();
        $request['tKey'] = $time;
        $request['password'] = md5(md5($params['password']) . $time);
        $request['username'] = $username;
        $request['temId'] = $params['template_id'] ?? '';
        $url = "https://api-shss.zthysms.com/sms/v2/template/delete";
        $res = (new HttpHelper())->httpRequest('POST', $url, $request);
        if ($res['code'] != 200) {
            throw new \Exception('ZT' . $res['code'] . ":" . $res['msg']);
        }
    }


    /**
     * 获取订单列表
     * @param $username
     * @param $params
     * @return mixed
     */
    public function orderList($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ORDER_LIST_URL, $username);
        $res = (new HttpHelper())->get($url, $this->getPageParam() + $params);
        return $res;
    }

    /**
     * 获取订单详情
     * @param $username
     * @param $out_trade_no
     * @return mixed
     */
    public function orderInfo($username, $out_trade_no)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ORDER_INFO_URL, $username, $out_trade_no);
        $res = (new HttpHelper())->get($url);
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
        $url = $this->niushop_url_prefix . sprintf(self::ORDER_STATUS_URL, $username, $out_trade_no);
        $res = (new HttpHelper())->get($url);
        return $res;
    }

    /**
     * 创建订单
     * @param $username
     * @param $params
     * @return mixed
     */
    public function orderCreate($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ORDER_CREATE_URL, $username);
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }

    /**
     * 创建订单
     * @param $username
     * @param $params
     * @return mixed
     */
    public function calculate($username, $params)
    {
        $url = $this->niushop_url_prefix . sprintf(self::ORDER_CALCULATE_URL, $username);
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }

    /**
     * 获取订单支付信息
     * @param $username
     * @param $params
     * @return mixed
     */
    public function orderPayInfo($username, $params)
    {
        // 判断是否为https协议
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        // 获取域名（含端口）
        $host = $_SERVER['HTTP_HOST'];
        // 组合成全域名
        $return_url = $protocol . '://' . $host . "/site/setting/sms/pay";

        $url = $this->niushop_url_prefix . sprintf(self::ORDER_PAY_URL, $username);
        $params['notify_url'] = $this->niushop_url_prefix . self::ORDER_NOTIFY_URL;
        //TODO::待添加支付结果页面
        $params['return_url'] = $return_url;
        $res = (new HttpHelper())->post($url, $params);
        return $res;
    }
}