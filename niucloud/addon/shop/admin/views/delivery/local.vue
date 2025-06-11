<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-card class="box-card !border-none" shadow="never">
            <el-form label-width="120px" ref="formRef" :rules="formRules" :model="formData" class="page-form" v-loading="loading">
                <!-- <h3 class="panel-title">{{t('basicSettings')}}</h3> -->
                <el-form-item :label="t('deliveryType')" prop="delivery_type">
                    <el-checkbox-group v-model="formData.delivery_type">
                        <el-checkbox label="business">{{ t('business') }}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <!-- <el-form-item :label="t('timeIsOpen')" prop="time_is_open">
                    <div>
                        <el-radio-group v-model="formData.time_is_open">
                            <el-radio :label="1">{{ t('open') }}</el-radio>
                            <el-radio :label="0">{{ t('close') }}</el-radio>
                        </el-radio-group>
                        <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{t('timeIsOpenTips')}}</div>
                    </div>
                </el-form-item>
                <template v-if="formData.time_is_open === 1">
                    <el-form-item>
                        <el-radio-group v-model="formData.time_type">
                            <el-radio :label="0">{{ t('everyDay') }}</el-radio>
                            <el-radio :label="1">{{ t('custom') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item prop="time_week" v-if="formData.time_type===1">
                    <el-checkbox-group v-model="formData.time_week">
                        <el-checkbox label="1">{{ t('monday') }}</el-checkbox>
                        <el-checkbox label="2">{{ t('tuesday') }}</el-checkbox>
                        <el-checkbox label="3">{{ t('wednesday') }}</el-checkbox>
                        <el-checkbox label="4">{{ t('thursday') }}</el-checkbox>
                        <el-checkbox label="5">{{ t('friday') }}</el-checkbox>
                        <el-checkbox label="6">{{ t('saturday') }}</el-checkbox>
                        <el-checkbox label="7">{{ t('sunday') }}</el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                </template> -->
                <el-form-item :label="t('deliveryAddress')" prop="delivery_address">
                    <div class="flex flex-col">
                        <div class="flex">
                            {{ defaultDeliveryAddress ? defaultDeliveryAddress.full_address : t('defaultDeliveryAddressEmpty') }}
                            <el-button type="primary" @click="router.push('/shop/order/address')" link class="ml-[10px]">{{ defaultDeliveryAddress ? t('update') : t('toSetting') }}</el-button>
                        </div>
                        <div class="text-error leading-none" v-if="formData.center.lat && defaultDeliveryAddress && (formData.center.lat != defaultDeliveryAddress.lat || formData.center.lng != defaultDeliveryAddress.lng)">{{ t('deliveryAddressChange') }}</div>
                    </div>
                </el-form-item>
                <el-form-item :label="t('feeType')">
                    <el-radio-group v-model="formData.fee_type">
                        <el-radio label="region">{{ t('region') }}</el-radio>
                        <el-radio label="distance">{{ t('distance') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('feeSetting')" prop="distance" v-show="formData.fee_type == 'distance'">
                    <div class="flex">
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.number="formData.base_dist" type="text" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('feeSettingTextOne') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.base_price" type="text" @keyup="filterDigit($event)"/>
                        </div>
                        {{ t('feeSettingTextTwo') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.number="formData.grad_dist" type="text" @keyup="filterDigit($event)"/>
                        </div>
                        {{ t('feeSettingTextThree') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.grad_price" type="text" @keyup="filterDigit($event)"/>
                        </div>
                        {{ t('priceUnit') }}
                    </div>
                </el-form-item>
                <el-form-item :label="t('weightFee')" prop="">
                    <div class="flex">
                        {{ t('weightFeeTextOne') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.weight_start" type="text"  @keyup="filterDigit($event)"/>
                        </div>
                        {{ t('weightFeeTextTwo') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.weight_unit" type="text"  @keyup="filterDigit($event)"/>
                        </div>
                        {{ t('weightFeeTextThree') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.weight_price" type="text"  @keyup="filterDigit($event)" />
                        </div>
                        {{ t('priceUnit') }}
                    </div>
                </el-form-item>

                <el-form-item prop="area" v-loading="mapLoading">
                    <div class="relative w-full">
                        <div id="container" class="w-full h-[520px]"></div>
                        <div class="absolute bg-white w-[270px] h-[500px] top-[10px] left-[10px] region-list">
                            <el-scrollbar>
                                <div class="p-[10px] region-item pr-[50px] relative" v-for="(item, index) in formData.area" :key="index" :class="{ '!border-primary': index == currArea }" @click="selectArea(index)">
                                    <el-form label-width="80px" :model="item" :rules="formRules" class="page-form" ref="areaFromRef">
                                        <div class="pb-[18px]">
                                            <el-form-item :label="t('areaName')" prop="area_name">
                                                <el-input v-model.trim="formData.area[index].area_name" type="text" />
                                            </el-form-item>
                                        </div>
                                        <div class="pb-[18px]">
                                            <el-form-item :label="t('startPrice')" prop="start_price">
                                                <el-input v-model.trim="formData.area[index].start_price" type="text"  @keyup="filterDigit($event)" />
                                            </el-form-item>
                                        </div>
                                        <div class="pb-[10px]" v-show="formData.fee_type == 'region'">
                                            <el-form-item :label="t('deliveryPrice')" prop="delivery_price">
                                                <el-input v-model.trim="formData.area[index].delivery_price" type="text"   @keyup="filterDigit($event)"/>
                                            </el-form-item>
                                        </div>
                                        <el-form-item :label="t('areaType')">
                                            <el-radio-group v-model="formData.area[index].area_type" @click.stop="" @change="areaTypeChange(index)">
                                                <el-radio label="radius" size="large" class="!mr-[10px]">{{ t('radius') }}</el-radio>
                                                <el-radio label="custom" size="large" class="!mr-[0px]">{{ t('custom') }}</el-radio>
                                            </el-radio-group>
                                        </el-form-item>
                                    </el-form>
                                    <el-button type="primary" link class="absolute z-1 top-[10px] right-[10px]" @click.stop="deleteArea(index)">{{ t('delete') }}</el-button>
                                </div>
                                <div class="p-[10px] text-center">
                                    <el-button plain @click="addArea">{{ t('addDeliveryArea') }}</el-button>
                                </div>
                            </el-scrollbar>
                        </div>
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
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, onBeforeUnmount,toRaw } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from "@element-plus/icons-vue"
import { useRoute, useRouter } from 'vue-router'
import { getMap } from '@/app/api/sys'
import { guid, filterDigit, deepClone } from '@/utils/common'
import { createCircle, deleteGeometry, createPolygon, selectGeometry, createMarker } from '@/utils/qqmap'
import { setLocal, getLocal } from '@/addon/shop/api/delivery'
import { FormInstance } from 'element-plus'
import Test from '@/utils/test'
import { getShopDefaultDeliveryAddressInfo } from '@/addon/shop/api/shop_address'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const pageName = route.meta.title
const formRef = ref<FormInstance>()
const areaFromRef: any = ref<FormInstance[]>()
interface addressType{
    full_address:string
    lat:string
    lng:string
}
const defaultDeliveryAddress:any = ref<addressType|null>(null)
const getDefaultDeliveryAddress = async () => {
    await getShopDefaultDeliveryAddressInfo().then(({ data }) => {
        defaultDeliveryAddress.value = data
    }).catch()
}
getDefaultDeliveryAddress()

const formData = ref({
    center: {
        lat: '',
        lng: ''
    },
    delivery_type: ['business'],
    fee_type: 'region',
    time_is_open:1,
    time_type:0,
    time_week: <any>[],
    base_dist: '',
    base_price: '',
    grad_dist: '',
    grad_price: '',
    weight_start: 0.000,
    weight_unit: 0,
    weight_price: 0,
    area: [
        {
            area_name: '',
            area_type: 'radius',
            start_price: 0,
            delivery_price: 0,
            area_json: {
                key: guid()
            }
        }
    ]
})

// 表单验证规则
const formRules = computed(() => {
    return {
        time_week: [{ required: true, message: t('timeWeekRequire'), trigger: 'change' }],
        delivery_address: [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (!defaultDeliveryAddress.value) {
                        callback(new Error(t('defaultDeliveryAddressEmpty')))
                    }
                    callback()
                }
            }
        ],
        delivery_type: [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (!formData.value.delivery_type.length) {
                        callback(new Error(t('deliveryTypeRequire')))
                    }
                    callback()
                }
            }
        ],
        distance: [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (formData.value.fee_type == 'distance') {
                        if (Test.require(formData.value.base_dist)) {
                            callback(new Error(t('baseDistRequire')))
                        }
                        if (Test.require(formData.value.base_price)) {
                            callback(new Error(t('basePriceRequire')))
                        }
                        if (Test.require(formData.value.grad_dist)) {
                            callback(new Error(t('gradDistRequire')))
                        }
                        if (Test.require(formData.value.grad_price)) {
                            callback(new Error(t('gradPriceRequire')))
                        }
                    }
                    callback()
                },
                trigger: 'blur'
            }
        ],
        area_name: [{ required: true, message: t('areaNameRequire'), trigger: 'blur' }],
        start_price: [
            { required: true, message: t('startPriceRequire'), trigger: 'blur' },
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (parseInt(value) < 0) {
                        callback(new Error(t('startPriceMin')))
                    }
                    callback()
                },
                trigger: 'blur'
            }
        ],
        delivery_price: [
            { required: formData.value.fee_type == 'region', message: t('deliveryPriceRequire'), trigger: 'blur' },
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (parseInt(value) < 0) {
                        callback(new Error(t('deliveryPriceMin')))
                    }
                    callback()
                },
                trigger: 'blur'
            }
        ],
        area: [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (Test.empty(formData.value.area)) {
                        callback(new Error(t('areaPlaceholder')))
                    }
                    callback()
                },
                trigger: 'blur'
            }
        ]
    }
})

getLocal().then(({ data }) => {
    loading.value = false
    if (data) Object.assign(formData.value, data)
    formData.value.time_week = formData.value.time_week?formData.value.time_week.split(','):[]
}).catch(()=>{
    loading.value = false
})

onMounted(() => {
    const mapScript = document.createElement('script')
    getMap().then(res => {
        mapScript.type = 'text/javascript'
        mapScript.src = 'https://map.qq.com/api/gljs?libraries=tools,service&v=1.exp&key=' + res.data.key
        document.body.appendChild(mapScript)
    })
    mapScript.onload = () => {
        setTimeout(() => {
            initMap()
        }, 500)
    }
})

/**
 * 初始化地图
 */
let map: any
const mapLoading = ref(true)
const initMap = () => {
    const TMap = (window as any).TMap
    const LatLng = TMap.LatLng
    const center = new LatLng(defaultDeliveryAddress.value ? defaultDeliveryAddress.value.lat : 39.980619, defaultDeliveryAddress.value ? defaultDeliveryAddress.value.lng : 116.321277)

    map = new TMap.Map('container', {
        center,
        zoom: 14
    })
    createMarker(map)

    map.on('tilesloaded', () => {
        mapLoading.value = false
    })

    formData.value.area.forEach(item => {
        item.area_type == 'radius' ? createCircle(map, item.area_json) : createPolygon(map, item.area_json)
    })
}

const currArea = ref<number>(0)

/**
 * 添加配送区域
 */
const addArea = () => {
    formData.value.area.push({
        area_name: '',
        area_type: 'radius',
        start_price: 0,
        delivery_price: 0,
        area_json: {
            key: guid()
        }
    })
    const index = formData.value.area.length - 1
    createCircle(map, formData.value.area[index].area_json)
}

/**
 * 删除配送区域
 */
const deleteArea = (index: number) => {
    const data = formData.value.area[index]
    deleteGeometry(data.area_json.key)
    formData.value.area.splice(index, 1)
}

const selectArea = (index: number) => {
    currArea.value = index
    const data = formData.value.area[index]
    selectGeometry(data.area_json.key)
}

const areaTypeChange = (index: number) => {
    const data = formData.value.area[index]
    deleteGeometry(data.area_json.key)
    data.area_type == 'radius' ? createCircle(map, data.area_json) : createPolygon(map, data.area_json)
}

onBeforeUnmount(() => {
    map.destroy()
})

const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async (valid) => {
        let areaValidate = true

        for (let i = 0; i < areaFromRef.value?.length; i++) {
            const ref = areaFromRef.value[i]
            await ref.validate(async (valid) => {
                areaValidate = valid
            })
            if (!areaValidate) break
        }
        if (!areaValidate) return

        if (valid) {
            loading.value = true

            formData.value.center = {
                lat: defaultDeliveryAddress.value.lat,
                lng: defaultDeliveryAddress.value.lng
            }

            await formEl.validate(async (valid) => {
                const param = deepClone(toRaw(formData.value))
                param.time_week = param.time_week.toString()
                setLocal(param).then(() => {
                    loading.value = false
                }).catch(() => {
                    loading.value = false
                })
            })
        }
    })
}

const back = () => {
    router.push({ path: '/shop/order/delivery' })
}
</script>

<style lang="scss" scoped>
.region-list {
    border: 1px solid var(--el-border-color-lighter);
    z-index: 3;

    .region-item {
        border: 1px solid transparent;
        border-bottom-color: var(--el-border-color-lighter);
    }
}
#container :deep(div){
    z-index: 2 !important;
}
</style>
