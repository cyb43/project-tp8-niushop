<template>
    <div class="main-container" v-loading="loading">
        <el-card class="box-card !border-none" shadow="never">
            <div class="flex justify-between items-center mb-[20px]">
                <span class="text-page-title">{{ pageName }}</span>
            </div>
            <div ref="tableRef" :key="toggleIndex" v-if="!loading">
                <template v-for="(item, index) in tableData" :key="item.key">
                    <div class="mb-[20px] bg-[#fff]">
                        <el-card shadow="never">
                            <template #header>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="iconfont icontuodong vues-rank mr-[5px]"></i>
                                        <el-input v-focus v-if="index === activeIndex" v-model.trim="inputValue" class="w-[120px]" maxlength="10" @blur="inputBlur" />
                                        <span v-else class="font-600 text-[14px]">{{ item.name }}</span>
                                        <el-icon class="text-color ml-[10px] cursor-pointer" @click="edit(index)">
                                            <EditPen />
                                        </el-icon>
                                    </div>
                                    <el-switch v-model="item.status" :active-value="1" :inactive-value="2" @change="update" />
                                </div>
                            </template>
                            <div class="flex items-center justify-between">
                                <span class="text-[#666666] text-[14px]">{{ t(item.key) }}</span>
                                <div>
                                    <template v-if="item.key === 'local_delivery'">
                                        <el-button type="primary" link @click="goRouter('/shop/order/delivery/staff')">{{ t('deliveryStaff') }}</el-button>
                                        <el-button type="primary" link @click="goRouter('/shop/order/delivery/local')">{{ t('localConfig') }}</el-button>
                                    </template>
                                    <template v-if="item.key === 'express'">
                                        <el-button type="primary" link @click="goRouter('/shop/order/delivery/company')">{{ t('deliveryCompany') }}</el-button>
                                        <el-button type="primary" link @click="goRouter('/shop/order/shipping/template')">{{ t('deliveryTemplate') }}</el-button>
                                        <el-button type="primary" link @click="goRouter('/shop/order/delivery/search')">{{ t('deliverySearch') }}</el-button>
                                    </template>
                                    <template v-if="item.key === 'store'">
                                        <el-button type="primary" link @click="goRouter('/shop/order/delivery/store')">{{ t('deliveryStore') }}</el-button>
                                    </template>
                                </div>
                            </div>
                        </el-card>
                    </div>
                </template>
            </div>
        </el-card>
    </div>
</template>
<script lang="ts" setup>
import { onMounted, nextTick, ref, toRaw } from 'vue'
import { t } from '@/lang'
import Sortable from 'sortablejs'
import { getShopDeliveryList, setShopDeliveryConfig } from '@/addon/shop/api/delivery'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const loading = ref(false)

interface TableDataType {
    key: string
    name: string
    status: number
}

const tableData = ref<TableDataType[]>([])
const getShopDeliveryListFn = () => {
    loading.value = true
    getShopDeliveryList().then(res => {
        tableData.value = res.data
        loading.value = false
        nextTick(() => {
            if (rowDrop) rowDrop()
        })
    }).catch(() => {
        loading.value = false
    })
}
onMounted(() => {
    getShopDeliveryListFn()
})
// 拖拽排序
const toggleIndex = ref(0)
const tableRef = ref()
const rowDrop = () => {
    Sortable.create(tableRef.value, {
        handle: '.vues-rank',
        animation: 300,
        onEnd({ newIndex, oldIndex }) {
            const currRow = tableData.value.splice(oldIndex, 1)[0]
            tableData.value.splice(newIndex, 0, currRow)
            toggleIndex.value += 1
            nextTick(() => {
                rowDrop()
            })
            update()
        }
    })
}

// 编辑名称
const activeIndex = ref<number | null>(null)
const inputValue = ref('')
const edit = (index: number) => {
    activeIndex.value = index
    inputValue.value = toRaw(tableData.value[index].name)
}
const inputBlur = () => {
    if (inputValue.value == '' || tableData.value[activeIndex.value].name === inputValue.value) {
        activeIndex.value = null
        inputValue.value = ''
        return false
    }
    tableData.value[activeIndex.value].name = inputValue.value
    activeIndex.value = null
    update()
}
const update = () => {
    setShopDeliveryConfig({
        value: tableData.value
    })
}
const goRouter = (path: string) => {
    router.push({ path })
}
</script>

<style lang="scss" scoped>
    .text-color {
        color: var(--el-color-primary);
    }

    :deep(.el-card__header) {
        padding-top: 5px !important;
        padding-bottom: 5px !important;
    }
</style>
