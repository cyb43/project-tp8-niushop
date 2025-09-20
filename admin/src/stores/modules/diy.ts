import { defineStore } from 'pinia'
import { t } from '@/lang'
import { toRaw } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { cloneDeep } from 'lodash-es'

const useDiyStore = defineStore('diy', {
    state: () => {
        return {
            id: 0,
            load: false, // 加载状态
            currentIndex: -99, // 当前正在编辑的组件下标
            currentComponent: 'edit-page', // 当前正在编辑的组件名称
            pageMode: 'diy',
            editTab: 'content',// 编辑页面
            pageTitle: '', // 页面名称（用于后台展示）
            name: '', // 页面标识
            type: '', // 页面模板
            typeName: '',  // 页面模板名称
            templateName: '', // 页面模板标识
            isDefault: 0, // 是否默认页面
            predefineColors: [
                '#F4391c',
                '#ff4500',
                '#ff8c00',
                '#FFD009',
                '#ffd700',
                '#19C650',
                '#90ee90',
                '#00ced1',
                '#1e90ff',
                '#c71585',
                '#FF407E',
                '#CFAF70',
                '#A253FF',
                'rgba(255, 69, 0, 0.68)',
                'rgb(255, 120, 0)',
                'hsl(181, 100%, 37%)',
                'hsla(209, 100%, 56%, 0.73)',
                '#c7158577'
            ],
            components: <any>[], // 组件集合
            position: ['top_fixed', 'right_fixed', 'bottom_fixed', 'left_fixed', 'fixed'],
            global: {
                title: "页面", // 页面标题（用于前台展示）
                completeLayout: 'style-1', // 整体布局，目前万能表单用到，表单布局，排版风格，style-1：单列平铺‌，style-2：左右排列‌
                completeAlign: 'left', // 左右布局 对齐方式，left：左对齐，right：右对齐
                borderControl: true, // 控制表单组件左右布局时，边框是否显示

                pageStartBgColor: "", // 页面背景颜色（开始）
                pageEndBgColor: "", // 页面背景颜色（结束）
                pageGradientAngle: 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                bgUrl: '', // 页面背景图片
                bgHeightScale: 0, // 页面背景高度比例，单位%，0为高度自适应
                imgWidth: '',  // 页面背景图片宽度
                imgHeight: '', // 页面背景图片高度

                // 顶部导航栏
                topStatusBar: {
                    control: true, // 是否允许展示编辑
                    isShow: true, // 是否显示
                    bgColor: "#ffffff", // 头部背景颜色
                    rollBgColor: "#ffffff", // 滚动时，头部背景颜色
                    style: 'style-1', // 导航栏风格样式（style-1：文字，style-2：图片+文字，style-3：图片+搜索，style-4：定位）
                    styleName: '风格1',
                    textColor: "#333333", // 文字颜色
                    rollTextColor: "#333333", // 滚动时，头部文字颜色
                    textAlign: 'center', // 文字对齐方式
                    inputPlaceholder: '请输入搜索关键词',
                    imgUrl: '', // 图片
                    link: { // 跳转链接
                        name: ""
                    }
                },

                // 底部导航
                bottomTabBar: {
                    control: true, // 是否允许展示编辑
                    isShow: true, // 是否显示
                },

                // 弹框 count：不弹出 -1，首次弹出 1，每次弹出 0
                popWindow: {
                    imgUrl: "",
                    imgWidth: '',
                    imgHeight: '',
                    count: 'once', // 'once'（仅一次） | 'always'（每次）
                    show: 0,
                    link: {
                        name: ""
                    },
                },

                // 公共模板属性，所有组件都继承，无需重复定义，组件内部根据业务自行调用
                template: {
                    textColor: "#303133", // 文字颜色
                    pageStartBgColor: "", // 组件底部背景颜色（开始）
                    pageEndBgColor: "", // 组件底部背景颜色（结束）
                    pageGradientAngle: 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）

                    componentBgUrl: '', // 组件背景图片
                    componentBgAlpha: 2, // 组件背景图片的透明度，0~10
                    componentStartBgColor: '', // 组件背景颜色（开始）
                    componentEndBgColor: '', // 组件背景颜色（结束）
                    componentGradientAngle: 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    topRounded: 0, // 组件上圆角
                    bottomRounded: 0, // 组件下圆角

                    elementBgColor: '', // 元素背景颜色
                    topElementRounded: 0, // 元素上圆角
                    bottomElementRounded: 0, // 元素下圆角

                    margin: {
                        top: 0, // 上边距
                        bottom: 0, // 下边距
                        both: 0, // 左右边距
                    },
                    isHidden: false // 是否隐藏该组件 true：是，false：否，增加问号说明：勾选后该组件将隐藏，适用于你不希望看到该组件字段又不希望删除的情况；
                }

            },
            // 组件集合
            value: <any>[]
        }
    },
    getters: {
        editComponent: (state) => {
            if (state.currentIndex == -99) {
                return state.global;
            } else {
                return state.value[state.currentIndex];
            }
        },
    },
    actions: {
        // 初始化数据
        init() {
            this.global = {
                title: "页面", // 页面标题
                completeLayout: 'style-1',
                completeAlign: 'left',
                borderControl: true,

                pageStartBgColor: "", // 页面背景颜色（开始）
                pageEndBgColor: "", // 页面背景颜色（结束）
                pageGradientAngle: 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                bgUrl: '', // 页面背景图片
                bgHeightScale: 100, // 页面背景高度比例，单位%
                imgWidth: '',  // 页面背景图片宽度
                imgHeight: '', // 页面背景图片高度

                // 顶部导航栏
                topStatusBar: {
                    control: true, // 是否允许展示编辑
                    isShow: true, // 是否显示
                    bgColor: "#ffffff", // 头部背景颜色
                    rollBgColor: "#ffffff", // 滚动时，头部背景颜色
                    style: 'style-1', // 导航栏风格样式（style-1：文字，style-2：图片+文字，style-3：图片+搜索，style-4：定位）
                    styleName: '风格1',
                    textColor: "#333333", // 文字颜色
                    rollTextColor: "#333333", // 滚动时，头部文字颜色
                    textAlign: 'center', // 文字对齐方式
                    inputPlaceholder: '请输入搜索关键词',
                    imgUrl: '', // 图片
                    link: { // 跳转链接
                        name: ""
                    }
                },

                // 底部导航
                bottomTabBar: {
                    control: true, // 是否允许展示编辑
                    isShow: true, // 是否显示
                },

                // 弹框 count：不弹出 -1，首次弹出 1，每次弹出 0
                popWindow: {
                    imgUrl: "",
                    imgWidth: '',
                    imgHeight: '',
                    count: 'once', // 'once'（仅一次） | 'always'（每次）
                    show: 0,
                    link: {
                        name: ""
                    },
                },

                // 公共模板属性，所有组件都继承，无需重复定义，组件内部根据业务自行调用
                template: {
                    textColor: "#303133", // 文字颜色
                    pageStartBgColor: "", // 组件底部背景颜色（开始）
                    pageEndBgColor: "", // 组件底部背景颜色（结束）
                    pageGradientAngle: 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）

                    componentBgUrl: '', // 组件背景图片
                    componentBgAlpha: 2, // 组件背景图片的透明度
                    componentStartBgColor: '', // 组件背景颜色（开始）
                    componentEndBgColor: '', // 组件背景颜色（结束）
                    componentGradientAngle: 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    topRounded: 0, // 组件上圆角
                    bottomRounded: 0, // 组件下圆角

                    elementBgColor: '', // 元素背景颜色
                    topElementRounded: 0, // 元素上圆角
                    bottomElementRounded: 0, // 元素下圆角

                    margin: {
                        top: 0, // 上边距
                        bottom: 0, // 下边距
                        both: 0, // 左右边距
                    },
                    isHidden: false // 是否隐藏该组件 true：是，false：否，增加问号说明：勾选后该组件将隐藏，适用于你不希望看到该组件字段又不希望删除的情况；
                }

            };
            this.value = [];
        },
        // 添加组件
        addComponent(key: string, data: any) {
            // 加载完才能添加组件
            if (!this.load) return;

            // 删除不用的字段
            let component = cloneDeep(data);

            component.id = this.generateRandom();
            component.componentName = key;
            component.componentTitle = component.title;
            component.ignore = []; // 忽略公共属性

            Object.assign(component, component.value);
            delete component.title;
            delete component.value;
            // delete component.type;  // todo 考虑删除，没用到
            delete component.icon;
            delete component.render; // 渲染值，万能表单用到了

            // 默认继承全局属性
            let template = cloneDeep(this.global.template);
            Object.assign(component, template);

            if (component.template) {
                // 按照组件初始的属性覆盖默认值
                Object.assign(component, component.template);
                delete component.template;
            }

            if (!this.checkComponentIsAdd(component)) {
                // 组件最多只能添加n个
                ElMessage({
                    type: 'warning',
                    message: `${ component.componentTitle }${ t('componentCanOnlyAdd') }${ component.uses }${ t('piece') }`,
                });
                return;
            }

            // 置顶组件，只能在第一个位置中添加
            if (component.position && this.position.indexOf(component.position) != -1) {

                if (component.position == 'top_fixed') {
                    // 顶部置顶

                    this.currentIndex = 0;
                    // 指定位置添加组件
                    this.value.splice(0, 0, component);

                } else if (component.position == 'bottom_fixed') {
                    // 底部置顶

                    // 指定位置添加组件
                    this.value.splice(this.value.length, 0, component);
                    this.currentIndex = this.value.length - 1;
                } else {

                    this.currentIndex = 0;
                    // 指定位置添加组件
                    this.value.splice(0, 0, component);
                }

            } else if (this.currentIndex === -99) {
                let index = this.currentIndex;
                for (let i = this.value.length - 1; i >= 0; i--) {
                    if (this.value[i].position == 'bottom_fixed') {
                        index = i; // 在定位组件之前添加
                        break;
                    }
                }

                if (index == -99) {
                    this.value.push(component);
                    // 添加组件后（不是编辑调用的），选择最后一个
                    this.currentIndex = this.value.length - 1;
                } else {

                    // 指定位置添加组件
                    this.value.splice(index, 0, component);
                    this.currentIndex = index;
                }

            } else {
                let index = -1;
                for (let i = this.value.length - 1; i >= 0; i--) {
                    if (this.value[i].position && this.value[i].position == 'bottom_fixed') {
                        index = i; // 在定位组件之前添加
                        break;
                    }
                }

                // 判断当前添加的位置跟定位组件是否相邻
                if (index != -1 && (index == this.currentIndex || (index - this.currentIndex) == 1)) {

                    // 未找到定位组件，在当前下标之后添加组件
                    if (index == -1) {
                        this.value.splice(++this.currentIndex, 0, component);
                    } else {
                        // 指定位置添加组件
                        this.value.splice(index, 0, component);
                        this.currentIndex = index;
                    }
                } else {
                    this.value.splice(++this.currentIndex, 0, component);
                }

            }

            this.currentComponent = component.path;
        },
        generateRandom(len: number = 5) {
            return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36);
        },
        // 将数据发送到uniapp
        postMessage() {
            const diyData = JSON.stringify({
                pageMode: this.pageMode,
                currentIndex: this.currentIndex,
                global: toRaw(this.global),
                value: toRaw(this.value)
            });
            window.previewIframe.contentWindow.postMessage(diyData, '*');
        },
        // 选中正在编辑的组件
        changeCurrentIndex(index: number, component: any = null) {
            this.currentIndex = index;
            if (this.currentIndex == -99) {
                this.currentComponent = 'edit-page';
            } else if (component) {
                this.currentComponent = component.path;
            }
        },
        // 删除组件
        delComponent() {
            if (this.currentIndex == -99) return;

            ElMessageBox.confirm(
                t('delComponentTips'),
                t('warning'),
                {
                    confirmButtonText: t('confirm'),
                    cancelButtonText: t('cancel'),
                    type: 'warning',
                    autofocus: false
                }
            ).then(() => {
                this.value.splice(this.currentIndex, 1);

                // 如果组件全部删除，则选中页面设置
                if (this.value.length === 0) {
                    this.currentIndex = -99;
                }

                // 如果当前选中的组件不存在，则选择上一个
                if (this.currentIndex === this.value.length) {
                    this.currentIndex--;
                }
                let component = cloneDeep(this.value[this.currentIndex]);

                this.changeCurrentIndex(this.currentIndex, component)

            }).catch(() => {
            })

        },
        // 上移组件
        moveUpComponent() {
            const temp = cloneDeep(this.value[this.currentIndex]); // 当前选中组件
            let prevIndex = this.currentIndex - 1;
            const temp2 = cloneDeep(this.value[prevIndex]); // 上个组件

            if ((this.currentIndex - 1) < 0 || temp2.position && this.position.indexOf(temp2.position) != -1) return; // 从0开始

            temp.id = this.generateRandom(); // 更新id，刷新组件数据
            temp2.id = this.generateRandom(); // 更新id，刷新组件数据

            if (temp.position && this.position.indexOf(temp.position) != -1) {
                ElMessage({
                    type: 'warning',
                    message: `${ t('componentNotMoved') }`,
                });
                return;
            }

            this.value[this.currentIndex] = temp2;
            this.value[prevIndex] = temp;

            this.changeCurrentIndex(prevIndex, temp);
        },
        // 下移组件
        moveDownComponent() {
            if (this.currentIndex < -1 || (this.currentIndex + 1) >= this.value.length) return; // 最后一个不能下移

            const nextIndex = this.currentIndex + 1;

            const temp = cloneDeep(this.value[this.currentIndex]); // 当前选中组件
            temp.id = this.generateRandom(); // 更新id，刷新组件数据

            const temp2 = cloneDeep(this.value[nextIndex]); // 下个组件
            temp2.id = this.generateRandom(); // 更新id，刷新组件数据

            if (temp2.position && this.position.indexOf(temp2.position) != -1) return;

            if (temp.position && this.position.indexOf(temp.position) != -1) {
                ElMessage({
                    type: 'warning',
                    message: `${ t('componentNotMoved') }`,
                });
                return;
            }

            this.value[this.currentIndex] = temp2;
            this.value[nextIndex] = temp;

            this.changeCurrentIndex(nextIndex, temp);

        },
        // 复制组件
        copyComponent() {
            if (this.currentIndex < 0) return; // 从0开始

            let component = cloneDeep(this.value[this.currentIndex]); // 当前选中组件
            component.id = this.generateRandom(); // 更新id，刷新组件数据

            if (!this.checkComponentIsAdd(component)) {
                ElMessage({
                    type: 'warning',
                    message: `${ t('notCopy') }，${ component.componentTitle }${ t('componentCanOnlyAdd') }${ component.uses }${ t('piece') }`,
                });
                return;
            }

            if (component.position && this.position.indexOf(component.position) != -1) {
                ElMessage({
                    type: 'warning',
                    message: `${ t('notCopy') }，${ component.componentTitle }${ t('componentCanOnlyAdd') }1${ t('piece') }`,
                });
                return;
            }

            const index = this.currentIndex + 1;
            this.value.splice(index, 0, component);

            this.changeCurrentIndex(index, component);

        },
        // 检测组件是否允许添加，true：允许 false：不允许
        checkComponentIsAdd(component: any) {

            //为0时不处理
            if (component.uses === 0) return true;

            let count = 0;

            //遍历已添加的自定义组件，检测是否超出数量
            for (let i in this.value) if (this.value[i].componentName === component.componentName) count++;

            if (count >= component.uses) return false;
            else return true;
        },
        // 重置当前组件数据
        resetComponent() {
            if (this.currentIndex < 0) return; // 从0开始

            ElMessageBox.confirm(
                t('resetComponentTips'),
                t('warning'),
                {
                    confirmButtonText: t('confirm'),
                    cancelButtonText: t('cancel'),
                    type: 'warning',
                    autofocus: false
                }
            ).then(() => {
                // 重置当前选中的组件数据
                for (let i = 0; i < this.components.length; i++) {
                    if (this.components[i].componentName == this.editComponent.componentName) {
                        Object.assign(this.editComponent, this.components[i]);
                        break;
                    }
                }

            }).catch(() => {
            })

        },
        // 组件验证
        verify() {
            if (this.pageTitle === "") {
                ElMessage({
                    message: t('diyPageTitlePlaceholder'),
                    type: 'warning'
                })
                this.changeCurrentIndex(-99);
                return false;
            }

            // if (this.global.title === "") {
            //     ElMessage({
            //         message: t('diyTitlePlaceholder'),
            //         type: 'warning'
            //     })
            //     this.changeCurrentIndex(-99);
            //     return false;
            // }

            if (this.global.popWindow.show && !this.global.popWindow.imgUrl) {
                ElMessage({
                    message: '请上传弹窗图片',
                    type: 'warning'
                })
                return false;
            }

            for (let i = 0; i < this.value.length; i++) {
                try {
                    if (this.value[i].verify) {
                        const res = this.value[i].verify(i);
                        if (!res.code) {
                            this.changeCurrentIndex(i, this.value[i])
                            ElMessage({
                                message: res.message,
                                type: 'warning'
                            })
                            return false;
                        }
                    }
                } catch (e) {
                    console.log("verify Error:", e, i, this.value[i]);
                }
            }
            return true;
        }
    }
})

export default useDiyStore
