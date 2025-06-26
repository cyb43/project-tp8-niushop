<template>
    <div class="main-container" >
        <el-card class="box-card !border-none" shadow="never" v-loading="loading">
            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>
            <div class="flex flex-wrap px-2 plug-list pb-10 mt-[20px] ">
                <div class="flex items-center  p-3 w-[295px] relative plug-item mr-4 mb-4 bg-[var(--el-color-info-light-9)] cursor-pointer">
                    <div class="flex flex-col ml-2">
                        <span class="text-sm truncate w-[190px]">{{t('dataCache')}}</span>
                        <span class="text-xs text-gray-400 mt-1 truncate w-[190px]" :title="t('dataCacheDesc')">{{t('dataCacheDesc')}}</span>
                    </div>
                    <span class="plug-item-operate" @click="schemaCache()">{{t('refresh')}}</span>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import { clearCache } from '@/app/api/sys'
import { ElMessageBox } from 'element-plus'
import { useRoute } from 'vue-router'

const loading = ref<Boolean>(false)
const route = useRoute()
const pageName = route.meta.title

// 数据缓存
const schemaCache = () => {
    ElMessageBox.confirm(t('clearCacheTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        loading.value = true
        clearCache({}).then(res => {
            loading.value = false
        }).catch(() => {
            loading.value = false
        })
    })
}

</script>

<style lang="scss" scoped>
.demo-tabs > .el-tabs__content {
    padding: 32px;
    color: #6b778c;
    font-size: 32px;
    font-weight: 600;
}
.plug-item{
    .plug-item-operate{
        @apply text-xs absolute right-3 cursor-pointer;
        color: var(--el-color-primary);
    }
}
</style>
