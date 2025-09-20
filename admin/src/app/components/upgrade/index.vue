<template>
    <el-dialog v-model="showDialog" :title="t('upgrade.title')" width="850px" :close-on-click-modal="false" :close-on-press-escape="false" :before-close="dialogClose">

        <template v-if="upgradeContent">
            <!-- 检测服务是否到期 -->
            <template v-if="step == 1">
                <template v-for="(item, index) in upgradeContent.content">
                    <div class="text-lg">
                        <template v-if="item.upgrade_version">
                            <span>【{{ item.app.app_name }}】本次升级将从</span>
                            <span class="font-bold px-[2px]">{{ item.version }}</span>
                            <span>升级到</span>
                            <span class="font-bold px-[2px]">{{ item.upgrade_version }}</span>
                            <span>版本</span>
                        </template>
                        <template v-else>
                            <template v-if="upgradeContent.content.length > 1">
                                <span>【{{ item.app.app_name }}】当前版本</span>
                                <span class="font-bold px-[2px]">{{ item.version }}</span>
                            </template>
                            <template v-else>
                                <span>当前版本</span>
                                <span class="font-bold px-[2px]">{{ item.version }}</span>
                            </template>
                        </template>
                    </div>
                    <div class="mt-[10px]" :class="{ 'mb-[10px]' : (index + 1) < upgradeContent.content.length }" v-if="item.upgrade_version != item.last_version">
                        <el-alert type="info" show-icon :closable="false">
                            <template #title>
                                <span>当前最新版本为{{ item.last_version }}，您的服务{{ item.expire_time ? `已于${item.expire_time}到期` : '长期有效' }}。</span>
                                <span>如需升级到最新版可在<a class="text-primary" href="https://www.niucloud.com" target="_blank">niucloud-admin官网</a>购买相关服务后再进行升级</span>
                            </template>
                        </el-alert>
                    </div>
                </template>
            </template>
            <div v-if="step == 2">
                <el-steps :active="numberOfSteps" align-center class="number-of-steps" process-status="process" v-if="!errorDialog && active != 'complete'">
                    <el-step :title="t('testDirectoryPermissions')" />
                    <el-step :title="t('upgrade.option')" />
                    <el-step :title="t('startUpgrade')" />
                    <el-step :title="t('upgradeEnd')" />
                </el-steps>
                <div class="h-[400px]" style="overflow: auto">
<!--                    <div class="time-dialog-wrap mt-[30px]" v-show="active == 'content'">-->
<!--                        <el-timeline style="width: 100%">-->
<!--                            <el-timeline-item v-for="(item, index) in upgradeContent.version_list" :key="index" placement="left">-->
<!--                                <div class="relative">-->
<!--                                    <span class="text-[#333333] text-[16px] absolute">{{ timeSplit(item.release_time)[0] }}</span>-->
<!--                                    <br />-->
<!--                                    <span class="text-[#999999] text-[14px] w-[78px] block mt-[10px] absolute" style="text-align: right"> {{ timeSplit(item.release_time)[1] }}</span>-->
<!--                                </div>-->
<!--                                <el-collapse v-model="activeName" accordion>-->
<!--                                    <el-collapse-item :name="index">-->
<!--                                        <template #title>-->
<!--                                            <span class="text-[#333] text-[16px]"> v{{ item.version_no }} </span>-->
<!--                                        </template>-->

<!--                                        <div class="px-[20px] py-[20px] bg-overlay timeline-log-wrap whitespace-pre-wrap rounded-[4px]" style="background: rgba(25, 103, 249, 0.03);" v-if="item['upgrade_log']">-->
<!--                                            <div v-html="item['upgrade_log']"></div>-->
<!--                                        </div>-->
<!--                                    </el-collapse-item>-->
<!--                                </el-collapse>-->
<!--                            </el-timeline-item>-->
<!--                        </el-timeline>-->
<!--                    </div>-->

                    <!-- 判断文件权限 -->
                    <div v-show="active == 'upgrade'">
                        <div class="flex flex-col" v-if="upgradeCheck && !upgradeTask">
                            <el-scrollbar>
                                <div class="bg-[#fff] my-3" v-if="upgradeCheck.dir">
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

                                        <div style="height: calc(300px); overflow: auto">
                                            <el-row class="pb-[10px] items pl-[15px]" v-for="item in upgradeCheck.dir.is_readable">
                                                <el-col :span="18">
                                                    <span>{{ item.dir }}</span>
                                                </el-col>
                                                <el-col :span="3">
                                                    <span>{{ t("upgrade.readable") }}</span>
                                                </el-col>
                                                <el-col :span="3" >
                                                    <span v-if="item.status">
                                                        <el-icon color="green">
                                                            <Select />
                                                        </el-icon>
                                                    </span>
                                                    <span v-else>
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
                                                    <span>{{ t("upgrade.write") }}</span>
                                                </el-col>
                                                <el-col :span="3">
                                                    <span v-if="item.status">
                                                        <el-icon color="green">
                                                            <Select />
                                                        </el-icon>
                                                    </span>
                                                    <span v-else>
                                                        <el-icon color="red">
                                                            <CloseBold />
                                                        </el-icon>
                                                    </span>
                                                </el-col>
                                            </el-row>
                                        </div>
                                    </div>
                                </div>
                            </el-scrollbar>
                        </div>
                        <div class="h-[370px] mt-[30px]" v-show="showTerminal && upgradeTask && !errorDialog">
                            <terminal ref="terminalRef" :name="`upgrade-${terminalId}`"  :context="upgradeTask ? upgradeTask.upgrade.app_key : ''" :init-log="null" :show-header="false" :show-log-time="true" @exec-cmd="onExecCmd" />
                        </div>

                    </div>
                    <!-- 是否备份选择 -->
                    <div class="flex flex-col" v-show="active == 'backup'">
                        <el-scrollbar>
                            <div class="bg-[#fff] my-3">
                                <div class="p-[20px] mt-[50px] mx-[10px] border-[1px] border-[#E6E6E6] rounded-[10px]">
                                    <div class="flex justify-between items-center mt-[-9px]">
                                        <el-checkbox v-model="upgradeOption.is_need_cloudbuild" :label="t('upgrade.isNeedCloudbuild')" :true-value="true" :false-value="false" size="large" ></el-checkbox>
                                    </div>
                                    <div class="text-[14px] text-[#374151] mb-[10px]">{{ t('upgrade.cloudbuildTips') }}</div>
                                </div>
                                <div class="p-[20px] mt-[20px] mx-[10px] border-[1px] border-[#E6E6E6] rounded-[10px]" v-if="upgradeContent.last_backup">
                                    <div class="flex justify-between items-center mt-[-9px]">
                                        <el-checkbox v-model="upgradeOption.is_need_backup" :label="t('upgrade.isNeedBackup')" :true-value="true" :false-value="false" size="large" ></el-checkbox>
                                        <el-button link type="primary" class="!text-[#9699B6]" @click="toBackupRecord">{{ t('upgrade.isNeedBackupBtn') }}</el-button>
                                    </div>

                                    <div class="text-[14px] text-[#374151] mb-[10px]">{{ t('upgrade.isNeedBackupTips') }}</div>
                                    <div class="text-[14px] text-[#9699B6]">{{ t('上次备份时间：') }}{{ upgradeContent.last_backup.complete_time }}</div>
                                </div>
                            </div>
                        </el-scrollbar>
                    </div>
                    <div class="mt-[20px] h-[370px]" v-show="errorDialog">
                        <el-result icon="error" :title="t('升级失败')">
                            <template #icon>
                                <img src="@/app/assets/images/error_icon.png" alt="" />
                            </template>
                            <template #extra>
                                <el-scrollbar class="max-h-[120px] !overflow-auto text-[15px] text-[#4F516D] mb-[15px] mt-[-15px]">
                                    {{errorMsg}}
                                </el-scrollbar>
                                <el-button @click="handleBack()" class="!w-[90px]">错误信息</el-button>
                                <el-button @click="showDialog=false" type="primary" class="!w-[90px]">完成</el-button>
                            </template>
                        </el-result>
                    </div>
                    <div class="mt-[20px]" v-show="active == 'complete'">
                        <el-result icon="success" :title="t('upgrade.upgradeSuccess')">
                            <template #icon>
                                <img src="@/app/assets/images/success_icon.png" alt="">
                            </template>
                            <template #extra>
                                <div class="text-[16px] text-[#4F516D] mt-[-5px]" v-show="upgradeTask && upgradeTask.executed && !upgradeTask.executed.includes('cloudBuild')">{{ t('upgrade.upgradeCompleteTips') }}</div>
                                <div class="text-[16px] text-[#9699B6] mt-[10px]">本次升级用时{{ formatUpgradeDuration }}</div>
                                <div class="mt-[20px]">
                                    <el-button @click="handleBack()" class="!w-[90px]">返回</el-button>
                                    <el-button @click="showDialog=false" type="primary" class="!w-[90px]">完成</el-button>
                                </div>
                            </template>
                        </el-result>
                        <!-- <el-alert :title="t('upgrade.upgradeCompleteTips')" type="error" :closable="false" v-show="upgradeTask && upgradeTask.executed && !upgradeTask.executed.includes('cloudBuild')"/> -->
                    </div>
                </div>
            </div>
        </template>
        <template #footer>
            <div class="dialog-footer">
                <!-- 查看升级内容 -->
                <el-button v-if="step == 1 && upgradeContent.content.length && isAllowUpgrade" @click="step = 2" type="primary">{{ t("upgrade.upgradeButton") }}</el-button>
                <el-button type="primary" v-show="step == 2 && showTerminal && upgradeTask && !errorDialog"  :loading="timeloading" class="!w-[140px]">已用时 {{ formatUpgradeDuration }}</el-button>
                <template v-if="step == 2 && active != 'complete'">
                <!--                <el-button v-if="active == 'content'" @click="showDialog = false">{{ t("return") }}</el-button>-->
                    <el-button type="primary" :disabled="!is_pass" v-if="active == 'upgrade' && !upgradeTask" @click="() => { active = 'backup'; numberOfSteps = 1 }">{{ t("nextStep") }}</el-button>
                    <el-button v-if="active == 'backup'" @click="() => { active = 'upgrade'; numberOfSteps = 1 } ">{{ t("prev") }}</el-button>
                    <el-button type="primary" v-if="active == 'backup'" :loading="loading" @click="() => { upgradeAddonFn() }">{{ t("nextStep") }}</el-button>
                    <el-button v-if="active == 'complete'" @click="showDialog = false">{{ t("complete") }}</el-button>
                </template>
            </div>
        </template>
    </el-dialog>

    <el-dialog v-model="upgradeTipsShowDialog" :title="t('warning')" width="500px" draggable>
        <span v-html="t('upgrade.upgradeTips')"></span>
        <template #footer>
            <div class="flex justify-end">
                <el-button @click="upgradeTipsConfirm(true)" type="primary">{{ t("upgrade.knownToKnow") }}</el-button>
                <el-button @click="handleUpgrade()" type="primary" plain :loading="readyLoading">{{ t("upgrade.upgradeButton") }}</el-button>
                <el-button @click="upgradeTipsShowDialog = false">{{ t("cancel") }}</el-button>
            </div>
        </template>
    </el-dialog>

    <el-dialog v-model="cloudBuildErrorTipsShowDialog" :title="t('warning')" width="500px" draggable :show-close="false">
        <span v-html="t('upgrade.cloudBuildErrorTips')"></span>
        <template #footer>
            <div class="flex justify-end">
                <el-button @click="cloudBuildError('local')" type="primary">{{ t("upgrade.localBuild") }}</el-button>
                <el-button @click="cloudBuildError('retry')" type="primary">{{ t("upgrade.cloudBuild") }}（{{ retrySecond }}S）</el-button>
                <el-button @click="cloudBuildError('rollback')" type="primary">{{ t("upgrade.rollback") }}</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, h, watch, computed } from 'vue'
import { t } from '@/lang'
import { getVersions } from '@/app/api/auth'
import { getFrameworkNewVersion } from '@/app/api/module'
import {
    getUpgradeContent,
    getUpgradeTask,
    upgradeAddon,
    executeUpgrade,
    preUpgradeCheck,
    clearUpgradeTask, upgradeUserOperate
} from '@/app/api/upgrade'
import { Terminal, TerminalFlash } from 'vue-web-terminal'
import 'vue-web-terminal/lib/theme/dark.css'
import { AnyObject } from '@/types/global'
import { ElNotification, ElMessage, ElMessageBox } from 'element-plus'
import Storage from '@/utils/storage'
import { useRouter } from 'vue-router'

const router = useRouter()
const terminalId = ref(Date.now()); 
const showDialog = ref<boolean>(false)
const upgradeContent = ref<null | AnyObject>(null)
const isAllowUpgrade = ref(true) // 是否允许升级
const upgradeTask = ref<null | AnyObject>(null)
const active = ref('upgrade')
const step = ref(1)
const upgradeCheck = ref<null | AnyObject>(null)
const loading = ref(false)
const terminalRef: any = ref(null)
const emits = defineEmits(['complete', 'cloudbuild'])
const upgradeTipsShowDialog = ref<boolean>(false)
let upgradeLog: any = []
let errorLog: any = []
const cloudBuildErrorTipsShowDialog = ref<boolean>(false)
const retrySecond = ref(30)
let retrySecondInterval: any = null
const upgradeOption = ref({
    is_need_backup: true,
    is_need_cloudbuild: true
})

// 升级步骤排除，backupCode 备份代码，backupSql 备份数据库
const excludeSteps: any = ref([
    {
        name: '备份源码',
        code: 'backupCode'
    },
    {
        name: '备份数据库',
        code: 'backupSql'
    }
])
/**
 * 查询升级任务
 */
const timeloading = ref(false)
const showTerminal = ref(false)
const upgradeStartTime = ref<number | null>(null)
const upgradeDuration = ref(0) // 单位：秒
let upgradeTimer: ReturnType<typeof setInterval> | null = null
const errorDialog = ref(false)
const errorMsg = ref('')
const getUpgradeTaskFn = () => {
    getUpgradeTask().then(({ data }) => {
        if (!data) return

        if (!upgradeContent.value) {
            upgradeContent.value = data.upgrade_content

            if (upgradeContent.value || !data.upgrade_content || !Array.isArray(data.upgrade_content.content)) {
                return
            }

            let upgradeCount = 0
            let failUpgradeCount = 0
            for (let i = 0; i < upgradeContent.value.content.length; i++) {
                if (upgradeContent.value.content[i].version_list.length) {
                    upgradeCount++
                } else {
                    failUpgradeCount++
                }
            }
            if (upgradeContent.value.content.length == upgradeCount) {
                isAllowUpgrade.value = true
            } else if (upgradeContent.value.content.length == failUpgradeCount) {
                isAllowUpgrade.value = false
            }
        }

        // 检测有没有正在进行中的升级任务
        if (!showDialog.value) {
            showElNotification()
            return
        }
        if (!upgradeTask.value) {
            showTerminal.value = true
            terminalRef.value.execute('clear')
            terminalRef.value.execute('开始升级')
            timeloading.value = true
            upgradeStartTime.value = Date.now()
            upgradeDuration.value = 0
            if (upgradeTimer) clearInterval(upgradeTimer)
            upgradeTimer = setInterval(() => {
                upgradeDuration.value++
            }, 1000)
        }

        upgradeTask.value = data

        data.log.forEach((item) => {
            if (!upgradeLog.includes(item)) {
                terminalRef.value.pushMessage({ content: `${item}` })
                upgradeLog.push(item)
            }
        })
        // 安装失败
        if (data.error) {
            data.error.forEach((item) => {
                if (!errorLog.includes(item)) {
                    terminalRef.value.pushMessage({ content: item, class: 'error' })
                    errorLog.push(item)
                    errorMsg.value = item
                }
            })
            errorDialog.value = true
            showTerminal.value = false
            if (upgradeTimer) {
                clearInterval(upgradeTimer)
                upgradeTimer = null
            }
            timeloading.value = false
        }
        // 恢复完毕
        if (data.step == 'restoreComplete') {
            flashInterval && clearInterval(flashInterval)
            return
        }
        // 升级完成
        if (data.step == 'upgradeComplete') {
            active.value = 'complete'
            showTerminal.value = false
            numberOfSteps.value = 4
            notificationEl && notificationEl.close()
            emits('complete')
            if (upgradeTimer) {
                clearInterval(upgradeTimer)
                upgradeTimer = null
            }
            timeloading.value = false
            clearUpgradeTask()
            return
        }
        numberOfSteps.value = 2
        active.value = 'upgrade'
        executeUpgradeFn()
    })
}

getUpgradeTaskFn()
const isBack = ref(false)
const handleBack = () => {
    active.value = 'upgrade'
    isBack.value = true
    showTerminal.value = true
    errorDialog.value = false // 隐藏错误弹窗
}

const formatUpgradeDuration = computed(() => {
    const s = upgradeDuration.value
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const sec = s % 60
    return [
        h > 0 ? `${h}小时` : '',
        m > 0 ? `${m}分钟` : '',
        `${sec}秒`
    ].filter(Boolean).join('')
})

const executeUpgradeFn = () => {
    executeUpgrade().then(() => {
        getUpgradeTaskFn()
    }).catch((err) => {
        if (err.message.indexOf('队列') != -1) {
            retrySecond.value = 30
            retrySecondInterval = setInterval(() => {
                retrySecond.value--
                if (retrySecond.value == 0) {
                    cloudBuildError('retry')
                }
            }, 1000)
            cloudBuildErrorTipsShowDialog.value = true
        } else {
            ElMessage({ message: err.message, type: 'error' })
        }
    })
}

let notificationEl: any = null
/**
 * 升级中任务提示
 */
const showElNotification = () => {
    notificationEl = ElNotification.success({
        title: t('warning'),
        dangerouslyUseHTMLString: true,
        message: h('div', {}, [t('upgrade.upgradingTips'), h('span', {
            class: 'text-primary cursor-pointer',
            onClick: elNotificationClick
        }, [t('upgrade.clickView')])]),
        duration: 0,
        showClose: false
    })
}

const elNotificationClick = () => {
    showDialog.value = true
    getUpgradeTaskFn()
    step.value = 2
    numberOfSteps.value = 3
    active.value = 'upgrade'
    notificationEl && notificationEl.close()
}

const frameworkVersion = ref('')
getVersions().then((res) => {
    frameworkVersion.value = res.data.version.version
})
const newFrameworkVersion = ref('')
getFrameworkNewVersion().then(({ data }) => {
    newFrameworkVersion.value = data.last_version
})

/**
 * 执行升级
 */
const is_pass = ref(false)
const repeat = ref(false)
const readyLoading = ref(false)

const handleUpgrade = async () => {
    if (repeat.value) return
    repeat.value = true
    readyLoading.value = true

    const appKey = upgradeContent.value?.upgrade_apps.join(',') != 'niucloud-admin' ? upgradeContent.value?.upgrade_apps.join(',') : ''

    await preUpgradeCheck(appKey).then(async ({ data }) => {
        upgradeCheck.value = data
        is_pass.value = data.is_pass
        active.value = 'upgrade'
        !upgradeTask.value ? (numberOfSteps.value = 0) : numberOfSteps.value
        upgradeTipsShowDialog.value = false
        showDialog.value = true
        repeat.value = false
        readyLoading.value = false
    }).catch(() => {
        repeat.value = false
        readyLoading.value = false
    })
}

const upgradeAddonFn = () => {
    if (!is_pass.value) return
    if (loading.value) return
    loading.value = true

    const appKey = upgradeContent.value?.upgrade_apps.join(',') != 'niucloud-admin' ? upgradeContent.value?.upgrade_apps.join(',') : ''

    upgradeAddon(appKey, upgradeOption.value).then(() => {
        getUpgradeTaskFn()
    }).catch(() => {
        loading.value = false
    })
}

const open = (addonKey: string = '', callback = null) => {
    errorDialog.value = false // 隐藏错误弹窗
    if (upgradeTask.value) {
        ElMessage({ message: '已有正在执行中的升级任务', type: 'error' })
        showDialog.value = true
        step.value = 2
        numberOfSteps.value = 3
        active.value = 'upgrade'
        if (callback) callback()
    } else {
        if (addonKey && frameworkVersion.value != newFrameworkVersion.value) {
            ElMessage({ message: '存在新版本框架，请先升级框架', type: 'error' })
            if (callback) callback()
            return
        }
        if (loading.value) return
        loading.value = true
        getUpgradeContent(addonKey).then(({ data }) => {
            loading.value = false
            upgradeContent.value = data
            let upgradeCount = 0
            let failUpgradeCount = 0
            for (let i = 0; i < upgradeContent.value.content.length; i++) {
                if (upgradeContent.value.content[i].version_list.length) {
                    upgradeCount++
                } else {
                    failUpgradeCount++
                }
            }
            if (upgradeContent.value.content.length == upgradeCount) {
                isAllowUpgrade.value = true
            } else if (upgradeContent.value.content.length == failUpgradeCount) {
                isAllowUpgrade.value = false
            }
            if (Storage.get('upgradeTipsLock')) {
                handleUpgrade()
            } else {
                upgradeTipsShowDialog.value = true
            }
            if (callback) callback()
        }).catch(() => {
            loading.value = false
            if (callback) callback()
        })
    }
}

/**
 * 升级进度动画
 */
let flashInterval: any = null
const terminalFlash = new TerminalFlash()
const onExecCmd = (key, command, success, failed, name) => {
    if (command == '开始升级') {
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
            if (nextIndex + 1 == array.length) {
                nextIndex = 0
            }
            return { value: array[nextIndex++] }
        }
    }
}

const dialogClose = (done: () => {}) => {
    if (active.value == 'upgrade' && upgradeTask.value && ['upgradeComplete', 'restoreComplete'].includes(upgradeTask.value.step) === false && !isBack.value) {
        ElMessageBox.confirm(t('upgrade.showDialogCloseTips'), t('warning'), {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }).then(() => {
            done()
        })
    } else {
        done()
    }
}

watch(
    () => showDialog.value,
    () => {
        if (!showDialog.value) {
            clearUpgradeTaskFn()
        }
    }
)

const clearUpgradeTaskFn = () => {
    active.value = 'upgrade'
    loading.value = false
    upgradeTask.value = null
    isBack.value = false
    errorDialog.value = false
    errorMsg.value = ''
    showTerminal.value = false
    upgradeLog = []
    errorLog = []
    numberOfSteps.value = 0
    flashInterval && clearInterval(flashInterval)
    retrySecondInterval && clearInterval(retrySecondInterval)
    upgradeOption.value = {
        is_need_backup: true,
        is_need_cloudbuild: true
    }
    step.value = 1
    clearUpgradeTask().then(() => {
    })
}

/**
 * 云编译队列不足操作
 * @param event
 */
const cloudBuildError = (event: string) => {
    cloudBuildErrorTipsShowDialog.value = false
    switch (event) {
        case 'local':
            upgradeUserOperate(event).then(() => {
                getUpgradeTaskFn()
            })
            break
        case 'retry':
            executeUpgradeFn()
            retrySecondInterval && clearInterval(retrySecondInterval)
            break
        case 'rollback':
            upgradeUserOperate(event).then(() => {
                getUpgradeTaskFn()
            })
            break
    }
}

const timeSplit = (str: string) => {
    const [date, time] = str.split(' ')
    const [hours, minutes] = time.split(':')
    return [date, `${hours}:${minutes}`]
}

const upgradeTipsConfirm = (isLock: boolean = false) => {
    isLock && Storage.set({ key: 'upgradeTipsLock', data: isLock })
    upgradeTipsShowDialog.value = false
    !isLock && (showDialog.value = true)
}
const activeName = ref(0)
const numberOfSteps = ref(0)

const toBackupRecord = () => {
    const routeUrl = router.resolve({
        path: '/tools/backup_records'
    })
    window.open(routeUrl.href, '_blank')
}

defineExpose({
    open,
    loading
})
</script>

<style lang="scss" scoped>
:deep(.el-button){
    border-radius: 4px !important;
}
.table-head-bg {
    background-color: var(--el-table-header-bg-color);
}
:deep(.el-checkbox__label){
    color: var(--el-color-primary);
}

:deep(.terminal .t-log-box span) {
    white-space: pre-wrap;
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
:deep(.el-dialog__title){
    font-size: 20px;
    font-weight: bold;
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
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
}
</style>

<style scoped>
.el-timeline-item {
    min-height: 100px;
}

.el-timeline-item >>> .el-timeline-item__node--normal {
    left: 117px;
    width: 18px;
    height: 18px;

    background: rgba(25, 103, 249, 0.12) !important;
    border-radius: 50%;
    /* 创建圆形 */
    position: relative;
    /* 用于定位伪元素 */
}

.el-timeline-item >>> .el-timeline-item__node--normal::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    /* 中心圆直径 */
    height: 8px;
    background-color: var(--el-color-primary);
    /* 中心圆颜色 */
    border-radius: 50%;
    /* 中心圆为圆形 */
    transform: translate(-50%, -50%);
    /* 居中显示 */
}

.el-timeline-item >>> .el-timeline-item__tail {
    left: 125px;
    border-left-color: #dddddd;
    border-left-style: dashed;
    margin: 24px 0 12px;
    height: calc(100% - 24px - 12px);
}

.time-dialog-wrap >>> .el-dialog__header {
    padding: 10px 20px;
    height: 25px;
    line-height: 25px;
    text-align: left;
    background: #fff;
    border-bottom: solid 1px #e4e7ed;
}

.time-dialog-wrap >>> .el-card__body {
    padding: 8px;
}

.time-dialog-wrap >>> .el-card.is-always-shadow {
    box-shadow: none;
}

.time-dialog-wrap >>> .el-dialog__headerbtn .el-dialog__close {
    color: #666;
}

.time-dialog-wrap >>> .el-dialog__headerbtn:hover .el-dialog__close {
    color: #666;
}

.time-dialog-wrap >>> .el-dialog__headerbtn {
    top: 14px;
}

.time-dialog-wrap >>> .el-collapse {
    margin-left: 119px;
    border: none;
    margin-top: -22px;
}

.time-dialog-wrap >>> .el-collapse-item__header {
    border: none;
    line-height: 25px;
    height: 25px;
    position: relative;
    z-index: 999;
}

.time-dialog-wrap >>> .el-collapse-item__wrap {
    border: none;
}

.time-dialog-wrap >>> .el-collapse-item__content {
    margin-top: 15px;
    padding-bottom: 0 !important;
}

.time-dialog-wrap >>> .el-timeline-item__node--01 {
    width: 18px !important;
    height: 18px !important;
    left: 117px !important;
}
</style>
<style>
.time-dialog-wrap .el-dialog {
    margin: 0 auto !important;
    max-height: 90%;
    overflow: hidden;
    top: 5%;
}

.time-dialog-wrap .el-dialog {
    margin: 0 auto !important;
    height: 65%;
    overflow: hidden;
    top: 10%;
}

.time-dialog-wrap .el-dialog__body {
    position: absolute;
    left: 0;
    top: 46px;
    bottom: 0;
    right: 0;
    z-index: 1;
    overflow: hidden;
    overflow-y: auto;
    padding: 10px 20px 0 0;
}

.time-dialog-wrap .el-timeline-item__wrapper {
    top: -20px !important;
}
</style>
