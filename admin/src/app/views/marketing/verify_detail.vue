<template>
    <!--核销详情-->
    <div class="main-container" v-loading="loading">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <h3 class="panel-title !text-sm">{{ t('核销信息') }}</h3>

            <div class="flex items-center mt-[15px]">
                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销类型') }}</span>
                <span class="text-[14px] text-[#666666]">
                    {{ verifyData.type_name }}
                </span>
            </div>
            <div class="flex items-center mt-[15px]">
                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销状态') }}</span>
                <span class="text-[14px] text-[#666666]">
                    已核销
                </span>
            </div>

            <div class="flex items-center mt-[15px]">
                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销人员') }}</span>
                <span class="text-[14px] text-[#666666]">
                    {{ verifyData.member ? verifyData.member.nickname : '--' }}
                </span>
            </div>
            <div class="flex items-center mt-[15px]">
                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销时间') }}</span>
                <span class="text-[14px] text-[#666666]">
                    {{verifyData.create_time}}
                </span>
            </div>
            <div class="flex items-center mt-[15px]" v-for="(item,index) in verifyContentData.fixed" :key="index">
                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ item.title }}</span>
                <span class="text-[14px] text-[#666666]">
                    {{ item.value }}
                </span>
            </div>

            <div v-for="(item,index) in verifyData.verify_info" :key="index">
                <div class="flex items-center mt-[15px]" v-for="(val,key) in item" :key="key">
                    <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ val.name }}</span>
                    <span class="text-[14px] text-[#666666]">
                        {{ val.value }}
                    </span>
                </div>
            </div>

        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never" v-for="(item,index) in verifyContentData.diy" :key="index">
            <h3 class="panel-title !text-sm">{{ item.title }}</h3>

            <div class="flex items-center mt-[15px]" v-for="(subItem,subIndex) in item.list" :key="subIndex">
                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ subItem.title }}</span>
                <span class="text-[14px] text-[#666666]">
                    {{ subItem.value }}
                </span>
            </div>
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <h3 class="panel-title !text-sm">{{ t('商品信息') }}</h3>

            <el-table :data="verifyGoodsList" size="large">
                <el-table-column :label="t('商品名称')" align="left" width="300">
                    <template #default="{ row }">
                        <div class="flex">
                            <div class="flex items-center shrink-0">
                                <img class="w-[50px] h-[50px] mr-[10px]" :src="img(row.cover)" />
                            </div>
                            <div class="flex flex-col">
                                <p class="multi-hidden text-[14px]">{{ row.name }}</p>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="num" :label="t('数量')" min-width="50" align="right" />
            </el-table>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { useRouter, useRoute } from 'vue-router'
import { getVerifyDetail } from '@/app/api/verify'
import { ElMessage } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { img } from '@/utils/common'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const back = () => {
    router.push('/marketing/verify')
}

const loading = ref(true)

// 获取核销信息
const code: any = route.query.code
const verifyData: any = ref({})
const verifyContentData: any = ref({})
const verifyGoodsList: any = ref([])

const getVerifyDetailFn = async () => {
    loading.value = true
    if (code) {
        const data = await (await getVerifyDetail(code)).data
        if (!data || Object.keys(data).length == 0) {
            ElMessage.error(t('memberNull'))
            setTimeout(() => {
                router.go(-1)
            }, 2000)
            return false
        }
        verifyData.value = data
        verifyContentData.value = data.value.content || {}
        verifyGoodsList.value = data.value.list || []
        loading.value = false
    } else {
        loading.value = false
    }
}
getVerifyDetailFn()
</script>

<style lang="scss" scoped></style>
