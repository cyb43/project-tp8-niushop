import { ref, reactive, computed } from 'vue';
import { onLoad, onShow, onHide, onPageScroll, onUnload } from '@dcloudio/uni-app';
import { img, handleOnloadParams, getToken, deepClone } from '@/utils/common';
import { getDiyFormInfo } from '@/app/api/diy_form';
import useDiyStore from '@/app/stores/diy';
import { useLogin } from '@/hooks/useLogin';

export function useDiyForm(params: any = {}) {

    const loading = ref(true);
    const diyStore = useDiyStore();

    const form_id = ref(params.form_id || 0)
    const name = ref(params.name || '')
    const template = ref('')
    const currRoute = ref('') //当前路由
    const requestData: any = reactive({});
    const needLogin = ref(params.needLogin || false) // 是否检测登录

    // 自定义页面 数据
    const diyData = reactive({
        pageMode: 'diy',
        title: '',
        global: {},
        value: [],
        status: 0
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
        var style = '';
        if (data.value.global.pageStartBgColor) {
            if (data.value.global.pageStartBgColor && data.value.global.pageEndBgColor) style += `background:linear-gradient(${ data.value.global.pageGradientAngle },${ data.value.global.pageStartBgColor },${ data.value.global.pageEndBgColor });`;
            else style += 'background-color:' + data.value.global.pageStartBgColor + ';';
        }
        if (data.value.global.bottomTabBarSwitch) {
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
    const onLoadLifeCycle = (callback: any = null) => {
        onLoad((option: any) => {
            // #ifdef MP-WEIXIN
            // 处理小程序场景值参数
            option = handleOnloadParams(option);
            // #endif

            // #ifdef H5
            // 装修模式
            diyStore.mode = option.mode || '';
            if (diyStore.mode == 'decorate') {
                requestData.status = 1;
                requestData.error = [];
                loading.value = false;
            }
            // #endif

            form_id.value = option.form_id || '';
            // if (name.value == '') name.value = option.name || '';
            // template.value = option.template || '';
            getData(callback)
        });
    }

    // 监听页面显示
    const onShowLifeCycle = (callback: any = null) => {
        onShow(() => {
            if (callback) callback(requestData)
        })
    }

    const getData = (callback: any = null) => {
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

            if (!form_id.value) return; // 空值情况下不调用接口

            //  填写万能表单需要检测登录
            if (needLogin.value && !getToken()) {
                useLogin().setLoginBack({
                    url: '/app/pages/index/diy_form',
                    param: {
                        form_id: form_id.value
                    }
                })
                return;
            }

            getDiyFormInfo({
                form_id: form_id.value,
                // name: name.value, todo 暂时没用
                // template: template.value // todo 暂时没用
            }).then((res: any) => {
                Object.assign(requestData, res.data);
                diyData.status = res.data.status;
                if (requestData.value) {
                    diyData.pageMode = requestData.mode;
                    diyData.title = requestData.title;

                    diyStore.id = requestData.form_id;
                    let sources = requestData.value;
                    // 匹配缓存，赋值
                    let diyFormStorage = uni.getStorageSync('diyFormStorage_' + diyStore.id)
                    if (diyFormStorage) {
                        var date = new Date();
                        let currentTime: any = parseInt(date.getTime() / 1000); // 存储信息 5分钟内有效，过期后将重新获取定位信息
                        if (diyFormStorage.validTime > currentTime) {
                            if (diyFormStorage.components) {
                                diyFormStorage.components.forEach((item: any) => {
                                    for (let i = 0; i < sources.value.length; i++) {
                                        if (sources.value[i].componentType == 'diy_form' && item.id == sources.value[i].id) {
                                            let comp = deepClone(item);
                                            let field = deepClone(comp.field);
                                            delete comp.field;
                                            // 移除不需要的数据
                                            delete field.required; // 是否必填
                                            delete field.unique; // 内容不可重复提交
                                            delete field.autofill; // 自动填充上次填写的内容
                                            delete field.privacyProtection; // 隐私保护
                                            Object.assign(sources.value[i], comp)
                                            Object.assign(sources.value[i].field, field)
                                            break;
                                        }
                                    }
                                })
                            }
                        } else {
                            uni.removeStorageSync('diyFormStorage_' + diyStore.id) // 清除过期的表单缓存数据
                        }
                    }
                    diyStore.value = sources.value; // 用于 万能表单 收集数据，组件之间的数据通讯
                    diyData.global = sources.global;
                    diyData.value = sources.value;
                    diyData.value.forEach((item: any, index) => {
                        if (item.isHidden) {
                            // 隐藏组件
                            diyData.value.splice(index, 1)
                            // 过滤表单-提交组件且按钮悬空的情况下不设置样式
                        } else {
                            item.pageStyle = '';
                            if (item.componentName != 'FormSubmit' || item.componentName == 'FormSubmit' && item.btnPosition != 'hover_screen_bottom') {
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
                            }
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
    const onUnloadLifeCycle = () => {
        onUnload(() => {
        })
    }

    // 监听滚动事件
    const onPageScrollLifeCycle = () => {
        onPageScroll((e) => {
            if (e.scrollTop > 0) {
                diyStore.scrollTop = e.scrollTop;
            }
        })
    }

    return {
        getLoading,
        requestData,
        data: data.value,
        isShowTopTabbar,
        pageStyle,
        onLoad: onLoadLifeCycle,
        onShow: onShowLifeCycle,
        onHide: onHideLifeCycle,
        onUnload: onUnloadLifeCycle,
        onPageScroll: onPageScrollLifeCycle,
        getData
    }
}
