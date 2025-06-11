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
namespace core\sms;


use app\dict\notice\NoticeTypeDict;
use app\model\sys\NiuSmsTemplate;
use app\service\core\http\HttpHelper;
use core\exception\CommonException;
use think\facade\Log;

class Niuyun extends BaseSms
{

    protected $username = '';
    protected $password = '';
    protected $signature = '';
//    private const SEND_URL = "https://api-ogw.zthysms.com/partner/v1/sms/sub-accounts/message/%s/template";
    private const SEND_URL = "https://api-shss.zthysms.com/v2/sendSmsTp";

    /**
     * @param array $config
     * @return void
     */
    protected function initialize(array $config = [])
    {
        Log::write("SEND_NY_SMS init " . json_encode($config, 256));
        parent::initialize($config);
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->signature = $config['signature'] ?? '';
    }


    /**
     * 模版发送短信
     * @param string $mobile
     * @param string $template_id
     * @param array $data
     * @return void
     */
    public function send(string $mobile, string $template_id, array $data = [])
    {
        Log::write("SEND_NY_SMS pre " . json_encode($data, 256));
        if (empty($this->signature)) {
            throw new CommonException('签名未配置');
        }
        $template_info = (new NiuSmsTemplate())->where('template_id', $template_id)->findOrEmpty();
        Log::write("SEND_NY_SMS pre signature" . json_encode($template_info->toArray(), 256));
        if ($template_info->isEmpty()) {
            throw new CommonException('模版未报备');
        }
        if ($template_info->audit_status != NoticeTypeDict::API_AUDIT_RESULT_PASS) {
            throw new CommonException('模版审核未通过');
        }
        $url = self::SEND_URL;
        $template_info = $template_info->toArray();
        $data = $this->formatParams($data, $template_info);

        $params['records'] = [
            [
                'mobile' => $mobile,
                'tpContent' => $data
            ]
        ];
        $params['tpId'] = $template_id;
        $params['username'] = $this->username;
        $tKey = time();
        $params['tKey'] = $tKey;
        $params['password'] = md5(md5($this->password) . $tKey);
        $params['signature'] = $this->signature;
        Log::write("SEND_NY_SMS params " . json_encode($params, 256));
        try {
            $res = (new HttpHelper())->httpRequest('POST', $url, $params);
            Log::write("SEND_NY_SMS res " . json_encode($res, 256));
            if ($res['code'] != 200) {
                throw new CommonException('ZT-' . $res['code'] . ":" . $res['msg']);
            }
            return $res;
        } catch (\Exception $e) {
            throw new CommonException($e->getMessage());
        }
    }

    private function formatParams($data, $template_info)
    {
        $params_json = $template_info['param_json'];
        $params_type_arr = NoticeTypeDict::getApiParamsType();
        $type_arr = array_column($params_type_arr, null, 'type');
        $return = [];
        foreach ($params_json as $param => $validate) {
            $value = $data[$param];
            $pattern = $type_arr[$validate]['rule'] ?? '';
            $max = $type_arr[$validate]['max'] ?? 1;
            $min = $type_arr[$validate]['max'] ?? mb_strlen($value);
            if (!empty($pattern) && in_array($validate, [NoticeTypeDict::PARAMS_TYPE_CHINESE, NoticeTypeDict::PARAMS_TYPE_OTHERS])) {
                $value = str_replace(' ', '', $value);
                $value = str_replace('.', '', $value);
                $filtered = preg_replace($pattern, '', $value);
                $value = (mb_strlen($filtered, 'UTF-8') >= $min && mb_strlen($filtered, 'UTF-8') <= 35)
                    ? $filtered  // 长度合法，保留过滤后的字符串
                    : mb_substr($filtered, 0, $max);        // 长度非法，返回空字符串
            }
            if (empty($value)) {
                Log::write("SEND_NY_SMS 参数异常，无法发送 param:" . $param);
                throw new \Exception('NY:参数异常，无法发送');
            }
            $return[$param] = $value;
        }
        return $return;
    }

    public function modify(string $sign, string $mobile, string $code)
    {
    }

    public function template(int $page = 0, int $limit = 10, int $type = 1)
    {
    }

    public function apply(string $title, string $content, int $type)
    {
    }

    public function localTemplate(int $type, int $page, int $limit)
    {
    }

    public function record($id)
    {
    }
}