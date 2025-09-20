<template>
    <!--短信设置-->
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <div class="mt-[20px]">
                <el-table :data="smsTableData.data" size="large" v-loading="smsTableData.loading">
                    <template #empty>
                        <span>{{ !smsTableData.loading ? t('emptyData') : '' }}</span>
                    </template>

                    <el-table-column prop="name" :label="t('name')" min-width="100" :show-overflow-tooltip="true"/>
                    <el-table-column :label="t('isUse')" min-width="180" align="center">
                        <template #default="{ row }">
                            <el-tag class="ml-2" type="success" v-if="row.is_use == 1">{{ t('statusNormal') }}</el-tag>
                            <el-tag class="ml-2" type="error" v-if="row.is_use == 0">{{t('statusDeactivate') }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" align="right" fixed="right" width="100">
                       <template #default="{ row, $index }">
                           <el-button type="primary" link @click="editEvent(row, $index)">{{ t('config') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
            </div>

            <template v-for="(item, index) in smsTableData.data">
                <component :is="item.component" :ref="(el) => setSmsTypeRefs(el, index)" v-if="item.component" @complete="loadSmsList()"/>
            </template>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { defineAsyncComponent, reactive, ref } from 'vue'
import { t } from '@/lang'
import { getSmsList, getAccountIsLogin } from '@/app/api/notice'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
const pageName = route.meta.title
const smsTypeRefs = ref([])

const smsTableData = reactive({
    loading: true,
    data: []
})

const modules: any = import.meta.glob('@/**/*.vue')
/**
 * 获取配置信息
 */
const loadSmsList = () => {
    smsTableData.loading = true
    getSmsList().then(({ data }) => {
        Object.keys(data).forEach((key: string) => {
            data[key].component && (data[key].component = defineAsyncComponent(modules[data[key].component]))
        })
        smsTableData.data = data
        smsTableData.loading = false
    }).catch(() => {
        smsTableData.loading = false
    })
}

const setSmsTypeRefs = (el, index) => {
    smsTypeRefs.value[index] = (el)
}

loadSmsList()
const editEvent = (data: any, index: number) => {
    if (data.sms_type == 'niuyun') {
        getAccountIsLogin().then((res: any) => {
            router.push('/setting/niusms/setting')
        })
    } else {
        smsTypeRefs.value[index].setFormData(data)
        smsTypeRefs.value[index].showDialog = true
    }
}

</script>

<style lang="scss" scoped></style>
