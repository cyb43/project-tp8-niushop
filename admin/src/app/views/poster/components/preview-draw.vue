<template>
    <div class="overflow-hidden" :style="componentStyle"></div>
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
    style += `background-color: ${prop.value.bgColor};`
    style += `width: ${prop.value.width}px;height: ${prop.value.height}px;`
    const box: any = document.getElementById(prop.value.id)
    if (box) {
        style += `width:${box.offsetWidth}px;height:${box.offsetHeight}px;`
    }
    return style
})

defineExpose({})

</script>

<style lang="scss" scoped></style>
