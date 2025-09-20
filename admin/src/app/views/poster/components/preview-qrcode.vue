<template>
    <div class="pointer-events-none max-w-[720px]" :style="componentStyle">
        <img :src="img('static/resource/images/diy/qrcode.png')" class="w-full h-full" />
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
    return prop.value;
})

const componentStyle = computed(() => {
    let style = '';
    style += `width: ${ prop.value.width }px;`;
    return style;
})

defineExpose({})

</script>

<style lang="scss" scoped></style>
