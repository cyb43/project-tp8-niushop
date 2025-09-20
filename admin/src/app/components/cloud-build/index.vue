<template>
    <el-dialog v-model="showDialog" :title="t('cloudbuild.title')" width="850px" :close-on-click-modal="false" :close-on-press-escape="false" :before-close="dialogClose">

        <div v-show="active == 'build'" class="h-[50vh]" v-loading="loading">
            <div class="h-[50vh] flex flex-col" v-if="cloudBuildCheck && !cloudBuildTask">
                <!-- <el-scrollbar> -->
                    <div class="bg-[#fff] my-3" v-if="cloudBuildCheck.dir">
                        <div>
                            <p class="pl-[20px] ">{{ t('cloudbuild.dirPermission') }}</p>
                            <div class="mt-[10px] mx-[20px] text-[14px] cursor-pointer text-primary flex items-center justify-between bg-[#EFF6FF] rounded-[4px] p-[10px]" @click="cloudBuildCheckDirFn">
                                <div class="flex items-center">
                                    <el-icon :size="17"><QuestionFilled /></el-icon>
                                    <span class="ml-[5px] leading-[20px]">编译权限错误，查看解决方案</span></div>
                                <div class="border-[1px] border-primary rounded-[3px] w-[72px] h-[26px] leading-[25px] text-center">立即查看</div>
                            </div>
                        </div>

                        <div class="px-[20px] pt-[10px] text-[14px] el-table">
                            <el-row class="py-[10px] items table-head-bg pl-[15px] mb-[10px]">
                                <el-col :span="18">
                                    <span>{{ t('cloudbuild.path') }}</span>
                                </el-col>
                                <el-col :span="3">
                                    <span>{{ t('cloudbuild.demand') }}</span>
                                </el-col>
                                <el-col :span="3">
                                    <span>{{ t('status') }}</span>
                                </el-col>
                            </el-row>
                            <el-scrollbar style="height: calc(300px); overflow: auto">
                                <el-row class="pb-[10px] items pl-[15px]" v-for="item in cloudBuildCheck.dir.is_readable">
                                    <el-col :span="18">
                                        <span>{{ item.dir }}</span>
                                    </el-col>
                                    <el-col :span="3">
                                        <span>{{ t('cloudbuild.readable') }}</span>
                                    </el-col>
                                    <el-col :span="3">
                                        <span v-if="item.status"><el-icon color="green"><Select /></el-icon></span>
                                        <span v-else>
                                                <el-icon color="red">
                                                    <CloseBold />
                                                </el-icon>
                                            </span>
                                    </el-col>
                                </el-row>
                                <el-row class="pb-[10px] items pl-[15px]" v-for="item in cloudBuildCheck.dir.is_write">
                                    <el-col :span="18">
                                        <span>{{ item.dir }}</span>
                                    </el-col>
                                    <el-col :span="3">
                                        <span>{{ t('cloudbuild.write') }}</span>
                                    </el-col>
                                    <el-col :span="3" >
                                        <span v-if="item.status"><el-icon color="green"><Select /></el-icon></span>
                                        <span v-else>
                                            <el-icon color="red">
                                                <CloseBold />
                                            </el-icon>
                                        </span>
                                    </el-col>
                                </el-row>
                            </el-scrollbar>
                        </div>
                    </div>
                <!-- </el-scrollbar> -->
            </div>
            <div class="h-[45vh]" v-show="cloudBuildTask">
                <terminal ref="terminalRef" :name="`cloud-build-${terminalId}`" context="" :init-log="null" :show-header="false" :show-log-time="true" @exec-cmd="onExecCmd"/>
            </div>
            <div class="flex justify-end mt-[20px]" v-show="cloudBuildTask">
                <el-button @click="dialogCancel()" class="!w-[90px]">取消</el-button>
                <el-button type="primary" :loading="timeloading" class="!w-[140px]">已用时 {{ formattedDuration }}</el-button>
            </div>
        </div>
        <div v-show="active == 'error'">
            <div class="h-[50vh] flex flex-col">
                <div class="flex-1 h-0 flex justify-center items-center flex-col">
                    <el-result icon="error" :title="t('编译失败')">
                        <template #icon>
                            <img src="@/app/assets/images/error_icon.png" alt="">
                        </template>
                        <template #extra>
                            <el-scrollbar class="max-h-[150px] !overflow-auto text-[15px] text-[#4F516D] mb-[15px] mt-[-15px]">
                                {{errorInfo}}
                            </el-scrollbar>
                            <el-button @click="handleReturn" class="!w-[90px]">错误信息</el-button>
                            <el-button @click="showDialog=false" type="primary" class="!w-[90px]">完成</el-button>
                        </template>
                    </el-result>
                </div>
            </div>
        </div>
        <div v-show="active == 'complete'">
            <div class="h-[50vh] flex flex-col">
                <div class="flex-1 h-0 flex justify-center items-center flex-col">
                    <el-result icon="success" :title="t('cloudbuild.cloudbuildSuccess')"  :sub-title="`编译耗时${formattedDuration}，成功编译完成。`">
                        <template #icon>
                            <img src="@/app/assets/images/success_icon.png" alt="">
                        </template>
                        <template #extra>
                            <el-button @click="handleReturn" class="!w-[90px]">返回</el-button>
                            <el-button @click="showDialog=false" type="primary" class="!w-[90px]">完成</el-button>
                        </template>
                    </el-result>
                </div>
            </div>
        </div>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, h, watch, computed } from 'vue'
import { t } from '@/lang'
import { getCloudBuildLog, getCloudBuildTask, cloudBuild, clearCloudBuildTask, preBuildCheck } from '@/app/api/cloud'
import { Terminal, TerminalFlash } from 'vue-web-terminal'
import 'vue-web-terminal/lib/theme/dark.css'
import { AnyObject } from '@/types/global'
import { ElNotification, ElMessageBox } from 'element-plus'

const showDialog = ref<boolean>(false)
const terminalId = ref(Date.now());
const cloudBuildTask = ref<null | AnyObject>(null)
const active = ref('build')
const cloudBuildCheck = ref<null | AnyObject>(null)
const loading = ref(false)
const terminalRef = ref(null)

let cloudBuildLog = []

// 计时器相关
const buildStartTime = ref<number | null>(null)
const buildDuration = ref<number>(0)
let buildTimer: number | null = null
const formattedDuration = computed(() => {
    const seconds = buildDuration.value
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return mins > 0 ? `${mins}分${secs}秒` : `${secs}秒`
})

/**
 * 查询升级任务
 */
const getCloudBuildTaskFn = () => {
    getCloudBuildTask().then(({ data }) => {
        if (!data) return

        cloudBuildTask.value = data

        if (!showDialog.value) {
            // showElNotification()
            localStorage.setItem('cloud_build_task', 'true')
        }
    }).catch()
}
getCloudBuildTaskFn()
const errorInfo = ref('')
const timeloading = ref(false)
const getCloudBuildLogFn = () => {
    timeloading.value = true
    getCloudBuildLog().then(res => {
        if (!res.data) {
            if (showDialog.value && cloudBuildLog.length) {
                active.value = 'complete'
                timeloading.value = false
                terminalRef.value.execute('clear')
                clearCloudBuildTask()
                buildTimer && clearInterval(buildTimer) // 清除计时器
                localStorage.removeItem('cloud_build_start_time')
                localStorage.removeItem('cloud_build_task')
            }
            notificationEl && notificationEl.close()
            // cloudBuildTask.value = null
            return
        }

        const data = res.data.data ?? []
        let error = ''

        if (data[0] && data[0].length && showDialog.value) {
            if (cloudBuildLog.length == 0) {
                const storedTime = localStorage.getItem('cloud_build_start_time')
                if (storedTime) {
                    buildStartTime.value = Number(storedTime)
                } else {
                    const now = Date.now()
                    buildStartTime.value = now
                    localStorage.setItem('cloud_build_start_time', String(now))
                }

                buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
                buildTimer && clearInterval(buildTimer)
                buildTimer = setInterval(() => {
                    if (buildStartTime.value) {
                        buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
                    }
                }, 1000)
                terminalRef.value.execute('clear')
                terminalRef.value.execute('开始编译')
            }

            data[0].forEach(item => {
                if (!cloudBuildLog.includes(item.action)) {
                    terminalRef.value.pushMessage({ content: `${item.action}` })
                    cloudBuildLog.push(item.action)

                    if (item.code == 0) {
                        error = item.msg
                        terminalRef.value.pushMessage({ content: item.msg, class: 'error' })
                        timeloading.value = false
                        active.value = 'error'
                        terminalRef.value.execute('clear')
                        clearCloudBuildTask()
                        errorInfo.value = item.msg
                        // 停止计时器
                        if (buildTimer) {
                            clearInterval(buildTimer)
                            buildTimer = null
                        }

                        // 保证 duration 也被最后更新一次
                        if (buildStartTime.value) {
                            buildDuration.value = Math.floor((Date.now() - buildStartTime.value) / 1000)
                        }
                        localStorage.removeItem('cloud_build_start_time')
                        localStorage.removeItem('cloud_build_task')
                    }
                }
            })
        }

        if (error) return

        setTimeout(() => {
            getCloudBuildLogFn()
        }, 2000)
    }).catch()
}

const closeType = ref('normal')
const handleReturn = () => {
    active.value = 'build'
    closeType.value = 'success'
}

const notificationEl : any = null
/**
 * 升级中任务提示
 */
// const showElNotification = () => {
//     notificationEl = ElNotification.success({
//         title: t('warning'),
//         dangerouslyUseHTMLString: true,
//         message: h('div', {}, [
//             t('cloudbuild.executingTips'),
//             h('span', { class: 'text-primary cursor-pointer', onClick: elNotificationClick }, [t('cloudbuild.clickView')])
//         ]),
//         duration: 0,
//         showClose: false
//     })
// }

const elNotificationClick = () => {
    showDialog.value = true
    active.value = 'build'
    getCloudBuildLogFn()
}

const open = async () => {
    loading.value = true
    active.value = 'build'
    closeType.value = 'normal'
    if (cloudBuildTask.value) {
        showDialog.value = true
        loading.value = false
        getCloudBuildLogFn()
        return
    }

    preBuildCheck().then(async ({ data }) => {
        if (data.is_pass) {
            cloudBuild().then(({ data }) => {
                loading.value = false
                cloudBuildTask.value = data
                showDialog.value = true
                getCloudBuildLogFn()
            }).catch(() => {
                showDialog.value = false
                loading.value = false
            })
        } else {
            loading.value = false
            cloudBuildCheck.value = data
            showDialog.value = true
        }
    }).catch(() => {
        showDialog.value = false
    })
}

/**
 * 升级进度动画
 */
let flashInterval: null | number = null
const terminalFlash = new TerminalFlash()
const onExecCmd = (key, command, success, failed, name) => {
    if (command == '开始编译') {
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

const dialogClose = (done: () => {}) => {
    if (active.value == 'build' && cloudBuildTask.value && closeType.value == 'normal') {
        ElMessageBox.confirm(
            t('cloudbuild.showDialogCloseTips'),
            t('warning'),
            {
                confirmButtonText: t('confirm'),
                cancelButtonText: t('cancel'),
                type: 'warning'
            }
        ).then(() => {
            terminalRef.value.execute('clear')
            localStorage.removeItem('cloud_build_start_time')
            localStorage.removeItem('cloud_build_task')
            done()
            buildTimer && clearInterval(buildTimer)
            buildTimer = null
            buildStartTime.value = null
            buildDuration.value = 0
        }).catch(() => { })
    } else {
        done()
    }
}

const dialogCancel = () => {
    if (active.value == 'build' && cloudBuildTask.value && closeType.value == 'normal') {
        ElMessageBox.confirm(
            t('cloudbuild.showDialogCloseTips'),
            t('warning'),
            {
                confirmButtonText: t('confirm'),
                cancelButtonText: t('cancel'),
                type: 'warning'
            }
        ).then(() => {
            terminalRef.value.execute('clear')
            localStorage.removeItem('cloud_build_start_time')
            localStorage.removeItem('cloud_build_task')
            buildTimer && clearInterval(buildTimer)
            buildTimer = null
            buildStartTime.value = null
            buildDuration.value = 0
            showDialog.value = false
        }).catch(() => { })
    } else {
        showDialog.value = false
    }
}

const cloudBuildCheckDirFn = () => {
    window.open('https://doc.niucloud.com/v6.html?keywords=/chang-jian-wen-ti-chu-li/er-shi-wu-3001-sheng-7ea7-yun-bian-yi-mu-lu-du-xie-quan-xian-zhuang-tai-bu-tong-guo-ru-he-chu-li')
}

watch(() => showDialog.value, () => {
    if (!showDialog.value) {
        cloudBuildTask.value = null
        active.value = 'build'
        cloudBuildLog = []
        flashInterval && clearInterval(flashInterval)
        buildTimer && clearInterval(buildTimer)
        buildStartTime.value = null
        buildDuration.value = 0
        clearCloudBuildTask()
    }
})

defineExpose({
    open,
    cloudBuildTask,
    loading,
    elNotificationClick,
    getCloudBuildTaskFn
})
</script>

<style lang="scss" scoped>
.table-head-bg {
    background-color: var(--el-table-header-bg-color);
}
:deep(.terminal .t-log-box span) {
    white-space: pre-wrap;
}
:deep(.el-result__icon) {
  color: unset !important; // 清除默认颜色
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
	-webkit-line-clamp: 5;
	-webkit-box-orient: vertical;
}
:deep(.el-result){
    margin-top: -80px !important;
}
</style>
