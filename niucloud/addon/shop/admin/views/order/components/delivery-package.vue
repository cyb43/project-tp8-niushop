<template>
    <el-dialog v-model="showDialog" :title="t('packageInfo')" width="700px" class="diy-dialog-wrap" :destroy-on-close="true">
        <div v-loading="loading" class="max-h-[600px] overflow-y-auto">
            <h3 class="panel-title">{{ t('deliveryInfo') }}</h3>
            <div class="pl-[20px] mb-[20px] text-[14px] flex justify-between">
                <div>
                    <span>{{ t('devliveryTime') }}：</span><span>{{ packageData.create_time }}</span>
                </div>
            </div>
            <div class="pl-[20px] mb-[20px] text-[14px] flex">
                <div v-if="packageData.company">
                    <span>{{ t('companyName') }}：</span><span>{{ packageData.company.company_name }}</span>
                </div>
                <div v-if="packageData.express_number">
                    <span class="ml-[60px]">{{ t('logisticNo') }}：</span><span>{{ packageData.express_number }}</span>
                </div>
            </div>
            <h3 class="panel-title">{{ t('goodsInfo') }}</h3>
            <div class="pl-[20px] mb-[20px]">
                <el-table :data="packageData.order_goods" size="large">
                    <el-table-column :label="t('goodsName')" align="left" width="300">
                        <template #default="{ row }">
                            <div class="flex">
                                <div class="flex items-center w-[50px] h-[50px] mr-[10px]">
                                    <img class="w-[50px] h-[50px]" :src="img(row.goods_image_thumb_small)" />
                                </div>
                                <div class="flex flex-col flex-1">
                                    <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                    <span class="text-[12px] text-[#999]">{{ row.sku_name }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" :label="t('price')" min-width="50" align="left" />
                    <el-table-column prop="num" :label="t('num')" min-width="50" align="right" />
                </el-table>
            </div>
            <h3 class="panel-title">{{ t('logisticInfo') }}</h3>
            <template v-if="packageData.sub_delivery_type != 'none_express'">
                <div class="pl-[20px]" v-if="packageData.traces">
                    <template v-if="packageData.traces.list">
                        <div class="flex justify-between mb-[15px]" v-for="(item, index) in packageData.traces.list"
                             :key="index">
                            <span class="block w-[150px]">{{ item.datetime }}</span><span
                            class="block w-[500px]">{{ item.remark }}</span>
                        </div>
                    </template>
                    <div>{{ packageData.traces.reason }}</div>
                </div>
                <div class="pl-[20px]" v-else>
                    <div>{{ t('notLogistics') }}</div>
                </div>
            </template>
            <span class="pl-[20px]" v-else>{{ t('noLogisticsRequired') }}</span>
        </div>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import { deliveryPackage } from '@/addon/shop/api/order'
import { img } from '@/utils/common'

const showDialog = ref(false)
const loading = ref(false)

interface PackageDataType {
    create_time: string
    express_number: string
    company: {
        company_name: string
    }
    order_goods: {
        goods_name: string
        goods_image_thumb_small: string
        sku_name: string
        price: number
        num: number
    }[]
    traces: {
        list: {
            datetime: string
            remark: string
        }[]
        reason: string
    }
}

const packageData = ref<PackageDataType | any>('')

const setFormData = async(id: number, mobile: number) => {
    loading.value = true
    if (id) {
        deliveryPackage({
            id,
            mobile
        }).then((data) => {
            packageData.value = data.data
            loading.value = false
        })
    }
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
<style lang="scss">
.diy-dialog-wrap .el-form-item__label {
    height: auto !important;
}

.notes-form .el-dialog__body .el-form-item {
    margin-bottom: 0px !important;
}

/* 多行超出隐藏 */
.multi-hidden {
    word-break: break-all;
    text-overflow: ellipsis;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
