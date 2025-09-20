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

namespace app\model\diy_form;

use app\model\member\Member;
use core\base\BaseModel;
use think\model\relation\HasOne;
use think\db\Query;


/**
 * 自定义万能表单填写记录模型
 * Class DiyFormRecords
 * @package app\model\diy
 */
class DiyFormRecords extends BaseModel
{

    protected $type = [
        'create_time' => 'timestamp',
        'update_time' => 'timestamp',
    ];

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'record_id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'diy_form_records';

    // 设置json类型字段
    protected $json = [ 'value' ];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    /**
     * 搜索器:记录id
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchRecordIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("record_id", $value);
        }
    }

    /**
     * 搜索器:关联表单id
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchFormIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->where("form_id", $value);
        }
    }

    /**
     * 搜索器:创建时间
     * @param Query $query
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr(Query $query, $value, $data)
    {
        $start_time = empty($value[ 0 ]) ? 0 : strtotime($value[ 0 ]);
        $end_time = empty($value[ 1 ]) ? 0 : strtotime($value[ 1 ]);
        if ($start_time > 0 && $end_time > 0) {
            $query->whereBetweenTime('diy_form_records.create_time', $start_time, $end_time);
        } else if ($start_time > 0 && $end_time == 0) {
            $query->where([ [ 'diy_form_records.create_time', '>=', $start_time ] ]);
        } else if ($start_time == 0 && $end_time > 0) {
            $query->where([ [ 'diy_form_records.create_time', '<=', $end_time ] ]);
        }
    }

    /**
     * 会员关联
     * @return HasOne
     */
    public function member()
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id')->joinType('left')
            ->withField('member_id,member_no, username, mobile, nickname, headimg');
    }

    /**
     * 关联表单字段列表
     * @return \think\model\relation\HasMany
     */
    public function formFieldList()
    {
        return $this->hasMany(DiyFormFields::class, 'form_id', 'form_id');
    }

    /**
     * 关联填写字段列表
     * @return \think\model\relation\HasMany
     */
    public function recordsFieldList()
    {
        return $this->hasMany(DiyFormRecordsFields::class, 'record_id', 'record_id');
    }

    /**
     * 关联表单提交页配置
     * @return HasOne
     */
    public function submitConfig()
    {
        return $this->hasOne(DiyFormSubmitConfig::class, 'form_id', 'form_id')->joinType('left')
            ->withField('id,form_id,submit_after_action,tips_type,tips_text,success_after_action');
    }

    /**
     * 表单关联
     * @return HasOne
     */
    public function form()
    {
        return $this->hasOne(DiyForm::class, 'form_id', 'form_id')->joinType('left')
            ->withField('form_id,page_title,type')->append([ 'type_name' ]);
    }

}
