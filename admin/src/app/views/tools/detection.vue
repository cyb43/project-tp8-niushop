<template>
    <!--环境检测-->
    <div class="main-container min-h-[70vh]" v-loading="loading">
        <el-card class="box-card !border-none" shadow="never" v-if="Object.keys(systemService).length">

            <div>
                <h3 class="panel-title !text-sm">{{ t('serverInformation') }}</h3>

                <div class="text-[14px]">
                    <el-table :data="systemService.server" size="large">
                        <el-table-column prop="name" :label="t('environment')" align="left" min-width="200" />
                        <el-table-column prop="server" :label="t('version')" align="left" min-width="140" />
                    </el-table>
                </div>
            </div>

            <div class="mt-[20px]">
                <h3 class="panel-title !text-sm">{{ t('systemDemand') }}</h3>

                <div class="text-[14px]">
                    <el-table :data="systemService.server_version" size="large">
                        <el-table-column prop="name" :label="t('environment')" align="left" min-width="200" />
                        <el-table-column prop="demand" :label="t('demand')" align="left" min-width="140" />
                        <el-table-column prop="server" :label="t('version')" align="left" min-width="140" />
                    </el-table>
                </div>
            </div>

            <div class="mt-[20px]">
                <h3 class="panel-title !text-sm">{{ t('authorityStatus') }}</h3>

                <div class="text-[14px]">
                    <el-table :data="systemService.system_variables" size="large">
                        <el-table-column prop="name" :label="t('name')" align="left" min-width="200" />
                        <el-table-column prop="need" :label="t('demand')" align="left" min-width="140" />
                        <el-table-column :label="t('status')" align="left" min-width="140">
                            <template #default="{ row }">
                                <span v-if="row.status"><el-icon color="green"><Select /></el-icon></span>
                                <span v-else>
                                    <el-icon color="red">
                                        <CloseBold />
                                    </el-icon>
                                </span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>

            <div class="mt-[20px]">
                <h3 class="panel-title !text-sm">{{ t('process') }}</h3>

                <div class="text-[14px]">
                    <el-table :data="systemService.process" size="large">
                        <el-table-column prop="name" :label="t('name')" align="left" min-width="200" />
                        <el-table-column prop="need" :label="t('demand')" align="left" min-width="140" />
                        <el-table-column :label="t('status')" align="left" min-width="140">
                            <template #default="{ row }">
                                <span v-if="row.status"><el-icon color="green"><Select /></el-icon></span>
                                <span v-else>
                                    <el-icon color="red">
                                        <CloseBold />
                                    </el-icon>
                                </span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import { getSystem } from '@/app/api/tools'

const systemService = ref({})
const loading = ref(true)
const getSystemService = () => {
    getSystem().then(res => {
        systemService.value = res.data
        loading.value = false
    })
}
getSystemService()
</script>

<style lang="scss" scoped></style>
