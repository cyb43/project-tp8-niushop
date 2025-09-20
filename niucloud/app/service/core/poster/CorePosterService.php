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

namespace app\service\core\poster;

use app\dict\sys\FileDict;
use app\dict\sys\PosterDict;
use app\model\sys\Poster;
use app\service\core\upload\CoreFetchService;
use core\base\BaseCoreService;
use core\dict\DictLoader;
use core\exception\CommonException;
use core\poster\PosterLoader;
use Throwable;

/**
 * 自定义海报处理层
 */
class CorePosterService extends BaseCoreService
{
    /**
     * 根据类型及插件获取默认海报
     * @param $type
     * @return int|mixed
     */
    public function getDefaultPosterIdByType($type, $addon)
    {
        return (new Poster())->where([
            'type' => $type,
            'is_default' => 1,
            'status' => 1,
            'addon' => $addon,
        ])->value('id') ?: 0;
    }

    /**
     * 创建模板
     * @param $addon
     * @param $data
     * @return true
     */
    public function add($addon, $data)
    {
        $data['addon'] = $addon;
        (new Poster())->create($data);
        return true;
    }


    /**
     * 删除
     * @param $condition
     * @return true
     */
    public function del($condition)
    {
        (new Poster())->where($condition)->delete();
        return true;
    }

    /**
     * 海报类型
     * @param string $type
     * @return array
     */
    public function getType($type = '')
    {
        return PosterDict::getType($type);
    }

    /**
     * 海报模板
     * @param $addon
     * @param $type
     * @return array|null
     */
    public function getTemplateList($addon = '', $type = '')
    {
        $addon_load = new DictLoader('Poster');
        return $addon_load->load([
            'addon' => $addon,
            'type' => $type
        ]);
    }

    /**
     * 实例化模板引擎
     * @param string $poster_type
     * @return PosterLoader
     */
    public function driver($poster_type = 'poster')
    {
        return new PosterLoader($poster_type, []);
    }

    /**
     * 获取海报
     * @param string|int $id 海报id
     * @param string|int $type 模板类型
     * @param array $param
     * @param string $channel
     * @param bool $is_throw_exception
     * @return string|null
     */
    public function get($id, $type, array $param = [], $channel = '', $is_throw_exception = true)
    {
        $condition = [
            ['type', '=', $type],
            ['status', '=', PosterDict::ON],
        ];
        if (!empty($id)) {
            // 查询指定海报
            $condition[] = ['id', '=', $id];
        } else {
            // 查询默认海报
            $condition[] = ['is_default', '=', 1];
        }
        $poster = (new Poster())->where($condition)->findOrEmpty();

        try {

            if ($poster->isEmpty()) {
                // 查询指定类型的海报模板
                $template = $this->getTemplateList('', $type);
                if (!empty($template)) {
                    $poster = $template[0]['data'];
                }
            } else {
                $poster = $poster->toArray();
                $poster = $poster['value'];
            }

            if (empty($poster)) throw new CommonException('海报模板不存在');

            $poster_data = [];
            $poster_data_arr = array_values(array_filter(event('GetPosterData', [
                'type' => $type,
                'param' => $param,
                'channel' => $channel
            ])));

            // 合并模版数据
            foreach ($poster_data_arr as $k => $v) {
                $poster_data = array_merge($poster_data, $v);
            }

            $dir = 'upload/poster';
            $temp1 = md5(json_encode($poster));
            $temp2 = md5(json_encode($poster_data));
            $file_path = 'poster' . $temp1 . '_' . $temp2 . '_' . $channel . '.png';
            $path = $dir . '/' . $file_path;

            //判断当前海报是否存在,存在直接返回地址,不存在的话则创建

            if (is_file($path)) {
                return $path;
            } else {
                return $this->create($poster, $poster_data, $dir, $file_path, $channel);
            }
        } catch (Throwable $e) {
            if ($is_throw_exception) {
                throw new CommonException($e->getMessage() . $e->getFile() . $e->getLine());
            } else {
                return '';
            }

        }
    }

    /**
     * 生成海报
     * @param array $poster
     * @param array $data 填充数据(存在映射关系)
     * @param string $dir
     * @param string $file_path
     * @param string $channel
     * @return string|null
     */
    public function create(array $poster, array $data, string $dir, string $file_path, string $channel = '')
    {
        //将模版中的部分待填充值替换
        $core_upload_service = new CoreFetchService();
        if ($poster['global']['bgType'] == 'url') {
            if (!empty($poster['global']['bgUrl']) && str_contains($poster['global']['bgUrl'], 'http://') || str_contains($poster['global']['bgUrl'], 'https://')) {
                //判断是否是是远程图片,远程图片需要本地化
                $temp_dir = 'file/' . 'image' . '/' . date('Ym') . '/' . date('d');
                try {
                    $poster['global']['bgUrl'] = $core_upload_service->image($poster['global']['bgUrl'], $temp_dir, FileDict::LOCAL)['url'] ?? '';
                } catch (\Exception $e) {

                }
            }
        }

        foreach ($poster['value'] as &$v) {
            foreach ($data as $data_k => $data_v) {
                if ($data_k == $v['relate']) {
                    $v['value'] = $data_v; // 赋值
                    // 如果类型是二维码的话就根据渠道生成对应的二维码
                    if ($v['type'] == 'qrcode') {
                        $v['type'] = 'image';
                        // 将二维码类型转化为图片类型,并且将二维码链接转化为图片路径
                        $v['value'] = qrcode($data_v['url'], $data_v['page'], $data_v['data'], '', $channel);
                    } else if ($v['type'] == 'image') {//校验图片文件是否是远程文件
                        if (str_contains($v['value'], 'http://') || str_contains($v['value'], 'https://')) {
                            //判断是否是是远程图片,远程图片需要本地化
                            $temp_dir = 'file/' . 'image' . '/' . date('Ym') . '/' . date('d');
                            try {
                                $v['value'] = $core_upload_service->image($v['value'], $temp_dir, FileDict::LOCAL)['url'] ?? '';
                            } catch (\Exception $e) {
                                $v['value'] = '';
                            }
                        }

                    }
                }
            }
        }

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        //将填充后的数据赋值
        return $this->driver('poster')->createPoster($poster, $dir, $file_path);
    }

}
