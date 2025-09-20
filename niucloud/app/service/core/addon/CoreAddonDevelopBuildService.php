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

namespace app\service\core\addon;

use app\dict\sys\MenuDict;
use app\model\sys\SysMenu;
use core\base\BaseCoreService;
use core\exception\AddonException;

/**
 * 插件开发服务层
 */
class CoreAddonDevelopBuildService extends BaseCoreService
{
    protected $root_path;

    protected $addon_path;

    protected $addon;
    protected $build_path;

    public function __construct()
    {
        parent::__construct();
        $this->root_path = project_path();
    }

    /**
     * 插件打包
     * @param string $addon
     * @return true
     */
    public function build(string $addon)
    {
        $this->addon = $addon;
        $this->addon_path = root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;

        if (!is_dir($this->addon_path)) throw new AddonException('ADDON_IS_NOT_EXIST');//当前目录中不存在此项插件

        $this->admin();
        $this->uniapp();
        $this->buildUniappPagesJson();
        $this->buildUniappLangJson();
        $this->web();
        $this->resource();
        $this->menu('admin');

        // 先拷贝
        dir_copy($this->addon_path, runtime_path() . $addon . DIRECTORY_SEPARATOR . $addon);

        $zip_file = runtime_path() . $addon . '.zip';
        if (file_exists($zip_file)) unlink($zip_file);

        (new CoreAddonDevelopDownloadService(''))->compressToZip(runtime_path() . $addon, $zip_file);

        del_target_dir(runtime_path() . $addon, true);

        return true;
    }

    /**
     * 下载
     * @param string $addon
     * @return array|string|string[]
     */
    public function download(string $addon) {
        $zip_file = runtime_path() . $addon . '.zip';
        if (!file_exists($zip_file)) throw new AddonException('ADDON_ZIP_ERROR');//下载失败
        return str_replace(project_path(), '', $zip_file);
    }

    /**
     * 同步菜单
     * @param string $app_type
     * @return true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function menu(string $app_type) {
        $where = [ ['app_type', '=', $app_type], ['addon', '=', $this->addon] ];
        $field = 'menu_name,menu_key,menu_short_name,parent_select_key,parent_key,menu_type,icon,api_url,router_path,view_path,methods,sort,status,is_show';
        $menu = (new SysMenu())->where($where)->field($field)->order('sort', 'desc')->select()->toArray();
        if (!empty($menu)) {
            $menu = $this->menuToTree($menu, 'menu_key', 'parent_key', 'children');
            (new SysMenu())->where($where)->update(['source' => MenuDict::SYSTEM]);
        }

        $addon_dict = $this->addon_path . 'app' . DIRECTORY_SEPARATOR . 'dict' . DIRECTORY_SEPARATOR . 'menu' . DIRECTORY_SEPARATOR . $app_type . '.php';

        $delete = [];
        if (file_exists($addon_dict)) {
            $menus = include $addon_dict;
            $delete = $menus['delete'] ?? [];
        }

        $content = '<?php' . PHP_EOL;
        $content .= 'return [' . PHP_EOL;
        $content .= $this->arrayFormat($menu);
        if (!empty($delete)) {
            $delete = array_map(function ($item) {
                return "'{$item}'";
            }, $delete);
            $content .=  "    'delete' => [". implode(',', $delete) ."]" . PHP_EOL;
        }
        $content .= '];';
        file_put_contents($addon_dict, $content);

        return true;
    }

    /**
     * 把返回的数据集转换成Tree(专属于)
     * @param $list 要转换的数据集
     * @param string $pk
     * @param string $pid
     * @param string $child
     * @param int $root
     * @return array
     */
    private function menuToTree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = '')
    {
        // 创建Tree
        $tree = array();
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parent_id = $data[$pid];
                if ($root == $parent_id) {
                    $tree[] =& $list[$key];
                } else {
                    if (isset($refer[$parent_id])) {
                        $parent =& $refer[$parent_id];
                        $parent[$child][] =& $list[$key];
                    } else {
                        $tree[] =& $list[$key];
                    }
                }
            }
        }
        return $tree;

    }

    private function arrayFormat($array, $level = 1) {
        $tab = '';
        for ($i = 0; $i < $level; $i++) {
            $tab .= '    ';
        }
        $content = '';
        foreach ($array as $k => $v) {
            if (in_array($k, ['status_name', 'menu_type_name']) || ($level > 2 && $k == 'parent_key')) continue;
            if (is_array($v)) {
                $content .= $tab;
                if (is_string($k)) {
                    $content .= "'{$k}' => ";
                }
                $content .= '[' . PHP_EOL . $this->arrayFormat($v, $level + 1);
                $content .= $tab . '],' . PHP_EOL;
            } else {
                $content .= $tab ."'{$k}' => '{$v}'," . PHP_EOL;
            }
        }
        return $content;
    }

    /**
     * admin打包
     * @return true
     */
    public function admin()
    {
        $admin_path = $this->root_path . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($admin_path)) return true;

        $addon_admin_path = $this->addon_path . 'admin' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_admin_path)) del_target_dir($addon_admin_path, true);
        dir_copy($admin_path, $addon_admin_path);

        // 打包admin icon文件
        $icon_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'icon' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon;
        if (is_dir($icon_dir)) dir_copy($icon_dir, $addon_admin_path . 'icon');

        return true;
    }

    /**
     * wap打包
     * @return true
     */
    public function uniapp()
    {
        $uniapp_path = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($uniapp_path)) return true;

        $addon_uniapp_path = $this->addon_path . 'uni-app' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_uniapp_path)) del_target_dir($addon_uniapp_path, true);
        dir_copy($uniapp_path, $addon_uniapp_path);

        return true;
    }

    public function buildUniappPagesJson() {
        $pages_json = file_get_contents($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'pages.json');
        $code_begin = strtoupper($this->addon) . '_PAGE_BEGIN' . PHP_EOL;
        $code_end = strtoupper($this->addon) . '_PAGE_END' . PHP_EOL;

        if(strpos($pages_json, $code_begin) !== false && strpos($pages_json, $code_end) !== false)
        {
            $pattern = "/\/\/\s+{$code_begin}([\S\s]+)\/\/\s+{$code_end}?/";
            preg_match($pattern, $pages_json, $match);

            if (!empty($match)) {
                $addon_pages = str_replace(PHP_EOL.','.PHP_EOL, '', $match[1]);

                $content = '<?php' . PHP_EOL;
                $content .= 'return [' . PHP_EOL . "    'pages' => <<<EOT" . PHP_EOL . '        // PAGE_BEGIN' . PHP_EOL;
                $content .= $addon_pages;
                $content .= '// PAGE_END' . PHP_EOL . 'EOT' . PHP_EOL . '];';

                if (!is_dir($this->addon_path . 'package')) dir_mkdir($this->addon_path . 'package');
                file_put_contents($this->addon_path . 'package' . DIRECTORY_SEPARATOR . 'uni-app-pages.php', $content);
            }
        }
        return true;
    }

    public function buildUniappLangJson() {
        $zh_json = json_decode(file_get_contents($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . 'zh-Hans.json'), true);
        $en_json = json_decode(file_get_contents($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . 'en.json'), true);

        $zh = [];
        $en = [];
        foreach ($zh_json as $key => $value) {
            if (strpos($key, $this->addon . '.') === 0) {
                $key = str_replace($this->addon . '.', '', $key);
                $zh[$key] = $value;
            }
        }
        foreach ($en_json as $key => $value) {
            if (strpos($key, $this->addon . '.') === 0) {
                $key = str_replace($this->addon . '.', '', $key);
                $en[$key] = $value;
            }
        }
        $addon_lang_dir = $this->addon_path . 'uni-app' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR;
        if (!is_dir($addon_lang_dir)) dir_mkdir($addon_lang_dir);
        (new CoreAddonBaseService())->writeArrayToJsonFile($zh, $addon_lang_dir . 'zh-Hans.json');
        (new CoreAddonBaseService())->writeArrayToJsonFile($en, $addon_lang_dir . 'en.json');

        return true;
    }

    /**
     * web打包
     * @return true
     */
    public function web()
    {
        $web_path = $this->root_path . 'web' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($web_path)) return true;

        $addon_web_path = $this->addon_path . 'web' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_web_path)) del_target_dir($addon_web_path, true);
        dir_copy($web_path, $addon_web_path);

        $layout = $this->root_path . 'web' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->addon;
        if (is_dir($layout)) {
            $layout_dir = $addon_web_path . 'layouts' . DIRECTORY_SEPARATOR . $this->addon;
            if (is_dir($layout_dir)) del_target_dir($layout_dir, true);
            else dir_mkdir($layout_dir);
            dir_copy($layout, $layout_dir);
        }

        return true;
    }

    /**
     * 打包资源文件
     * @return true
     */
    public function resource() {
        $resource_path = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($resource_path)) return true;

        $addon_resource_path = $this->addon_path . 'resource' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_resource_path)) del_target_dir($addon_resource_path, true);
        dir_copy($resource_path, $addon_resource_path);

        return true;
    }
}
