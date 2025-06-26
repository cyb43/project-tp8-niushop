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

namespace app\adminapi\controller\member;

use app\service\admin\member\MemberLevelService;
use core\base\BaseAdminController;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;

/**
 * 会员等级
 * Class MemberLevel
 * @description 会员等级
 * @package app\adminapi\controller\member
 */
class MemberLevel extends BaseAdminController
{
    /**
     * 会员等级分页列表
     * @description 会员等级分页列表
     * @return Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ 'level_name', '' ],
        ]);
        return success(( new MemberLevelService() )->getPage($data));
    }

    /**
     * 会员等级详情
     * @description 会员等级详情
     * @param int $id
     * @return Response
     */
    public function info(int $id)
    {
        return success(( new MemberLevelService() )->getInfo($id));
    }

    /**
     * 添加会员等级
     * @description 添加会员等级
     * @return Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ 'level_name', '' ],
            [ 'growth', 0 ],
            [ 'remark', '' ],
            [ 'level_benefits', [] ],
            [ 'level_gifts', [] ]
        ]);
        $this->validate($data, 'app\validate\member\MemberLevel.add');
        $id = ( new MemberLevelService() )->add($data);
        return success('ADD_SUCCESS', [ 'label_id' => $id ]);
    }

    /**
     * 编辑会员等级
     * @description 编辑会员等级
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ 'level_name', '' ],
            [ 'growth', 0 ],
            [ 'remark', '' ],
            [ 'level_benefits', [] ],
            [ 'level_gifts', [] ],
        ]);
        $this->validate($data, 'app\validate\member\MemberLevel.edit');
        ( new MemberLevelService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 会员等级删除
     * @description 会员等级删除
     * @param int $id
     * @return Response
     */
    public function del(int $id)
    {
        ( new MemberLevelService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 获取标签
     * @description 获取标签
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getAll()
    {
        return success(( new MemberLevelService() )->getAll());
    }

}
