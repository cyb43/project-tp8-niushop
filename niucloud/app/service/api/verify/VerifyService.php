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

namespace app\service\api\verify;

use app\model\verify\Verifier;
use app\model\verify\Verify;
use app\service\core\verify\CoreVerifyService;
use core\base\BaseApiService;

/**
 * 核销服务层
 */
class VerifyService extends BaseApiService
{

    /**
     * 获取核销码(对应业务调用)
     * @param $type
     * @param $data = ['order_id' => , 'goods_id' => ]
     * @return string
     */
    public function getVerifyCode($type, array $data)
    {
        $list = ( new CoreVerifyService() )->create($this->member_id, $type, $data);
        $temp = [];
        foreach ($list as $item) {
            $temp[] = [
                'code' => $item,
                'qrcode' => qrcode($item, '', [], outfile: false)
            ];
        }
        return $temp;
    }

    /**
     * 获取核销信息
     * @param $code
     * @return bool
     */
    public function getInfoByCode($code)
    {
        return ( new CoreVerifyService() )->getInfoByCode($this->member_id,$code);
    }

    /**
     * 核销
     * @param $code
     * @return bool
     */
    public function verify($code)
    {
        return ( new CoreVerifyService() )->verify($code, $this->member_id);
    }

    /**
     * 校验是否是核销员
     * @return bool
     */
    public function checkVerifier()
    {
        $verifier = ( new Verifier() )->where([ [ 'member_id', '=', $this->member_id ] ])->findOrEmpty();
        if (!$verifier->isEmpty()) return true;
        return false;
    }

    /**
     * 获取核销员核销记录
     * @param $data
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getRecordsPageByVerifier(array $data)
    {
        $field = '*';
        $search_model = ( new Verify() )->where([
            [ 'verifier_member_id', '=', $this->member_id ]
        ])->withSearch([ 'code', 'type', 'create_time', 'relate_tag', 'keyword' ], $data)
            ->with([
                'member' => function($query) {
                    $query->field('member_id, nickname, mobile, headimg');
                }
            ])
            ->field($field)
            ->order('create_time desc')
            ->append([ 'type_name' ]);
        return $this->pageQuery($search_model);
    }

    /**
     * 获取记录详情
     * @param int $id
     * @return array
     */
    public function getRecordsDetailByVerifier($code)
    {
        $field = '*';
        return ( new Verify() )->where([
            [ 'verifier_member_id', '=', $this->member_id ],
            [ 'code', '=', $code ]
        ])->with([
            'member' => function($query) {
                $query->field('member_id, nickname, mobile, headimg');
            }
        ])->field($field)->append([ 'type_name' ])->findOrEmpty()->toArray();
    }

}
