<template>
    <view class="danmu-container">
        <barrage-item v-for="(item, index) in danmuList" :key="`${item.id}-${item.timestamp}`" :data="item" :delay="item.delay" :speed="item.speed"
        :textColor="props.textColor" :bgColor="props.bgColor" :rounded="props.rounded" />
    </view>
</template>

<script setup>
import { ref, onMounted, computed,nextTick, watch, onUpdated, onUnmounted } from 'vue'
import barrageItem from '@/components/barrage/barrage-item.vue'
const props = defineProps({
    bgColor: {
        type: String,
        default: '#B0B0B0'
    },
    textColor: {
        type: String,
        default: '#FFFFFF'
    },
    rounded: {
        type: Number,
        default: 20
    },
    maxCount: {
        type: Number,
        default: 6
    },
    speed: {
        type: Number,
        default: 6
    },
    interval: {
        type: Number,
        default: 1.5
    },
    data: {
        type: Object,   
        default: []
    },
    play:{ // 是否播放弹幕,true:播放,false:不播放
        type: Boolean,
        default: true
    }
})

const danmuList = ref([]) // 当前弹幕列表
const waitList = ref([]) // 等待弹幕列表
const initTimeId = ref([]) // 初始化时间id
const loopTimeId = ref(null) // 初始化时间id


// 初始化弹幕数据
const initDanmu = () => {
    nextTick(() => {
        danmuList.value = props.data.map((item, index) => {
            let obj = {}
            const timestamp = Date.now() + Math.random()
            obj.id = item.id ? `danmu-${item.id}-${timestamp}` : `danmu-${timestamp}`
            obj.timestamp = timestamp
            obj.nickname = item.nickname
            obj.headimg = item.headimg
            obj.time = formatTimestampToHumanTime(item.time)
            obj.delay = index * props.interval,
            obj.speed = props.speed
            return obj
        })
        
        danmuList.value.forEach((item, index) => {
            initTimeId.value[index] = setTimeout(() => {
                waitList.value.push(item)
                danmuList.value.shift();
                loopTriggerFn()
            }, (item.delay + item.speed) * 1000)
        }) 
    })
}
// 循环触发
const loopTriggerFn = () => {
    if (!danmuList.value.length) {
        loopTimeId.value = setTimeout(() => {
            // 创建新的数据对象，避免组件复用
            danmuList.value = waitList.value.map(item => {
                const timestamp = Date.now() + Math.random()
                return {
                    ...item,
                    id: `${item.id}-loop-${timestamp}`,
                    timestamp: timestamp
                }
            })
            waitList.value = []
            danmuList.value.forEach((item, index) => {
                initTimeId.value[index] = setTimeout(() => {
                    waitList.value.push(item)
                    danmuList.value.shift();
                    loopTriggerFn()
                }, (item.delay + item.speed) * 1000)
            })
        }, 1000)
    }
}
watch(() => props.play, (newVal, oldVal) => {
    nextTick(() => {
        if (newVal) {
            initTimeId.value.forEach((item, index) => {
                if (item) {
                    clearTimeout(item)
                }
            })
            clearTimeout(loopTimeId.value)
            waitList.value = []
            danmuList.value = []
            initDanmu()
        }else{
            initTimeId.value.forEach((item, index) => {
                if (item) {
                    clearTimeout(item)
                }
            })
            clearTimeout(loopTimeId.value)
            waitList.value = []
            danmuList.value = []
        } 
    })
})

onMounted(() => {
    initDanmu()
})

/**
* 将时间戳转换为最合适的可读时间单位（分钟、小时、天等），并舍去小数部分
* @param {number} timestamp - 时间戳（毫秒）
* @returns {string} 人类可读的相对时间字符串
*/
const formatTimestampToHumanTime = (timestamp) => {
    const now = Date.now();
    let seconds = Math.round((now - timestamp) / 1000); // 保留时间差为秒数，四舍五入

    // 如果是未来的时间
    if (seconds < 0) {
        return '未来';
    }

    const intervals = [
        { label: '年', seconds: 31536000 },   // 60 * 60 * 24 * 365
        { label: '个月', seconds: 2592000 },  // 60 * 60 * 24 * 30
        { label: '周', seconds: 604800 },     // 60 * 60 * 24 * 7
        { label: '天', seconds: 86400 },      // 60 * 60 * 24
        { label: '小时', seconds: 3600 },     // 60 * 60
        { label: '分钟', seconds: 60 }
    ];

    for (let i = 0; i < intervals.length; i++) {
        const interval = intervals[i];
        const count = Math.floor(seconds / interval.seconds); // 舍去小数部分
        if (count >= 1) {
            return `${count}${interval.label}前`;
        }
    }

    return '刚刚';
}
</script>

<style scoped>
.danmu-container {
    position: fixed;
    top: 160rpx;
    left: 0;
    height: 500rpx;
    width: 100%;
    pointer-events: none;
    z-index: 9;
    overflow: hidden;
}
</style>