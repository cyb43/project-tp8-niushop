<template>
    <div class="main-container">
        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="id ? t('updateStore') : t('addStore')" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never" v-loading="loading">
            <el-form :model="formData" label-width="140px" ref="formRef" :rules="formRules" class="page-form">
                <el-form-item :label="t('storeName')" prop="store_name">
                    <el-input v-model.trim="formData.store_name" clearable :placeholder="t('storeNamePlaceholder')" class="input-width" maxlength="30" />
                </el-form-item>
                <el-form-item :label="t('storeDesc')">
                    <el-input v-model.trim="formData.store_desc" type="textarea" rows="4" clearable :placeholder="t('storeDescPlaceholder')" class="input-width" maxlength="200" />
                </el-form-item>
                <el-form-item :label="t('storeLogo')">
                    <upload-image v-model="formData.store_logo" />
                </el-form-item>
                <el-form-item :label="t('storeMobile')" prop="store_mobile">
                    <el-input v-model.trim="formData.store_mobile" clearable :placeholder="t('storeMobilePlaceholder')" class="input-width" @keyup="filterNumber($event)" @blur="formData.store_mobile = $event.target.value" maxlength="11" />
                </el-form-item>
                <el-form-item :label="t('tradeTime')" prop="trade_time">
                    <div>
                        <el-input v-model.trim="formData.trade_time" clearable :placeholder="t('tradeTimePlaceholder')" class="input-width" />
                        <p class="text-[12px] text-[#999]">{{ t('tradeTimeTips') }}</p>
                    </div>
                </el-form-item>

                <el-form-item :label="t('storeDate')" prop="time_week">
                    <el-checkbox-group v-model="formData.time_week">
                        <el-checkbox :label="'1'">{{ t('monday') }}</el-checkbox>
                        <el-checkbox :label="'2'">{{ t('tuesday') }}</el-checkbox>
                        <el-checkbox :label="'3'">{{ t('wednesday') }}</el-checkbox>
                        <el-checkbox :label="'4'">{{ t('thursday') }}</el-checkbox>
                        <el-checkbox :label="'5'">{{ t('friday') }}</el-checkbox>
                        <el-checkbox :label="'6'">{{ t('saturday') }}</el-checkbox>
                        <el-checkbox :label="'0'">{{ t('sunday') }}</el-checkbox>
                    <br />
                    </el-checkbox-group>
                </el-form-item>

                <el-form-item :label="t('storeTime')" prop="trade_time_json">
                    <div>
                        <div>
                            <div v-for="(timeRange, index) in formData.trade_time_json" :key="index" class="mb-3">
                                <el-time-picker v-model="timeRange.start_time" :placeholder="t('startTime')" format="HH:mm" value-format="HH:mm" :picker-options="{selectableRange: '00:00 - 23:59'}"/>
                                <span class="mx-2">-</span>
                                <el-time-picker v-model="timeRange.end_time" :placeholder="t('endTime')" format="HH:mm" value-format="HH:mm" :picker-options="{selectableRange: '00:00 - 23:59'}"/>
                                <span v-if="index > 0"  class="text-primary cursor-pointer ml-[10px]" @click="removeTimeRange(index)"> {{ t('delete') }}</span>
                            </div>
                            <span class="text-primary cursor-pointer mr-[10px]" @click="addTimeRange" v-if="formData.trade_time_json.length < 3"> {{ t('addTimeRange') }}</span>
                        </div>
                        <div class="text-[12px] text-[#999]">{{ t('storeDateTips') }}</div>
                    </div>
                    
                </el-form-item>

                <el-form-item :label="t('storeTimeInterval')" prop="time_interval">
                    <div>
                        <el-radio-group v-model="formData.time_interval">
                            <el-radio v-for="(item, key) in time_interval_list" :key="key" :label="item.type">{{ item.name }}</el-radio>
                        </el-radio-group>
                        <p class="text-[12px] text-[#999]">{{ t('storeTimeIntervalTips') }}</p>
                    </div>
                    
                </el-form-item>

                <el-form-item :label="t('storeAddress')" prop="address_area">
                    <el-select v-model="formData.province_id" value-key="id" clearable class="w-[200px]"  ref="provinceRef">
                        <el-option :label="t('provincePlaceholder')" :value="0"/>
                        <el-option v-for="(item, index) in areaList.province" :key="index" :label="item.name"  :value="item.id"/>
                    </el-select>
                    <el-select v-model="formData.city_id" value-key="id" clearable class="w-[200px] ml-3" ref="cityRef">
                        <el-option :label="t('cityPlaceholder')" :value="0"/>
                        <el-option v-for="(item, index) in areaList.city " :key="index" :label="item.name"  :value="item.id"/>
                    </el-select>
                    <el-select v-model="formData.district_id" value-key="id" clearable class="w-[200px] ml-3"  ref="districtRef">
                        <el-option :label="t('districtPlaceholder')" :value="0"/>
                        <el-option v-for="(item, index) in areaList.district " :key="index" :label="item.name"  :value="item.id"/>
                    </el-select>
                </el-form-item>
                <el-form-item prop="address">
                    <el-input v-model.trim="formData.address" clearable :placeholder="t('addressPlaceholder')" @input="areaChange()"  class="input-width"/>
                </el-form-item>

                <el-form-item>
                    <div id="container" class="w-[800px] h-[520px] relative" v-loading="mapLoading"></div>
                </el-form-item>
            </el-form>
        </el-card>
        <div class="fixed-footer-wrap">
            <div class="fixed-footer !z-[1000]">
                <el-button type="primary" @click="onSave(formRef)">{{ t('save') }}</el-button>
                <el-button @click="back()">{{ t('cancel') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { t } from '@/lang'
import type { FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { getStoreInfo, addStore, editStore, getStoreInit } from '@/addon/shop/api/delivery'
import { getMap, getAreaListByPid, getAreaByCode } from '@/app/api/sys'
import { useRoute } from 'vue-router'
import { createMarker, latLngToAddress, addressToLatLng } from '@/utils/qqmap'
import { filterNumber, debounce } from '@/utils/common'
import { cloneDeep } from 'lodash-es'

const route = useRoute()
const id: number = parseInt(route.query.id as string)
const loading = ref(false)
interface areaType{
    province: any[],
    city: any[],
    district: any[]
}
const areaList = reactive<areaType>({
    province: [],
    city: [],
    district: []
})
const provinceRef = ref()
const cityRef = ref()
const districtRef = ref()

/**
 * 获取省
 */
getAreaListByPid(0).then(res => {
    areaList.province = res.data
})

let mapKey: string = ''
onMounted(() => {
    const mapScript = document.createElement('script')
    getMap().then(res => {
        mapKey = res.data.key
        mapScript.type = 'text/javascript'
        mapScript.src = 'https://map.qq.com/api/gljs?libraries=tools,service&v=1.exp&key=' + res.data.key
        document.body.appendChild(mapScript)
    })
    mapScript.onload = () => {
        setTimeout(() => {
            initMap()
        }, 500)
    }
    getStoreInitFn()
})
const week_list = ref({})
const time_interval_list = ref({})
const getStoreInitFn = () => {
    getStoreInit().then(res => {
        week_list.value = res.data.week_list
        time_interval_list.value = res.data.time_interval_list
    })
}

/**
 * 初始化地图
 */
let map: any
let marker: any
const mapLoading = ref(true)
const initMap = () => {
    const TMap = (window as any).TMap
    const LatLng = TMap.LatLng
    const center = new LatLng(formData.latitude, formData.longitude)

    map = new TMap.Map('container', {
        center,
        zoom: 14
    })

    map.on('tilesloaded', () => {
        mapLoading.value = false
    })

    marker = createMarker(map)

    map.on('click', (evt: any) => {
        map.setCenter(evt.latLng)
        marker.updateGeometries({
            id: 'center',
            position: evt.latLng
        })
        latLngChange(evt.latLng.lat, evt.latLng.lng)
    })

    latLngChange(center.lat, center.lng)
}

const storeArea = reactive({
    province_id: 0,
    city_id: 0,
    district_id: 0
})

const latLngChange = (lat: number, lng: number) => {
    latLngToAddress({ mapKey, lat, lng }).then(({ message, result }) => {
        if (message == 'query ok' || message == 'Success') {
            formData.latitude = result.location.lat
            formData.longitude = result.location.lng
            formData.address = result.formatted_addresses.recommend

            getAreaByCode(result.ad_info.adcode).then(({ data }) => {
                storeArea.province_id = data.province ? data.province.id : 0
                storeArea.city_id = data.city ? data.city.id : 0
                storeArea.district_id = data.district ? data.district.id : 0
            })
        } else {
            console.error(message, result)
        }
    }).catch(err => {
        console.log(err)
    })
}

/**
 * 表单数据
 */
const initialFormData = {
    store_id: 0,
    store_name: '',
    store_desc: '',
    store_logo: '',
    store_mobile: '',
    province_id: 0,
    province_name: '',
    city_id: 0,
    city_name: '',
    district_id: 0,
    district_name: '',
    address: '',
    full_address: '',
    longitude: 116.397190,
    latitude: 39.908626,
    trade_time: '',
    time_week: ['1', '2', '3', '4', '5', '6', '0'],
    trade_time_json: [
        { start_time: '', end_time: '' } // 初始一个时间段
    ],
    time_interval: 30
}

const formData: Record<string, any> = reactive({ ...initialFormData })

// 添加时间段
const addTimeRange = () => {
    formData.trade_time_json.push({ start_time: '', end_time: '' })
}

// 删除时间段
const removeTimeRange = (index: number) => {
    formData.trade_time_json.splice(index, 1)
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

const setFormData = async (id: number = 0) => {
    loading.value = true
    Object.assign(formData, initialFormData)
    const data = await (await getStoreInfo(id)).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
        if (key == "trade_time_json" && Array.isArray(data[key])) {
            formData[key] = data[key].map((item: any) => ({
                start_time: timestampTransition(item.start_time),
                end_time: timestampTransition(item.end_time)
            }))
        }
    })
    loading.value = false
}

if (id) setFormData(id)

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        store_name: [
            { required: true, message: t('storeNamePlaceholder'), trigger: 'blur' }
        ],
        store_logo: [
            { required: true, message: t('storeLogoPlaceholder'), trigger: 'blur' }
        ],
        store_mobile: [
            { required: true, message: t('storeMobilePlaceholder'), trigger: 'blur' }
        ],
        trade_time: [
            { required: true, message: t('tradeTimePlaceholder'), trigger: 'blur' }
        ],
        address_area: [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (!formData.province_id) {
                        callback(new Error(t('provincePlaceholder')))
                    }
                    if (!formData.city_id) {
                        callback(new Error(t('cityPlaceholder')))
                    }
                    if (areaList.district.length && !formData.district_id) {
                        callback(new Error(t('districtPlaceholder')))
                    }
                    callback()
                }
            }
        ],
        address: [
            { required: true, message: t('addressPlaceholder'), trigger: 'blur' }
        ],
        time_week: [
            { required: true, message: t('selectBusinessDays'), trigger: 'change' }
        ],
        trade_time_json: [
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
        ]
    }
})

/**
 * 获取市
 */
watch(() => formData.province_id, (nval) => {
    if (nval) {
        getAreaListByPid(formData.province_id).then(res => {
            areaList.city = res.data

            const cityId = formData.city_id
            if (cityId) {
                let isExist = false
                for (let i = 0; i < res.data.length; i++) {
                    if (cityId == res.data[i].id) {
                        isExist = true
                        break
                    }
                }
                if (isExist) {
                    formData.city_id = cityId
                    return
                }
            }
            formData.city_id = 0
            areaChange()
        })
    } else {
        formData.city_id = 0
    }
})

/**
 * 获取区
 */
watch(() => formData.city_id, (nval) => {
    if (nval) {
        getAreaListByPid(formData.city_id).then(res => {
            areaList.district = res.data

            const districtId = formData.district_id
            if (districtId) {
                let isExist = false
                for (let i = 0; i < res.data.length; i++) {
                    if (districtId == res.data[i].id) {
                        isExist = true
                        break
                    }
                }
                if (isExist) {
                    formData.district_id = districtId
                    return
                }
            }
            areaChange()
            formData.district_id = 0
        })
    } else {
        formData.district_id = 0
    }
})

watch(() => formData.district_id, (nval) => {
    if (nval) {
        areaChange()
    }
})

const areaChange = debounce(() => {
    setTimeout(() => {
        const address = [
            formData.province_id ? provinceRef.value.states.selectedLabel : '',
            formData.city_id ? cityRef.value.states.selectedLabel : '',
            formData.district_id ? districtRef.value.states.selectedLabel : '',
            formData.address
        ]

        addressToLatLng({ mapKey, address: address.join('') }).then(({ message, result }) => {
            if (message == 'Success' || message == 'query ok') {
                const latLng = new (window as any).TMap.LatLng(result.location.lat, result.location.lng)
                map.setCenter(latLng)
                marker.updateGeometries({
                    id: 'center',
                    position: latLng
                })
                formData.latitude = result.location.lat
                formData.longitude = result.location.lng
            } else {
                console.error(message, result)
            }
        })
    }, 500)
}, 500)

/**
 * 地图点选获取市
 */
watch(() => storeArea.province_id, (nval) => {
    if (nval) {
        getAreaListByPid(storeArea.province_id).then(res => {
            areaList.city = res.data
            formData.province_id = storeArea.province_id
            formData.city_id = storeArea.city_id
        })
    }
})

/**
 * 地图点选获取区
 */
watch(() => storeArea.city_id, (nval) => {
    if (nval) {
        getAreaListByPid(storeArea.city_id).then(res => {
            areaList.district = res.data
            formData.city_id = storeArea.city_id
            formData.district_id = storeArea.district_id
        })
    }
})

/**
 * 地图点选获取区
 */
watch(() => storeArea.district_id, (nval) => {
    if (nval) {
        formData.district_id = storeArea.district_id
    }
})

const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async(valid) => {
        if (valid) {
            loading.value = true

            const data = cloneDeep(formData)
            formData.province_name = formData.province_id ? provinceRef.value.states.selectedLabel : ''
            formData.city_name = formData.city_id ? cityRef.value.states.selectedLabel : ''
            formData.district_name = formData.district_id ? districtRef.value.states.selectedLabel : ''
            const address = [
                data.province_id ? provinceRef.value.states.selectedLabel : '',
                data.city_id ? cityRef.value.states.selectedLabel : '',
                data.district_id ? districtRef.value.states.selectedLabel : '',
                data.address
            ]
            data.full_address = address.join('')

            // **转换时间段为时间戳**
            data.trade_time_json = formData.trade_time_json.map(range => ({
                start_time: range.start_time ? timeTransition(range.start_time) : null,
                end_time: range.end_time ? timeTransition(range.end_time) : null
            }))

            const save = id ? editStore : addStore

            save(data).then(res => {
                loading.value = false
                history.back()
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

const back = () => {
    history.back()
}
</script>

<style lang="scss" scoped></style>
