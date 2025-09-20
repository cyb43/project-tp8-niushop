
<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
                <el-button type="primary" @click="manualBackupEvent">
                    {{ t('manualBackup') }}
                </el-button>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('content')" prop="content">
                        <el-input v-model.trim="tableData.searchParam.content" :placeholder="t('contentPlaceholder')" />
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="loadList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="tableRef" @selection-change="handleSelectionChange">

                <template #empty>
                    <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                </template>

                <el-table-column type="selection" width="55" />
                <el-table-column prop="id" :label="t('id')" width="120" />
                <el-table-column prop="content" :label="t('操作')" width="120" align="left">
                    <template #default="{ row }">
                        <span v-if="row.content=='手动备份'" class="multi-hidden">
                            <el-tag type="primary">{{ row.content }}</el-tag>
                        </span>
                        <span v-else-if="row.content=='自动备份'" class="multi-hidden">
                            <el-tag type="success">{{ row.content }}</el-tag>
                        </span>
                        <span v-else class="multi-hidden">
                            <el-tag type="warning">{{ row.content }}</el-tag>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="version" :label="t('currentVersion')" width="120" />
                <el-table-column prop="backup_dir" :label="t('backupDir')" width="220" />
                <el-table-column prop="complete_time" :label="t('completeTime')" width="220" />
                <el-table-column prop="remark" :label="t('remark')">
                    <template #default="{ row }">
                        <span v-if="row.remark" class="multi-hidden">{{ row.remark }}</span>
                        <span v-else>{{ t('remarkEmpty') }}</span>
                    </template>
                </el-table-column>
                <el-table-column :label="t('operation')" align="right" fixed="right" width="200">
                    <template #default="{ row }">
                        <el-button type="primary" link @click="remarkEvent(row)">{{ t('remark') }}</el-button>
                        <el-button type="primary" link @click="restoreEvent(row)">{{ t('restore') }}</el-button>
                        <el-button type="primary" link @click="deleteEvent(row.id)">{{ t('delete') }}</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="mt-[10px] flex items-center">
                <el-button @click="batchDelete" size="small">{{ t('batchDelete') }}</el-button>
            </div>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="tableData.page"
                    v-model:page-size="tableData.limit" layout="total, sizes, prev, pager, next, jumper"
                    :total="tableData.total" @size-change="loadList()"
                    @current-change="loadList" />
            </div>
        </el-card>

        <el-dialog v-model="showDialog" :title="iSBackupRecovery == 1 ? t('manualBackupTitle') : t('restoreTitle')" width="850px" :close-on-click-modal="false" :close-on-press-escape="false" :show-close="true" :before-close="dialogClose">

            <el-steps :active="numberOfSteps" align-center class="number-of-steps" finish-status="success" process-status="process" v-if="active !='error' && active !='complete'">
                <template v-if="iSBackupRecovery == 1">
                    <!-- 手动备份 -->
                    <el-step :title="t('testDirectoryPermissions')" />
                    <el-step :title="t('startBackUp')" />
                    <el-step :title="t('backUpEnd')" />
                </template>
                <template v-else>
                    <!-- 恢复 -->
                    <el-step :title="t('testDirectoryPermissions')" />
                    <el-step :title="t('startUpgrade')" />
                    <el-step :title="t('upgradeEnd')" />
                </template>
            </el-steps>

            <div class="h-[400px]" style="overflow: auto">

                <!-- 检测目录权限 -->
                <div class="flex flex-col" v-show="active == 'check'">
                    <el-scrollbar>
                        <div class="bg-[#fff] my-3">
                            <div class="px-[20px] pt-[10px] text-[14px] el-table">
                                <el-row class="py-[10px] items table-head-bg pl-[15px] mb-[10px]">
                                    <el-col :span="18">
                                        <span>{{ t("upgrade.path") }}</span>
                                    </el-col>
                                    <el-col :span="3">
                                        <span>{{ t("upgrade.demand") }}</span>
                                    </el-col>
                                    <el-col :span="3">
                                        <span>{{ t("status") }}</span>
                                    </el-col>
                                </el-row>

                                <div style="height: calc(300px); overflow: auto" v-if="upgradeCheck && upgradeCheck.dir">
                                    <el-row class="pb-[10px] items pl-[15px]" v-for="item in upgradeCheck.dir.is_readable">
                                        <el-col :span="18">
                                            <span>{{ item.dir }}</span>
                                        </el-col>
                                        <el-col :span="3">
                                            <span :class="{ 'mx-[10px]' : (upgradeCheck.dir.is_readable.length + upgradeCheck.dir.is_write.length) > 9 }">{{ t("upgrade.readable") }}</span>
                                        </el-col>
                                        <el-col :span="3">
                                            <span v-if="item.status" :class="{ 'mx-[20px]' : (upgradeCheck.dir.is_readable.length + upgradeCheck.dir.is_write.length) > 9 }">
                                                <el-icon color="green">
                                                    <Select />
                                                </el-icon>
                                            </span>
                                            <span v-else :class="{ 'mx-[20px]' : (upgradeCheck.dir.is_readable.length + upgradeCheck.dir.is_write.length) > 9 }">
                                                <el-icon color="red">
                                                    <CloseBold />
                                                </el-icon>
                                            </span>
                                        </el-col>
                                    </el-row>
                                    <el-row class="pb-[10px] items pl-[15px]" v-for="item in upgradeCheck.dir.is_write">
                                        <el-col :span="18">
                                            <span>{{ item.dir }}</span>
                                        </el-col>
                                        <el-col :span="3">
                                            <span :class="{ 'mx-[10px]' : (upgradeCheck.dir.is_readable.length + upgradeCheck.dir.is_write.length) > 9 }">{{ t("upgrade.write") }}</span>
                                        </el-col>
                                        <el-col :span="3">
                                            <span v-if="item.status" :class="{ 'mx-[20px]' : (upgradeCheck.dir.is_readable.length + upgradeCheck.dir.is_write.length) > 9 }">
                                                <el-icon color="green">
                                                    <Select />
                                                </el-icon>
                                            </span>
                                            <span v-else :class="{ 'mx-[20px]' : (upgradeCheck.dir.is_readable.length + upgradeCheck.dir.is_write.length) > 9 }">
                                                <el-icon color="red">
                                                    <CloseBold />
                                                </el-icon>
                                            </span>
                                        </el-col>
                                    </el-row>
                                </div>
                                <div v-else>
                                    <div v-loading="true" style="height: calc(300px); overflow: auto"></div>
                                </div>
                            </div>
                        </div>
                    </el-scrollbar>
                </div>

                <!-- 执行任务 -->
                <div class="h-[370px] mt-[30px]" v-show="active == 'execute'">
                    <terminal ref="terminalRef" :name="`backup-restore-${terminalId}`" context="" :init-log="null" :show-header="false" :show-log-time="true" @exec-cmd="onExecCmd"/>
                </div>

                <!-- 完成 -->
                <div class="mt-[20px]" v-show="active == 'complete'">
                    <el-result icon="success" :title="iSBackupRecovery == 1 ? t('backupCompleteTips') : t('restoreCompleteTips')" :sub-title="iSBackupRecovery == 1 ?`备份耗时${formattedDuration}，成功备份完成。` : `恢复耗时${formattedDuration}，成功恢复完成。`">
                        <template #icon>
                            <img src="@/app/assets/images/success_icon.png" alt="">
                        </template>
                        <template #extra>
                            <el-button @click="handleReturn" class="!w-[90px]">返回</el-button>
                            <el-button @click="showDialog=false" type="primary" class="!w-[90px]">完成</el-button>
                        </template>
                    </el-result>
                </div>
                <!-- 失败 -->
                <div class="mt-[20px] h-[370px]" v-show="active == 'error'">
                    <el-result icon="success" :title="iSBackupRecovery == 1 ? t('备份失败') : t('恢复失败')">
                        <template #icon>
                            <img src="@/app/assets/images/error_icon.png" alt="">
                        </template>
                        <template #extra>
                            <el-scrollbar class="max-h-[120px] !overflow-auto text-[15px] text-[#4F516D] mb-[15px] mt-[-15px]">
                                {{backupErrorMessage}}
                            </el-scrollbar>
                            <el-button @click="handleReturn" class="!w-[90px]">错误信息</el-button>
                            <el-button @click="showDialog=false" type="primary" class="!w-[90px]">完成</el-button>
                        </template>
                    </el-result>
                </div>
            </div>

            <template #footer>
                <div class="dialog-footer" v-if="active == 'check'">
                    <!-- 手动备份 -->
                    <el-button v-if="iSBackupRecovery == 1" type="primary" :loading="uploading" :disabled="isPass" @click="manualBackupFn()">{{ t("nextStep") }}</el-button>

                    <!-- 恢复 -->
                    <el-button v-else type="primary" :loading="uploading" :disabled="isPass" @click="restoreUpgradeBackupFn(currentId)">{{ t("nextStep") }}</el-button>
                </div>
                <div class="dialog-footer" v-if="active == 'execute'">
                    <el-button type="primary"  :loading="timeloading" class="!w-[140px]">已用时 {{ formattedDuration }}</el-button>
                </div>
            </template>

        </el-dialog>

        <el-dialog v-model="showRemarkDialog" :title="t('remark')" width="460px" :destroy-on-close="true">
            <el-form :model="formData" ref="formRef" class="page-form" v-loading="remarkLoading">
                <el-form-item class="mb-0">
                    <el-input v-model.trim="formData.remark" :rows="5" type="textarea" maxlength="200" show-word-limit />
                </el-form-item>
            </el-form>
            <template #footer>
            <span class="dialog-footer">
                <el-button @click="showRemarkDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="remarkLoading" @click="modifyRemarkFn()">{{ t('confirm') }}</el-button>
            </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, nextTick, watch, h, computed } from 'vue'
import { t } from '@/lang'
import { ElMessage, ElMessageBox, FormInstance } from 'element-plus'
import { useRoute } from 'vue-router'
import { checkDirExist, checkPermission, getBackupRecords, restoreUpgradeBackup, deleteRecords, modifyBackupRemark, manualBackup, performBackupTasks, performRecoveryTasks } from '@/app/api/upgrade'
import { Terminal, TerminalFlash } from 'vue-web-terminal'
import 'vue-web-terminal/lib/theme/dark.css'
import { AnyObject } from '@/types/global'

const route = useRoute()
const terminalId = ref(Date.now());
const pageName = route.meta.title
const searchFormRef = ref<FormInstance>()
const multipleSelection: any = ref([]) // 选中数据
const tableRef = ref()
const repeat = ref(false)
const cloudBuildTask = ref<null | AnyObject>(null)
const tableData: any = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        content: ''
    }
})

const showDialog: any = ref<boolean>(false)
const active = ref('check')
const interrupt: any = ref(false) // 是否中断
const upgradeCheck = ref<null | AnyObject>(null)
const terminalRef: any = ref(null)
const cloudBuildLog: any = []
let notificationEl: any = null
const isPass: any = ref(false)
const uploading: any = ref(false)
const numberOfSteps = ref(0)
const currentId: any = ref(0)
let backupContents: any = []
let restoreContents: any = []

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return

    formEl.resetFields()
    loadList()
}

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val
}

/**
 * 获取列表
 */
const loadList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page
    getBackupRecords({
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
    }).catch(() => {
        tableData.loading = false
    })
}

loadList()
const iSBackupRecovery = ref(0) // 1：手动备份 2：恢复

// 手动备份
const manualBackupEvent = () => {
    ElMessageBox.confirm(t('manualBackupTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        // if (repeat.value) return
        // repeat.value = true
        backupContents = []
        numberOfSteps.value = 0
        iSBackupRecovery.value = 1
        showDialog.value = true
        uploading.value = true
        active.value = 'check'
        interrupt.value = false
        checkPermissionFn()
    })
}
// 计时器相关
const timeloading = ref(false)
const buildStartTime = ref<number | null>(null)
const buildDuration = ref<number>(0)
let buildTimer: number | null = null
const formattedDuration = computed(() => {
    const seconds = buildDuration.value
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return mins > 0 ? `${mins}分${secs}秒` : `${secs}秒`
})
const backupErrorMessage = ref('')

// 进入执行备份
const manualBackupFn = (task: any = '') => {
    // 执行一半，中途取消，拦截执行
    if (interrupt.value) return

    if (task == '') {
        numberOfSteps.value = 1
        active.value = 'execute'
    }
    manualBackup({ task }).then((res: any) => {
        const data = res.data
        if (task == '') {
            terminalRef.value.execute('clear')
            terminalRef.value.execute('开始执行')
            timeloading.value = true
            const storedTime = localStorage.getItem('manual_back_start_time')
            if (storedTime) {
                buildStartTime.value = Number(storedTime)
            } else {
                const now = Date.now()
                buildStartTime.value = now
                localStorage.setItem('manual_back_start_time', String(now))
            }
            buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
            buildTimer && clearInterval(buildTimer)
            buildTimer = setInterval(() => {
                if (buildStartTime.value) {
                    buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
                }
            }, 1000)
        }
        if (data.content && !backupContents.includes(data.content)) {
            backupContents.push(data.content)
            terminalRef.value.pushMessage({ content: `${data.content}` })
        }
        if (data.task == 'end') {
            numberOfSteps.value = 2
            setTimeout(() => {
                numberOfSteps.value = 3
                active.value = 'complete'
                timeloading.value = true
                buildTimer && clearInterval(buildTimer) // 清除计时器
                localStorage.removeItem('manual_back_start_time')
                loadList()
                repeat.value = false
            }, 1500)
        } else if (data.task == 'fail') {
            // 恢复失败
            setTimeout(() => {
                loadList()
                repeat.value = false
            }, 2000)
            backupErrorMessage.value = data.content
            active.value = 'error'
            timeloading.value = true
            // 停止计时器
            if (buildTimer) {
                clearInterval(buildTimer)
                buildTimer = null
            }
            // 保证 duration 也被最后更新一次
            if (buildStartTime.value) {
                buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
            }
            localStorage.removeItem('manual_back_start_time')
        } else {
            // 延迟2秒请求，等待恢复数据加载完成
            setTimeout(() => {
                manualBackupFn(data.task)
            }, 2000)
        }
    }).catch(() => {
        repeat.value = false
        tableData.loading = false
    })
}

/**
 * 查询备份任务
*/
const getCloudBuildTaskFn = () => {
    performBackupTasks({}).then(({ data }) => {
        if (!data) return

        cloudBuildTask.value = data

        if (!showDialog.value && data.data && data.data.length > 0) {
            showElNotification()
        }
    }).catch()
}

// 注释获取任务 后续改
// getCloudBuildTaskFn()
/**
 * 备份中任务提示
 */
const showElNotification = () => {
    notificationEl = ElNotification.success({
        title: t('warning'),
        dangerouslyUseHTMLString: true,
        message: h('div', {}, [
            t('cloudbuild.executingTips'),
            h('span', { class: 'text-primary cursor-pointer', onClick: elNotificationClick }, [t('cloudbuild.clickView')])
        ]),
        duration: 0,
        showClose: false
    })
}

// 点击进入备份窗口
const elNotificationClick = () => {
    iSBackupRecovery.value = 1
    showDialog.value = true
    nextTick(() => {
        notificationEl && notificationEl.close()
        terminalRef.value.execute('clear')
        terminalRef.value.execute('开始执行')
        getCloudBuildLogFn()
    })
}

// 窗口list展示
const getCloudBuildLogFn = () => {
    performBackupTasks({}).then(({ data }) => {
        if (!data) return
        cloudBuildTask.value = data
        cloudBuildTask.value.data.forEach(item => {
            if (!cloudBuildLog.includes(item.content)) {
                terminalRef.value.pushMessage({ content: `${item.content}` })
                cloudBuildLog.push(item.content)
            }
        })

        const lastTask = data.data[data.data.length - 1].task
        if (lastTask === 'end' || data.data.length == 0) {
            setTimeout(() => {
                active.value = 'complete'
                loadList()
                repeat.value = false
            }, 1500)
        } else if (lastTask === 'fail') {
            // 恢复失败
            setTimeout(() => {
                loadList()
                repeat.value = false
            }, 2000)
        } else {
            setTimeout(() => {
                getCloudBuildLogFn()
            }, 2000)
        }
    })
}

// 恢复备份
const restoreEvent = (data: any) => {
    ElMessageBox.confirm(t('restoreTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        // if (repeat.value) return
        // repeat.value = true
        numberOfSteps.value = 0
        restoreContents = []
        iSBackupRecovery.value = 2
        currentId.value = data.id
        active.value = 'check'
        interrupt.value = false
        checkDirExistFn(data.id)
    })
}

// 检测目录是否存在
const checkDirExistFn = (id: any) => {
    checkDirExist({ id }).then(({ data }) => {
        if (data) {
            showDialog.value = true
            uploading.value = true
            checkPermissionFn()
        }
    })
}

// 检测目录权限
const checkPermissionFn = () => {
    checkPermission({}).then(({ data }) => {
        upgradeCheck.value = data
        isPass.value = !data.is_pass
        uploading.value = false
    })
}

// 执行恢复备份
const restoreUpgradeBackupFn = (id: any, task: any = '') => {
    // 执行一半，中途取消，拦截执行
    if (interrupt.value) return
    if (task == '') {
        numberOfSteps.value = 1
        active.value = 'execute'
    }

    restoreUpgradeBackup({
        id,
        task
    }).then((res: any) => {
        const data = res.data
        if (task == '') {
            uploading.value = false
            terminalRef.value.execute('clear')
            terminalRef.value.execute('开始执行')
            const storedTime = localStorage.getItem('manual_back_start_time')
            if (storedTime) {
                buildStartTime.value = Number(storedTime)
            } else {
                const now = Date.now()
                buildStartTime.value = now
                localStorage.setItem('manual_back_start_time', String(now))
            }
            buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
            buildTimer && clearInterval(buildTimer)
            buildTimer = setInterval(() => {
                if (buildStartTime.value) {
                    buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
                }
            }, 1000)
        }
        if (data.content && !restoreContents.includes(data.content)) {
            restoreContents.push(data.content)
            terminalRef.value.pushMessage({ content: `${data.content}` })
        }
        if (data.task == 'end') {
            numberOfSteps.value = 2
            setTimeout(() => {
                numberOfSteps.value = 3
                active.value = 'complete'
                buildTimer && clearInterval(buildTimer) // 清除计时器
                localStorage.removeItem('manual_back_start_time')
                loadList()
                repeat.value = false
            }, 1500)
        } else if (data.task == 'fail') {
            // 恢复失败
            setTimeout(() => {
                loadList()
                repeat.value = false
            }, 2000)
            backupErrorMessage.value = data.content
            active.value = 'error'
            // 停止计时器
            if (buildTimer) {
                clearInterval(buildTimer)
                buildTimer = null
            }
            // 保证 duration 也被最后更新一次
            if (buildStartTime.value) {
                buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
            }
            localStorage.removeItem('manual_back_start_time')
        } else {
            // 延迟2秒请求，等待恢复数据加载完成
            setTimeout(() => {
                restoreContents = []
                restoreUpgradeBackupFn(id, data.task)
            }, 2000)
        }
    }).catch(() => {
        repeat.value = false
        tableData.loading = false
    })
}

/**
 * 查询恢复任务
 */
const resumeUpgradeTasks = () => {
    performRecoveryTasks({}).then(({ data }) => {
        if (!data) return

        cloudBuildTask.value = data

        if (!showDialog.value && data.data && data.data.length > 0) {
            recoveryTaskPrompt()
        }
    }).catch()
}
// 注释获取任务 后续改
// resumeUpgradeTasks()
/**
 * 恢复中任务提示
 */

const recoveryTaskPrompt = () => {
    notificationEl = ElNotification.success({
        title: t('warning'),
        dangerouslyUseHTMLString: true,
        message: h('div', {}, [
            t('cloudbuild.executingTips'),
            h('span', { class: 'text-primary cursor-pointer', onClick: recoveryTaskPromptClick }, [t('cloudbuild.clickView')])
        ]),
        duration: 0,
        showClose: false
    })
}

// 点击任务提示进入窗口
const recoveryTaskPromptClick = () => {
    iSBackupRecovery.value = 2
    showDialog.value = true
    nextTick(() => {
        notificationEl && notificationEl.close()
        terminalRef.value.execute('clear')
        terminalRef.value.execute('开始执行')
        restoreTaskList()
    })
}

// 进入恢复窗口列表
const restoreTaskList = () => {
    performRecoveryTasks({}).then(({ data }) => {
        if (!data) return
        cloudBuildTask.value = data
        cloudBuildTask.value.data.forEach(item => {
            if (!cloudBuildLog.includes(item.content)) {
                terminalRef.value.pushMessage({ content: `${item.content}` })
                cloudBuildLog.push(item.content)
            }
        })

        const lastTask = data.data[data.data.length - 1].task
        if (lastTask === 'end' || data.data.length == 0) {
            setTimeout(() => {
                active.value = 'complete'
                loadList()
                repeat.value = false
            }, 1500)
        } else if (lastTask === 'fail') {
            // 恢复失败
            setTimeout(() => {
                loadList()
                repeat.value = false
            }, 2000)
        } else {
            setTimeout(() => {
                restoreTaskList()
            }, 2000)
        }
    })
}
const isBack = ref(false)
const handleReturn = () => {
    active.value = 'execute'
    isBack.value = true
}

const dialogClose = (done: () => {}) => {
    if (active.value == 'execute' && !isBack.value) {
        ElMessageBox.confirm(
            t('showDialogCloseTips'),
            t('warning'),
            {
                confirmButtonText: t('confirm'),
                cancelButtonText: t('cancel'),
                type: 'warning'
            }
        ).then(() => {
            terminalRef.value.execute('clear')
            interrupt.value = true // 执行一半，中途取消，需要恢复初始状态
            done()
            localStorage.removeItem('manual_back_start_time')
            buildTimer && clearInterval(buildTimer)
            buildTimer = null
            buildStartTime.value = null
            buildDuration.value = 0
        }).catch(() => {
        })
    } else {
        if (active.value == 'complete') {
            // 恢复备份需要等待恢复数据加载完成，延迟刷新页面
            setTimeout(() => {
                location.reload()
            }, 500)
        }
        done()
    }
}

/**
 * 升级进度动画
 */
let flashInterval: null | number = null
const terminalFlash = new TerminalFlash()
const onExecCmd = (key, command, success, failed, name) => {
    if (command == '开始执行') {
        success(terminalFlash)
        const frames = makeIterator(['/', '——', '\\', '|'])
        flashInterval = setInterval(() => {
            terminalFlash.flush('> ' + frames.next().value)
        }, 150)
    }
}

const makeIterator = (array: string[]) => {
    let nextIndex = 0
    return {
        next () {
            if ((nextIndex + 1) == array.length) {
                nextIndex = 0
            }
            return { value: array[nextIndex++] }
        }
    }
}

watch(() => showDialog.value, () => {
    if (!showDialog.value) {
        active.value = 'execute'
        flashInterval && clearInterval(flashInterval)
    }
})

const showRemarkDialog: any = ref<boolean>(false)
const remarkLoading = ref(false)
const formData: any = reactive({
    id: 0,
    remark: ''
})

// 修改备注
const remarkEvent = (row: any) => {
    formData.id = row.id
    formData.remark = row.remark
    showRemarkDialog.value = true
}

const modifyRemarkFn = () => {
    remarkLoading.value = true
    modifyBackupRemark({
        id: formData.id,
        remark: formData.remark
    }).then(() => {
        showRemarkDialog.value = false
        remarkLoading.value = false
        loadList()
    }).catch(() => {
        remarkLoading.value = false
    })
}

// 删除升级记录
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('deleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true
        tableData.loading = true
        deleteRecords({
            ids: id
        }).then(() => {
            loadList()
            repeat.value = false
        }).catch(() => {
            repeat.value = false
            tableData.loading = false
        })
    })
}

// 批量删除升级记录
const batchDelete = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('deleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        tableData.loading = true
        if (repeat.value) return
        repeat.value = true

        const ids: any = []
        multipleSelection.value.forEach((item: any) => {
            ids.push(item.id)
        })

        deleteRecords({
            ids
        }).then(() => {
            loadList()
            repeat.value = false
        }).catch(() => {
            repeat.value = false
            tableData.loading = false
        })
    })
}
</script>

<style lang="scss" scoped>
:deep(.terminal .t-log-box span) {
    white-space: pre-wrap;
}

.table-head-bg {
    background-color: var(--el-table-header-bg-color);
}

::v-deep .number-of-steps {
    .el-step__line {
        margin: 0 25px;
        background: #dddddd;
    }

    .el-step__head {
        margin-top: 10px;
    }

    .is-success {
        color: var(--el-color-primary);
        border-color: var(--el-color-primary);

        .el-step__icon {
            background: var(--el-color-primary);
            color: #fff;
            // box-shadow: 0 0 0 4px var(--el-color-primary-light-9);

            i {
                color: #fff;
            }
        }

        .el-step__line {
            margin: 0 25px;
            background: var(--el-color-primary);
        }
    }
    .is-finish {
        color: var(--el-color-primary);
        border-color: var(--el-color-primary);

        .el-step__icon {
            background: var(--el-color-primary)!important;
            color: #fff !important;
            // box-shadow: 0 0 0 4px var(--el-color-primary-light-9);

            i {
                color: #fff;
            }
        }

        .el-step__line {
            margin: 0 25px;
            background: var(--el-color-primary);
        }
    }

    .is-process {
        color: var(--el-color-primary);
        font-weight: inherit;

        // font-size: 18px;
        .el-step__icon {
            padding: 10px;
            border: 1px solid var(--el-color-primary);
            background: var(--el-color-primary)!important;
            color: #fff !important;
            // box-shadow: 0 0 0 4px var(--el-color-primary-light-9);
        }
    }

    .is-wait {
        color: #333;
    }
}

:deep(.el-result__icon) {
  color: unset !important; // 清除默认颜色
}
:deep(.el-result__title p){
    font-size: 25px;
    color: #1D1F3A;
    font-weight: 500;
}
:deep(.el-result__subtitle p){
    font-size: 15px;
    color: #4F516D;
    font-weight: 500;
    word-break: break-all;
	text-overflow: ellipsis;
	overflow: hidden;
	display: -webkit-box;
	-webkit-line-clamp: 5;
	-webkit-box-orient: vertical;
}
/* 多行超出隐藏 */
.multi-hidden {
    word-break: break-all;
    text-overflow: ellipsis;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}
</style>
