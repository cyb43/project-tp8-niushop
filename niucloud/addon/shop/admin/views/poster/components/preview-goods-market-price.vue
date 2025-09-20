<template>
    <div class="overflow-hidden" :style="componentStyle">￥1999.00</div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'

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
    style += `font-size: ${prop.value.fontSize}px;color: ${prop.value.fontColor};line-height: ${prop.value.lineHeight + prop.value.fontSize}px;`
    if (prop.value.x == 'left' || prop.value.x == 'center' || prop.value.x == 'right') {
        style += `text-align: ${prop.value.x};`
    }
    if (prop.value.weight) {
        style += 'font-weight: bold;'
    }
    if (!prop.value.fontFamily || prop.value.fontFamily == 'static/font/OPLUSSANS3-REGULAR.ttf') {
        style += 'font-family: poster_price_font;'
    }
    const box: any = document.getElementById(prop.value.id)
    if (box) {
        style += `width:${box.offsetWidth}px;height:${box.offsetHeight}px;`
    } else {
        style += `width:${prop.value.width}px;height:${prop.value.height}px;`
    }
    return style
})

defineExpose({})

</script>

<style lang="scss" scoped></style>
