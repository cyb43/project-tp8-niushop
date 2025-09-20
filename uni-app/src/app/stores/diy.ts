import { defineStore } from 'pinia'
import { toRaw } from 'vue'
import { diyRedirect, currRoute, getToken } from '@/utils/common';
import { useLogin } from '@/hooks/useLogin';

interface Diy {
    mode: string, // 模式：decorate 装修，为空表示正常
    id: any,
    pageMode: string, // 页面展示模式，diy：自定义，fixed：固定
    currentIndex: number,
    global: {
        title: string,
        pageStartBgColor: string, // 页面背景颜色（开始）
        pageEndBgColor: string, // 页面背景颜色（结束）
        bottomTabBar: any, // 底部导航
        bgUrl: string
    },
    // 组件集合
    value: any[],
    topFixedStatus: string, // 置顶组件的状态
    scrollTop: number,
    topTabarHeight: number,
    componentRefs: any,
    imageAdsSameScreen: any // 图片广告, 是否设置沉浸式
}

const useDiyStore = defineStore('diy', {
    state: (): Diy => {
        return {
            mode: '',
            id: 0,
            pageMode: 'diy',
            currentIndex: -99,
            global: {
                title: "",
                pageStartBgColor: '', // 页面背景颜色（开始）
                pageEndBgColor: '', // 页面背景颜色（结束）
                bottomTabBar: {
                    control: true, // 是否允许展示编辑
                    isShow: true, // 是否显示
                },
                bgUrl: ''
            },
            value: [], // 组件集合
            topFixedStatus: 'home', // 顶部 置顶组件状态，home：展示首页数据、diy：展示置顶组件定义的子页面
            scrollTop: 0, // 滚动位置
            topTabarHeight: 0,
            componentRefs: null,
            imageAdsSameScreen: false
        }
    },
    getters: {},
    actions: {
        // 初始化
        init() {
            // #ifdef H5
            const data = JSON.stringify({
                type: 'init',
                load: true
            });
            // 传输给后台数据
            window.parent.postMessage(data, '*');

            // 监听父页面发来的消息
            window.addEventListener('message', event => {
                try {
                    let data = JSON.parse(event.data);
                    this.currentIndex = data.currentIndex;
                    this.pageMode = data.pageMode;
                    if (data.global) this.global = data.global;
                    if (data.value) this.value = data.value;

                    if (this.value) {
                        this.value.forEach((item, index) => {
                            item.pageStyle = '';
                            item.componentIsShow = true // 是否显示
                            if (item.pageStartBgColor) {
                                if (item.pageStartBgColor && item.pageEndBgColor) item.pageStyle += `background:linear-gradient(${ item.pageGradientAngle },${ item.pageStartBgColor },${ item.pageEndBgColor });`;
                                else item.pageStyle += 'background-color:' + item.pageStartBgColor + ';';
                            }

                            if (item.margin) {
                                if (item.margin.top > 0) {
                                    item.pageStyle += 'padding-top:' + item.margin.top * 2 + 'rpx' + ';';
                                } else {
                                    item.pageStyle += 'padding-top:2rpx' + ';'; // 装修实时预览需要设置
                                }
                                item.pageStyle += 'padding-bottom:' + item.margin.bottom * 2 + 'rpx' + ';';
                                item.pageStyle += 'padding-right:' + item.margin.both * 2 + 'rpx' + ';';
                                item.pageStyle += 'padding-left:' + item.margin.both * 2 + 'rpx' + ';';
                            }
                        });
                    }
                    // console.log('uniapp 接受后台装修返回的组件数据', data);
                } catch (e) {
                    console.log('uni-app diy 接受数据错误', e)
                }
            }, false);
            // #endif
        },
        // 将数据传输给后台
        postMessage(index: any, component: any) {
            // #ifdef H5
            this.currentIndex = index;
            if (component)
                var data: any = JSON.stringify({
                    type: 'data',
                    index: this.currentIndex,
                    global: toRaw(this.global),
                    value: toRaw(this.value),
                    component: toRaw(component)
                });
            // 传输给后台数据
            window.parent.postMessage(data, '*');
            // #endif
        },
        // 选中正在编辑的组件
        changeCurrentIndex(index: number, component: any = null) {
            // #ifdef H5

            // 实际展示禁止编辑
            if (this.mode == '') return;

            // 减少重复请求
            if (this.currentIndex == index) return;
            this.currentIndex = index;
            const data = JSON.stringify({
                type: 'change',
                index,
                component: toRaw(component)
            });
            window.parent.postMessage(data, '*');
            // #endif
        },
        toRedirect(data: any) {
            if (Object.keys(data).length) {
                if (!data.name) return;
                if (currRoute() == 'app/pages/member/index' && !getToken()) {
                    useLogin().setLoginBack({ url: data.url })
                    return;
                }
                diyRedirect(data);
            }
        },
    }
})

export default useDiyStore
