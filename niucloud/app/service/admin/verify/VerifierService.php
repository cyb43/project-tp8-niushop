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

namespace app\service\admin\verify;

use app\dict\verify\VerifyDict;
use app\model\member\Member;
use app\model\verify\Verifier;
use core\base\BaseAdminService;
use core\exception\CommonException;

/**
 *
 * Class VerifyerService
 * @package app\service\admin\verify
 */
class VerifierService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Verifier();
    }

    /**
     * 获取核销员表列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        $search_model = $this->model->where([ [ 'id', '>', 0 ] ])->with([ 'member' => function ($query) {
            $query->field('member_id, nickname, mobile, headimg');
        } ])->field('*')->order('create_time desc');
        return $this->pageQuery($search_model, function ($item, $key) {
            $item = $this->makeUp($item);
        });
    }

    /**
     * 获取核销员表列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        return $this->model->where([ [ 'id', '>', 0 ] ])->with([ 'member' => function ($query) {
            $query->field('member_id, nickname, mobile, headimg');
        } ])->field('*')->order('create_time desc')->select()->toArray();
    }

    /**
     * 组合整理数据
     * @param $data
     */
    public function makeUp($data)
    {
        //核销类型
        if (!empty($data[ 'verify_type' ])) {
            $type = VerifyDict::getType();
            $type_array = [];
            foreach ($data[ 'verify_type' ] as $key => $value) {
                if (array_key_exists($value, $type)) {
                    $type_array[ $key ][ 'verify_type' ] = $value;
                    $type_array[ $key ][ 'verify_type_name' ] = $type[ $value ][ 'name' ];
                }
            }
            $data[ 'verify_type_array' ] = $type_array;
        }
        return $data;
    }

    public function getDetail($id)
    {
        return $this->model->with([ 'member' => function ($query) {
            $query->field('member_id, nickname, mobile, headimg');
        } ])->field('')->findOrEmpty($id)->toArray();
    }

    /**
     * 添加核销员
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $member = ( new Member() )->where([ [ 'member_id', '=', $data[ 'member_id' ] ] ])->findOrEmpty();
        if ($member->isEmpty()) throw new CommonException('MEMBER_NOT_EXIST');

        $model = $this->model->where([ [ 'member_id', '=', $data[ 'member_id' ] ] ])->findOrEmpty();
        if (!$model->isEmpty()) throw new CommonException('VERIFIER_EXIST');

        $data[ 'create_time' ] = time();
        $res = $this->model->create($data);
        return $res->id;
    }

    /**
     * 编辑核销员
     * @param array $data
     * @return mixed
     */
    public function edit($id, $data)
    {
        $this->model->where([ [ 'id', '=', $id ] ])->update($data);
    }

    /**
     * 删除核销员
     * @param int $id
     * @return bool
     */
    public function del(int $id)
    {
        $res = $this->model->where([ [ 'id', '=', $id ] ])->delete();
        return $res;
    }
}
