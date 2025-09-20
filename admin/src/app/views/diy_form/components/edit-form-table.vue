<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]" @submit.prevent>
            <el-form-item :label="t('列设置')">
                <div ref="imageBoxRef">
                    <div v-for="(item, index) in diyStore.editComponent.columnList" :key="item.id"
                        class="border-b-[1px] border-[#e0e0e0] py-1">
                        <div class="flex items-center justify-between">
                            <div class="flex">
                                <span :class="['iconfont', 'ml-[5px]', 'cursor-pointer', getIconClass(item.type)]"></span>
                                <el-input v-model="item.name" class="input-style" :input-style="{ boxShadow: 'none' }"
                                    :placeholder="t('请输入列名')" />
                            </div>
                            <div class="flex">
                                <span v-if="diyStore.editComponent.columnList.length > 1" @click="removeOption(index)"
                                    class="cursor-pointer ml-[5px] nc-iconfont nc-icon-shanchu-yuangaizhiV6xx"></span>
                                <span class="cursor-pointer ml-[5px] nc-iconfont nc-icon-xiaV6xx"></span>
                            </div>
                        </div>
                        <div v-if="item.type == 'radio'" class="flex">
                            <div class="text-[#999] mr-3" >{{ item.options?.length || 0 }}个选项</div>
                            <span class="text-primary cursor-pointer mr-[10px]" @click="openRadioDialog(item, index)">{{ t('编辑') }}</span>
                        </div>
                        <div v-if="item.type == 'date'" class="flex">
                            <span class="text-primary cursor-pointer mr-[10px]" @click="openRadioDialog(item, index)">{{ t('设置日期格式') }}</span>
                        </div>
                        <div v-if="item.type == 'address'" class="flex">
                            <div class="text-[#999] mr-3">精确到详细地址</div>
                            <span class="text-primary cursor-pointer mr-[10px]" @click="openRadioDialog(item, index)">{{ t('设置') }}</span>
                        </div>
                    </div>
                </div>
                <el-popover placement="bottom" :width="50" trigger="hover">
                    <template #reference>
                        <span class="text-primary cursor-pointer mr-[10px]">{{ t('添加') }}</span>
                    </template>
                    <div v-for="(item, index) in columnTypeOptions" :key="index" @click="addOption(item)"
                        class="cursor-pointer hover:bg-[#d1e1ff] rounded text-center">
                        <div class="py-1 text-[var(--el-text-color-primary]">{{ item.label }}</div>
                    </div>
                </el-popover>

            </el-form-item>
            <el-form-item :label="t('是否自增')">
                <el-switch v-model="diyStore.editComponent.autoIncrementControl" />
            </el-form-item>
            <el-form-item :label="t('填写限制')" v-if="diyStore.editComponent.autoIncrementControl">
                <div class="flex items-center">
                    <span>默认显示</span>
                    <el-input v-model="diyStore.editComponent.writeLimit.default" class="input-short"  :placeholder="t('')" />
                    <span>项</span>
                </div>
                <div class="flex items-center my-1">
                    <span>最少填写</span>
                    <el-input v-model="diyStore.editComponent.writeLimit.min" class="input-short"  :placeholder="t('')" />
                    <span>项</span>
                </div>
                <div class="flex items-center">
                    <span>最多填写</span>
                    <el-input v-model="diyStore.editComponent.writeLimit.max" class="input-short"  :placeholder="t('')" />
                    <span>项</span>
                </div>
            </el-form-item>
            <el-form-item :label="t('按钮名称')" v-if="diyStore.editComponent.autoIncrementControl">
                <el-input v-model="diyStore.editComponent.btnText" :placeholder="t('请输入按钮名称')" />
            </el-form-item>
        </el-form>
        <!-- 单选项 -->
        <!-- <el-dialog v-model="radioDialogVisible" :title="t('设置单选项')" width="500">
            <div v-if="activeColumnTemp.type == 'radio'">
                <el-form label-width="80px" class="px-[10px]">
                    <el-form-item :label="t('选项名称')">
                        <el-input v-model="activeColumnTemp.name" :input-style="{ boxShadow: 'none' }" />
                    </el-form-item>
                    <el-form-item :label="t('设置选项')">
                        <div ref="radioBoxRef">
                            <div v-for="(opt, idx) in activeColumnTemp.options" :key="opt.id">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex-1">
                                        <el-input v-model="opt.label" :input-style="{ boxShadow: 'none' }"
                                            :placeholder="t('请输入')" />
                                    </div>
                                    <span v-if="activeColumnTemp.options.length > 1" @click="removeOptionItem(idx)"
                                        class="cursor-pointer ml-[5px] nc-iconfont nc-icon-shanchu-yuangaizhiV6xx"></span>
                                    <span class="cursor-pointer ml-[5px] nc-iconfont nc-icon-iconpaixu1"></span>
                                </div>
                            </div>
                            <span class="text-primary cursor-pointer mr-[10px]" @click="addOptionItem">{{ t('添加选项') }}</span>
                            <span class="text-primary cursor-pointer mr-[10px]" @click="addOtherOption">{{ t('添加其它项') }}</span>
                            <el-popover :visible="visible" placement="bottom" :width="300">
                                <p class="mb-[5px]">{{ t('addMultipleOption') }}</p>
                                <p class="text-[#888] text-[12px] mb-[5px]">{{ t('addOptionTips') }}</p>
                                <el-input v-model.trim="optionsValue" type="textarea" clearable maxlength="200"
                                    show-word-limit />
                                <div class="mt-[10px] text-right">
                                    <el-button size="small" text @click="visible = false">{{ t('cancel') }}</el-button>
                                    <el-button size="small" type="primary" @click="batchAddOptions">{{t('confirm')}}</el-button>
                                </div>
                                <template #reference>
                                    <span class="text-primary cursor-pointer"
                                        @click="visible = true">{{ t('addMultipleOption') }}</span>
                                </template>
                            </el-popover>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <div v-else-if="activeColumnTemp.type == 'date'">
                <el-form>
                    <el-form-item :label="t('dataFormat')">
                        <el-radio-group v-model="activeColumnTemp.dateFormat" class="!block">
                            <el-radio class="!block" label="YYYY年M月D日">{{ dateFormat.format1 }}</el-radio>
                            <el-radio class="!block" label="YYYY-MM-DD">{{ dateFormat.format2 }}</el-radio>
                            <el-radio class="!block" label="YYYY/MM/DD">{{ dateFormat.format3 }}</el-radio>
                            <el-radio class="!block" label="YYYY-MM-DD HH:mm">{{ dateFormat.format4 }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-form>
            </div>
            <div v-else-if="activeColumnTemp.type == 'address'">
                <el-form>
                    <el-form-item :label="t('地址格式')">
                        <el-radio-group v-model="activeColumnTemp.addressFormat" class="!block">
                            <el-radio class="!block" label="province/city/district/address">{{ t('省/市/区/街道/详细地址') }}</el-radio>
                            <el-radio class="!block" label="province/city/district/street">{{ t('省/市/区/街道(镇)') }}</el-radio>
                            <el-radio class="!block" label="province/city/district">{{ t('省/市/区(县)') }}</el-radio>
                            <el-radio class="!block" label="province/city">{{ t('省/市') }}</el-radio>
                            <el-radio class="!block" label="province">{{ t('省') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                </el-form>
            </div>
            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="radioDialogVisible = false">取消</el-button>
                    <el-button type="primary" @click="handleDialogConfirm">确定</el-button>
                </div>
            </template>
        </el-dialog> -->

        <div>

        </div>

        <!-- 表单组件 其他设置 -->
        <slot name="other"></slot>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <!-- 表单组件 字段样式 -->
        <slot name="style-field"></slot>

        <!-- 组件样式 -->
        <slot name="style"></slot>

    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import Sortable from 'sortablejs'
import { ref, watch, onMounted, nextTick, reactive, computed } from 'vue'
import useDiyStore from '@/stores/modules/diy'
import { range } from 'lodash-es'
const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性
// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    // todo 只需要考虑该组件自身的验证
    return res
}

// 类型选项数组
const columnTypeOptions = ref([
    { label: '单选项', value: 'radio' },
    { label: '文本', value: 'text' },
    { label: '数字', value: 'number' },
    { label: '手机号', value: 'mobile' },
    { label: '地址', value: 'address' },
    { label: '身份证', value: 'idcard' },
    { label: '性别', value: 'gender' },
    { label: '日期', value: 'date' }
])

const getIconClass = (type:any) => {
    switch (type) {
        case 'radio':
            return 'icona-duihaopc30'
        case 'text':
            return 'icona-danhangwenben-1pc30'
        case 'number':
            return 'icona-shuzipc30-1'
        case 'mobile':
            return 'icona-shoujipc30'
        case 'address':
            return 'iconbiaotipc'
        case 'idcard':
            return 'icona-shenfenzhengpc30'
        case 'gender':
            return 'el-icon-s-opportunity'
        case 'date':
            return 'icona-riqipc30'
        default:
            return ''
    }
}

const imageBoxRef = ref()
const generateId = () => Date.now().toString(36) + Math.random().toString(36).substr(2, 5)
// 添加列方法
const addOption = (item) => {
    const newColumn: any = {
        id: generateId(),
        name: item.label,
        type: item.value, // 列类型
        value: '' // 默认值（可选）
    }

    // 如果是单选项，初始化 options
    if (item.value === 'radio') {
        newColumn.options = [
            { id: generateId(), label: '选项1' },
            { id: generateId(), label: '选项2' }
        ]
    }
    // 如果是日期，初始化 dateFormat
    if (item.value === 'date') {
        newColumn.dateFormat = 'YYYY年M月D日' // 默认日期格式
    }
    // 如果是地址，初始化 addressFormat
    if (item.value === 'address') {
        newColumn.addressFormat = 'province/city/district/address' // 默认日期格式
    }

    diyStore.editComponent.columnList.push(newColumn)
}

const removeOption = (index: number) => {
    diyStore.editComponent.columnList.splice(index, 1)
}

onMounted(() => {
    // nextTick(() => {
    //     if (diyStore.editComponent.columnList.length < 2) return;
    //     const sortable = Sortable.create(imageBoxRef.value, {
    //         group: 'item-wrap',
    //         animation: 200,
    //         onEnd: event => {
    //             const temp = diyStore.editComponent.columnList[event.oldIndex!]
    //             diyStore.editComponent.columnList.splice(event.oldIndex!, 1)
    //             diyStore.editComponent.columnList.splice(event.newIndex!, 0, temp)
    //             sortable.sort(
    //                 range(diyStore.editComponent.columnList.length).map(value => {
    //                     return value.toString()
    //                 })
    //             )
    //         }
    //     })
    // })
    console.log(diyStore.editComponent.columnList)
})

const activeColumn = ref<any>({}) // 真正数据（原始数据，不动它）
const activeColumnTemp = ref<any>({}) // 弹窗编辑临时副本
const activeRadioIndex = ref(0) // 当前编辑列的下标

const radioDialogVisible = ref(false)
const radioBoxRef = ref()

const optionsValue = ref('')
const visible = ref(false)
const dateFormat: any = reactive({
    format1: '',
    format2: '',
    format3: '',
    format4: ''
})

const openRadioDialog = (item, index) => {
    activeRadioIndex.value = index // 记录当前列的下标，方便确定时更新
    activeColumn.value = item
    activeColumnTemp.value = JSON.parse(JSON.stringify(item)) // 深拷贝，避免联动
    if (item.type == 'radio') {
        if (!activeColumnTemp.value.options) activeColumnTemp.value.options = []
        radioDialogVisible.value = true
        // nextTick(() => initRadioSortable()) // 拖拽初始化
    } else if (item.type == 'date') {
        // 初始赋值当天日期
        const today = new Date()
        const year = today.getFullYear()
        const month = String(today.getMonth() + 1).padStart(2, '0')
        const day = String(today.getDate()).padStart(2, '0')

        const hours = String(today.getHours()).padStart(2, '0')
        const minutes = String(today.getMinutes()).padStart(2, '0')
        dateFormat.format1 = `${year}年${month}月${day}日`
        dateFormat.format2 = `${year}-${month}-${day}`
        dateFormat.format3 = `${year}/${month}/${day}`
        dateFormat.format4 = `${year}-${month}-${day} ${hours}:${minutes}`
        radioDialogVisible.value = true
    } else if (item.type == 'address') {
        radioDialogVisible.value = true
    }
}

// 初始化拖拽
// const initRadioSortable = () => {
//     Sortable.create(radioBoxRef.value, {
//         group: 'radio-option-wrap',
//         animation: 200,
//         draggable: '.drag-radio-item',
//         onEnd: event => {
//             const options = activeColumnTemp.value.options // 注意！这里用 temp 的
//             const temp = options[event.oldIndex!]
//             options.splice(event.oldIndex!, 1)
//             options.splice(event.newIndex!, 0, temp)
//         }
//     })
// }

const handleDialogConfirm = () => {
    console.log(activeColumnTemp.value)

    diyStore.editComponent.columnList[activeRadioIndex.value] = JSON.parse(JSON.stringify(activeColumnTemp.value)) // 同步副本到原数据
    radioDialogVisible.value = false // 关闭弹窗
}

const addOptionItem = () => {
    const newOption = { id: generateId(), label: '选项' + (activeColumnTemp.value.options.length + 1) }
    activeColumnTemp.value.options.push(newOption)
}

const addOtherOption = () => {
    const newOption = { id: generateId(), label: '其他' }
    activeColumnTemp.value.options.push(newOption)
}

const removeOptionItem = (index: number) => {
    activeColumnTemp.value.options.splice(index, 1)
}

// 数组去重
const uniqueByKey = (arr: any, key: any) => {
    const seen = new Set()
    return arr.filter((item: any) => {
        const serializedKey = JSON.stringify(item[key])
        return seen.has(serializedKey) ? false : seen.add(serializedKey)
    })
}
// 批量添加
const batchAddOptions = () => {
    if (optionsValue.value.trim()) {
        const newOptions = optionsValue.value.split(',').map((option: any) => {
            return {
                id: diyStore.generateRandom(),
                label: option.trim()
            }
        }).filter((option: any) => option.label !== '')

        // 去除重复的选项
        const uniqueNewOptions = uniqueByKey(newOptions, 'label')

        // 过滤掉已存在的选项
        const filteredNewOptions = uniqueNewOptions.filter(newOption =>
            !activeColumnTemp.value.options.some(existingOption => existingOption.label === newOption.label)
        )

        // 如果有新的选项，添加到选项列表中
        if (filteredNewOptions.length > 0) {
            activeColumnTemp.value.options.push(...filteredNewOptions)
        } else {
            ElMessage({
                message: t('errorTipsTwo'),
                type: 'warning'
            })
        }

        optionsValue.value = ''
        visible.value = false
    }
}

defineExpose({})

</script>

<style lang="scss" scoped>
:deep(.input-style .el-input__wrapper) {
    box-shadow: none !important;
}

.input-short{
    width: 80px;
    margin: 0 10px;
}

</style>
