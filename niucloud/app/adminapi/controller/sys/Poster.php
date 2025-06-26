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

namespace app\adminapi\controller\sys;

use app\service\admin\sys\PosterService;
use core\base\BaseAdminController;


/**
 * 自定义海报
 * Class Poster
 * @description 自定义海报
 * @package app\adminapi\controller\sys
 */
class Poster extends BaseAdminController
{

    /**
     * 获取自定义海报分页列表
     * @description 获取自定义海报分页列表
     * @return \think\Response
     */
    public function pages()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ 'type', '' ]
        ]);
        return success(( new PosterService() )->getPage($data));
    }

    /**
     * 获取自定义海报分页列表
     * @description 获取自定义海报分页列表
     * @return \think\Response
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ 'type', '' ]
        ]);
        return success(( new PosterService() )->getList($data));
    }

    /**
     * 自定义海报详情
     * @description 自定义海报详情
     * @param int $id
     * @return \think\Response
     */
    public function info(int $id)
    {
        return success(( new PosterService() )->getInfo($id));
    }

    /**
     * 添加自定义海报
     * @description 添加自定义海报
     * @return \think\Response
     */
    public function add()
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "type", "" ],
            [ "channel", '' ],
            [ "value", '' ],
            [ "status", '' ],
            [ "addon", '' ],
            [ 'is_default', 0 ]
        ]);
        $id = ( new PosterService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 自定义海报编辑
     * @description 自定义海报编辑
     * @param int $id 自定义海报id
     * @return \think\Response
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            [ "name", "" ],
            [ "type", "" ],
            [ "channel", '' ],
            [ "value", '' ],
            [ "status", '' ],
            [ "addon", '' ],
            [ 'is_default', 0 ]
        ]);
        ( new PosterService() )->edit($id, $data);
        return success('EDIT_SUCCESS');
    }

    /**
     * 自定义海报删除
     * @description 自定义海报删除
     * @param int $id 自定义海报id
     * @return \think\Response
     */
    public function del(int $id)
    {
        ( new PosterService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 修改自定义海报状态
     * @description 修改自定义海报状态
     * @return \think\Response
     */
    public function modifyStatus()
    {
        $data = $this->request->params([
            [ 'id', '' ],
            [ 'status', '' ],
        ]);
        ( new PosterService() )->modifyStatus($data);
        return success('SUCCESS');
    }

    /**
     * 将自定义海报修改为默认海报
     * @description 将自定义海报修改为默认海报
     * @return \think\Response
     */
    public function modifyDefault()
    {
        $data = $this->request->params([
            [ 'id', '' ]
        ]);
        ( new PosterService() )->modifyDefault($data);
        return success('SUCCESS');
    }

    /**
     * 获取自定义海报类型
     * @description 获取自定义海报类型
     * @return \think\Response
     */
    public function type()
    {
        $data = $this->request->params([
            [ 'type', '' ]
        ]);
        return success(( new PosterService() )->getType($data[ 'type' ]));
    }

    /**
     * 获取自定义海报模版
     * @description 获取自定义海报模版
     * @return \think\Response
     */
    public function template()
    {
        $data = $this->request->params([
            [ 'addon', '' ],
            [ 'type', '' ]
        ]);
        return success(( new PosterService() )->getTemplateList($data[ 'addon' ], $data[ 'type' ]));
    }

    /**
     * 获取自定义海报初始化数据
     * @description 获取自定义海报初始化数据
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function init()
    {
        $params = $this->request->params([
            [ 'id', "" ],
            [ "type", "" ],
            [ "name", "" ],
        ]);
        return success(( new PosterService() )->getInit($params));
    }

    /**
     * 获取自定义海报预览
     * @description 获取自定义海报预览
     * @return array|\think\Response
     */
    public function preview()
    {
        $data = $this->request->params([
            [ 'id', 0 ], // 海报id
            [ 'type', '' ], // 海报类型
            [ 'param', [
                'mode' => 'preview',
            ] ], // 数据参数
            [ 'channel', 'h5' ]
        ]);
        return success(data: poster(...$data));
    }

}
