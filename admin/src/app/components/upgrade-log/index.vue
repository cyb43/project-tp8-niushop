<template>
    <el-dialog v-model="dialogVisible" :title="t('gxx')" width="850" :destroy-on-close="true">
        <el-card class="box-card !border-none" shadow="never" >
            <div v-loading="loading">
                <div class="text-page-title mb-[20px]">历史版本</div>
                <div class="time-dialog h-[500px]" style="overflow: auto">
                    <el-scrollbar>
                        <el-timeline style="width: 100%" v-if="!loading">
                            <el-timeline-item v-for="(item, index) in frameworkVersionList" :key="index" placement="left" :color="color">
                                <div class="relative">
                                    <span class="text-[#333333] text-[14px] absolute">{{ timeSplit(item.release_time)[0] }}</span>
                                    <br />
                                    <span class="text-[#999999] text-[14px] w-[78px] block mt-[10px] absolute text-right">{{ timeSplit(item.release_time)[1] }}</span>
                                </div>
                                <el-collapse v-model="activeName" accordion>
                                    <el-collapse-item :name="index">
                                        <template #title>
                                            <span class="text-[#333] text-[14px]"> v{{ item.version_no }} </span>
                                        </template>
                                        <div class="px-[20px] py-[20px] bg-overlay timeline-log-wrap whitespace-pre-wrap rounded-[4px]" style="background: rgba(25, 103, 249, 0.03);" v-if="item['upgrade_log']">
                                            <div v-html="item['upgrade_log']"></div>
                                        </div>
                                    </el-collapse-item>
                                </el-collapse>
                            </el-timeline-item>
                        </el-timeline>
                    </el-scrollbar>
                </div>
            </div>
        </el-card>
    </el-dialog>
</template>
<script lang="ts" setup>
import { computed, ref, defineProps, nextTick } from "vue"
import { t } from "@/lang"
import { getAppVersionList, getFrameworkVersionList } from "@/app/api/module"

const props = defineProps({
    upgradeKey: {
        type: String,
        required: true
    }
})

const frameworkVersionList = ref([])

const newVersion: any = computed(() => {
    return frameworkVersionList.value.length ? frameworkVersionList.value[0] : null
})

const getAppVersionListFn = () => {
    getAppVersionList({ app_key: props.upgradeKey }).then(({ data }) => {
        loading.value = false
        data.forEach((item: any, index) => {
            if (index == 0) {
                item.important = 1
            } else {
                item.important = 0
            }
        })

        frameworkVersionList.value = data
        if(frameworkVersionList.value.length == 0){
            ElMessage.warning('暂无版本更新信息')
            return
        }else{
            dialogVisible.value = true
        }
    })
}
const getFrameworkVersionListFn = () => {
    getFrameworkVersionList().then(({ data }) => {
        loading.value = false
        data.forEach((item: any, index) => {
            if (index == 0) {
                item.important = 1
            } else {
                item.important = 0
            }
        })
        frameworkVersionList.value = data
        dialogVisible.value = true
    })
}

const activeName = ref(0)

// 提交信息
const loading = ref(true)
const dialogVisible = ref(false)
const open = async () => {
    nextTick(() => {
        activeName.value = 0 // 重置

        if (props.upgradeKey) {
            getAppVersionListFn()
        } else {
            getFrameworkVersionListFn()
        }
    })

}
const timeSplit = (str: string) => {
    const [date, time] = str.split(" ")
    const [hours, minutes] = time.split(":")
    return [date, `${ hours }:${ minutes }`]
}
defineExpose({
    open
})
</script>
<style lang="scss" scoped></style>
<style scoped>
.el-timeline-item {
    min-height: 75px;
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

.time-dialog >>> .el-dialog__header {
    padding: 10px 20px;
    height: 25px;
    line-height: 25px;
    text-align: left;
    background: #fff;
    border-bottom: solid 1px #e4e7ed;
}

.time-dialog >>> .el-card__body {
    padding: 8px;
}

.time-dialog >>> .el-card.is-always-shadow {
    box-shadow: none;
}

.time-dialog >>> .el-dialog__headerbtn .el-dialog__close {
    color: #666;
}

.time-dialog >>> .el-dialog__headerbtn:hover .el-dialog__close {
    color: #666;
}

.time-dialog >>> .el-dialog__headerbtn {
    top: 14px;
}

.time-dialog >>> .el-collapse {
    margin-left: 119px;
    border: none;
    margin-top: -22px;
}

.time-dialog >>> .el-collapse-item__header {
    border: none;
    line-height: 25px;
    height: 25px;
    position: relative;
    z-index: 999;
}

.time-dialog >>> .el-collapse-item__wrap {
    border: none;
}

.time-dialog >>> .el-collapse-item__content {
    margin-top: 15px;
    padding-bottom: 0 !important;
}

.time-dialog >>> .el-timeline-item__node--01 {
    width: 18px !important;
    height: 18px !important;
    left: 117px !important;
}
</style>
<style>
.time-dialog .el-dialog {
    margin: 0 auto !important;
    max-height: 90%;
    overflow: hidden;
    top: 5%;
}

.time-dialog .el-dialog {
    margin: 0 auto !important;
    height: 65%;
    overflow: hidden;
    top: 10%;
}

.time-dialog .el-dialog__body {
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

.time-dialog .el-timeline-item__wrapper {
    top: -20px !important;
}
.el-scrollbar__bar{
    z-index:999;
}
</style>
