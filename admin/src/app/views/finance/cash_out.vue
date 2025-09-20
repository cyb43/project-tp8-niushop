<template>
    <!--会员提现-->
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none !px-[35px]" shadow="never">
                <el-row class="flex">
                    <el-col :span="12">
                        <div class="statistic-card">
                            <el-statistic :value="statistics.transfered ? statistics.transfered.toFixed(2) : '0.00'"></el-statistic>
                            <div class="statistic-footer">
                                <div class="footer-item text-[14px] text-[#666]">
                                    <span>{{ t('totalTransfered') }}</span>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="12">
                        <div class="statistic-card">
                            <el-statistic :value="statistics.cash_outing ? statistics.cash_outing.toFixed(2) : '0'"></el-statistic>
                            <div class="statistic-footer">
                                <div class="footer-item text-[14px] text-[#666]">
                                    <span>{{ t('totalCashOuting') }}</span>
                                </div>
                            </div>
                        </div>
                    </el-col>
                </el-row>
            </el-card>

            <el-card class="box-card !border-none mb-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="orderTableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('applyTime')" prop="create_time">
                        <el-date-picker v-model="orderTableData.searchParam.create_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item :label="t('cashOutNumber')" prop="cash_out_no">
                        <el-input v-model.trim="orderTableData.searchParam.cash_out_no" class="w-[240px]"
                            :placeholder="t('cashOutNumberPlaceholder')" />
                    </el-form-item>

                    <el-form-item :label="t('memberInfo')" prop="keywords">
                        <el-input v-model.trim="orderTableData.searchParam.keywords" class="!w-[230px]"
                            :placeholder="t('memberInfoPlaceholder')" />
                    </el-form-item>

                    <el-form-item :label="t('cashOutMethod')" prop="transfer_type">
                        <el-select v-model="orderTableData.searchParam.transfer_type" clearable class="input-width">
                            <el-option :label="t('selectPlaceholder')" value="" />
                            <el-option :label="item.name" :value="key" v-for="(item, key) in Transfertype" :key="key"/>
                        </el-select>
                    </el-form-item>

                    <el-form-item :label="t('cashOutStatus')" prop="status">
                        <el-select v-model="orderTableData.searchParam.status" clearable class="input-width">
                            <el-option :label="t('selectPlaceholder')" value="" />
                            <el-option :label="item" :value="key" v-for="(item, key) in cashOutStatusList" :key="key"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('auditTime')" prop="audit_time">
                        <el-date-picker v-model="orderTableData.searchParam.audit_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item :label="t('transferTime')" prop="transfer_time">
                        <el-date-picker v-model="orderTableData.searchParam.transfer_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadOrderList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="orderTableData.data" size="large" class="table-top">
                    <el-table-column :label="t('memberInfo')" min-width="180" />
                    <el-table-column :label="t('cashOutMethod')" align="center" min-width="100" />
                    <el-table-column :label="t('cashOutInfo')" min-width="180" />
                    <el-table-column :label="t('applicationForWithdrawalAmount')" align="center" min-width="120" />
                    <el-table-column :label="t('actualTransferAmount')" align="center" min-width="120" />
                    <el-table-column :label="t('cashOutCommission')" align="center" min-width="110" />
                    <el-table-column :label="t('cashOutStatus')" align="center" min-width="150" />
                    <el-table-column :label="t('applyTime')" align="center" min-width="160" />
                    <el-table-column :label="t('auditTime')" align="center" min-width="160" />
                    <el-table-column :label="t('transferTime')" align="center" min-width="160" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120" />
                </el-table>
                <div class="table-body min-h-[150px]" v-loading="orderTableData.loading">
                    <div v-if="!orderTableData.loading">
                        <template v-if="orderTableData.data.length">
                            <div v-for="(item, index) in orderTableData.data" :key="index">
                                <el-table :data="[item]" size="large" :show-header="false">
                                    <el-table-column :show-overflow-tooltip="true"  min-width="180">
                                        <template #default="{ row }">
                                            <div class="flex items-center cursor-pointer " @click="toMember(row.member.member_id)">
                                                <img class="w-[50px] h-[50px] mr-[10px]" v-if="row.member.headimg" :src="img(row.member.headimg)" alt="">
                                                <img class="w-[50px] h-[50px] mr-[10px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                                <div class="flex flex-col items-baseline" style="width: calc(100% - 60px);">
                                                    <span class="w-[100%] truncate text-left">{{ row.member.nickname || row.member.username || '' }}</span>
                                                    <span class="w-[100%] truncate">{{ row.member.mobile || '' }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column  align="center" min-width="100">
                                        <template #default="{ row }">
                                            {{ row.transfer_type_name }}
                                        </template>
                                    </el-table-column>
                                    <el-table-column  min-width="180">
                                        <template #default="{ row }">
                                            <div class="flex flex-col" v-if="row.transfer_type=='wechat_code' || row.transfer_type=='alipay'">
                                                <div class="flex items-center">
                                                    <span class="w-[70px] flex-shrink-0 text-right">{{t('realname') }}：</span>
                                                    <span class="using-hidden">{{ row.transfer_realname }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="w-[70px] flex-shrink-0 text-right">{{t('account') }}：</span>
                                                    <span>{{ row.transfer_account }}</span>
                                                </div>
                                                <div class="flex items-start" v-if="row.transfer_payment_code">
                                                    <span class="w-[70px] flex-shrink-0 text-right">{{ t('transferCode') }}：</span>
                                                    <el-image   :src="img(row.transfer_payment_code)" :preview-src-list="[img(row.transfer_payment_code)]" :hide-on-click-modal="true" class="w-[50px] h-[50px]"></el-image>
                                                </div>
                                            </div>
                                            <div class="flex flex-col" v-else-if="row.transfer_type=='bank'">
                                                <span>{{t('bankRealname') }}：{{ row.transfer_realname }}</span>
                                                <span>{{ t('bankAccount') }}：{{ row.transfer_account }}</span>
                                                <span>{{ t('bankName') }}：{{ row.transfer_bank }}</span>
                                            </div>
                                            <div class="flex items-center" v-else-if="row.transfer_type=='wechatpay'">
                                                <img class="w-[50px] h-[50px] mr-[10px]" v-if="row.member.headimg" :src="img(row.member.headimg)" alt="">
                                                <img class="w-[50px] h-[50px] mr-[10px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                                <div class="flex flex-col items-baseline" style="width: calc(100% - 60px);">
                                                    <span class="w-[100%] truncate text-left">{{ row.member.nickname || row.member.username || '' }}</span>
                                                    <span class="w-[100%] truncate">{{ row.member.mobile || '' }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column prop="apply_money"  min-width="120" align="center" />

                                    <el-table-column prop="money" min-width="120" align="center" />

                                    <el-table-column prop="service_money" align="center" min-width="110" />

                                    <el-table-column prop="status_name" align="center" min-width="150">
                                        <template #default="{ row }">
                                            <div>{{ row.status_name }}</div>
                                            <div v-if="row.status == 2 && row.transfer_type == 'wechatpay'" class="text-[12px] text-[var(--el-color-success)]">(等待用户收款)</div>
                                        </template>
                                    </el-table-column>

                                    <el-table-column  min-width="160" align="center">
                                        <template #default="{ row }">
                                            {{ row.create_time || '' }}
                                        </template>
                                    </el-table-column>

                                    <el-table-column  min-width="160" align="center">
                                        <template #default="{ row }">
                                            {{ row.audit_time || '' }}
                                        </template>
                                    </el-table-column>

                                    <el-table-column  min-width="160" align="center">
                                        <template #default="{ row }">
                                            {{ row.transfer_time || '' }}
                                        </template>
                                    </el-table-column>

                                    <el-table-column align="right" fixed="right" width="120">
                                        <template #default="{ row }">
                                            <el-button  type="primary" link @click="successfulAuditFn(row)" v-if="row.status == 1"> {{ t('successfulAudit') }}</el-button>
                                            <el-button  type="primary" link @click="auditFailureFn(row)" v-if="row.status == 1"> {{ t('auditFailure') }}</el-button>
                                            <el-button  type="primary" link @click="memberCancelFn(row)" v-if="row.status == 1 || row.status == 2 || row.status == 4"> {{ t('cancelWithdrawal') }}</el-button>
                                            <el-button  type="primary" link @click="transferFn(row)" v-if="row.status == 2 && row.transfer_type !== 'wechatpay'"> {{ t('transfer') }}</el-button>
                                            <el-button  type="primary" link @click="detailFn(row.id)"> {{ t('detail') }}</el-button>
                                            <el-button  type="primary" link @click="handleRemark(row)"> {{ t('remark') }}</el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <div v-if="item.remark" class="text-[14px] min-h-[30px] leading-[30px] px-3 bg-[#fff0e5] text-[#ff7f5b] mb-[10px] relative remark">
                                    <span class="mr-[5px]">{{ t('notes') }}：</span>
                                    <span>{{ item.remark }}</span>
                                </div>
                            </div>
                        </template>
                        <el-empty v-else :image-size="1" :description="t('emptyData')" />
                    </div>
                </div>

                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="orderTableData.page" v-model:page-size="orderTableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="orderTableData.total"
                        @size-change="loadOrderList()" @current-change="loadOrderList" />
                </div>
            </div>
        </el-card>

        <!-- 详情 -->
        <el-dialog v-model="cashOutShowDialog" :title="t('cashOutDetail')" width="650px" :destroy-on-close="true">
            <el-form :model="cashOutInfo" label-width="120px" ref="formRef"  class="page-form" v-loading="cashOutLoading">
                <el-row>
                    <el-col :span="12">
                        <el-form-item :label="t('nickname')">
                            <div class="input-width"> {{ cashOutInfo.nickname|| cashOutInfo.username }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutAccountType')">
                            <div class="input-width"> {{ cashOutInfo.account_type_name }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutMethod')">
                            <div class="input-width"> {{ Transfertype[cashOutInfo.transfer_type].name }} </div>
                        </el-form-item>
                    </el-col>
                    <template v-if="cashOutInfo.transfer_type == 'alipay' || cashOutInfo.transfer_type == 'wechat_code'">
                        <el-col :span="12">
                            <el-form-item :label="t('realname')">
                                <div class="input-width"> {{ cashOutInfo.transfer_realname }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('alipayAccount')">
                                <div class="input-width"> {{ cashOutInfo.transfer_account }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('transferCode')">
                                <el-image  :src="img(cashOutInfo.transfer_payment_code)" :preview-src-list="[img(cashOutInfo.transfer_payment_code)]" :hide-on-click-modal="true" class="mr-[10px] w-[50px] h-[50px]"></el-image>
                            </el-form-item>
                        </el-col>
                    </template>
                    <template v-if="cashOutInfo.transfer_type == 'bank'">
                        <el-col :span="12">
                            <el-form-item :label="t('bankName')">
                                <div class="input-width"> {{ cashOutInfo.transfer_bank }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('bankAccount')">
                                <div class="input-width"> {{ cashOutInfo.transfer_account }} </div>
                            </el-form-item>
                        </el-col>
                    </template>
                    <el-col :span="12">
                        <el-form-item :label="t('applicationForWithdrawalAmount')">
                            <div class="input-width"> {{ cashOutInfo.apply_money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutCommission')">
                            <div class="input-width"> {{ cashOutInfo.service_money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('actualTransferAmount')">
                            <div class="input-width"> {{ cashOutInfo.money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutStatus')">
                            <div class="input-width"> {{ cashOutInfo.status_name }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('applyTime')">
                            <div class="input-width"> {{ cashOutInfo.create_time }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('auditTime')">
                            <div class="input-width"> {{ cashOutInfo.audit_time }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12" v-if="cashOutInfo.remark">
                        <el-form-item :label="t('remark')">
                            <div class="input-width"> {{ cashOutInfo.remark }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12" v-if="cashOutInfo.transfer && cashOutInfo.transfer.transfer_voucher">
                        <el-form-item :label="t('transferVoucher')">
                            <el-image  :src="img(cashOutInfo.transfer.transfer_voucher)" :preview-src-list="[img(cashOutInfo.transfer.transfer_voucher)]" :hide-on-click-modal="true" class="w-[50px] h-[50px]"></el-image>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12" v-if="cashOutInfo.transfer && cashOutInfo.transfer.transfer_remark">
                        <el-form-item :label="t('transferRemark')">
                            <div class="input-width"> {{ cashOutInfo.transfer.transfer_remark }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12" v-if="cashOutInfo.refuse_reason">
                        <el-form-item :label="t('remark')">
                            <div class="input-width"> {{ cashOutInfo.refuse_reason }} </div>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>

            <template #footer>
                <span class="dialog-footer">
                    <el-button type="primary" @click="cashOutShowDialog = false">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
        <!-- 审核通过 -->
        <el-dialog v-model="auditPassShowDialog" :title="t('passAudit')" width="650px" :destroy-on-close="true">
            <el-form :model="curData" label-width="120px" ref="formRef"  class="page-form">
                <el-row>
                    <el-col :span="12">
                        <el-form-item :label="t('nickname')">
                            <div class="input-width"> {{ curData.member.nickname ||curData.member.username }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutAccountType')">
                            <div class="input-width"> {{ curData.account_type_name }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutMethod')">
                            <div class="input-width"> {{ curData.transfer_type_name }} </div>
                        </el-form-item>
                    </el-col>
                    <template v-if="curData.transfer_type == 'alipay' || curData.transfer_type == 'wechat_code'">
                        <el-col :span="12">
                            <el-form-item :label="t('realname')">
                                <div class="input-width"> {{ curData.transfer_realname }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('account')">
                                <div class="input-width"> {{ curData.transfer_account }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('transferCode')">
                                <el-image  :src="img(curData.transfer_payment_code)" :preview-src-list="[img(curData.transfer_payment_code)]" :hide-on-click-modal="true" class="w-[50px] h-[50px]"></el-image>
                            </el-form-item>
                        </el-col>
                    </template>
                    <template v-if="curData.transfer_type == 'bank'">
                        <el-col :span="12">
                            <el-form-item :label="t('bankName')">
                                <div class="input-width"> {{ curData.transfer_bank }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('bankRealname')">
                                <div class="input-width"> {{ curData.transfer_realname }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('bankAccount')">
                                <div class="input-width"> {{ curData.transfer_account }} </div>
                            </el-form-item>
                        </el-col>
                    </template>
                    <el-col :span="12">
                        <el-form-item :label="t('applicationForWithdrawalAmount')">
                            <div class="input-width"> {{ curData.apply_money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutCommission')">
                            <div class="input-width"> {{ curData.service_money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('actualTransferAmount')">
                            <div class="input-width"> {{ curData.money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('applyTime')">
                            <div class="input-width"> {{ curData.create_time }} </div>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="auditPassShowDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary"  @click="handlePass()">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
        <!-- 是否审核拒绝 -->
        <el-dialog v-model="auditShowDialog" :title="t('rejectionAudit')" width="500px" :destroy-on-close="true">
            <el-form :model="auditFailure" label-width="90px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
                <el-form-item :label="t('reasonsRefusal')" prop="refuse_reason">
                    <el-input v-model.trim="auditFailure.refuse_reason" clearable maxlength="200" :show-word-limit="true" :placeholder="t('reasonsRefusalPlaceholder')" :rows="4" class="input-width" type="textarea" />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="auditShowDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" :loading="loading" @click="confirm()">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <!-- 是否转账 -->
        <el-dialog v-model="transferShowDialog" :title="t('transfer')" width="650px" :destroy-on-close="true">
            <el-form :model="transferData" label-width="120px" ref="formRef"  class="page-form">
                <el-row>
                    <template v-if="transferData.transfer_type == 'alipay' || transferData.transfer_type == 'wechat_code'">
                        <el-col :span="12">
                            <el-form-item :label="t('realname')">
                                <div class="input-width"> {{ transferData.transfer_realname }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('account')">
                                <div class="input-width"> {{ transferData.transfer_account }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('transferCode')">
                                <el-image  :src="img(transferData.transfer_payment_code)" :preview-src-list="[img(transferData.transfer_payment_code)]" :hide-on-click-modal="true" class="w-[50px] h-[50px]"></el-image>
                            </el-form-item>
                        </el-col>
                    </template>
                    <template v-if="transferData.transfer_type == 'bank'">
                        <el-col :span="12">
                            <el-form-item :label="t('bankName')">
                                <div class="input-width"> {{ transferData.transfer_bank }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('bankRealname')">
                                <div class="input-width"> {{ transferData.transfer_realname }} </div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item :label="t('bankAccount')">
                                <div class="input-width"> {{ transferData.transfer_account }} </div>
                            </el-form-item>
                        </el-col>
                    </template>
                    <el-col :span="12">
                        <el-form-item :label="t('applicationForWithdrawalAmount')">
                            <div class="input-width"> {{ transferData.apply_money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('cashOutCommission')">
                            <div class="input-width"> {{ transferData.service_money }} </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item :label="t('actualTransferAmount')">
                            <div class="input-width"> {{ transferData.money }} </div>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-form>
            <el-form :model="formTransfer" label-width="120px" ref="formTransferRef" :rules="formTransferRules" class="page-form">
                <el-form-item :label="t('transferVoucher')" prop="transfer_voucher">
                    <upload-image v-model="formTransfer.transfer_voucher" :limit="1" />
                </el-form-item>
                <el-form-item :label="t('transferRemark')" prop="transfer_remark">
                    <el-input v-model.trim="formTransfer.transfer_remark" type="textarea" rows="4" clearable
                        :placeholder="t('transferRemarkPlaceholder')" class="input-width" maxlength="200" show-word-limit />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="transferShowDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="handleTransfer(formTransferRef)">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
        <!-- 备注 -->
        <el-dialog v-model="remarkShowDialog" :title="t('remark')" width="500px" :destroy-on-close="true">
            <el-form :model="formData" label-width="90px" ref="formRemarkRef" :rules="formRemarkRules" class="page-form">
                <el-form-item :label="t('remark')" prop="remark">
                    <el-input v-model.trim="formData.remark" type="textarea" rows="4" clearable
                        :placeholder="t('remarkPlaceholder')" class="input-width" maxlength="200" show-word-limit />
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="remarkShowDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="save(formRemarkRef)">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed } from 'vue'
import { t } from '@/lang'
import { getCashOutList, getTransfertype, memberTransfer, memberAudit, getCashOutDetail, getCashOutStatusList, getCashOutStat, memberRemark, memberCheck, memberCancel } from '@/app/api/member'
import { img } from '@/utils/common'
import { ElMessageBox, FormInstance, FormRules } from 'element-plus'
import { useRouter, useRoute } from 'vue-router'

const cashOutStatusList = ref([])
const checkStatusList = async () => {
    cashOutStatusList.value = await (await getCashOutStatusList()).data
}
checkStatusList()
const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

// 表单验证规则
const formRules = reactive<FormRules>({})

const orderTableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        order_no: '',
        member_id: 0,
        create_time: [],
        status: '',
        cash_out_no: '',
        keywords: '',
        audit_time: '',
        transfer_time: '',
        transfer_type: ''
    }
})

const statistics = ref({
    transfered: 0,
    cash_outing: 0
})
const checkStatInfo = () => {
    getCashOutStat().then(res => {
        statistics.value = res.data
    })
}
checkStatInfo()

// 获取会员转账方式
const Transfertype = ref<Array<Object|any>>([])
const getTransfertypeFn = async () => {
    Transfertype.value = await (await getTransfertype()).data
}
getTransfertypeFn()

const searchFormRef = ref<FormInstance>()
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return

    formEl.resetFields()
    loadOrderList()
}

/**
 * 获取提现列表
 */
const loadOrderList = (page: number = 1) => {
    orderTableData.loading = true
    orderTableData.page = page

    getCashOutList({
        page: orderTableData.page,
        limit: orderTableData.limit,
        ...orderTableData.searchParam
    }).then(res => {
        orderTableData.loading = false
        orderTableData.data = res.data.data
        orderTableData.total = res.data.total
    }).catch(() => {
        orderTableData.loading = false
    })
}
loadOrderList()

/**
 * 转账
 * @param data
 */
const transferData = ref<any>({})
const transferShowDialog = ref(false)
const formTransferRef = ref<FormInstance>()
const formTransfer = reactive<any>({
    id: 0,
    transfer_voucher: '',
    transfer_remark: ''
})
const formTransferRules = computed(() => {
    return {
        transfer_voucher: [
            { required: true, message: t('transferVoucherPlaceholder'), trigger: 'blur' }
        ]
    }
})

const transferFn = (data:any) => {
    transferData.value = data
    formTransfer.id = data.id
    transferShowDialog.value = true
}
const handleTransfer = async (formEl: FormInstance | undefined) => {
    if (!formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            memberTransfer({ ...formTransfer }).then(res => {
                transferShowDialog.value = false
                loadOrderList()
            }).catch(() => {
                transferShowDialog.value = false
                loadOrderList()
            })
        }
    })
}

/**
 * 详情
 * @param data
 */
const cashOutShowDialog = ref(false)
const cashOutInfo = ref({
    nickname: '',
    account_type_name: '',
    transfer_type: '',
    apply_money: 0,
    service_money: 0,
    money: 0,
    status_name: ''
})
const cashOutLoading = ref(true)
const detailFn = (id:any) => {
    getCashOutDetail(id).then(res => {
        cashOutInfo.value = res.data
        cashOutShowDialog.value = true
        cashOutLoading.value = false
    }).catch(() => {
        loadOrderList()
    })
}

/**
 *  提现审核
 * @param data
 */

const auditPassShowDialog = ref(false)
const curData = ref<any>({})

// 审核成功弹框
const successfulAuditFn = (data: any) => {
    curData.value = data
    auditPassShowDialog.value = true
}
const handlePass = () => {
    const obj = {
        id: curData.value.id,
        action: 'agree'
    }
    cashOutAuditFn(obj)
}

/**
 *  拒绝审核
 */

const auditFailure = ref({ refuse_reason: '', id: 0, action: '' })
const auditShowDialog = ref(false)
const loading = ref(false)

const auditFailureFn = (data: any) => {
    auditFailure.value.id = data.id
    auditFailure.value.action = 'refuse'
    auditShowDialog.value = true
}
const confirm = () => {
    auditShowDialog.value = false
    cashOutAuditFn(auditFailure.value)
}

const repeat = ref(false)
const cashOutAuditFn = (data:any) => {
    if (repeat.value) return
    repeat.value = true
    memberAudit({
        ...data
    }).then(res => {
        repeat.value = false
        auditPassShowDialog.value = false
        loadOrderList()
    }).catch(() => {
        repeat.value = false
        auditPassShowDialog.value = false
        loadOrderList()
    })
}

// 取消提现
const memberCancelFn = (data: any) => {
    ElMessageBox.confirm(t('cancelTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        memberCancel({ id: data.id }).then((res) => {
            loadOrderList()
        })
    })
}

/**
 * 备注
 */

const formRemarkRef = ref<FormInstance>()
const remarkShowDialog = ref(false)
const formData = reactive({
    id: 0,
    remark: ''
})
const formRemarkRules = computed(() => {
    return {
        remark: [
            { required: true, message: t('remarkPlaceholder'), trigger: 'blur' }
        ]
    }
})
const handleRemark = (data: any) => {
    formData.id = data.id
    formData.remark = ''
    remarkShowDialog.value = true
}
const save = async (formEl: FormInstance | undefined) => {
    if (!formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            memberRemark(formData).then((res: any) => {
                loadOrderList()
                remarkShowDialog.value = false
            }).catch(() => {
                remarkShowDialog.value = false
            })
        }
    })
}
/**
 * 会员详情
 */
const toMember = (memberId: number) => {
    router.push(`/member/detail?id=${memberId}`)
}

// 检测打款进度
const checkFn = (id: number) => {
    memberCheck(id).then(res => {

    })
}
</script>

<style lang="scss" scoped>
.table-top :deep(.el-table__body-wrapper) {
	display: none;
}

:deep(.el-table) {
	--el-table-row-hover-bg-color: var(--el-transfer-border-color);
}
.remark{
    &::after{
        content: '';
        border-bottom: solid 1px var(--el-border-color-light);
        width: 100%;
        position: absolute;
        bottom: -10px;
        left: 0;
    }
}
</style>
