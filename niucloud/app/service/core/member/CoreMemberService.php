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

namespace app\service\core\member;

use app\dict\member\MemberAccountTypeDict;
use app\model\member\Member;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use core\dict\DictLoader;
use core\exception\AdminException;
use core\exception\CommonException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Log;
use think\Model;

/**
 * 会员信息服务层
 * Class CoreMemberService
 * @package app\service\core\member
 */
class CoreMemberService extends BaseCoreService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Member();
    }

    /**
     * 修改
     * @param int $member_id
     * @param string $field
     * @param $data
     * @return Member
     */
    public function modify(int $member_id, string $field, $data)
    {
        $field_name = match ( $field ) {
            'nickname' => 'nickname',
            'mobile' => 'mobile',
            'id_card' => 'id_card',
            'remark' => 'remark',
            'headimg' => 'headimg',
            'member_label' => 'member_label',
            'member_level' => 'member_level',
            'birthday' => 'birthday',
            'sex' => 'sex',
        };
        $where = array(
            [ 'member_id', '=', $member_id ],
        );
        return $this->model->where($where)->update([ $field_name => $data ]);
    }

    /**
     * 批量修改字段
     * @param $data
     * @return Member
     */
    public function batchModify($data)
    {
        $field = $data[ 'field' ];
        $member_ids = $data[ 'member_ids' ];
        $is_all = $data[ 'is_all' ];
        $field_name = match ( $field ) {
            'member_label' => 'member_label',
            'member_level' => 'member_level',
            default => throw new AdminException("不支持的字段: $field"),
        };
        $where = [];
        if (!$is_all) {
            $where[] = [ 'member_id', 'in', $member_ids ];
        } else {
            $where[] = [ 'member_id', 'not in', $member_ids ];
        }
        return $this->model->where($where)->withSearch([ 'keyword', 'register_type', 'create_time', 'is_del', 'member_label', 'register_channel', 'member_level' ], $data[ 'where' ])->update([ $field_name => $data[ 'value' ] ]);
    }

    /**
     * 通过会员查询openid
     * @param int $member_id
     * @return array
     */
    public function getInfoByMemberId(int $member_id, string $field = '*')
    {
        $where = array(
            [ 'member_id', '=', $member_id ]
        );
        return $this->model->where($where)->field($field)->findOrEmpty()->toArray();
    }

    /**
     * 查询会员实例
     * @param int $member_id
     * @return Member|array|mixed|Model
     */
    public function find(int $member_id)
    {
        $where = array(
            [ 'member_id', '=', $member_id ]
        );
        return $this->model->where($where)->findOrEmpty();
    }

    /**
     * 会员数量
     * @return int
     * @throws DbException
     */
    public function getCount(array $where = [])
    {
        $condition = array();
        if (!empty($where[ 'create_time' ])) {
            $condition[] = [ 'create_time', 'between', $where[ 'create_time' ] ];
        }
        if (!empty($where[ 'sex' ])) {
            $condition[] = [ 'sex', '=', $where[ 'sex' ] ];
        }
        if (!empty($where[ 'last_visit_time' ])) {
            $condition[] = [ 'last_visit_time', 'between', $where[ 'last_visit_time' ] ];
        }
        return $this->model->where($condition)->count();
    }

    /**
     * 生成会员编码
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function createMemberNo()
    {
        $config = ( new CoreMemberConfigService() )->getMemberConfig();

        $no = ( new self() )->getMemberNoConfig();
        $no += 1;
        $member_no = $config[ 'prefix' ] . ( strlen($config[ 'prefix' ]) > $config[ 'length' ] ? $no : str_pad($no, ( $config[ 'length' ] - strlen($config[ 'prefix' ]) ), "0", STR_PAD_LEFT) );

        $member = ( new Member() )->where([ [ 'member_no', '=', $member_no ] ])->findOrEmpty();

        if ($member->isEmpty()) {
            return $member_no;
        } else {
            // 变更站点最大会员码值
            ( new self() )->setMemberNoConfig($no);
            return self::createMemberNo();
        }
    }

    /**
     * 设置会员会员码
     * @param int $member_id
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function setMemberNo(int $member_id)
    {
        $config = ( new CoreMemberConfigService() )->getMemberConfig();

        $no = ( new self() )->getMemberNoConfig();
        $no += 1;
        $member_no = $config[ 'prefix' ] . ( strlen($config[ 'prefix' ]) > $config[ 'length' ] ? $no : str_pad($no, ( $config[ 'length' ] - strlen($config[ 'prefix' ]) ), "0", STR_PAD_LEFT) );

        $member = ( new Member() )->where([ [ 'member_no', '=', $member_no ] ])->findOrEmpty();

        // 变更站点最大会员码值
        ( new self() )->setMemberNoConfig($no);

        if ($member->isEmpty()) {
            ( new Member() )->update([ 'member_no' => $member_no ], [ [ 'member_id', '=', $member_id ] ]);
        } else {
            self::setMemberNo($member_id);
        }
    }

    public function setMemberNoConfig(int $no)
    {
        return ( new CoreConfigService() )->setConfig('MEMBER_NO', [ 'no' => $no ]);
    }

    public function getMemberNoConfig()
    {
        return round(( new CoreConfigService() )->getConfigValue('MEMBER_NO')[ 'no' ] ?? 0);
    }

    /**
     * 发放成长值
     * @param int $member_id
     * @param string $key
     * @param array $param
     * @return void
     */
    public static function sendGrowth(int $member_id, string $key, array $param = [])
    {
        $config = ( new CoreMemberConfigService() )->getGrowthRuleConfig();
        if (!isset($config[ $key ]) || empty($config[ $key ]) || empty($config[ $key ][ 'is_use' ])) return true;

        $config = $config[ $key ];

        $dict = ( new DictLoader("GrowthRule") )->load();
        if (!isset($dict[ $key ])) return true;
        $dict = $dict[ $key ];

        $growth = $config[ 'growth' ] ?? 0;
        if (isset($dict[ 'calculate' ]) && !empty($dict[ 'calculate' ])) {
            $calculate = $dict[ 'calculate' ];
            if ($calculate instanceof \Closure) {
                $growth = $calculate($config, $param);
            } else if (class_exists($calculate)) {
                $growth = ( new $calculate() )->handle($config, $param);
            }
        }

        if ($growth <= 0) return true;

        ( new CoreMemberAccountService() )->addLog($member_id, MemberAccountTypeDict::GROWTH, $growth, $param[ 'from_type' ] ?? '', $param[ 'momo' ] ?? $dict[ 'desc' ], $param[ 'related_id' ] ?? 0);
        return true;
    }

    /**
     * 发放积分
     * @param int $member_id
     * @param string $key
     * @param array $param
     * @return true
     */
    public static function sendPoint(int $member_id, string $key, array $param = [])
    {
        $config = ( new CoreMemberConfigService() )->getPointRuleConfig()[ 'grant' ] ?? [];
        if (!isset($config[ $key ]) || empty($config[ $key ]) || empty($config[ $key ][ 'is_use' ])) return true;

        $config = $config[ $key ];

        $dict = ( new DictLoader("PointRule") )->load()[ 'grant' ] ?? [];
        if (!isset($dict[ $key ])) return true;
        $dict = $dict[ $key ];

        $point = $config[ 'point' ] ?? 0;
        if (isset($dict[ 'calculate' ]) && !empty($dict[ 'calculate' ])) {
            $calculate = $dict[ 'calculate' ];
            if ($calculate instanceof \Closure) {
                $point = $calculate($config, $param);
            } else if (class_exists($calculate)) {
                $point = ( new $calculate() )->handle($config, $param);
            }
        }

        if ($point <= 0) return true;

        ( new CoreMemberAccountService() )->addLog($member_id, MemberAccountTypeDict::POINT, $point, $param[ 'from_type' ] ?? '', $param[ 'momo' ] ?? $dict[ 'desc' ], $param[ 'related_id' ] ?? 0);
        return true;
    }

    /**
     * 会员礼包发放
     * @param $member_id
     * @param $gifts
     * @return void
     */
    public function memberGiftGrant($member_id, $gifts = [], $param = [])
    {
        try {
            $dict = ( new DictLoader("MemberGift") )->load();

            foreach ($gifts as $key => $item) {
                if (!$item[ 'is_use' ] || !isset($dict[ $key ]) || !isset($dict[ $key ][ 'grant' ]) || empty($dict[ $key ][ 'grant' ])) continue;
                try {
                    $grant = $dict[ $key ][ 'grant' ];
                    if ($grant instanceof \Closure) {
                        $grant($member_id, $item, $param);
                    } else if (class_exists($grant)) {
                        ( new $grant() )->handle($member_id, $item, $param);
                    }
                } catch (CommonException $e) {
                    Log::write('会员礼包' . $key . '发放失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
                }
            }
            return true;
        } catch (CommonException $e) {
            Log::write('会员礼包发放失败，错误原因：' . $e->getMessage() . $e->getFile() . $e->getLine());
            Log::write('参数：' . json_encode([
                    'member_id' => $member_id,
                    'gifts' => $gifts,
                    'param' => $param
                ]));
        }
    }

    /**
     * 获取礼包内容
     * @param $gifts
     * @return void
     */
    public static function getGiftContent(array $gifts, $scene = 'admin')
    {
        $dict = ( new DictLoader("MemberGift") )->load();

        foreach ($gifts as $k => $item) {
            $gifts[ $k ][ 'content' ] = null;
            if (!isset($item[ 'is_use' ]) || !$item[ 'is_use' ] || !isset($dict[ $k ][ 'content' ]) || empty($dict[ $k ][ 'content' ]) || !isset($dict[ $k ][ 'content' ][ $scene ])) {
                continue;
            } else {
                $content = $dict[ $k ][ 'content' ][ $scene ];
                if ($content instanceof \Closure) {
                    $gifts[ $k ][ 'content' ] = $content($item);
                } else if (class_exists($content)) {
                    $gifts[ $k ][ 'content' ] = ( new $content() )->handle($item);
                }
            }
        }
        return $gifts;
    }

    /**
     * 获取权益内容
     * @param $gifts
     * @return void
     */
    public static function getBenefitsContent(array $benefits, $scene = 'admin')
    {
        $dict = ( new DictLoader("MemberBenefits") )->load();

        foreach ($benefits as $k => $item) {
            $benefits[ $k ][ 'content' ] = null;
            if (!isset($item[ 'is_use' ]) || !$item[ 'is_use' ] || !isset($dict[ $k ][ 'content' ]) || empty($dict[ $k ][ 'content' ]) || !isset($dict[ $k ][ 'content' ][ $scene ])) {
                continue;
            } else {
                $content = $dict[ $k ][ 'content' ][ $scene ];
                if ($content instanceof \Closure) {
                    $benefits[ $k ][ 'content' ] = $content($item);
                } else if (class_exists($content)) {
                    $benefits[ $k ][ 'content' ] = ( new $content() )->handle($item);
                }
            }
        }
        return $benefits;
    }

    /**
     * 获取成长值规则内容
     * @param $gifts
     * @return void
     */
    public static function getGrowthRuleContent(array $config, $scene = 'admin')
    {
        $dict = ( new DictLoader("GrowthRule") )->load();

        foreach ($config as $k => $item) {
            $config[ $k ][ 'content' ] = null;
            if (!isset($item[ 'is_use' ]) || !$item[ 'is_use' ] || !isset($dict[ $k ][ 'content' ]) || empty($dict[ $k ][ 'content' ]) || !isset($dict[ $k ][ 'content' ][ $scene ])) {
                continue;
            } else {
                $content = $dict[ $k ][ 'content' ][ $scene ];
                if ($content instanceof \Closure) {
                    $config[ $k ][ 'content' ] = $content($item);
                } else if (class_exists($content)) {
                    $config[ $k ][ 'content' ] = ( new $content() )->handle($item);
                }
            }
        }
        return $config;
    }

    /**
     * 获取积分发放规则内容
     * @param $gifts
     * @return void
     */
    public static function getPointGrantRuleContent(array $config, $scene = 'admin')
    {
        $dict = ( new DictLoader("PointRule") )->load()[ 'grant' ];

        foreach ($config as $k => $item) {
            $config[ $k ][ 'content' ] = null;
            if (!isset($item[ 'is_use' ]) || !$item[ 'is_use' ] || !isset($dict[ $k ][ 'content' ]) || empty($dict[ $k ][ 'content' ]) || !isset($dict[ $k ][ 'content' ][ $scene ])) {
                continue;
            } else {
                $content = $dict[ $k ][ 'content' ][ $scene ];
                if ($content instanceof \Closure) {
                    $config[ $k ][ 'content' ] = $content($item);
                } else if (class_exists($content)) {
                    $config[ $k ][ 'content' ] = ( new $content() )->handle($item);
                }
            }
        }
        return $config;
    }

    /**
     * 获取积分消费规则内容
     * @param $gifts
     * @return void
     */
    public static function getPointConsumeRuleContent(array $config, $scene = 'admin')
    {
        $dict = ( new DictLoader("PointRule") )->load()[ 'consume' ];

        foreach ($config as $k => $item) {
            $config[ $k ][ 'content' ] = null;
            if (!isset($item[ 'is_use' ]) || !$item[ 'is_use' ] || !isset($dict[ $k ][ 'content' ]) || empty($dict[ $k ][ 'content' ]) || !isset($dict[ $k ][ 'content' ][ $scene ])) {
                continue;
            } else {
                $content = $dict[ $k ][ 'content' ][ $scene ];
                if ($content instanceof \Closure) {
                    $config[ $k ][ 'content' ] = $content($item);
                } else if (class_exists($content)) {
                    $config[ $k ][ 'content' ] = ( new $content() )->handle($item);
                }
            }
        }
        return $config;
    }
}
