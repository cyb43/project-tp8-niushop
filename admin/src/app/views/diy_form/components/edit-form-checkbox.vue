<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]">
            <el-form-item :label="t('style')">
                <el-radio-group v-model="diyStore.editComponent.style">
                    <el-radio label="style-1">{{ t('defaultSources') }}</el-radio>
                    <el-radio label="style-2">{{ t('listStyle') }}</el-radio>
                    <el-radio label="style-3">{{ t('dropDownStyle') }}</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item :label="t('option')">
                <div ref="formCheckboxRef">
                    <div v-for="(option, index) in diyStore.editComponent.options" :key="option.id" class="option-item flex items-center mb-[15px]">
                        <el-input v-model="diyStore.editComponent.options[index].text" class="!w-[215px]" :placeholder="t('optionPlaceholder')" maxlength="30" clearable />
                        <span v-if="diyStore.editComponent.options.length > 1" @click="removeOption(index)" class="cursor-pointer ml-[5px] nc-iconfont nc-icon-shanchu-yuangaizhiV6xx"></span>
                    </div>
                </div>
                <span class="text-primary cursor-pointer mr-[10px]" @click="addOption">{{ t('addSingleOption') }}</span>
                <el-popover :visible="visible" placement="bottom" :width="300">
                    <p class="mb-[5px]">{{ t('addMultipleOption') }}</p>
                    <p class="text-[#888] text-[12px] mb-[5px]">{{ t('addOptionTips') }}</p>
                    <el-input v-model.trim="optionsValue" type="textarea" clearable maxlength="200" show-word-limit />
                    <div class="mt-[10px] text-right">
                        <el-button size="small" text @click="visible = false">{{ t('cancel') }}</el-button>
                        <el-button size="small" type="primary" @click="batchAddOptions">{{ t('confirm') }}</el-button>
                    </div>
                    <template #reference>
                        <span class="text-primary cursor-pointer" @click="visible = true">{{ t('addMultipleOption') }}</span>
                    </template>
                </el-popover>
            </el-form-item>
        </el-form>

        <!-- 表单组件 其他设置 -->
        <slot name="other"></slot>
        <!--		<el-form label-width="100px" class="px-[10px]">-->
        <!--			<el-form-item class="display-block">-->
        <!--				<template #label>-->
        <!--					<div class="flex items-center">-->
        <!--						<span class="mr-[3px]">{{ t('隐私保护') }}</span>-->
        <!--						<el-tooltip effect="light" placement="top">-->
        <!--							<template #content>-->
        <!--								<p>会自动将提交的个人信息做加密展示。</p>-->
        <!--								<p>适用于公开展示收集的数据且不暴露用户隐私。</p>-->
        <!--							</template>-->
        <!--							<el-icon>-->
        <!--								<QuestionFilled color="#999999" />-->
        <!--							</el-icon>-->
        <!--						</el-tooltip>-->
        <!--					</div>-->
        <!--				</template>-->
        <!--				<el-switch v-model="diyStore.editComponent.field.privacyProtection" />-->
        <!--				<div class="text-sm text-gray-400">{{ t('提交后自动隐藏内容，仅管理员可查看') }}</div>-->
        <!--			</el-form-item>-->

        <!--		</el-form>-->

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
import { ref, onMounted, nextTick } from 'vue'
import useDiyStore from '@/stores/modules/diy'
import Sortable from 'sortablejs'
import { range } from 'lodash-es'
import { ElMessage } from 'element-plus'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    let pass = true
    for (let i = 0; i < diyStore.value[index].options.length; i++) {
        if (!diyStore.value[index].options[i].text) {
            res.code = false
            res.message = t('optionPlaceholder')
            pass = false
            break
        }
    }

    if (!pass) return res

    const uniqueOptions = uniqueByKey(diyStore.value[index].options, 'text')
    if (uniqueOptions.length != diyStore.value[index].options.length) {
        res.code = false
        res.message = t('errorTipsOne')
    }
    return res
}

diyStore.editComponent.options.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

const visible = ref(false)
const optionsValue = ref()
const addOption = () => {
    diyStore.editComponent.options.push({
        id: diyStore.generateRandom(),
        text: '选项' + (diyStore.editComponent.options.length + 1)
    })
}

const removeOption = (index: any) => {
    diyStore.editComponent.options.splice(index, 1)
}

// 批量添加选项
const batchAddOptions = () => {
    if (optionsValue.value.trim()) {
        const newOptions = optionsValue.value.split(',').map((option: any) => {
            return {
                id: diyStore.generateRandom(),
                text: option.trim()
            }
        }).filter((option: any) => option.text !== '')

        // 去除重复的选项
        const uniqueNewOptions = uniqueByKey(newOptions, 'text')

        // 过滤掉已存在的选项
        const filteredNewOptions = uniqueNewOptions.filter((newOption: any) =>
            !diyStore.editComponent.options.some((existingOption: any) => existingOption.text === newOption.text)
        )

        // 如果有新的选项，添加到选项列表中
        if (filteredNewOptions.length > 0) {
            diyStore.editComponent.options.push(...filteredNewOptions)
        } else {
            ElMessage({
                message: t('errorTipsTwo'),
                type: 'error'
            })
        }

        optionsValue.value = ''
        visible.value = false
    }
}

// 数组去重
const uniqueByKey = (arr: any, key: any) => {
    const seen = new Set()
    return arr.filter((item: any) => {
        const serializedKey = JSON.stringify(item[key])
        return seen.has(serializedKey) ? false : seen.add(serializedKey)
    })
}

const formCheckboxRef = ref()

onMounted(() => {
    nextTick(() => {
        const sortable = Sortable.create(formCheckboxRef.value, {
            group: 'option-item',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.options[event.oldIndex!]
                diyStore.editComponent.options.splice(event.oldIndex!, 1)
                diyStore.editComponent.options.splice(event.newIndex!, 0, temp)
                sortable.sort(
                    range(diyStore.editComponent.options.length).map(value => {
                        return value.toString()
                    })
                )
            }
        })
    })
})

defineExpose({})

</script>

<style lang="scss" scoped></style>
