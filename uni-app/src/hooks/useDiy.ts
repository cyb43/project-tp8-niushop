import { ref, reactive, computed } from 'vue';
import { onLoad, onShow, onHide, onPageScroll, onUnload } from '@dcloudio/uni-app';
import { img, handleOnloadParams } from '@/utils/common';
import { getDiyInfo } from '@/app/api/diy';
import useDiyStore from '@/app/stores/diy';

export function useDiy(params: any = {}) {

    const loading = ref(true);
    const diyStore = useDiyStore();

    const id = ref(0)
    const name = ref(params.name || '')
    const template = ref('')
    const currRoute = ref('') //当前路由
    const requestData: any = reactive({});

    // 自定义页面 数据
    const diyData = reactive({
        pageMode: 'diy',
        title: '',
        global: {},
        value: []
    })

    const getLoading = () => {
        return loading.value;
    }

    const data: any = computed(() => {
        if (diyStore.mode == 'decorate') {
            return diyStore;
        } else {
            return diyData;
        }
    })

    const isShowTopTabbar = ref(false);

    const pageStyle = () => {
        let style = '';
        if (data.value.global.pageStartBgColor) {
            if (data.value.global.pageStartBgColor && data.value.global.pageEndBgColor) style += `background:linear-gradient(${ data.value.global.pageGradientAngle },${ data.value.global.pageStartBgColor },${ data.value.global.pageEndBgColor });`;
            else style += 'background-color:' + data.value.global.pageStartBgColor + ';';
        }
        if (data.value.global.bottomTabBar && data.value.global.bottomTabBar.isShow) {
            style += 'min-height:calc(100vh - 50px);';
        } else {
            style += 'min-height:calc(100vh);';
        }
        if (data.value.global.bgUrl) {
            style += `background-image:url('${ img(data.value.global.bgUrl) }');`;
        }

        if (data.value.global.bgHeightScale) {
            style += `background-size: 100% ${ data.value.global.bgHeightScale }%;`;
        }

        return style;
    };

    // 监听页面加载
    const onLoadLifeCycle = () => {
        onLoad((option: any) => {
            // #ifdef MP-WEIXIN
            // 处理小程序场景值参数
            option = handleOnloadParams(option);
            // #endif

            // #ifdef H5
            // 装修模式
            diyStore.mode = option.mode || '';
            if (diyStore.mode == 'decorate') {
                loading.value = false;
            }
            // #endif

            id.value = option.id || '';
            if (name.value == '') name.value = option.name || '';
            template.value = option.template || '';
        });
    }

    // 监听页面显示
    const onShowLifeCycle = (callback: any = null) => {
        onShow(() => {
            /******** 解决跳转自定义页面空白问题-第二步-start **********/
            let curPage: any = getCurrentPages();
            currRoute.value = curPage[curPage.length - 1] ? curPage[curPage.length - 1].route : ''; //获取当前页面的路由
            let urlArr = []
            if (uni.getStorageSync('diyPageBlank')) {
                urlArr = uni.getStorageSync('diyPageBlank');
            }
            if (!urlArr.length || urlArr.length && urlArr.indexOf(currRoute.value) == -1) {
                diyStore.topFixedStatus = 'home'
            } else if (urlArr.length && urlArr.indexOf(currRoute.value) != -1) {
                diyStore.topFixedStatus = 'diy'
            }
            /******** 解决跳转自定义页面空白问题-第二步-end **********/

            // 装修模式
            if (diyStore.mode == 'decorate') {
                diyStore.init();
            } else {
                getDiyInfo({
                    id: id.value,
                    name: name.value,
                    template: template.value
                }).then((res: any) => {
                    Object.assign(requestData, res.data);
                    if (requestData.value) {
                        diyData.pageMode = requestData.mode;
                        diyData.title = requestData.title;

                        let sources = JSON.parse(requestData.value); // todo diy的结构应该后台处理好，前端就不需要再转换了

                        diyData.global = sources.global;

                        diyData.global.id = requestData.id;
                        diyData.global.type = requestData.type;

                        // 用于区分微页面之间弹窗的id
                        if (diyData.global.popWindow && diyData.global.popWindow.show) {
                            diyData.global.popWindow.id = requestData.id;
                        }
                        
                        diyData.value = sources.value;
                        diyData.value.forEach((item: any, index) => {
                            item.pageStyle = '';
                            item.componentIsShow = true // 是否显示
                            if (item.pageStartBgColor) {
                                if (item.pageStartBgColor && item.pageEndBgColor) item.pageStyle += `background:linear-gradient(${ item.pageGradientAngle },${ item.pageStartBgColor },${ item.pageEndBgColor });`;
                                else item.pageStyle += 'background-color:' + item.pageStartBgColor + ';';
                            }

                            if (item.margin) {
                                if (item.margin.top > 0) {
                                    item.pageStyle += 'padding-top:' + item.margin.top * 2 + 'rpx' + ';';
                                }
                                item.pageStyle += 'padding-bottom:' + item.margin.bottom * 2 + 'rpx' + ';';
                                item.pageStyle += 'padding-right:' + item.margin.both * 2 + 'rpx' + ';';
                                item.pageStyle += 'padding-left:' + item.margin.both * 2 + 'rpx' + ';';
                            }
                        });

                        // 控制自定义头部是否出现 | 微信小程序
                        isShowTopTabbar.value = diyData.value.some((item: any) => {
                            return item && item.position && item.position == 'top_fixed'
                        });

                        uni.setNavigationBarTitle({
                            title: diyData.title
                        });
                    }

                    loading.value = false;

                    if (callback) callback(requestData)

                });
            }
        })
    }

    // 监听页面隐藏
    const onHideLifeCycle = (callback: any = null) => {
        onHide(() => {
            /******** 解决跳转自定义页面空白问题-第一步 -start **********/
            let url = [];
            if (uni.getStorageSync('diyPageBlank')) {
                url = uni.getStorageSync('diyPageBlank');
            }

            // 清空重复、与当前页面路径一致的url
            if (url.length) {
                url = Array.from(new Set(url))
                url.forEach((item, index, arr) => {
                    if (item == currRoute.value) {
                        arr.splice(index, 1);
                    }
                })
            }

            // 当diyStore.topFixedStatus == "diy"时,存储到diyPageBlank缓存中
            if (diyStore.topFixedStatus == "diy") {
                url.push(currRoute.value);
            }
            uni.setStorageSync('diyPageBlank', url);
            /******** 解决跳转自定义页面空白问题-第一步 -end **********/

            if (callback) callback()
        })
    }

    // 监听页面卸载
    const onUnloadLifeCycle = (callback: any = null) => {
        onUnload(() => {
            if (callback) callback()
        })
    }

    // 监听滚动事件
    const onPageScrollLifeCycle = () => {
        onPageScroll((e) => {
            if (e.scrollTop > 0) {
                diyStore.scrollTop = e.scrollTop;
            }
            // uni.$emit('scroll')
        })
    }

    return {
        getLoading,
        data: data.value,
        isShowTopTabbar,
        pageStyle,
        onLoad: onLoadLifeCycle,
        onShow: onShowLifeCycle,
        onHide: onHideLifeCycle,
        onUnload: onUnloadLifeCycle,
        onPageScroll: onPageScrollLifeCycle,
    }
}
