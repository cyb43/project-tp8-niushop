import { ref, onMounted, nextTick, computed } from 'vue';
import Sortable from 'sortablejs';
import { range } from 'lodash-es';
import { onPageScroll, onHide, onShow } from '@dcloudio/uni-app';
import useDiyStore from '@/app/stores/diy';

export function useDiyGroup(params: any = {}) {

    let scrollVal: any = ""; //组件滚动值集合
    const componentsScrollBool: any = ref({}); //组件是否根据滚动进行相应改变
    const diyStore = useDiyStore();

    const positionFixed = ref(['fixed', 'top_fixed', 'right_fixed', 'bottom_fixed', 'left_fixed']);

    const data = computed(() => {
        if (diyStore.mode == 'decorate') {
            return diyStore;
        } else {
            return params.data;
        }
    })

    const getComponentClass = (index: any, component: any) => {
        let obj: any = {
            relative: true,
            selected: diyStore.currentIndex == index,
            decorate: diyStore.mode == 'decorate'
        }
        obj['top-fixed-' + diyStore.topFixedStatus] = true;

        if (component.position && positionFixed.value.indexOf(component.position) != -1) {
            //  找出置顶组件，设置禁止拖动
            obj['ignore-draggable-element'] = true;
        } else {
            obj['draggable-element'] = true;
        }
        if (component.componentName == 'ImageAds') {
            obj['overflow-hidden'] = true
        }
        return obj;
    }

    // 是否显示占位区域，用于禁止选中负上边距的内容
    const isShowPlaceHolder = (index: any, component: any) => {
        // #ifdef H5
        if (diyStore.mode == 'decorate') {
            let el: any = document.getElementById('componentList');
            if (el && el.children.length && el.children[index]) {
                let height = el.children[index].offsetHeight;
                let top = 0;
                if (component.margin.top < 0) {
                    top = component.margin.top * 2 * -1;
                    // 若负上边距大于组件的高度，则允许选中进行装修
                    if (top > height) {
                        return false;
                    }
                }
            }
            return true;
        }
        // #endif
        return false;
    }

    // 监听页面加载完成
    const onMountedLifeCycle = () => {
        onMounted(() => {
            // #ifdef H5
            if (diyStore.mode == 'decorate') {
                const el: any = document.getElementById('componentList');
                const sortable = Sortable.create(el, {
                    draggable: '.draggable-element',
                    animation: 200,
                    // 结束拖拽
                    onEnd: event => {
                        let temp = diyStore.value[event.oldIndex!];
                        diyStore.value.splice(event.oldIndex!, 1);
                        diyStore.value.splice(event.newIndex!, 0, temp);

                        nextTick(() => {
                            sortable.sort(range(diyStore.value.length).map((value: any) => {
                                return value.toString();
                            }));

                            diyStore.postMessage(event.newIndex, diyStore.value[event.newIndex]);
                        });
                    }
                });
            }
            // #endif

            nextTick(() => {
                setTimeout(() => {
                    // 初始化组件滚动值
                    scrollVal = uni.getStorageSync('componentsScrollValGroup');
                    if (scrollVal) {
                        for (let key in scrollVal) {
                            componentsScrollBool.value[key] = -1;
                        }
                    }
                }, 500)
            });
        });
    }

    // 页面onShow调用时，也会触发改方法
    const refresh = () => {
        nextTick(() => {
            let time: any = null;
            let fn = () => {
                diyStore.componentRefs = params.getFormRef().componentRefs;
                data.value.componentRefs = params.getFormRef().componentRefs;
                params.getFormRef().componentRefs.topTabbarRef?.refresh();
                if (time) clearInterval(time);
            }

            let getPass = () => {
                let pass = false;
                try {
                    params.getFormRef()
                    pass = true;
                } catch (e) {
                    pass = false;
                }
                if (pass) {
                    fn();
                }
                return pass;
            }

            if (!getPass()) {
                time = setInterval(() => {
                    getPass()
                }, 100)
            }

        })
    }

    // 空函数，禁止选中
    const placeholderEvent = () => {
    }

    const isPagesHide = ref(false)
    onShow(() => {
        isPagesHide.value = false;
    })
    onHide(() => {
        isPagesHide.value = true;
    })

    // 监听滚动事件
    const scrollValStr = ref()
    const onPageScrollLifeCycle = () => {
        onPageScroll((e) => {
            if (scrollVal && !isPagesHide.value) {
                for (let key in scrollVal) {
                    if (e.scrollTop <= 0) {
                        // -1 表示页面滚动值小于零，组件随页面下拉而下来
                        componentsScrollBool.value[key] = -1;
                    } else if (e.scrollTop > scrollVal[key]) {
                        // 1 表示页面滚动值大于传入滚动值，组件随页面上拉背景、文字颜色等采用滚动后的变量
                        componentsScrollBool.value[key] = 1;
                    } else {
                        // 2 表示页面滚动值小于传入滚动值，组件随页面下拉背景、文字颜色等采用滚动前的变量
                        componentsScrollBool.value[key] = 2
                    }
                }
            }
        })
    }

    return {
        scrollV: scrollValStr.value,
        data: data.value,
        componentsScrollBool: componentsScrollBool.value,
        placeholderEvent,
        refresh,
        isShowPlaceHolder,
        getComponentClass,
        onPageScroll: onPageScrollLifeCycle,
        onMounted: onMountedLifeCycle
    }
}
