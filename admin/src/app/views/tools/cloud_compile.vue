<template>
    <div v-loading="loading" class="main-container w-full">
        <div class="p-5 bg-[#fff] overflow-hidden">

            <div class="bg-[#fff] w-[100%] rounded-[8px] overflow-hidden">
                <div class=" relative pb-[13px]" style="border-bottom: 2px solid #f0f2f6">
                    <div class="w-[66px] bg-primary h-[2px] absolute bottom-[0px]"></div>
                    <span class=" text-primary text-[18px] ml-[4px]">云编译</span>
                </div>
                <div class="flex mt-[20px]  ml-[20px]">
                    <el-button class="w-[98px] !h-[36px]" type="primary" @click="handleCloudBuild" :loading="cloudBuildRef?.loading">云编译</el-button>
                    <div class="btn-time w-[181px] h-[36px] rounded-[4px] text-[#606266] text-[14px] ml-[10px]">
                        <span>云编译执行时间大约</span>
                         <span class="text-[16px] text-[#D43030] mx-[3px]">3</span>
                        <span>分钟</span>
                    </div>
                </div>
                <div class="mt-[21px] flex mb-[21px] items-center">
                    <span class="flex ml-[20px] text-[16px] items-center">
                        <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i>
                        温馨提示
                    </span>
                    <span class="text-[14px] text-[#606266] ml-[7px]"> 以下情况可以进行云编译</span>
                </div>

                <div class="text-[14px] text-[#606266] ml-[13px] mb-[18px]">云编译不需要本地安装node环境即可进行，针对使用者方便快捷</div>
                <div class="ml-[40px] text-[14px] text-[#606266] mb-[18px]">1、系统或插件，每次安装或升级完成后，需要云编译</div>
                <div class="ml-[40px] text-[14px] text-[#606266] mb-[18px]">2、开发者编写完前端代码之后，可以使用云编译进行源码编译</div>
                <div class="ml-[40px] text-[14px] text-[#606266] mb-[18px]">3、由于云编译不是针对某个插件进行编译，而是系统整体编译，因此如果同时需要安装多个插件时，往往需要安装到最后一个插件才整体进行云编译</div>
                <div class="mt-[21px] flex mb-[21px] text-[16px] items-center">
                    <span class="flex ml-[20px] items-center">
                        <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i>
                        云编译流程
                    </span>
                </div>
                <div class="ml-[40px]">
                    <el-timeline>
                        <el-timeline-item color="#4268EF">
                            <template #dot>
                                <div class="w-[15px] h-[15px] bg-primary rounded-[50%] text-[9px] text-[#fff] flex items-center justify-center">1</div>
                            </template>
                            <div class="text-[16px] text-[#303133]">编译admin代码</div>
                            <div class="py-[12px] px-[10px] bg-[#F7F8FA] mt-[10px] text-[#606266] text-[14px] w-[1085px]">
                                <span>云编译会将admin端的vue代码编译为对应的html文件，同时将生成的代码下载到系统 niucloud 下的</span>
                                <span class="text-[#FF9D31] mx-[3px]">public/admin</span>
                                <span>目录中。后台的访问路径将变为</span>
                                <span class="text-primary ml-[3px]">https://域名/admin</span>
                            </div>
                        </el-timeline-item>
                        <el-timeline-item color="#4268EF">
                            <template #dot>
                                <div class="w-[15px] h-[15px] bg-primary rounded-[50%] text-[9px] text-[#fff] flex items-center justify-center">2</div>
                            </template>
                            <div class="text-[16px] text-[#303133]">编译uniapp代码</div>
                            <div class="py-[12px] px-[10px] bg-[#F7F8FA] mt-[10px] text-[#606266] text-[14px] w-[1085px]">
                                <span>云编泽会将uniapp端的vue代码编译为对应的html文件，同时将生成的代码下载到系统 niucloud下的</span>
                                <span class="text-[#FF9D31] mx-[3px]">public/wap</span>
                                <span>目录中，这样手机端网页的访问路径将变为</span>
                                <span class="text-primary ml-[3px]"> https://域名/wap</span>
                            </div>
                        </el-timeline-item>
                        <el-timeline-item color="#4268EF">
                            <template #dot>
                                <div class="w-[15px] h-[15px] bg-primary rounded-[50%] text-[9px] text-[#fff] flex items-center justify-center">3</div>
                            </template>
                            <div class="text-[16px] text-[#303133]">编译web代码</div>
                            <div class="py-[12px] px-[10px] bg-[#F7F8FA] mt-[10px] text-[#606266] text-[14px] w-[1085px]">
                                <span>云编泽会将web端的vue代码编译为对应的html文件，同时将生成的代码下载到系统 niucloud下的</span>
                                <span class="text-[#FF9D31] mx-[3px]">public/web</span>
                                <span>目录中，这样电脑端网页的访问路径将变为</span>
                                <span class="text-primary ml-[3px]"> https://域名/web</span>
                            </div>
                        </el-timeline-item>
                    </el-timeline>
                </div>
            </div>
        </div>
        <div class="p-5 bg-[#fff] mt-[26px]">
            <div class="bg-[#fff] w-[100%] rounded-[8px] overflow-hidden">
                <div class="relative pb-[13px]" style="border-bottom: 2px solid #f0f2f6">
                    <div class="w-[85px] bg-primary h-[2px] absolute bottom-[0px]"></div>
                    <span class="text-primary text-[18px] ml-[4px]">本地编译</span>
                </div>
                <div class="mt-[21px] flex mb-[21px] text-[16px] items-center">
                    <span class="flex ml-[20px] items-center">
                        <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i>
                        温馨提示
                    </span>
                </div>
                <div class="ml-[40px] text-[14px] text-[#606266] mb-[18px]">
                    <span>1、如果本地安装了Node环境，可以进行本地编译，要求</span>
                    <span class="text-[#D43030] ml-[3px]">Node版本>18</span>
                </div>
                <div class="ml-[40px] text-[14px] text-[#606266] mb-[18px]">2、默认本地编译流程与云编译相同，执行本地编译命令后，会将编译后的代码移动到系统niucloud下的public下的对应端口目录下</div>
                <div class="ml-[40px] text-[14px] text-[#606266] mb-[18px]">3、由于云编译配置的访问路径时固定的，针对客户有独立部署admin，wap，web等个性化端口名称配置需求，需要进行本地编译</div>
                <div class="mt-[34px] flex mb-[21px] text-[16px] items-center">
                    <span class="flex ml-[20px] items-center">
                        <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i>
                        本地编译命令参考
                    </span>
                </div>
                <div>
                    <div class="ml-[40px] text-[14px] text-[#606266]">
                        <span class=" text-[#303133]">安装依赖：</span>
                        进入admin端与uniapp端以及web端目录都可执行
                    </div>
                    <div class="ml-[40px] w-[900px] h-[42px] bg-[#282C34] rounded-[4px] mt-[10px] flex items-center">
                        <span class="text-[16px] text-[#FF9D31] ml-[10px]">npm install</span>
                        <span class="w-[58px] h-[20px] bg-[rgba(204,204,204,0.3)] ml-[auto] text-[#fff] text-[10px] flex cursor-pointer rounded-[4px] mr-[17px] items-center justify-center" @click="copyEvent('npm install')">复制命令</span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#606266]">
                        <span class="text-[#303133]">后台admin端口打包：</span>
                        <span>进入admin目录下执行，执行后编译代码默认移动到系统的niucloud下的</span>
                        <span class="text-[#FF9D31] mx-[3px]">public/admin</span>
                        <span>目录下</span>
                    </div>
                    <div class="ml-[40px] w-[900px] h-[42px] bg-[#282C34] rounded-[4px] mt-[10px] flex items-center">
                        <span class="text-[16px] text-[#FF9D31] ml-[10px]">npm run build</span>
                        <span class="w-[58px] h-[20px] bg-[rgba(204,204,204,0.3)] ml-[auto] text-[#fff] text-[10px] flex cursor-pointer rounded-[4px] mr-[17px] items-center justify-center" @click="copyEvent('npm run build')">复制命令</span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#606266]">
                        <span class="text-[#303133]">使用uniapp打包H5：</span>
                        <span>进入uniapp目录下执行，执行后编译代码默认移动到系统niucloud下的</span>
                        <span class="text-[#FF9D31] mx-[3px]">public/wap</span>
                        <span>目录下</span>
                    </div>
                    <div class="ml-[40px] w-[900px] h-[42px] bg-[#282C34] rounded-[4px] mt-[10px] flex items-center">
                        <span class="text-[16px] text-[#FF9D31] ml-[10px]">npm run build:h5</span>
                        <span class="w-[58px] h-[20px] bg-[rgba(204,204,204,0.3)] ml-[auto] text-[#fff] text-[10px] flex cursor-pointer rounded-[4px] mr-[17px] items-center justify-center" @click="copyEvent('npm run build:h5')">复制命令</span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#606266]">
                        <span class=" text-[#303133]">使用uniapp打包微信小程序：</span>
                        <span>进入uniapp目录下执行，执行后编译代码默认移动到系统niucloud下的</span>
                        <span class="text-[#FF9D31] mx-[3px]">uni-app/dist/build/mp-weixin</span>
                        <span>目录</span>
                    </div>
                    <div class="ml-[40px] w-[900px] h-[42px] bg-[#282C34] rounded-[4px] mt-[10px] flex items-center">
                        <span class="text-[16px] text-[#FF9D31] ml-[10px]">npm run build:mp-weixin</span>
                        <span class="w-[58px] h-[20px] bg-[rgba(204,204,204,0.3)] ml-[auto] text-[#fff] text-[10px] flex cursor-pointer rounded-[4px] mr-[17px] items-center justify-center" @click="copyEvent('npm run build:mp-weixin')">复制命令</span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#606266]">
                        <span class="text-[#303133]">web端打包：</span>
                        <span>进入web目录下执行，执行后编译代码默认移动到系统niucloud下的</span>
                        <span class="text-[#FF9D31] mx-[3px]">public/web</span>
                        <span>目录下</span>
                    </div>
                    <div class="ml-[40px] w-[900px] h-[42px] bg-[#282C34] rounded-[4px] mt-[10px] flex items-center">
                        <span class="text-[16px] text-[#FF9D31] ml-[10px]">npm run generate</span>
                        <span class="w-[58px] h-[20px] bg-[rgba(204,204,204,0.3)] ml-[auto] text-[#fff] text-[10px] flex cursor-pointer rounded-[4px] mr-[17px] items-center justify-center" @click="copyEvent('npm run build')">复制命令</span>
                    </div>
                </div>
            </div>
        </div>
        <upgrade ref="upgradeRef" @cloudbuild="handleCloudBuild"/>
        <cloud-build ref="cloudBuildRef"/>
    </div>
</template>

<script lang="ts" setup>
import { ref, watch } from "vue"
import { ElMessage } from "element-plus"
import { useClipboard } from "@vueuse/core"
import { t } from "@/lang"
import Upgrade from "@/app/components/upgrade/index.vue"
import CloudBuild from "@/app/components/cloud-build/index.vue"

const loading = ref<Boolean>(false)

// 云编译调用
const cloudBuildRef = ref<any>(null)
const handleCloudBuild = () => {
    ElMessageBox.confirm(t("cloudBuildTips"), t("warning"), {
        confirmButtonText: t("confirm"),
        cancelButtonText: t("cancel"),
        type: "warning"
    }).then(() => {
        cloudBuildRef.value?.open()
    })
}

// 复制命令
const { copy, isSupported, copied } = useClipboard()

const copyEvent = (text: string) => {
    if (!isSupported.value) {
        ElMessage({
            message: t("notSupportCopy"),
            type: "warning"
        })
        return
    }
    copy(text)
}

watch(copied, () => {
    if (copied.value) {
        ElMessage({
            message: t("copySuccess"),
            type: "success"
        })
    }
})
</script>

<style lang="scss" scoped>
.btn-time {
    line-height: 36px;
    text-align: center;
}
</style>
