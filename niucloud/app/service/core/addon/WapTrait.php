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


/**
 * 编译手机端文件
 */
trait WapTrait
{

    // TODO 主题色调 theme

    // TODO 图标库 iconfont

    /**
     * 编译 diy-group 自定义组件代码文件
     * @param $compile_path
     * @param $addon
     * @return false|int
     */
    public function compileDiyComponentsCode($compile_path, $addon)
    {
        $content = "<template>\n";
        $content .= "    <view class=\"diy-group\" id=\"componentList\">\n";
        $content .= "        <top-tabbar :scrollBool=\"diyGroup.componentsScrollBool.TopTabbar\" v-if=\"data.global && Object.keys(data.global).length && data.global.topStatusBar && data.global.topStatusBar.isShow\" ref=\"topTabbarRef\" :data=\"data.global\" />\n";
        $content .= "        <pop-ads v-if=\"data.global && Object.keys(data.global).length && data.global.popWindow && data.global.popWindow.show\" ref=\"popAbsRef\" :data=\"data.global\" />\n";
        $content .= "        <template v-for=\"(component, index) in data.value\" :key=\"component.id\">\n";
        $content .= "           <view v-show=\"component.componentIsShow\"\n";
        $content .= "               @click=\"diyStore.changeCurrentIndex(index, component)\"\n";
        $content .= "               :class=\"diyGroup.getComponentClass(index,component)\" :style=\"component.pageStyle\">\n";
        $content .= "                <view class=\"relative\" :style=\"{ marginTop : component.margin.top < 0 ? (component.margin.top * 2) + 'rpx' : '0' }\">\n";
        $content .= "                   <!-- 装修模式下，设置负上边距后超出的内容，禁止选中设置 -->\n";
        $content .= "                    <view v-if=\"diyGroup.isShowPlaceHolder(index,component)\" class=\"absolute w-full z-1\" :style=\"{ height : (component.margin.top * 2 * -1) + 'rpx' }\" @click.stop=\"diyGroup.placeholderEvent\"></view>\n";

        $root_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'app/components/diy'); // 系统自定义组件根目录
        $file_arr = getFileMap($root_path);

        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, 'index.vue')) {

                    $path = str_replace($root_path . '/', '', $ck);
                    $path = str_replace('/index.vue', '', $path);
                    if ($path == 'group') {
                        continue;
                    }

                    // 获取自定义组件 key 关键词
                    $name_arr = explode('-', $path);
                    foreach ($name_arr as $k => $v) {
                        // 首字母大写
                        $name_arr[ $k ] = strtoupper($v[ 0 ] ?? '') . substr($v, 1);
                    }
                    $name = implode('', $name_arr);
                    $file_name = 'diy-' . $path;

                    $content .= "                <template v-if=\"component.componentName == '{$name}'\">\n";
                    $event_str = '$event';
                    $content .= "                   <$file_name ref=\"diy{$name}Ref\" :component=\"component\" :global=\"data.global\" :index=\"index\" :scrollBool=\"diyGroup.componentsScrollBool.{$name}\" @update:componentIsShow=\"component.componentIsShow = {$event_str}\" />\n";
                    $content .= "                </template>\n";
                }
            }
        }

        // 查询已安装的插件
        $addon_import_content = "";
        $addon_service = new CoreAddonService();
        $addon_list = $addon_service->getInstallAddonList();
        $addon_arr = [];
        if (!empty($addon_list)) {
            foreach ($addon_list as $k => $v) {
                $addon_arr[] = $v[ 'key' ];
            }
        }
        if (!empty($addon)) {
            $addon_arr[] = $addon; // 追加新装插件
        }
        $addon_arr = array_unique($addon_arr);

        foreach ($addon_arr as $k => $v) {
            $addon_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/' . $v . '/components/diy'); // 插件自定义组件根目录
            $addon_file_arr = getFileMap($addon_path);
            if (!empty($addon_file_arr)) {
                foreach ($addon_file_arr as $ck => $cv) {
                    if (str_contains($cv, 'index.vue')) {

                        $path = str_replace($addon_path . '/', '', $ck);
                        $path = str_replace('/index.vue', '', $path);

                        // 获取自定义组件 key 关键词
                        $name_arr = explode('-', $path);
                        foreach ($name_arr as $nk => $nv) {
                            // 首字母大写
                            $name_arr[ $nk ] = strtoupper($nv[ 0 ] ?? '') . substr($nv, 1);
                        }
                        $name = implode('', $name_arr);
                        $file_name = 'diy-' . $path;

                        $content .= "                <template v-if=\"component.componentName == '{$name}'\">\n";
                        $event_str = '$event';
                        $content .= "                   <$file_name ref=\"diy{$name}Ref\" :component=\"component\" :global=\"data.global\" :index=\"index\" :scrollBool=\"diyGroup.componentsScrollBool.{$name}\" @update:componentIsShow=\"component.componentIsShow = {$event_str}\" />\n";

                        $content .= "                </template>\n";

                        $addon_import_content .= "   import diy{$name} from '@/addon/" . $v . "/components/diy/{$path}/index.vue';\n";
                    }
                }
            }
        }

        $content .= "                </view>\n";
        $content .= "            </view>\n";
        $content .= "        </template>\n";
        $content .= "        <template v-if=\"diyStore.mode == '' && data.global && data.global.bottomTabBar && data.global.bottomTabBar.isShow\">\n";
        $content .= "            <view class=\"pt-[20rpx]\"></view>\n";
        $content .= "            <tabbar />\n";
        $content .= "        </template>\n";
        $content .= "    </view>\n";
        $content .= "</template>\n";

        $content .= "<script lang=\"ts\" setup>\n";

        if (!empty($addon_import_content)) {
            $content .= $addon_import_content;
        }

        $content .= "   import topTabbar from '@/components/top-tabbar/top-tabbar.vue'\n";
        $content .= "   import popAds from '@/components/pop-ads/pop-ads.vue'\n";
        $content .= "   import useDiyStore from '@/app/stores/diy';\n";
        $content .= "   import { useDiyGroup } from './useDiyGroup';\n";
        $content .= "   import { ref,getCurrentInstance } from 'vue';\n\n";

        $content .= "   const props = defineProps(['data']);\n";
        $content .= "   const instance: any = getCurrentInstance();\n";
        $content .= "   const getFormRef = () => {\n";
        $content .= "       return {\n";
        $content .= "           componentRefs: instance.refs\n";
        $content .= "       }\n";
        $content .= "   }\n";

        $content .= "   const diyStore = useDiyStore();\n";
        $content .= "   const diyGroup = useDiyGroup({\n";
        $content .= "       ...props,\n";
        $content .= "       getFormRef\n";
        $content .= "   });\n";

        $content .= "   const data = ref(diyGroup.data);\n\n";

        $content .= "   // 监听页面加载完成\n";
        $content .= "   diyGroup.onMounted();\n\n";

        $content .= "   // 监听滚动事件\n";
        $content .= "   diyGroup.onPageScroll();\n";

        $content .= "   defineExpose({\n";
        $content .= "       refresh: diyGroup.refresh,\n";
        $content .= "       getFormRef\n";
        $content .= "   })\n";

        $content .= "</script>\n";

        $content .= "<style lang=\"scss\" scoped>\n";
        $content .= "   @import './index.scss';\n";
        $content .= "</style>\n";

        return file_put_contents($compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/components/diy/group/index.vue'), $content);
    }

    /**
     * 编译 pages.json 页面路由代码文件，// {{PAGE}}
     * @param $compile_path
     * @return bool|int|void
     */
    public function installPageCode($compile_path)
    {
        if (!file_exists($this->geAddonPackagePath($this->addon) . 'uni-app-pages.php')) return;

        $uniapp_pages = require $this->geAddonPackagePath($this->addon) . 'uni-app-pages.php';

        if (empty($uniapp_pages[ 'pages' ])) {
            return;
        }

        $pages = [];
        $addon_arr = array_unique(array_merge([ $this->addon ], array_column(( new CoreAddonService() )->getInstallAddonList(), 'key')));
        foreach ($addon_arr as $addon) {
            if (!file_exists($this->geAddonPackagePath($addon) . 'uni-app-pages.php')) continue;
            $uniapp_pages = require $this->geAddonPackagePath($addon) . 'uni-app-pages.php';
            if (empty($uniapp_pages[ 'pages' ])) continue;

            $page_begin = strtoupper($addon) . '_PAGE_BEGIN';
            $page_end = strtoupper($addon) . '_PAGE_END';

            // 对0.2.0之前的版本做处理
            $uniapp_pages[ 'pages' ] = preg_replace_callback('/(.*)(\\r\\n.*\/\/ PAGE_END.*)/s', function ($match) {
                return $match[ 1 ] . ( substr($match[ 1 ], -1) == ',' ? '' : ',' ) . $match[ 2 ];
            }, $uniapp_pages[ 'pages' ]);

            $uniapp_pages[ 'pages' ] = str_replace('PAGE_BEGIN', $page_begin, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('PAGE_END', $page_end, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('{{addon_name}}', $addon, $uniapp_pages[ 'pages' ]);

            $pages[] = $uniapp_pages[ 'pages' ];
        }

        $content = @file_get_contents($compile_path . "pages.json");
        $content = preg_replace_callback('/(.*\/\/ \{\{ PAGE_BEGAIN \}\})(.*)(\/\/ \{\{ PAGE_END \}\}.*)/s', function ($match) use ($pages) {
            return $match[ 1 ] . PHP_EOL . implode(PHP_EOL, $pages) . PHP_EOL . $match[ 3 ];
        }, $content);

        // 找到页面路由文件 pages.json，写入内容
        return file_put_contents($compile_path . "pages.json", $content);
    }

    /**
     * 编译 pages.json 页面路由代码文件
     * @param $compile_path
     * @return bool|int|void
     */
    public function uninstallPageCode($compile_path)
    {
        if (!file_exists($this->geAddonPackagePath($this->addon) . 'uni-app-pages.php')) return;

        $uniapp_pages = require $this->geAddonPackagePath($this->addon) . 'uni-app-pages.php';

        if (empty($uniapp_pages[ 'pages' ])) {
            return;
        }

        $pages = [];
        $addon_arr = array_diff(array_column(( new CoreAddonService() )->getInstallAddonList(), 'key'), [ $this->addon ]);

        foreach ($addon_arr as $addon) {
            if (!file_exists($this->geAddonPackagePath($addon) . 'uni-app-pages.php')) continue;
            $uniapp_pages = require $this->geAddonPackagePath($addon) . 'uni-app-pages.php';
            if (empty($uniapp_pages[ 'pages' ])) continue;

            $page_begin = strtoupper($addon) . '_PAGE_BEGIN';
            $page_end = strtoupper($addon) . '_PAGE_END';

            $uniapp_pages[ 'pages' ] = str_replace('PAGE_BEGIN', $page_begin, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('PAGE_END', $page_end, $uniapp_pages[ 'pages' ]);
            $uniapp_pages[ 'pages' ] = str_replace('{{addon_name}}', $addon, $uniapp_pages[ 'pages' ]);

            $pages[] = $uniapp_pages[ 'pages' ];
        }

        $content = @file_get_contents($compile_path . "pages.json");
        $content = preg_replace_callback('/(.*\/\/ \{\{ PAGE_BEGAIN \}\})(.*)(\/\/ \{\{ PAGE_END \}\}.*)/s', function ($match) use ($pages) {
            return $match[ 1 ] . PHP_EOL . implode(PHP_EOL, $pages) . PHP_EOL . $match[ 3 ];
        }, $content);
        // 找到页面路由文件 pages.json，写入内容
        return file_put_contents($compile_path . "pages.json", $content);
    }

    /**
     * 编译 加载插件标题语言包
     * @param $compile_path
     * @param $addon
     */
    public function compileLocale($compile_path, $addon)
    {
        $locale_data = [];

        $root_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'locale'); // 系统语言包根目录
        $file_arr = getFileMap($root_path, []);
        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, '.json')) {
                    $app_json = @file_get_contents($ck);
                    $json = json_decode($app_json, true);
                    // 清空当前安装/卸载的插件语言包
                    foreach ($json as $jk => $jc) {
                        if (strpos($jk, $addon) !== false) {
                            unset($json[ $jk ]);
                        }
                    }
                    $locale_data[ $cv ] = [
                        'path' => $ck,
                        'json' => $json
                    ];
                }
            }
        }

        // 查询已安装的插件
        $addon_service = new CoreAddonService();
        $addon_list = $addon_service->getInstallAddonList();
        $addon_arr = [];
        if (!empty($addon_list)) {
            foreach ($addon_list as $k => $v) {
                $addon_arr[] = $v[ 'key' ];
            }
        }
        $addon_arr[] = $addon; // 追加新装插件
        $addon_arr = array_unique($addon_arr);
        foreach ($addon_arr as $k => $v) {
            $addon_path = $compile_path . str_replace('/', DIRECTORY_SEPARATOR, 'addon/' . $v . '/locale'); // 插件语言包根目录
            $addon_file_arr = getFileMap($addon_path, []);
            if (!empty($addon_file_arr)) {
                foreach ($addon_file_arr as $ck => $cv) {
                    if (str_contains($cv, '.json')) {
                        $json = @file_get_contents($ck);
                        $json = json_decode($json, true);
                        $addon_json = [];
                        foreach ($json as $jk => $jv) {
                            $addon_json[ $v . '.' . $jk ] = $jv;
                        }
                        if (isset($locale_data[ $cv ])) $locale_data[ $cv ][ 'json' ] = array_merge($locale_data[ $cv ][ 'json' ], $addon_json);
                    }
                }
            }
        }

        foreach ($locale_data as $k => $v) {
            file_put_contents($v[ 'path' ], json_encode($v[ 'json' ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    /**
     * 合并manifest.json
     * @param string $compile_path
     * @param array $merge_data
     * @return void
     */
    public function mergeManifestJson(string $compile_path, array $merge_data)
    {
        $manifest_json = str_replace('/', DIRECTORY_SEPARATOR, $compile_path . 'src/manifest.json');
        $manifest_content = $this->jsonStringToArray(file_get_contents($manifest_json));

        ( new CoreAddonBaseService() )->writeArrayToJsonFile(array_merge2($manifest_content, $merge_data), $manifest_json);
    }

    /**
     * json 字符串解析成数组
     * @param $string
     * @return array
     */
    private function jsonStringToArray($string)
    {
        $list = explode(PHP_EOL, $string);

        $json_array = [];
        foreach ($list as $index => $item) {
            if (strpos($item, '/*') === false) {
                $json_array[] = $item;
            }
        }
        return json_decode(implode(PHP_EOL, $json_array), true);
    }
}
