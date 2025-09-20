<?php
declare ( strict_types = 1 );

namespace app\listener\system;


use app\listener\poster\FriendspayPoster;
use app\model\member\Member;

/**
 * 系统海报数据
 */
class Poster
{
    /**
     * 系统海报数据
     * @param $data
     * @return array
     */
    public function handle($data)
    {
        $type = $data[ 'type' ] ?? '';
        switch ($type) {
            case 'friendspay':// 找朋友帮忙付海报
                return ( new FriendspayPoster() )->handle($data);
            default:
                $param = $data[ 'param' ];
                $member_id = $param[ 'member_id' ] ?? 0;

                $member_model = new Member();
                $member_info = $member_model->where([
                    [ 'member_id', '=', $member_id ]
                ])->field('nickname,headimg')->findOrEmpty()->toArray();

                if (empty($member_info)) {
                    return [];
                }

                $nickname = $member_info[ 'nickname' ];
                if (mb_strlen($nickname, 'UTF-8') > 10) {
                    $nickname = mb_strlen($nickname) > 10 ? mb_substr($nickname, 0, 7, 'UTF-8') . '...' : $nickname;
                }

                $headimg = $member_info[ 'headimg' ];
                if (empty($headimg)) {
                    $headimg = 'static/resource/images/default_headimg.png';
                }
                $return_data = [
                    'nickname' => $nickname,
                    'headimg' => $headimg,
                ];
                return $return_data;
        }
    }
}
