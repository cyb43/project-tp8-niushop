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
namespace core\dict;


class Notice extends BaseDict
{
    /**
     * 系统uniapp页面链接
     * @param array $data
     * @return array|mixed
     */
    public function load(array $data)
    {
        $with_addon = ($data['with_addon'] ?? 0) == 1;
        $template_files = [];
        $system_path = $this->getDictPath() . "notice" . DIRECTORY_SEPARATOR . $data['type'] . ".php";
        if (is_file($system_path)) {
            $template_files['app'] = $system_path;
        }
        $addons = $this->getLocalAddons();
        foreach ($addons as $v) {
            $template_path = $this->getAddonDictPath($v) . "notice" . DIRECTORY_SEPARATOR . $data['type'] . ".php";
            if (is_file($template_path)) {
                $template_files[$v] = $template_path;
            }
        }
        if ($with_addon) {
            $template_files_data = $this->loadFilesWithAddon($template_files);
        } else {
            $template_files_data = $this->loadFiles($template_files);
        }

        $template_data_array = [];
        foreach ($template_files_data as $file_data) {
            $template_data_array = empty($template_data_array) ? $file_data : array_merge($template_data_array, $file_data);
        }
        return $template_data_array;
    }
}
