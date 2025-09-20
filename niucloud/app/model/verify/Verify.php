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

namespace app\model\verify;

use app\dict\verify\VerifyDict;
use app\model\member\Member;
use core\base\BaseModel;
use think\db\Query;

/**
 * 核销记录模型
 */
class Verify extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'verify';

    // 设置json类型字段
    protected $json = ['data', 'value'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    public function member()
    {
        return $this->hasOne(Member::class, 'member_id', 'verifier_member_id');
    }

    /**
     * 核销码搜索
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchCodeAttr(Query $query, $value, $data)
    {
        if ($value != '') {
            $query->whereLike('code', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }

    /**
     * 关键词搜索
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchKeywordAttr(Query $query, $value, $data)
    {
        if ($value != '') {
            $query->whereLike('code|body', '%' . $this->handelSpecialCharacter($value) . '%');
        }
    }

    /**
     * 业务id搜索
     * @param $query
     * @param $value
     * @param $data
     * @return void
     */
    public function searchRelateTagAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('relate_tag', '=', $value);
        }
    }

    /**
     * 核销员
     * @param $query
     * @param $value
     * @param $data
     * @return void
     */
    public function searchVerifierMemberIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('verifier_member_id', '=', $value);
        }
    }

    /**
     * 核销类型搜索
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTypeAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('type', '=', $value);
        }
    }

    /**
     * 创建时间搜索器
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[0]) ? 0 : strtotime($value[0]);
        $end_time = empty($value[1]) ? 0 : strtotime($value[1]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('create_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([['create_time', '>=', $start_time]]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([['create_time', '<=', $end_time]]);
        }
    }


    /**
     * 订单id搜索
     * @param $query
     * @param $value
     * @param $data
     * @return void
     */
    public function searchOrderIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("data", "like", '%"' . $value . '"%');
        }
    }

    /**
     * 会员id搜索
     * @param $query
     * @param $value
     * @param $data
     * @return void
     */
    public function searchMemberIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("data", "like", '%"' . $value . '"%');
        }
    }

    /**
     * 核销类型转换
     * @param $value
     * @param $data
     * @return void
     */
    public function getTypeNameAttr($value, $data)
    {
        if (empty($data['type']))
            return '';
        return VerifyDict::getType()[$data['type']]['name'] ?? '';
    }

}
