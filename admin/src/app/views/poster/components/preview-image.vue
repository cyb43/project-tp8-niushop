<template>
    <div class="pointer-events-none max-w-[720px]" :style="componentStyle">
        <img v-if="data.value" :src="img(data.value)" class="w-full h-full" />
        <img v-else :src="img('static/resource/images/diy/crack_figure.png')" class="w-full h-full" />
    </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { img } from '@/utils/common'

const prop = defineProps({
    value: {
        type: Object,
        default: {}
    }
})

const data = computed(() => {
    return prop.value
})

const componentStyle = computed(() => {
    let style = ''
    style += `width: ${prop.value.width}px;`
    return style
})

defineExpose({})

</script>

<style lang="scss" scoped></style>
