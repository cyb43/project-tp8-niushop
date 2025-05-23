<template>
    <el-aside :class="['h-screen layout-aside w-auto h-screen', { 'bright': !dark }]">
        <side class="hidden-xs-only" />
    </el-aside>

    <el-drawer v-model="systemStore.menuDrawer" direction="ltr" :with-header="false" custom-class="aside-drawer" size="210px">
        <template #default>
            <side />
        </template>
    </el-drawer>
</template>

<script lang="ts" setup>
import { watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import useSystemStore from '@/stores/modules/system'
import side from './side.vue'

const route = useRoute()
const systemStore = useSystemStore()
const dark = computed(() => {
    return systemStore.dark
})

watch(route, () => {
    systemStore.$patch(state => {
        state.menuDrawer = false
    })
})
</script>

<style lang="scss">
.layout-aside {
    background-color: var(--side-dark-color, var(--el-bg-color));
    border-right: 1px solid var(--el-border-color-lighter);

    &.bright {

        li {

            &.is-active:not(.is-opened) {
                position: relative;
                color: var(--el-color-primary);
            }
        }
    }
}

.aside-drawer {
    .el-drawer__body {
        padding: 0px !important;
    }
}
</style>
