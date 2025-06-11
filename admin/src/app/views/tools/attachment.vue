<template>
    <div class="main-container attachment-container">
        <el-card class="box-card !border-none full-container" shadow="never">

            <div class="flex justify-between items-center mb-[20px]">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-tabs v-model="type" tab-position="top">
                <el-tab-pane :label="t(tab)" v-for="(tab, index) in attachmentType" :name="tab" :key="index">
                    <attachment scene="attachment" :type="tab" />
                </el-tab-pane>
            </el-tabs>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import attachment from '@/components/upload-attachment/attachment.vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const pageName = route.meta.title
const attachmentType: string[] = ['image', 'video', 'icon']
const type = ref(attachmentType[0])

</script>

<style lang="scss">
.attachment-container {
    overflow: hidden;
    min-height: calc(100vh - 94px);
    background-color: var(--el-bg-color-overlay);

    .full-container {
        height: calc(100vh - 100px);
    }

    .el-card__body {
        height: 100%;
    }

    .el-tabs {
        display: flex;
        flex-direction:  column;
        height: calc(100% - 40px);
    }

    .el-tabs__content {
        flex: 1;

        .el-tab-pane {
            height: 100%;
        }
    }

    .el-tabs__nav-wrap::after {
        height: 1px;
    }

    .main-wrap {
        border: none;

        .group-wrap {
            padding: 0 15px 0 0;
        }

        .attachment-list-wrap {
            padding: 0 0 0 15px;
        }
    }
}
</style>
