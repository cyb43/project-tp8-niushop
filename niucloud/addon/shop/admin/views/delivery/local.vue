<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-card class="box-card !border-none" shadow="never">
            <el-form label-width="120px" ref="formRef" :rules="formRules" :model="formData" class="page-form"
                v-loading="loading">
                <!-- <h3 class="panel-title">{{t('basicSettings')}}</h3> -->
                <el-form-item :label="t('deliveryType')" prop="delivery_type">
                    <!-- <el-checkbox-group v-model="formData.delivery_type">
                        <el-checkbox label="business">{{ t('business') }}</el-checkbox>
                        <el-checkbox label="third">{{ t('三方配送') }}</el-checkbox>
                    </el-checkbox-group> -->
                    <el-radio-group v-model="formData.delivery_type">
                        <el-radio :label="'business'">{{ t('business') }}</el-radio>
                        <el-radio :label="'third'">{{ t('thrid') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('thrid')" prop="time_is_open" v-if="formData.delivery_type=='third'">
                    <div v-for="(service, index) in thirdPartyData" :key="service.type">
                        <div class="flex items-center mr-[20px]">
                            <span class="mr-[10px]">{{ service.name }}</span>
                            <el-switch v-model="service.isEnabled" @change="handleServiceChange(index)"></el-switch>
                        </div>
                    </div>
                </el-form-item>
                <template v-if="activeService && formData.delivery_type=='third'">
                    <el-form-item :label="t('AppKey')" prop="app_key">
                        <el-input v-model="activeService.config.app_key" clearable :placeholder="t('AppKeyRequire')" class="input-width" maxlength="100" show-word-limit />
                    </el-form-item>
                    <el-form-item :label="t('AppSecret')" prop="app_secret">
                        <el-input v-model="activeService.config.app_secret" clearable :placeholder="t('AppSecretRequire')" class="input-width" maxlength="100" show-word-limit />
                    </el-form-item>
                    <el-form-item :label="t('shopId')" prop="shop_id">
                        <el-input v-model="activeService.config.shop_id" clearable :placeholder="t('shopIdRequire')" class="input-width" maxlength="100" show-word-limit />
                    </el-form-item>
                    <el-form-item :label="t('shopStoreNo')" prop="shop_store_no">
                        <el-input v-model="activeService.config.shop_store_no" clearable :placeholder="t('shopStoreNoRequire')" class="input-width" maxlength="100" show-word-limit />
                    </el-form-item>
                </template>
                <el-form-item :label="t('timeIsOpen')" prop="time_is_open">
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
                            <el-checkbox label="0">{{ t('sunday') }}</el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item :label="t('deliveryTime')" prop="delivery_time">
                        <div>
                            <div>
                                <div v-for="(timeRange, index) in formData.delivery_time" :key="index" class="mb-3">
                                    <el-time-picker v-model="timeRange.start_time" :placeholder="t('startTime')"
                                        format="HH:mm" value-format="HH:mm"
                                        :picker-options="{selectableRange: '00:00 - 23:59'}" />
                                    <span class="mx-2">-</span>
                                    <el-time-picker v-model="timeRange.end_time" :placeholder="t('endTime')" format="HH:mm"
                                        value-format="HH:mm" :picker-options="{selectableRange: '00:00 - 23:59'}" />
                                    <span v-if="index > 0" class="text-primary cursor-pointer ml-[10px]"
                                        @click="removeTimeRange(index)"> {{ t('delete') }}</span>
                                </div>
                                <span class="text-primary cursor-pointer mr-[10px]" @click="addTimeRange"
                                    v-if="formData.delivery_time.length < 3"> {{ t('addTime') }}</span>
                            </div>
                            <div class="text-[12px] text-[#999]">{{ t('deliveryTimeTips') }}</div>
                        </div>

                    </el-form-item>

                    <el-form-item :label="t('timeInterval')" prop="time_interval">
                        <div>
                            <el-radio-group v-model="formData.time_interval">
                                <el-radio :label="30">{{ t('30minute') }}</el-radio>
                                <el-radio :label="60">{{ t('90minute') }}</el-radio>
                                <el-radio :label="90">{{ t('oneHour') }}</el-radio>
                                <el-radio :label="120">{{ t('twoHour') }}</el-radio>
                            </el-radio-group>
                            <!-- <p class="text-[12px] text-[#999]">{{ t('storeTimeIntervalTips') }}</p> -->
                        </div>

                    </el-form-item>
                    <el-form-item :label="t('advancaDay')" prop="advance_day">
                        <div>
                            <div class="flex">
                                {{ t('advance') }}
                                <div class="w-[100px] mx-[5px]">
                                    <el-input v-model.trim="formData.advance_day" />
                                </div>
                                {{ t('day') }}
                            </div>
                            <p class="text-[12px] text-[#999]">{{ t('advanceTips') }}</p>
                        </div>
                    </el-form-item>
                    <el-form-item :label="t('mostDays')" prop="most_day">
                        <div>
                            <div class="flex">
                               {{ t('reservationAvailable') }}
                                <div class="w-[100px] mx-[5px]">
                                    <el-input v-model.trim="formData.most_day" />
                                </div>
                              {{ t('withinDays') }}
                            </div>
                            <p class="text-[12px] text-[#999]">{{ t('mostDaysTips') }}</p>
                        </div>
                    </el-form-item>
                </template>

                <el-form-item :label="t('deliveryAddress')" prop="delivery_address">
                    <div class="flex flex-col">
                        <div class="flex">
                            {{ defaultDeliveryAddress ? defaultDeliveryAddress.full_address :
                            t('defaultDeliveryAddressEmpty') }}
                            <el-button type="primary" @click="router.push('/shop/order/address')" link
                                class="ml-[10px]">{{ defaultDeliveryAddress ? t('update') : t('toSetting')
                                }}</el-button>
                        </div>
                        <div class="text-error leading-none"
                            v-if="formData.center.lat && defaultDeliveryAddress && (formData.center.lat != defaultDeliveryAddress.lat || formData.center.lng != defaultDeliveryAddress.lng)">
                            {{ t('deliveryAddressChange') }}</div>
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
                            <el-input v-model.number="formData.base_dist" type="text" maxlength="6" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('feeSettingTextOne') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.base_price" type="text"  maxlength="8" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('feeSettingTextTwo') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.number="formData.grad_dist" type="text"  maxlength="6" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('feeSettingTextThree') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.grad_price" type="text"  maxlength="8" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('priceUnit') }}
                    </div>
                </el-form-item>
                <el-form-item :label="t('weightFee')" prop="">
                    <div class="flex">
                        {{ t('weightFeeTextOne') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.weight_start" type="text"  maxlength="6" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('weightFeeTextTwo') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.weight_unit" type="text"  maxlength="6" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('weightFeeTextThree') }}
                        <div class="w-[60px] mx-[5px]">
                            <el-input v-model.trim="formData.weight_price" type="text"  maxlength="8" @keyup="filterDigit($event)" />
                        </div>
                        {{ t('priceUnit') }}
                    </div>
                </el-form-item>

                <el-form-item prop="area" v-loading="mapLoading">
                    <div class="relative w-full">
                        <div id="container" class="w-full h-[520px]"></div>
                        <div class="absolute bg-white w-[270px] h-[500px] top-[10px] left-[10px] region-list">
                            <el-scrollbar>
                                <div class="p-[10px] region-item pr-[50px] relative"
                                    v-for="(item, index) in formData.area" :key="index"
                                    :class="{ '!border-primary': index == currArea }" @click="selectArea(index)">
                                    <el-form label-width="80px" :model="item" :rules="formRules" class="page-form"
                                        ref="areaFromRef">
                                        <div class="pb-[18px]">
                                            <el-form-item :label="t('areaName')" prop="area_name">
                                                <el-input v-model.trim="formData.area[index].area_name" type="text" />
                                            </el-form-item>
                                        </div>
                                        <div class="pb-[18px]">
                                            <el-form-item :label="t('startPrice')" prop="start_price">
                                                <el-input v-model.trim="formData.area[index].start_price"  maxlength="8" type="text"
                                                    @keyup="filterDigit($event)" />
                                            </el-form-item>
                                        </div>
                                        <div class="pb-[10px]" v-show="formData.fee_type == 'region'">
                                            <el-form-item :label="t('deliveryPrice')" prop="delivery_price">
                                                <el-input v-model.trim="formData.area[index].delivery_price" type="text"
                                                    @keyup="filterDigit($event)" />
                                            </el-form-item>
                                        </div>
                                        <el-form-item :label="t('areaType')">
                                            <el-radio-group v-model="formData.area[index].area_type" @change="areaTypeChange(index)">
                                                <el-radio label="radius" size="large" class="!mr-[10px]">{{ t('radius') }}</el-radio>
                                                <el-radio label="custom" size="large" class="!mr-[0px]">{{ t('custom') }}</el-radio>
                                            </el-radio-group>
                                        </el-form-item>
                                    </el-form>
                                    <el-button type="primary" link class="absolute z-1 top-[10px] right-[10px]"
                                        @click.stop="deleteArea(index)">{{ t('delete') }}</el-button>
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
import { setLocal, getLocal ,getThird} from '@/addon/shop/api/delivery'
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
const thirdPartyData = ref<any>(null)

const getThirdFn = ()=> {
    getThird().then((res) => {
        thirdPartyData.value = res.data.map((item: any) => ({
            ...item,
            isEnabled: false, // 默认关闭
            config: {
                app_key: '',
                app_secret: '',
                shop_id: '',
                shop_store_id: ''
            }
        }));
    })
}

const activeService = computed(() => {
    if (!thirdPartyData.value || thirdPartyData.value.length === 0) {
        return null;
    }
    return thirdPartyData.value.find(service => service.isEnabled) || null;
});

// 处理服务开关变化（只能开启一个服务）
const handleServiceChange = (index: number) => {
    const service = thirdPartyData.value[index];
    if (service.isEnabled) {
        thirdPartyData.value.forEach((s, i) => {
        if (i !== index) {
            s.isEnabled = false;
        }
        });
    }
};

const buildThirdPartyConfig = () => {
    const config: Record<string, any> = {};

    thirdPartyData.value.forEach(service => {
        config[service.type] = {
            app_key: service.config.app_key,
            app_secret: service.config.app_secret,
            shop_id: service.config.shop_id,
            shop_store_no: service.config.shop_store_no
        };

        if (service.isEnabled) {
            config.default = service.type;
        }
    });

    return config;
};


getThirdFn()
const formData = ref({
    center: {
        lat: '',
        lng: ''
    },
    // delivery_type: ['business'],
    delivery_type: 'business',
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
    ],
    delivery_time: [
        { start_time: '', end_time: '' } // 初始一个时间段
    ],
    time_interval: 30,
    most_day: 7,
    advance_day: 0,
    time_most: 1,
    time_advance:1,
    third_party_config:{}
})

// 正则表达式
const regExp = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}

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
        // delivery_type: [
        //     {
        //         validator: (rule: any, value: any, callback: any) => {
        //             if (!formData.value.delivery_type.length) {
        //                 callback(new Error(t('deliveryTypeRequire')))
        //             }
        //             callback()
        //         }
        //     }
        // ],
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
        ],
        delivery_time: [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (!value || value.length === 0) {
                        return callback(new Error(t('tradeTimePlaceholderTwo')))
                    }
                    for (let i = 0; i < value.length; i++) {
                        const timeRange = value[i]
                        if (!timeRange.start_time || !timeRange.end_time) {
                            return callback(new Error(t('tradeTimePlaceholderTwo')))
                        }
                        // 结束时间不能小于或等于开始时间
                        if (timeRange.end_time <= timeRange.start_time) {
                            return callback(new Error(t('tradeTimePlaceholderFour')))
                        }
                        // 确保后一个时间段的开始时间不能小于前一个时间段的结束时间
                        if (i > 0 && value[i].start_time < value[i - 1].end_time) {
                            return callback(new Error(t('tradeTimePlaceholderFive')))
                        }
                    }
                    callback()
                },
                trigger: 'change',
                required: true
            }
        ],
        time_interval: [
            { required: true, message: t('tradeTimePlaceholderThree'), trigger: 'change' }
        ],
        advance_day:[
            {
                validator(rule, value, callback) {
                    if (value === null || value === '') {
                        callback()
                    } else if (isNaN(value) || !regExp.number.test(value)) {
                        callback(t('formatError'))
                    } else if (value < 0) {
                        callback(t('notLessThanZero'))
                    } else {
                        callback();
                    }
                },
                trigger: 'blur'
            }
        ],
        most_day:[
            {
                validator(rule, value, callback) {
                    if (value === null || value === '') {
                        callback()
                    } else if (isNaN(value) || !regExp.number.test(value)) {
                        callback(t('formatError'))
                    } else if (value <= 0) {
                        callback(t('mustBeGreaterThanZero'))
                    } else {
                        callback();
                    }
                },
                trigger: 'blur'
            }
        ]
    }
})
const validateThirdPartyConfig = () => {
    if (formData.value.delivery_type !== 'third') return true;

    const active = thirdPartyData.value.find(item => item.isEnabled);
    if (!active) {
        ElMessage.error(t('thridRequire'));
        return false;
    }

    const { app_key, app_secret, shop_id ,shop_store_no} = active.config;
    if (!app_key || !app_secret || !shop_id || !shop_store_no) {
        ElMessage.error(t('thridSeting'));
        return false;
    }

    return true;
};

// 添加时间段
const addTimeRange = () => {
    formData.value.delivery_time.push({ start_time: '', end_time: '' })
}

// 删除时间段
const removeTimeRange = (index: number) => {
    formData.value.delivery_time.splice(index, 1)
}

const timeTransition = (time:any) => {
    const arr = time.split(':')
    const num = arr[0] * 60 * 60 + arr[1] * 60
    return num
}

const timestampTransition = (timeStamp:any) => {
    let hour = Math.floor(timeStamp / (60 * 60))
    let minute = Math.floor(timeStamp / 60) - (hour * 60)
    hour = hour < 10 ? ('0' + hour) : hour
    minute = minute < 10 ? ('0' + minute) : minute

    return hour + ':' + minute
}

getLocal().then(({ data }) => {
    loading.value = false
    if (data) Object.assign(formData.value, data)
    formData.value.time_week = formData.value.time_week?formData.value.time_week.split(','):[]
    if (Array.isArray(data.delivery_type) && data.delivery_type.length > 0) {
        formData.value.delivery_type = data.delivery_type[0];
    } else {
        formData.value.delivery_type = data.delivery_type || 'business';
    }
     // 处理 delivery_time 格式
    if (data.delivery_time === '' || data.delivery_time === null) {
        formData.value.delivery_time = [{ start_time: '', end_time: '' }];
    } else if (Array.isArray(data.delivery_time)) {
        formData.value.delivery_time = data.delivery_time.map((item: any) => ({
            start_time: timestampTransition(item.start_time),
            end_time: timestampTransition(item.end_time)
        }));
    }

    // 回显 third_party_config 到 thirdPartyData
    if (data.third_party_config) {
        thirdPartyData.value.forEach(service => {
            const config = data.third_party_config[service.type];
            if (config) {
                service.isEnabled = service.type == data.third_party_config.default;
                service.config = {
                    app_key: config.app_key || '',
                    app_secret: config.app_secret || '',
                    shop_id: config.shop_id || '',
                    shop_store_id: config.shop_store_id || '',
                    shop_store_no: config.shop_store_no || '',
                };
            }
        });
    }
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
        if (!validateThirdPartyConfig()) return;

        if (valid) {
            loading.value = true

            formData.value.center = {
                lat: defaultDeliveryAddress.value.lat,
                lng: defaultDeliveryAddress.value.lng
            }
            
            await formEl.validate(async (valid) => {
                const param = deepClone(toRaw(formData.value))
                param.time_week = param.time_week.toString()
                param.delivery_time = param.delivery_time.map(range => ({
                    start_time: range.start_time ? timeTransition(range.start_time) : null,
                    end_time: range.end_time ? timeTransition(range.end_time) : null
                }))
                param.third_party_config = buildThirdPartyConfig()
                param.delivery_type = [param.delivery_type]
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
