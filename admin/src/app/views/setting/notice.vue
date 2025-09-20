<template>
    <!--消息模板-->
    <div class="main-container" v-loading="noticeTableData.loading">
        <el-card class="box-card !border-none" shadow="never">
            <h3 class="panel-title !text-sm">{{ t('buyerNotice') }}</h3>

            <div class="flex flex-row flex-wrap">
                <el-table :data="noticeTableData.buyer" size="large" :span-method="buyerSpan">
                    <el-table-column prop="addon_name" :label="t('addon')" min-width="120" />
                    <el-table-column prop="name" :label="t('noticeType')" min-width="120" />
                    <el-table-column :label="t('operation')" align="right" fixed="right" min-width="300">
                        <template #default="{ row }">
                            <div class="flex">
                                <div class="text-sm mr-1 flex items-center cursor-pointer" v-if="row.support_type.indexOf('sms') != -1" @click="setNotice(row, 'sms')">
                                    <el-icon class="text-[15px] mr-[3px]" :class="row.is_sms ? 'open' : ''">
                                        <SuccessFilled />
                                    </el-icon>
                                    <span class="ml-0.5">{{ t('sms') }}</span>
                                </div>
                                <div class="text-sm  flex items-center cursor-pointer ml-[20px]" v-if="row.support_type.indexOf('wechat') != -1" @click="setNotice(row, 'wechat')">
                                    <el-icon class="text-[15px] mr-[3px]" :class="row.is_wechat ? 'open' : ''">
                                        <SuccessFilled />
                                    </el-icon>
                                    <span class="ml-0.5">{{ t('wechat') }}</span>
                                </div>
                                <div class="text-sm  flex items-center cursor-pointer ml-[20px]" v-if="row.support_type.indexOf('weapp') != -1" @click="setNotice(row, 'weapp')">
                                    <el-icon class="text-[15px] mr-[3px]" :class="row.is_weapp ? 'open' : ''">
                                        <SuccessFilled />
                                    </el-icon>
                                    <span class="ml-0.5">{{ t('weapp') }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <h3 class="panel-title !text-sm">{{ t('sellerNotice') }}</h3>

            <div class="flex flex-row flex-wrap">
                <el-table :data="noticeTableData.seller" size="large" :span-method="buyerSpan">
                    <el-table-column prop="addon_name" :label="t('addon')" min-width="120" />
                    <el-table-column prop="name" :label="t('noticeType')" min-width="120" />
                    <el-table-column :label="t('operation')" align="right" fixed="right" min-width="300">
                        <template #default="{ row }">
                            <div class="flex">
                                <div class="text-sm mr-1 flex items-center cursor-pointer" v-if="row.support_type.indexOf('sms') != -1" @click="setNotice(row, 'sms')">
                                    <el-icon class="text-[15px] mr-[3px]" :class="row.is_sms ? 'open' : ''">
                                        <SuccessFilled />
                                    </el-icon>
                                    <span class="ml-0.5">{{ t('sms') }}</span>
                                </div>
                                <div class="text-sm  flex items-center cursor-pointer ml-[20px]" v-if="row.support_type.indexOf('wechat') != -1" @click="setNotice(row, 'wechat')">
                                    <el-icon class="text-[15px] mr-[3px]" :class="row.is_wechat ? 'open' : ''">
                                        <SuccessFilled />
                                    </el-icon>
                                    <span class="ml-0.5">{{ t('wechat') }}</span>
                                </div>
                                <div class="text-sm  flex items-center cursor-pointer ml-[20px]" v-if="row.support_type.indexOf('weapp') != -1" @click="setNotice(row, 'weapp')">
                                    <el-icon class="text-[15px] mr-[3px]" :class="row.is_weapp ? 'open' : ''">
                                        <SuccessFilled />
                                    </el-icon>
                                    <span class="ml-0.5">{{ t('weapp') }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-card>

        <sms ref="smsDialog" @complete="loadNoticeList()" />
        <wechat ref="wechatDialog" @complete="loadNoticeList()" />
        <weapp ref="weappDialog" @complete="loadNoticeList()" />

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getNoticeList } from '@/app/api/notice'
import Sms from '@/app/views/setting/components/notice-sms.vue'
import Wechat from '@/app/views/setting/components/notice-wechat.vue'
import Weapp from '@/app/views/setting/components/notice-weapp.vue'

const smsDialog : Record<string, any> | null = ref(null)
const wechatDialog : Record<string, any> | null = ref(null)
const weappDialog : Record<string, any> | null = ref(null)

const noticeTableData = reactive({
    loading: true,
    buyer: [],
    seller: []
})

/**
 * 获取配置信息
 */
const loadNoticeList = () => {
    noticeTableData.loading = true

    getNoticeList({}).then(res => {
        noticeTableData.buyer = []
        noticeTableData.seller = []
        res.data.forEach(item => {
            if (item.notice.length) {
                const buyer = []; const seller = []
                Object.keys(item.notice).forEach((key, index) => {
                    const notice = item.notice[key]
                    notice.addon_name = item.title
                    notice.receiver_type == 1 ? buyer.push(notice) : seller.push(notice)
                })
                if (buyer.length) {
                    buyer[0].rowspan = buyer.length
                    noticeTableData.buyer = noticeTableData.buyer.concat(buyer)
                }
                if (seller.length) {
                    seller[0].rowspan = seller.length
                    noticeTableData.seller = noticeTableData.seller.concat(seller)
                }
            }
        })
        noticeTableData.loading = false
    }).catch((e) => {
        noticeTableData.loading = false
    })
}

const buyerSpan = (row : any) => {
    if (row.columnIndex === 0) {
        if (row.row.rowspan) {
            return {
                rowspan: row.row.rowspan,
                colspan: 1
            }
        } else {
            return {
                rowspan: 0,
                colspan: 0
            }
        }
    }
}

loadNoticeList()

const setNotice = (data : any, type : string) => {
    data.type = type
    data.status = data['is_' + type]
    if (type === 'sms') {
        smsDialog.value.setFormData(data)
        smsDialog.value.showDialog = true
    } else if (type === 'wechat') {
        wechatDialog.value.setFormData(data)
        wechatDialog.value.showDialog = true
    } else if (type === 'weapp') {
        weappDialog.value.setFormData(data)
        weappDialog.value.showDialog = true
    }
}
</script>

<style lang="scss" scoped>
    .open {
        color: var(--el-color-primary);
    }

    .notice-type {
        >div:nth-last-child(1):first-child {
            width: 100%;
        }
    }
</style>
