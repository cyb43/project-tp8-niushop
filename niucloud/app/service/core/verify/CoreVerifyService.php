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

namespace app\service\core\verify;

use app\dict\verify\VerifyDict;
use app\model\verify\Verifier;
use app\model\verify\Verify;
use core\base\BaseCoreService;
use core\exception\CommonException;
use think\facade\Cache;
use function DI\string;

/**
 * 核销服务层
 */
class CoreVerifyService extends BaseCoreService
{


    /**
     * 生成核销码(对应业务调用)
     * @param int $member_id
     * @param string|int $type
     * @param array $param
     * @return array
     */
    public function create(int $member_id, string|int $type, array $param)
    {
        $param['member_id'] = (string)$member_id;
        if (!array_key_exists($type, VerifyDict::getType())) throw new CommonException('VERIFY_TYPE_ERROR');//核销类型错误
        //遇到错误直接抛出即可
        $result = event('VerifyCreate', ['type' => $type, 'member_id' => $member_id, 'data' => $param]);
        foreach ($result as $item) {
            if(!empty($item)){
                $result = $item;
                break;
            }
        }
        $data = [];
        if (empty($result)) {
            $count = 1;
        } else {
            $count = $result['count'] ?? 1;
            $data = $result['data'] ?? [];
            $body = $result['body'] ?? '';
            $relate_tag = $result['relate_tag'] ?? 0;
            $expire_time = $result['expire_time'] ?? null;
        }
        $strData = json_encode($param);
        $value = [
            'type' => $type,
            'type_name' => VerifyDict::getType()[$type]['name'] ?? '',
            'data' => $param,
            'value' => $data,
            'body' => $body ?? '',
            'relate_tag' => $relate_tag ?? 0,
        ];
        $verify_code_list = [];
        $temp = 0;

        while ($temp < $count) {
            $salt = uniqid();
            $verify_code = md5($salt . $strData);
            $this->createCode($verify_code, $value, $expire_time ?? null);
            $verify_code_list[] = $verify_code;
            $temp++;
        }
        return $verify_code_list;
    }

    /**
     * 获取核销码信息
     * @param string $member_id
     * @param string $verify_code
     * @return array
     */
    public function getInfoByCode(string $member_id, $verify_code)
    {
        //获取核销码数据
        $value = $this->getCodeData($verify_code);
        //检测站点数据
        $data = event('VerifyCheck', $value);

        if (!empty($data)) {
            foreach ($data as $datum) {
                if (!empty($datum)) {
                    $value = $datum;
                    break;
                }
            }
        }

        // 检测核销员身份，是否有核销权限
        $verifier = (new Verifier())->where([['member_id', '=', $member_id]])->field('id,verify_type')->findOrEmpty()->toArray();
        if (!empty($verifier)) {
            if (!in_array($value['type'], $verifier['verify_type'])) {
                throw new CommonException('VERIFIER_NOT_AUTH');
            }
        }

        return $value;
    }

    /**
     * 核销(核销api调用)
     * @return true
     */
    public function verify(string $verify_code, int $verify_member_id)
    {
        //获取核销码数据
        $value = $this->getCodeData($verify_code);
        //检测核销员身份
        $verifierModel = new Verifier();
        $verifier = $verifierModel->where([['member_id', '=', $verify_member_id]])->findOrEmpty()->toArray();
        if (empty($verifier)) throw new CommonException('VERIFIER_NOT_EXIST');

        $verify_data = [
            'code' => $verify_code,
            'data' => $value['data'],
            'value' => $value['value'],
            'type' => $value['type'],
            'body' => $value['body'],
            'relate_tag' => $value['relate_tag'],
            'create_time' => time(),
            'verifier_member_id' => $verify_member_id,
        ];
        //核销
        event('Verify', $verify_data); //todo:相关核销业务回调
        $model = new Verify();
        $model->create($verify_data);
        //是核销码失效
        $this->clearCode($verify_code);

        return true;
    }

    /**
     * 核销(核销api调用)
     * @return true
     */
    public function adminVerify(string $verify_code)
    {
        //获取核销码数据
        $value = $this->getCodeData($verify_code);

        $verify_data = [
            'code' => $verify_code,
            'data' => $value['data'],
            'value' => $value['value'],
            'type' => $value['type'],
            'body' => $value['body'],
            'relate_tag' => $value['relate_tag'],
            'create_time' => time(),
            'verifier_member_id' => 0,
            'is_admin' => 1,
        ];
        //核销
        event('Verify', $verify_data); //todo:相关核销业务回调
        $model = new Verify();
        $model->create($verify_data);
        //是核销码失效
        $this->clearCode($verify_code);

        return true;
    }

    /**
     * 获取核销码信息
     * @param string $member_id
     * @param string $verify_code
     * @return array
     */
    public function adminGetInfoByCode($verify_code)
    {
        //获取核销码数据
        $value = $this->getCodeData($verify_code);

        $data = event('VerifyCheck', $value);

        if (!empty($data)) {
            foreach ($data as $datum) {
                if (!empty($datum)) {
                    $value = $datum;
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * 设置核销码数据缓存
     * @param $verify_code
     * @param $value
     * @param null $expire_time
     * @return void
     */
    private function createCode($verify_code, $value, $expire_time = null)
    {
        Cache::tag('verify')->set($verify_code, $value, $expire_time);
    }

    /**
     * 清除核销
     * @param $verify_code
     * @return void
     */
    private function clearCode($verify_code)
    {
        Cache::delete($verify_code);
    }

    /**
     * 获取核销码数据缓存
     * @param $verify_code
     * @return array
     */
    private function getCodeData($verify_code)
    {
        $code_cache = Cache::get($verify_code, []);

        if (empty($code_cache)) throw new CommonException('VERIFY_CODE_EXPIRED');//核销码已过期
        return $code_cache;
    }

}
