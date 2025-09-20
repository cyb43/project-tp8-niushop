<template>
    <el-drawer v-model="showDialog" title="核销记录详情" direction="rtl" :before-close="handleClose" size="1300px">
        <div class="main-container" v-loading="loading">
            <el-tabs v-model="activeName" class="pb-[10px]" @tab-change="handleClick">
                <el-tab-pane label="核销信息" name="verifyInfo" />
                <el-tab-pane label="商品信息" name="goodsInfo" />
            </el-tabs>
            <div v-if="activeName == 'verifyInfo'">
                <div class="text-[14px] min-w-[110px] border-solid border-l-[3px] border-[var(--el-color-primary)] pl-[5px]">核销信息</div>
                <el-row>
                    <el-col :span="8">
                        <div class="flex items-center mt-[15px]">
                            <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销类型') }}</span>
                            <span class="text-[14px] text-[#666666]">
                                {{ verifyData.type_name }}
                            </span>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <div class="flex items-center mt-[15px]">
                            <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销状态') }}</span>
                            <span class="text-[14px] text-[#666666]">
                                已核销
                            </span>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <div class="flex items-center mt-[15px]">
                            <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销人员') }}</span>
                            <span class="text-[14px] text-[#666666]">
                                {{ (verifyData.is_admin == 1 ? '后台核销' : verifyData.member?.nickname) || '--' }}
                            </span>
                        </div>
                    </el-col>
                    <el-col :span="8">
                        <div class="flex items-center mt-[15px]">
                            <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ t('核销时间') }}</span>
                            <span class="text-[14px] text-[#666666]">
                                {{verifyData.create_time}}
                            </span>
                        </div>
                    </el-col>

                    <template v-for="(item,index) in verifyContentData.fixed">
                        <el-col :span="8">
                            <div class="flex items-center mt-[15px]" v-if="item.title">
                                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ item.title }}</span>
                                <span class="text-[14px] text-[#666666]">
                                    {{ item.value }}
                                </span>
                            </div>
                        </el-col>
                    </template>
                    <template v-for="(item,index) in verifyData.verify_info">
                        <el-col :span="8" v-for="(val,key) in item">
                            <div class="flex items-center mt-[15px]" v-if="val.name">
                                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ val.name }}</span>
                                <span class="text-[14px] text-[#666666]">
                                    {{ val.value }}
                                </span>
                            </div>
                        </el-col>
                    </template>
                </el-row>
                <template v-for="(item,index) in verifyContentData.diy">
                    <div class="text-[14px] min-w-[110px] border-solid border-l-[3px] border-[var(--el-color-primary)] pl-[5px] mt-[20px]">{{item.title}}</div>
                    <el-row>
                        <el-col  :span="8" v-for="(subItem,subIndex) in item.list" :key="subIndex">
                            <div class="flex items-center mt-[15px]">
                                <span class="text-[14px] w-[130px] text-right mr-[20px]">{{ subItem.title }}</span>
                                <span class="text-[14px] text-[#666666]">
                                    {{ subItem.value }}
                                </span>
                            </div>
                        </el-col>
                    </el-row>
                </template>
            </div>
            <div v-if="activeName == 'goodsInfo'">
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
            </div>
        </div>
    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getVerifyDetail } from '@/app/api/verify'
import { ElMessage } from 'element-plus'
import { useRouter } from 'vue-router'
import { img } from '@/utils/common'

const showDialog = ref(false)
const loading = ref(true)
const router = useRouter()

const activeName = ref('verifyInfo')

const handleClick = (data:string) => {
    activeName.value = data
}

const handleClose = (done: () => void) => {
    showDialog.value = false;
}

// 获取核销信息
let code: any = ''
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

const setFormData = async (row: any = null) => {
    code = row.code;
    await getVerifyDetailFn();
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
