<template>
    <u-popup :show="show" @close="show = false" mode="bottom" :round="10">
        <view @touchmove.prevent.stop class="popup-common">
            <view class="title">请选择地区</view>
            <view class="flex p-[30rpx] pt-[0] text-sm font-500">
                <view v-if="areaList.province.length" class="flex-1 pr-[10rpx]" :class="{'text-[var(--primary-color)]': currSelect == 'province'}" @click="currSelect = 'province'">
                    <view v-if="selected.province">{{ selected.province.name }}</view>
                    <view v-else>请选择</view>
                </view>
                <view v-if="areaList.city.length" class="flex-1 pr-[10rpx]" :class="{'text-[var(--primary-color)]': currSelect == 'city' }" @click="currSelect = 'city'">
                    <view v-if="selected.city">{{ selected.city.name }}</view>
                    <view v-else>请选择</view>
                </view>
                <view v-if="areaList.district.length" class="flex-1 pr-[10rpx]" :class="{'text-[var(--primary-color)]': currSelect == 'district' }" @click="currSelect = 'district'">
                    <view v-if="selected.district">{{ selected.district.name }}</view>
                    <view v-else>请选择</view>
                </view>
                <view class="flex-1 pr-[10rpx]" v-else></view>
            </view>
            <scroll-view scroll-y="true" class="h-[50vh]" :scroll-top="scrollTop" scroll-with-animation @touchmove.stop>
                <view class="flex p-[30rpx] pt-[0] text-sm font-500 h-[50vh]">
                    <view v-if="areaList.province.length" class="flex-1 pr-[10rpx]" :style="{ opacity: currSelect == 'province' ? 1 : 0, pointerEvents: currSelect == 'province' ? 'auto' : 'none',height: currSelect == 'province' ? 'auto' : '0',overflow: currSelect == 'province' ? 'auto' : 'hidden' }">
                        <view v-for="(item, index) in areaList.province" :key="item.id" class="h-[80rpx] flex items-center" :class="{'text-[var(--primary-color)]': selected.province && selected.province.id == item.id }" @click="handleProvinceClick(item)">{{ item.name }}</view>
                    </view>
                    <view v-if="areaList.city.length" class="flex-1 pr-[10rpx]" :style="{ opacity: currSelect == 'city' ? 1 : 0, pointerEvents: currSelect == 'city' ? 'auto' : 'none',height: currSelect == 'city' ? 'auto' : '0',overflow: currSelect == 'city' ? 'auto' : 'hidden' }">
                        <view v-for="(item, index) in areaList.city" :key="item.id" class="h-[80rpx] flex items-center" :class="{'text-[var(--primary-color)]': selected.city && selected.city.id == item.id }" @click="handleCityClick(item)">{{ item.name }}</view>
                    </view>
                    <view v-if="areaList.district.length" class="flex-1 pr-[10rpx]" :style="{ opacity: currSelect == 'district' ? 1 : 0, pointerEvents: currSelect == 'district' ? 'auto' : 'none',height: currSelect == 'district' ? 'auto' : '0',overflow: currSelect == 'district' ? 'auto' : 'hidden' }">
                        <view v-for="(item, index) in areaList.district" :key="item.id" class="h-[80rpx] flex items-center " :class="{'text-[var(--primary-color)]': selected.district && selected.district.id == item.id }" @click="selected.district = item">{{ item.name }}</view>
                    </view>
                    <view class="flex-1 pr-[10rpx]" v-else></view>
                </view>
            </scroll-view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref, reactive, watch, nextTick } from 'vue'
import { getAreaListByPid, getAreaByCode } from '@/app/api/system'

const prop = defineProps({
    areaId: {
        type: Number,
        default: 0
    }
})

const show = ref(false)
const areaList = reactive({
    province: [],
    city: [],
    district: []
})
const currSelect = ref('province')

const selected = reactive({
    province: null,
    city: null,
    district: null
})

// 滚动控制
const scrollTop = ref(0)

getAreaListByPid(0).then(({ data }) => {
    areaList.province = data
}).catch()

watch(() => prop.areaId, (nval, oval) => {
    if (nval && !oval) {
        getAreaByCode(nval).then(({ data }) => {
            data.province && (selected.province = data.province)
            data.city && (selected.city = data.city)
            data.district && (selected.district = data.district)
        })
    }
}, {
    immediate: true
})

/**
 * 监听省变更
 */
watch(() => selected.province, () => {
    getAreaListByPid(selected.province.id).then(({ data }) => {
        areaList.city = data
        currSelect.value = 'city'

        if (selected.city) {
            let isExist = false
            let selectedIndex = -1
            for (let i = 0; i < data.length; i++) {
                if (selected.city.id == data[i].id) {
                    isExist = true
                    selectedIndex = i
                    break
                }
            }
            if (!isExist) {
                selected.city = null
            } else {
                // 滚动到选中的城市位置
                setTimeout(() => {
                    scrollToSelected('city', selectedIndex)
                }, 100)
            }
        }
    }).catch()
}, { deep: true })

/**
 * 监听市变更
 */
watch(() => selected.city, (nval) => {
    if (nval) {
        getAreaListByPid(selected.city.id).then(({ data }) => {
            areaList.district = data
            currSelect.value = 'district'

            if (selected.district) {
                let isExist = false
                let selectedIndex = -1
                for (let i = 0; i < data.length; i++) {
                    if (selected.district.id == data[i].id) {
                        isExist = true
                        selectedIndex = i
                        break
                    }
                }
                if (!isExist) {
                    selected.district = null
                } else {
                    // 滚动到选中的区县位置
                    setTimeout(() => {
                        scrollToSelected('district', selectedIndex)
                    }, 100)
                }
            }
			if (!data.length) {
			    emits('complete', selected)
			    show.value = false
			}

        }).catch()
    } else {
        areaList.district = []
        selected.district = null
    }

}, { deep: true })

const emits = defineEmits(['complete'])

// 滚动到选中项位置
const scrollToSelected = (type: string, selectedIndex: number) => {
    // 计算目标位置，让选中项显示在列表的下半部分
    const itemHeight = 80 // 每项高度 80rpx
    const targetScrollTop = Math.max(0, (selectedIndex - 2) * itemHeight) // 让选中项显示在第3个位置左右
    scrollTop.value = targetScrollTop
}

// 重置滚动位置
const resetScrollTop = () => {
    scrollTop.value = 0
}

// 监听当前选择标签变化，重置滚动位置并滚动到选中项
watch(() => currSelect.value, (newVal) => {
    // 先重置滚动位置
    resetScrollTop()
    
    setTimeout(() => {
        if (newVal === 'province' && selected.province) {
            const index = areaList.province.findIndex((item: any) => item.id === selected.province.id)
            if (index >= 0) {
                scrollToSelected('province', index)
            }
        } else if (newVal === 'city' && selected.city) {
            const index = areaList.city.findIndex((item: any) => item.id === selected.city.id)
            if (index >= 0) {
                scrollToSelected('city', index)
            }
        } else if (newVal === 'district' && selected.district) {
            const index = areaList.district.findIndex((item: any) => item.id === selected.district.id)
            if (index >= 0) {
                scrollToSelected('district', index)
            }
        }
    }, 150)
})

/**
 * 监听区县变更
 */
watch(() => selected.district, (nval) => {
    if (nval) {
        currSelect.value = 'district'
        emits('complete', selected)
        show.value = false
    }
}, { deep: true })

// 处理省份点击
const handleProvinceClick = (item: any) => {
    selected.province = item
    // 立即滚动到顶部，确保新数据可见
    nextTick(() => {
        resetScrollTop()
    })
}

// 处理城市点击
const handleCityClick = (item: any) => {
    selected.city = item
    // 立即滚动到顶部，确保新数据可见
    nextTick(() => {
        resetScrollTop()
    })
}

const open = () => {
    show.value = true
    if(prop.areaId){
        currSelect.value = 'district'
    }
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped></style>
