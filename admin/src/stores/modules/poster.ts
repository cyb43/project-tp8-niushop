import {defineStore} from 'pinia'
import {t} from '@/lang'
import {ElMessage, ElMessageBox} from 'element-plus'
import {cloneDeep} from 'lodash-es'
import {img} from '@/utils/common'

const usePosterStore = defineStore('poster', {
    state: () => {
        return {

            contentBoxWidth: 720, // 360*2=720
            contentBoxHeight: 1280, //  640*2=1280

            id: 0,
            name: '', // 页面名称
            type: '', // 海报类型
            typeName: '',
            channel: '', // 海报支持的渠道
            status: 1, // 是否启用
            isDefault: 0, // 是否默认
            addon: '', // 海报所属插件

            currentIndex: -99, // 当前正在编辑的组件下标
            currentComponent: 'edit-page', // 当前正在编辑的组件名称
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
            global: {
                width: 720, // 海报宽度
                height: 1280, //  海报高度
                bgType: 'url',
                bgColor: "#ffffff", // 背景颜色
                bgUrl: '', // 背景图片
            },
            // 组件类型，文本：text，image：图片，qrcode：二维码
            template: {
                width: 200, // 宽度
                height: 200, // 高度
                minWidth: 60, // 最小宽度
                minHeight: 60, // 最小高度
                x: 0, // 横向坐标 → 
                y: 0, // 纵向坐标 ↑
                angle: 0, // 旋转角度 0~360
                zIndex: 0 // 层级
            },
            // 各组件类型的默认值
            templateType: {
                text: {
                    height: 60,
                    minWidth: 120,
                    minHeight: 44,
                    fontFamily: 'static/font/PingFang-Medium.ttf',
                    fontSize: 40,
                    weight: false,
                    lineHeight: 10,
                    fontColor: '#303133'
                },
                image: {
                    shape: 'normal' // 圆形 circle 方形  normal
                },
                qrcode: {},
                // 绘画
                draw: {
                    draw_type: 'Polygon',
                    points: [[0, 1210], [720, 1210], [720, 1280], [0, 1280]],
                    bgColor: '#eeeeee'
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
                width: 720, // 海报宽度
                height: 1280, //  海报高度
                bgType: 'url',
                bgColor: "#ffffff", // 页面背景颜色（开始）
                bgUrl: '' // 页面背景图片
            };
            this.value = [];
        },
        // 添加组件
        addComponent(key: string, data: any) {

            // 删除不用的字段
            let component = cloneDeep(data);

            component.id = this.generateRandom();
            component.componentName = key;
            component.componentTitle = component.title;

            delete component.title;
            delete component.icon;

            // 继承默认属性
            let template: any = cloneDeep(this.template);
            Object.assign(component, template);

            let templateType: any = cloneDeep(this.templateType);
            Object.assign(component, templateType[component.type]);

            if (component.template) {
                // 按照组件初始的属性覆盖默认值
                Object.assign(component, component.template);
                delete component.template;
            }

            if (!this.checkComponentIsAdd(component)) {
                // 组件最多只能添加n个
                ElMessage({
                    type: 'warning',
                    message: `${component.componentTitle}${t('componentCanOnlyAdd')}${component.uses}${t('piece')}`,
                });
                return;
            }

            component.zIndex = this.value.length + 1;
            this.value.push(component);
            // 添加组件后（不是编辑调用的），选择最后一个
            this.currentIndex = this.value.length - 1;

            this.currentComponent = 'edit-' + component.path;
        },
        // 生成随机数
        generateRandom(len: number = 5) {
            return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36);
        },
        // 选中正在编辑的组件
        changeCurrentIndex(index: number, component: any = null) {
            this.currentIndex = index;
            if (this.currentIndex == -99) {
                this.currentComponent = 'edit-page';
            } else if (component) {
                this.currentComponent = 'edit-' + component.path;
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
        // 上移一层组件
        moveUpComponent() {
            if (this.currentIndex < -1) return; // 从0开始

            this.value[this.currentIndex].zIndex++;
            if (this.value[this.currentIndex].zIndex >= this.value.length) {
                this.value[this.currentIndex].zIndex = this.value.length;
            }

        },
        // 下移一层组件
        moveDownComponent() {
            if (this.currentIndex < -1) return; // 从0开始

            this.value[this.currentIndex].zIndex--;

            if (this.value[this.currentIndex].zIndex < 0) {
                this.value[this.currentIndex].zIndex = 0;
            }
        },
        // 复制组件
        copyComponent() {
            if (this.currentIndex < 0) return; // 从0开始

            let component = cloneDeep(this.value[this.currentIndex]); // 当前选中组件

            component.id = this.generateRandom(); // 更新id，刷新组件数据
            component.x = 0; // 重置坐标
            component.y = 0; // 重置坐标

            // 暂不复制宽高
            // let box: any = document.getElementById(this.value[this.currentIndex].id)
            // component.width = box.offsetWidth
            // component.height = box.offsetHeight
            // component.auto = false;

            if (!this.checkComponentIsAdd(component)) {
                ElMessage({
                    type: 'warning',
                    message: `${t('notCopy')}，${component.componentTitle}${t('componentCanOnlyAdd')}${component.uses}${t('piece')}`,
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

                        let templateType: any = cloneDeep(this.templateType);
                        Object.assign(this.editComponent, templateType[this.editComponent.type]);
                        this.editComponent.angle = 0;
                        break;
                    }
                }

            }).catch(() => {
            })

        },
        // 组件验证
        verify() {
            if (this.name === "") {
                ElMessage({
                    message: t('posterNamePlaceholder'),
                    type: 'warning'
                });
                this.changeCurrentIndex(-99);
                return false;
            }

            if (this.value.length == 0) {
                ElMessage({
                    message: t('diyPosterValueEmptyTips'),
                    type: 'warning'
                });
                this.changeCurrentIndex(-99);
                return false;
            }

            for (let i = 0; i < this.value.length; i++) {
                try {
                    if (this.value[i].verify) {
                        const res = this.value[i].verify(i);
                        if (!res.code) {
                            this.changeCurrentIndex(i, this.value[i]);
                            ElMessage({
                                message: res.message,
                                type: 'warning'
                            });
                            return false;
                        }
                    }
                } catch (e) {
                    console.log("verify Error:", e, i, this.value[i]);
                }
            }
            return true;
        },
        // 移动事件
        mouseDown(e: any, id: any, index: any) {
            const box: any = document.getElementById(id);
            const disX = (e.clientX * 2 ) - box.offsetLeft;
            const disY = (e.clientY * 2 ) - box.offsetTop;

            // 鼠标移动时
            document.onmousemove = (e) => {
                let clientX = e.clientX * 2;
                let clientY = e.clientY * 2;
                
                if (this.contentBoxWidth == box.offsetWidth) {
                    box.style.left = 0
                } else {
                    box.style.left = (clientX - disX)  + 'px'
                }
                box.style.top = (clientY - disY)  + 'px';

                // 边界判断
                if (clientX - disX < 0) {
                    box.style.left = 0
                }

                if (clientX - disX > this.contentBoxWidth - box.offsetWidth) {
                    box.style.left = this.contentBoxWidth - box.offsetWidth + 'px'
                }

                if (clientY - disY < 0) {
                    box.style.top = 0
                }

                if (clientY - disY > this.contentBoxHeight - box.offsetHeight) {
                    box.style.top = this.contentBoxHeight - box.offsetHeight + 'px'
                }
                
                this.value[index].x = box.offsetLeft;
                this.value[index].y = box.offsetTop;

            };

            // 鼠标抬起时
            document.onmouseup = (e) => {
                document.onmousemove = null
            }
        },
        // 拖拽缩放事件
        resizeMouseDown(e: any, item: any, index: any) {
            const oEv = e;
            oEv.stopPropagation();
            const box: any = document.getElementById(item.id);
            const className = e.target.className;

            // 获取移动前盒子的宽高，
            const oldWidth = box.offsetWidth;
            const oldHeight = box.offsetHeight;

            // 获取鼠标距离屏幕的left和top值
            const oldX = oEv.clientX;
            const oldY = oEv.clientY;

            // 元素相对于最近的父级定位
            const oldLeft = box.offsetLeft;
            const oldTop = box.offsetTop;

            // 设置最小的宽度
            let minWidth = 100;
            let minHeight = 100;

            if (item.type == 'text') {
                // 文本类型
                minWidth = 60;
                minHeight = 22;
            } else if (item.type == 'image' || item.type == 'qrcode') {
                // 图片类型
                minWidth = 30;
                minHeight = 30;
            } else if (item.type == 'draw') {
                // 绘画类型
                minWidth = 20;
                minHeight = 20;
            }

            document.onmousemove = (e) => {
                const oEv = e;
                // console.log('move', "width：" + oldWidth,
                //     '，oldLeft: ' + oldLeft, '，oldTop: ' + oldTop,
                //     '，oldX：clientX-- ' + oldX + '：' + oEv.clientX,
                //     '，oldY：clientY-- ' + oldY + '：' + oEv.clientY,
                // )

                // 左上角
                if (className == 'box1') {
                    let width = oldWidth - (oEv.clientX - oldX);
                    const maxWidth = this.contentBoxWidth;

                    let height = oldHeight - (oEv.clientY - oldY);
                    const maxHeight = this.contentBoxHeight - oldTop;

                    let left = oldLeft + (oEv.clientX - oldX);
                    let top = oldTop + (oEv.clientY - oldY);

                    if (width < minWidth) {
                        width = minWidth
                    }
                    if (width > maxWidth) {
                        width = maxWidth
                    }

                    if (height < minHeight) {
                        height = minHeight
                    }
                    if (height > maxHeight) {
                        height = maxHeight
                    }

                    if (oldLeft == 0 && oldTop == 0) {
                        // 坐标：left = 0，top = 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 最小宽度，top = 最小高度
                            left = minWidth;
                            top = minHeight;
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 最小宽度，top = 不予处理
                            left = minWidth;
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，left = 不予处理，top = 最小高度
                            top = minHeight;
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，left = 不予处理，top = 不予处理
                        }
                    } else if (oldLeft == 0 && oldTop > 0) {
                        // 坐标：left = 0，top > 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 最小宽度，top = 元素上偏移位置
                            left = minWidth;
                            top = box.offsetTop;
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 最小宽度，top = 元素上偏移位置
                            left = minWidth;
                            top = box.offsetTop;
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，left = 不予处理，top = 元素上偏移位置
                            top = box.offsetTop;
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，left = 不予处理，top = 不予处理
                        }
                    } else if (oldLeft > 0 && oldTop == 0) {
                        // 坐标：left > 0，top = 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 元素左偏移位置，top = 元素上偏移位置
                            left = box.offsetLeft;
                            top = box.offsetTop;
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 元素左偏移位置，top = 0
                            left = box.offsetLeft;
                            top = 0;
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，left = 不予处理，top = 元素上偏移位置
                            top = box.offsetTop;
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，left = 不予处理，top = 不予处理
                        }
                    } else if (oldLeft > 0 && oldTop > 0) {
                        // 坐标：left > 0，top > 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 元素左偏移位置，top = 元素上偏移位置
                            left = box.offsetLeft;
                            top = box.offsetTop;
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 元素左偏移位置，top = 元素上偏移位置
                            left = box.offsetLeft;
                            top = box.offsetTop;
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，left = 不予处理，top = 元素上偏移位置
                            top = box.offsetTop;
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，left = 不予处理，top = 不予处理
                        }
                    }

                    // 左上宽
                    if (left < 0) {
                        left = 0;
                        width = oldWidth - (oEv.clientX - oldX) + (oldLeft + (oEv.clientX - oldX));
                    }

                    // 左上 高
                    if (top < 0) {
                        top = 0;
                        height = oldTop + (oEv.clientY - oldY) + (oldHeight - (oEv.clientY - oldY));
                    }

                    box.children[0].style.width = width + 'px';

                    // 文本设置高度，图片自适应 无需设置
                    if (item.type == 'text' || item.type == 'draw') {
                        box.children[0].style.height = height + 'px';
                    }
                    box.style.left = left + 'px';
                    box.style.top = top + 'px';
                } else if (className == 'box2') {
                    // 右上角

                    let width = oldWidth + (oEv.clientX - oldX);
                    const maxWidth = this.contentBoxWidth - oldLeft;

                    let height = oldHeight - (oEv.clientY - oldY);
                    const maxHeight = this.contentBoxHeight - oldTop;

                    let top = oldTop + (oEv.clientY - oldY);

                    if (width < minWidth) {
                        width = minWidth
                    }
                    if (width > maxWidth) {
                        width = maxWidth
                    }

                    if (height < minHeight) {
                        height = minHeight
                    }
                    if (height > maxHeight) {
                        height = maxHeight
                    }

                    if (oldLeft == 0 && oldTop == 0) {
                        // 坐标：left = 0，top = 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，top = 最小高度
                            top = minHeight
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，不予处理
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，top = 最小高度
                            top = minHeight
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    } else if (oldLeft == 0 && oldTop > 0) {
                        // 坐标：left = 0，top > 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    } else if (oldLeft > 0 && oldTop == 0) {
                        // 坐标：left = 0，top = 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，top = 0
                            top = 0
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    } else if (oldLeft > 0 && oldTop > 0) {
                        // 坐标：left > 0，top > 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，top = 元素上偏移位置
                            top = box.offsetTop
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    }

                    // 右上高
                    if (top < 0) {
                        top = 0;
                        height = oldTop + (oEv.clientY - oldY) + (oldHeight - (oEv.clientY - oldY))
                    }

                    box.children[0].style.width = width + 'px';

                    // 文本设置高度，图片自适应 无需设置
                    if (item.type == 'text' || item.type == 'draw') {
                        box.children[0].style.height = height + 'px'
                    }
                    box.style.top = top + 'px'
                } else if (className == 'box3') {
                    // 左下角

                    let width = oldWidth - (oEv.clientX - oldX);
                    const maxWidth = this.contentBoxWidth;

                    let height = oldHeight + (oEv.clientY - oldY);
                    const maxHeight = this.contentBoxHeight - oldTop;

                    let left = oldLeft + (oEv.clientX - oldX);

                    if (width < minWidth) {
                        width = minWidth
                    }
                    if (width > maxWidth) {
                        width = maxWidth
                    }

                    if (height < minHeight) {
                        height = minHeight
                    }
                    if (height > maxHeight) {
                        height = maxHeight
                    }

                    if (oldLeft == 0 && oldTop == 0) {
                        // 坐标：left = 0，top = 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 最小宽度
                            left = minWidth
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 最小宽度
                            left = minWidth
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，不予处理
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    } else if (oldLeft == 0 && oldTop > 0) {
                        // 坐标：left = 0，top > 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 最小宽度
                            left = minWidth
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 最小宽度
                            left = minWidth
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，不予处理
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    } else if (oldLeft > 0 && oldTop == 0) {
                        // 坐标：left > 0，top = 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 元素左偏移位置
                            left = box.offsetLeft
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 元素左偏移位置
                            left = box.offsetLeft
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，不予处理
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    } else if (oldLeft > 0 && oldTop > 0) {
                        // 坐标：left > 0，top > 0

                        if (width == minWidth && height == minHeight) {
                            // 宽高 = 最小值，left = 元素左偏移位置
                            left = box.offsetLeft
                        } else if (width == minWidth && height > minHeight) {
                            // 宽 = 最小值，高 > 最小值，left = 元素左偏移位置
                            left = box.offsetLeft
                        } else if (width > minWidth && height == minHeight) {
                            // 宽 > 最小值，高 = 最小值，不予处理
                        } else if (width > minWidth && height > minHeight) {
                            // 宽 > 最小值，高 > 最小值，不予处理
                        }
                    }

                    if (left < 0) {
                        left = 0;
                        width = oldWidth - (oEv.clientX - oldX) + (oldLeft + (oEv.clientX - oldX))
                    }

                    box.children[0].style.width = width + 'px';

                    // 文本设置高度，图片自适应 无需设置
                    if (item.type == 'text' || item.type == 'draw') {
                        box.children[0].style.height = height + 'px'
                    }
                    box.style.left = left + 'px'
                } else if (className == 'box4') {
                    // 右下角

                    let width = oldWidth + (oEv.clientX - oldX);
                    const maxWidth = this.contentBoxWidth - oldLeft;

                    let height = oldHeight + (oEv.clientY - oldY);
                    const maxHeight = this.contentBoxHeight - oldTop;

                    if (width < minWidth) {
                        width = minWidth
                    }
                    if (width > maxWidth) {
                        width = maxWidth
                    }

                    if (height < minHeight) {
                        height = minHeight
                    }
                    if (height > maxHeight) {
                        height = maxHeight
                    }

                    box.children[0].style.width = width + 'px';

                    // 文本设置高度，图片自适应 无需设置
                    if (item.type == 'text' || item.type == 'draw') {
                        box.children[0].style.height = height + 'px'
                    }
                }

                this.value[index].x = box.offsetLeft;
                this.value[index].y = box.offsetTop;

                this.value[index].width = parseInt(box.children[0].style.width.replace('px', ''));

            };

            // 鼠标抬起时
            document.onmouseup = () => {
                document.onmousemove = null;
                document.onmouseup = null
            }
        },
        getGlobalStyle() {
            let style = '';
            if (this.global.bgType == 'color') {
                style += `background-color:${this.global.bgColor};`;
            } else if (this.global.bgType == 'url') {
                if (this.global.bgUrl) {
                    style += `background-image:url("${img(this.global.bgUrl)}")`;
                }
            }
            return style;
        },
        getMaxX() {
            const box: any = document.getElementById(this.editComponent.id);
            let x = this.contentBoxWidth;
            if (box) {
                x -= box.offsetWidth;
            }
            return x;
        },
        getMaxY() {
            const box: any = document.getElementById(this.editComponent.id);
            let y = this.contentBoxHeight;
            if (box) {
                y -= box.offsetHeight;
            }
            return y;
        },
        getMaxWidth() {
            let width = this.contentBoxWidth;
            width -= this.editComponent.x;
            return width;
        },
    }
});

export default usePosterStore