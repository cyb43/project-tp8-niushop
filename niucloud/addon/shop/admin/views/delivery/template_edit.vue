<template>
    <div class="main-container">
        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
                <el-form-item :label="t('templateName')" prop="template_name">
                    <el-input v-model.trim="formData.template_name" clearable :placeholder="t('templateNamePlaceholder')" class="input-width" maxlength="60" />
                </el-form-item>
                <el-form-item :label="t('feeTypeName')" prop="fee_type">
                    <el-radio-group v-model="formData.fee_type">
                        <el-radio label="num" size="large">{{ t('num') }}</el-radio>
                        <el-radio label="weight" size="large">{{ t('weight') }}</el-radio>
                        <el-radio label="volume" size="large">{{ t('volume') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('feeSetting')" prop="fee_data">
                    <el-table :data="feeData" style="width: 100%" size="default">
                        <el-table-column :label="t('deliveryArea')">
                            <template #default="{ row, $index }">
                                <div class="area-input">
                                    <span v-if="$index" @click="selectArea('fee', $index)" class="cursor-pointer">{{ row.fee_area_names ? row.fee_area_names : t('areaPlaceholder') }}</span>
                                    <span v-else>{{ row.fee_area_names ? row.fee_area_names : t('areaPlaceholder') }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="feeLabel.first">
                            <template #default="{ $index }">
                                <el-input v-model.trim="feeData[$index].snum"  maxlength="8"  @keyup="filterDigit($event)" @blur="feeData[$index].snum = $event.target.value"/>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('fee')">
                            <template #default="{ $index }">
                                <el-input v-model.trim="feeData[$index].sprice"  maxlength="8" @keyup="filterDigit($event)" @blur="feeData[$index].sprice = $event.target.value"/>
                            </template>
                        </el-table-column>
                        <el-table-column :label="feeLabel.continue">
                            <template #default="{ $index }">
                                <el-input v-model.trim="feeData[$index].xnum" maxlength="8" @keyup="filterDigit($event)" @blur="feeData[$index].xnum = $event.target.value"/>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('continueFee')">
                            <template #default="{ $index }">
                                <el-input v-model.trim="feeData[$index].xprice" @keyup="filterDigit($event)"  maxlength="8"  @blur="feeData[$index].xprice = $event.target.value"/>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('operation')" align="right" width="150">
                            <template #default="{ $index }">
                                <el-button type="primary" @click="delArea('fee', $index)" link v-if="$index">{{ t('delete') }}</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div class="mt-[10px]">
                        <el-button type="primary" @click="addArea('fee')">{{ t('addDeliveryArea') }}</el-button>
                    </div>
                </el-form-item>
                <!-- 指定区域包邮 -->
                <el-form-item :label="t('freeShipping')" prop="is_free_shipping">
                    <el-switch v-model="formData.is_free_shipping" size="small" :inactive-value="0" :active-value="1" />
                </el-form-item>
                <el-form-item v-show="formData.is_free_shipping" prop="free_shipping_data">
                    <el-table :data="freeShippingData" style="width: 100%" size="default">
                        <el-table-column :label="t('freeShippingArea')">
                            <template #default="{ row, $index }">
                                <div class="area-input">
                                    <el-input v-model.trim="row.free_shipping_area_names" :placeholder="t('areaPlaceholder')" readonly @click="selectArea('free_shipping', $index)" />
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="freeShippingLabel">
                            <template #default="{ $index }">
                                <el-input v-model.trim="freeShippingData[$index].free_shipping_num" @keyup="filterDigit($event)"  maxlength="8" @blur="freeShippingData[$index].free_shipping_num = $event.target.value"/>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('freeShippingPrice')">
                            <template #default="{ $index }">
                                <el-input v-model.trim="freeShippingData[$index].free_shipping_price"  @keyup="filterDigit($event)"  maxlength="8"  @blur="freeShippingData[$index].free_shipping_price = $event.target.value"/>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('operation')" align="right" width="150">
                            <template #default="{ $index }">
                                <el-button type="primary" @click="delArea('free_shipping', $index)" link>{{ t('delete') }}</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div class="form-tip">{{ t('freeShippingAreaTips') }}</div>
                    <div class="mt-[10px]">
                        <el-button type="primary" @click="addArea('free_shipping')">{{ t('addFreeShippingArea') }}</el-button>
                    </div>
                </el-form-item>
                <!-- 指定区域不配送 -->
                <el-form-item :label="t('noDelivery')" prop="no_delivery">
                    <el-switch v-model="formData.no_delivery" size="small" :inactive-value="0" :active-value="1" />
                </el-form-item>
                <el-form-item v-show="formData.no_delivery" prop="no_delivery_data">
                    <el-table :data="noDeliveryData" style="width: 100%" size="default">
                        <el-table-column :label="t('noDelivery')">
                            <template #default="{ row, $index }">
                                <div class="area-input">
                                    <el-input v-model.trim="row.no_delivery_area_names" readonly @click="selectArea('no_delivery', $index)" :placeholder="t('areaPlaceholder')" />
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('operation')" align="right" width="150">
                            <template #default="{ $index }">
                                <el-button type="primary" @click="delArea('no_delivery', $index)" link>{{ t('delete') }}</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div class="mt-[10px]">
                        <el-button type="primary" @click="addArea('no_delivery')">{{ t('addNoDelivery') }}</el-button>
                    </div>
                </el-form-item>
            </el-form>
        </el-card>
        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(formRef)" :disabled="loading">{{ t('save') }}</el-button>
                <el-button @click="back()">{{ t('cancel') }}</el-button>
            </div>
        </div>
    </div>

    <!-- 选择地区弹窗 -->
    <el-dialog v-model="showSelectAreaDialog" :title="t('selectArea')" width="80%" class="diy-dialog-wrap" :destroy-on-close="true" @opened="showSelectOpened">

        <el-scrollbar height="50vh">
            <el-tree :data="areaTreeData" :props="{ children: 'child', label: 'name' }" default-expand-all show-checkbox ref="areaTreeRef" :default-checked-keys="selectedArea" node-key="id" />
        </el-scrollbar>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showSelectAreaDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loading" @click="confirmSelectArea">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import { ElTree, FormInstance, ElMessage } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { addShippingTemplate, editShippingTemplate, getShippingTemplateInfo } from '@/addon/shop/api/delivery'
import { AnyObject } from '@/types/global'
import { useRoute, useRouter } from 'vue-router'
import { getAreatree } from '@/app/api/sys'
import { filterDigit } from '@/utils/common'
import Test from '@/utils/test'

const showSelectAreaDialog = ref(false)
const route = useRoute()
const router = useRouter()
const loading = ref(false)
/**
 * 表单数据
 */
const initialFormData = {
    template_id: '',
    template_name: '',
    fee_type: 'num',
    area: [],
    no_delivery: 0,
    is_free_shipping: 0,
    fee_data: [],
    free_shipping_data: [],
    no_delivery_data: []
}
const pageName = route.meta.title
const formData: Record<string, any> = reactive({ ...initialFormData })
const formRef = ref<FormInstance>()
const areaTree = ref<AnyObject[]>([])

if (route.query.id) {
    loading.value = true
    getShippingTemplateInfo(route.query.id).then(({ data }) => {
        if (data) {
            Object.keys(formData).forEach((key: string) => {
                if (data[key] != undefined) formData[key] = data[key]
            })
            feeData.value = data.fee_data
            noDeliveryData.value = data.no_delivery_data
            freeShippingData.value = data.free_shipping_data
        }
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

getAreatree(2).then(res => {
    areaTree.value = res.data
}).catch()

// 表单验证规则
const formRules = computed(() => {
    return {
        template_name: [
            { required: true, message: t('templateNamePlaceholder'), trigger: 'blur' }
        ],
        fee_data: [{ validator: feeDataValidate }],
        free_shipping_data: [{ validator: freeShippingDataValidate }],
        no_delivery_data: [{ validator: noDeliveryDataValidate }]
    }
})

/**
 * 运费模板运费数据校验
 */
const feeDataValidate = (rule: any, value: any, callback: any) => {
    for (let i = 0; i < feeData.value.length; i++) {
        const item = feeData.value[i]
        if (!item.area_ids.length) {
            callback(new Error(t('areaPlaceholder')))
            break
        }
        if (Test.empty(item.snum) || item.snum < 0) {
            callback(new Error(feeLabel.value.first + t('notUnderZero')))
            break
        }
        if (Test.empty(item.xnum) || item.snum < 0) {
            callback(new Error(feeLabel.value.continue + t('notUnderZero')))
            break
        }
    }
    callback()
}

/**
 * 运费模板包邮数据校验
 */
const freeShippingDataValidate = (rule: any, value: any, callback: any) => {
    if (formData.is_free_shipping) {
        for (let i = 0; i < freeShippingData.value.length; i++) {
            const item = freeShippingData.value[i]
            if (!item.area_ids.length) {
                callback(new Error(t('freeShippingPlaceholder')))
                break
            }
            if (Test.empty(item.free_shipping_num) || item.free_shipping_num < 0) {
                callback(new Error(freeShippingLabel.value + t('notUnderZero')))
                break
            }
        }
        callback()
    } else {
        callback()
    }
}

/**
 * 运费模板不配送地区校验
 */
const noDeliveryDataValidate = (rule: any, value: any, callback: any) => {
    if (formData.no_delivery) {
        for (let i = 0; i < noDeliveryData.value.length; i++) {
            const item = noDeliveryData.value[i]
            if (!item.area_ids.length) {
                callback(new Error(t('noDeliveryPlaceholder')))
                break
            }
        }
        callback()
    } else {
        callback()
    }
}

const feeLabel = computed(() => {
    const label: AnyObject = {
        num: {
            first: t('firstNum'),
            continue: t('continueNum')
        },
        weight: {
            first: t('firstWeight'),
            continue: t('continueWeight')
        },
        volume: {
            first: t('firstVolume'),
            continue: t('continueVolume')
        }
    }
    return label[formData.fee_type]
})

const freeShippingLabel = computed(() => {
    const label: AnyObject = {
        num: t('freeShippingNum'),
        weight: t('freeShippingWeight'),
        volume: t('freeShippingVolume')
    }
    return label[formData.fee_type]
})

// 运费数据
const feeData = ref<AnyObject[]>([
    { area_ids: [0], fee_area_names: '全国', snum: 1, sprice: 0, xnum: 1, xprice: 0 }
])
// 包邮区域数据
const freeShippingData = ref<AnyObject[]>([])
// 不配送区域
const noDeliveryData = ref<AnyObject[]>([])

/**
 * 添加地区
 * @param type
 */
const addArea = (type: string) => {
    switch (type) {
        case 'fee':
            feeData.value.push({ area_ids: [], fee_area_names: '', snum: 1, sprice: 0, xnum: 1, xprice: 0 })
            break
        case 'free_shipping':
            freeShippingData.value.push({ area_ids: [], free_shipping_area_names: '', free_shipping_num: 0, free_shipping_price: 0 })
            break
        case 'no_delivery':
            noDeliveryData.value.push({ area_ids: [], no_delivery_area_names: '' })
            break
    }
}

/**
 *删除地区
 * @param type
 * @param index
 */
const delArea = (type: string, index: number) => {
    switch (type) {
        case 'fee':
            feeData.value.splice(index, 1)
            break
        case 'free_shipping':
            freeShippingData.value.splice(index, 1)
            break
        case 'no_delivery':
            noDeliveryData.value.splice(index, 1)
            break
    }
}

// 选中的区域
let selectedArea: number[] = []
// 禁止选择的区域
const disabledArea = ref <Array<string|number>>([])

let currSelect = { type: '', index: 0 }

const selectArea = (type: string, index: number) => {
    currSelect = { type, index }

    let data: AnyObject[] = []
    switch (type) {
        case 'fee':
            data = feeData.value
            break
        case 'free_shipping':
            data = freeShippingData.value
            break
        case 'no_delivery':
            data = noDeliveryData.value
            break
    }
    selectedArea = data[index].area_ids
    disabledArea.value = []
    data.forEach((item, $index) => {
        if (index != $index) disabledArea.value.push(...item.area_ids)
    })
    showSelectAreaDialog.value = true
}

const areaTreeData = computed(() => {
    areaTree.value.forEach(province => {
        province.child.forEach((city:any) => {
            city.disabled = disabledArea.value.includes(city.id)
        })
    })
    return areaTree.value
})

const areaTreeRef = ref<InstanceType<typeof ElTree>>()
const confirmSelectArea = () => {
    const nodes = areaTreeRef.value!.getCheckedNodes(false, false)
    const areaIds: number[] = []
    const areaNames: string[] = []

    nodes.forEach(item => {
        if (item.level == 2) {
            areaIds.push(item.id)
            areaNames.push(item.name)
        }
    })

    switch (currSelect.type) {
        case 'fee':
            feeData.value[currSelect.index].area_ids = areaIds
            feeData.value[currSelect.index].fee_area_names = areaNames.toString()
            break
        case 'free_shipping':
            freeShippingData.value[currSelect.index].area_ids = areaIds
            freeShippingData.value[currSelect.index].free_shipping_area_names = areaNames.toString()
            break
        case 'no_delivery':
            noDeliveryData.value[currSelect.index].area_ids = areaIds
            noDeliveryData.value[currSelect.index].no_delivery_area_names = areaNames.toString()
            break
    }
    showSelectAreaDialog.value = false
}

const showSelectOpened = () => {
    areaTreeRef.value!.setCheckedKeys(selectedArea, false)
}

/**
 * 确认
 * @param formEl
 */
const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    const save = formData.template_id ? editShippingTemplate : addShippingTemplate

    await formEl.validate(async (valid) => {
        if (valid) {
            if (formData.is_free_shipping && freeShippingData.value.length == 0) {
                ElMessage.error(t('freeShippingPlaceholder'))
                return
            }
            if (formData.no_delivery && noDeliveryData.value.length == 0) {
                ElMessage.error('noDeliveryPlaceholder')
                return
            }
            loading.value = true

            const data:AnyObject = {
                template_id: formData.template_id,
                template_name: formData.template_name,
                fee_type: formData.fee_type,
                no_delivery: formData.no_delivery,
                is_free_shipping: formData.is_free_shipping
            }
            const area: AnyObject = {}

            feeData.value.forEach(item => {
                item.area_ids.forEach((city: number) => {
                    area['city_' + city] = { city_id: city, fee_area_ids: item.area_ids.toString(), fee_area_names: item.fee_area_names, snum: item.snum, sprice: item.sprice, xnum: item.xnum, xprice: item.xprice }
                })
            })
            freeShippingData.value.forEach(item => {
                item.area_ids.forEach((city: number) => {
                    if (area['city_' + city]) {
                        Object.assign(area['city_' + city], { free_shipping_area_ids: item.area_ids.toString(), free_shipping_area_names: item.free_shipping_area_names, free_shipping_num: item.free_shipping_num, free_shipping_price: item.free_shipping_price })
                    } else {
                        area['city_' + city] = { city_id: city, free_shipping_area_ids: item.area_ids.toString(), free_shipping_area_names: item.free_shipping_area_names, free_shipping_num: item.free_shipping_num, free_shipping_price: item.free_shipping_price }
                    }
                })
            })
            noDeliveryData.value.forEach(item => {
                item.area_ids.forEach((city: number) => {
                    if (area['city_' + city]) {
                        Object.assign(area['city_' + city], { no_delivery_area_ids: item.area_ids.toString(), no_delivery_area_names: item.no_delivery_area_names })
                    } else {
                        area['city_' + city] = { city_id: city, no_delivery_area_ids: item.area_ids.toString(), no_delivery_area_names: item.no_delivery_area_names }
                    }
                })
            })
            data.area = Object.values(area)
            save(data).then(() => {
                loading.value = false
                router.push({ path: '/shop/order/shipping/template' })
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

const back = () => {
    router.push({ path: '/shop/order/shipping/template' })
}
</script>

<style lang="scss" scoped>
:deep(.el-tree-node.is-expanded>.el-tree-node__children) {
    display: flex !important;
    flex-wrap: wrap;
}

:deep(.area-input .el-input__wrapper) {
    box-shadow: none !important;
    padding: 0 !important;
    background: none;

    input {
        cursor: pointer;
    }
}
</style>
