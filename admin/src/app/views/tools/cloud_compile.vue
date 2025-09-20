<template>
    <div v-loading="loading" class="main-container w-full">
        <div class="p-5 bg-[#fff] overflow-hidden">

            <div class="bg-[#fff] w-[100%] overflow-hidden">
                <div class="flex items-center justify-between mb-[20px]">
                    <div class="text-[#1D1F3A] text-[16px] font-bold ml-[4px]">云编译</div>
                    <div class="flex ml-[20px]">
                        <div class="btn-time w-[181px] h-[36px] rounded-[4px] text-[#9699B6] text-[12px] ml-[10px]">
                            <span>云编译执行时间大约</span>
                            <span class="text-[14px] text-[#DA203E] mx-[3px]">5</span>
                            <span>分钟</span>
                        </div>
                        <el-button class="w-[98px] !h-[36px]" type="primary" @click="handleCloudBuild" :loading="cloudBuildRef?.loading">云编译</el-button>
                    </div>
                </div>
                <div class="panel-title bg-[#F4F5F7] border-[#E6E6E6] border-solid border-b-[1px] h-[40px] flex items-center p-[10px]">
                    <span class="text-[16px] font-500 text-[#1D1F3A]">云编译</span>
                    <span class="text-[12px] text-[#9699B6] ml-[10px]">云编译不需要本地安装node环境即可进行，针对使用者方便快捷</span>
                </div>
                <div class="mt-[20px] flex mb-[14px] items-center">
                    <span class="flex ml-[20px] font-500 text-[16px] items-center text-[#1D1F3A]">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        温馨提示
                    </span>
                    <span class="text-[12px] text-[#9699B6] ml-[10px]"> 以下情况可以进行云编译</span>
                </div>

                <!-- <div class="text-[14px] text-[#606266] ml-[13px] mb-[18px]">云编译不需要本地安装node环境即可进行，针对使用者方便快捷</div> -->
                <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">1、系统或插件，每次安装或升级完成后，需要云编译</div>
                <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">2、开发者编写完前端代码之后，可以使用云编译进行源码编译</div>
                <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">3、由于云编译不是针对某个插件进行编译，而是系统整体编译，因此如果同时需要安装多个插件时，往往需要安装到最后一个插件才整体进行云编译</div>
                <div class="mt-[21px] flex mb-[21px] text-[16px] text-[#1D1F3A] font-500 items-center">
                    <span class="flex ml-[20px] items-center">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        云编译流程
                    </span>
                </div>
                <div class="ml-[40px]">
                    <el-timeline>
                        <el-timeline-item :hollow="true">
                            <!-- <template #dot>
                                <div class="w-[15px] h-[15px] bg-primary rounded-[50%] text-[9px] text-[#fff] flex items-center justify-center">1</div>
                            </template> -->
                            <div class="text-[16px] text-[#1D1F3A]">编译admin代码</div>
                            <div class="p-[10px] bg-[#F9F9FB] mt-[10px] text-[#4F516D] text-[14px] w-[1085px] border-[#F1F1F8] border-solid border-[1px] h-[40px] flex items-center rounded-[4px]">
                                <span>云编译会将admin端的vue代码编译为对应的html文件，同时将生成的代码下载到系统 niucloud 下的</span>
                                <span class="text-[#F09000] mx-[3px] font-bold">public/admin</span>
                                <span>目录中。后台的访问路径将变为</span>
                                <span class="text-primary ml-[3px] font-500">https://域名/admin</span>
                            </div>
                        </el-timeline-item>
                        <el-timeline-item :hollow="true">
                            <div class="text-[16px] text-[#1D1F3A]">编译uniapp代码</div>
                            <div class="p-[10px] bg-[#F9F9FB] mt-[10px] text-[#4F516D] text-[14px] w-[1085px] border-[#F1F1F8] border-solid border-[1px] h-[40px] flex items-center rounded-[4px]">
                                <span>云编泽会将uniapp端的vue代码编译为对应的html文件，同时将生成的代码下载到系统 niucloud下的</span>
                                <span class="text-[#F09000] mx-[3px] font-bold">public/wap</span>
                                <span>目录中，这样手机端网页的访问路径将变为</span>
                                <span class="text-primary ml-[3px] font-500"> https://域名/wap</span>
                            </div>
                        </el-timeline-item>
                        <el-timeline-item :hollow="true">
                            <div class="text-[16px] text-[#1D1F3A]">编译web代码</div>
                            <div class="p-[10px] bg-[#F9F9FB] mt-[10px] text-[#4F516D] text-[14px] w-[1085px] border-[#F1F1F8] border-solid border-[1px] h-[40px] flex items-center rounded-[4px]">
                                <span>云编泽会将web端的vue代码编译为对应的html文件，同时将生成的代码下载到系统 niucloud下的</span>
                                <span class="text-[#F09000] mx-[3px] font-bold">public/web</span>
                                <span>目录中，这样电脑端网页的访问路径将变为</span>
                                <span class="text-primary ml-[3px] font-500"> https://域名/web</span>
                            </div>
                        </el-timeline-item>
                    </el-timeline>
                </div>
            </div>
            <div class="mt-[10px]">
                <div class="panel-title bg-[#F4F5F7] border-[#E6E6E6] border-solid border-b-[1px] h-[40px] flex items-center p-[10px]">
                    <span class="text-[16px] font-500 text-[#1D1F3A]">第三方云编译</span>
                     <el-switch v-model="isCloudCompilation" :active-value="1" :inactive-value="0" class="ml-[10px]" @change="confirm" />
                     <span class="ml-[10px] text-[#9699B6] text-[12px]">自己搭建第三方云编译服务器，无需等待</span>
                </div>
                <div class="mt-[20px] flex mb-[14px] text-[16px] items-center text-[#1D1F3A]">
                    <span class="flex ml-[20px] items-center">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        温馨提示
                    </span>
                    <span class="text-[12px] text-[#9699B6] ml-[10px]">运行环境要求：需预先配置 Nodejs 环境</span>
                    <span class="text-[14px]  text-primary cursor-pointer ml-[10px] border-b-[1px] border-solid border-primary"  @click="linkEvent('https://doc.niucloud.com/saas.html?keywords=/di-san-fang-yun-bian-yi-pei-zhi')">搭建教程</span>
                </div>
                 <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">
                    <span>1、下载第三方云编译服务器搭建程序包</span><span class="text-primary cursor-pointer "  @click="linkEvent('https://gitee.com/niucloud-team/niucloud-compile-server')"> niucloud-compile-server</span>
                </div>
                 <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">
                    <span>2、请在指定目录（不能包含中文）下执行 npm install 命令安装依赖包</span>
                </div>
                 <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">
                    <span>3、启动编译服务器：执行 node niucloud-compile-server.js 命令</span>
                 </div>
                 <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">
                    <span>4、填写服务器地址并成功连通测试后，点击开启即可享受自己搭建的云编译服务器，编译将无需排队等待。</span>
                 </div>
                <div class="mt-[20px] flex mb-[14px] text-[16px] items-center text-[#1D1F3A]">
                    <span class="flex ml-[20px] items-center">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        云编译服务器设置
                    </span>
                </div>
                <div class="mt-[20px] flex mb-[14px] text-[16px] items-center text-[#1D1F3A] ml-[20px]">
                    <span class="flex ml-[20px] items-center">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        服务器地址
                    </span>
                </div>
                <div class="flex ml-[40px] mb-[30px] items-center">
                        <el-input clearable placeholder="请输入服务器地址" class="!w-[520px]"  maxlength="200" v-model="serverurl" />
                        <el-button type="primary"  class="ml-[10px]" @click="confirm">确定</el-button>
                        <el-button type="primary" plain class="ml-[10px]" @click="connect" :loading="testConnectLoading">连通测试</el-button>
                </div>
            </div>
            <div class="mt-[10px]">
                <div class="panel-title bg-[#F4F5F7] border-[#E6E6E6] border-solid border-b-[1px] h-[40px] flex items-center p-[10px]">
                    <span class="text-[16px] font-500 text-[#1D1F3A]">本地编译</span>
                </div>
                <div class="mt-[20px] flex mb-[14px] text-[16px] items-center text-[#1D1F3A]">
                    <span class="flex ml-[20px] items-center">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        温馨提示
                    </span>
                </div>
                <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">
                    <span>1、如果本地安装了Node环境，可以进行本地编译，要求</span>
                    <span class="text-[#DA203E] ml-[3px] font-500">Node版本>=18</span>
                </div>
                <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">2、默认本地编译流程与云编译相同，执行本地编译命令后，会将编译后的代码移动到系统niucloud下的public下的对应端口目录下</div>
                <div class="ml-[40px] text-[14px] text-[#4F516D] mb-[18px]">3、由于云编译配置的访问路径是固定的，针对客户有独立部署admin，wap，web等个性化端口名称配置需求，需要进行本地编译</div>
                <div class="mt-[20px] flex mb-[14px] text-[16px] items-center text-[#1D1F3A]">
                    <span class="flex ml-[20px] items-center">
                        <!-- <i class="w-[3px] h-[12px] bg-primary mr-[6px] block"></i> -->
                        本地编译命令参考
                    </span>
                </div>
                <div>
                    <div class="ml-[40px] text-[#374151] text-[14px] italic">
                        <span class="text-[16px] italic">#安装依赖：</span>
                        <span class="italic">进入admin端与uniapp端以及web端目录都可执行</span>
                    </div>
                    <div class="ml-[40px] w-[1085px] h-[40px]  bg-[#F9F9FB] rounded-[4px] mt-[10px] flex items-center justify-between border-[#F1F1F8] border-solid border-[1px] px-[10px]">
                        <span class="text-[14px] text-[#374151]">npm install</span>
                        <span class="iconfont iconfuzhiV6xx1 !text-[#252B3A] cursor-pointer" @click="copyEvent('npm install')"></span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#374151] italic">
                        <span class="text-[16px] italic">#后台admin端口打包：</span>
                        <span>进入admin目录下执行，执行后编译代码默认移动到系统的niucloud下的</span>
                        <span class="text-[#F09000] mx-[3px]">public/admin</span>
                        <span>目录下</span>
                    </div>
                    <div class="ml-[40px] w-[1085px] h-[40px]  bg-[#F9F9FB] rounded-[4px] mt-[10px] flex items-center justify-between border-[#F1F1F8] border-solid border-[1px] px-[10px]">
                        <span class="text-[14px] text-[#374151]">npm run build</span>
                        <span class="iconfont iconfuzhiV6xx1 !text-[#252B3A] cursor-pointer" @click="copyEvent('npm run build')"></span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#374151] italic">
                        <span class="text-[16px] italic">#使用uniapp打包H5：</span>
                        <span>进入uniapp目录下执行，执行后编译代码默认移动到系统niucloud下的</span>
                        <span class="text-[#F09000] mx-[3px]">public/wap</span>
                        <span>目录下</span>
                    </div>
                    <div class="ml-[40px] w-[1085px] h-[40px]  bg-[#F9F9FB] rounded-[4px] mt-[10px] flex items-center justify-between border-[#F1F1F8] border-solid border-[1px] px-[10px]">
                        <span class="text-[14px] text-[#374151]">npm run build:h5</span>
                        <span class="iconfont iconfuzhiV6xx1 !text-[#252B3A] cursor-pointer" @click="copyEvent('npm run build:h5')"></span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#374151] italic">
                        <span class="text-[16px] italic">#使用uniapp打包微信小程序：</span>
                        <span>进入uniapp目录下执行，执行后编译代码默认移动到系统niucloud下的</span>
                        <span class="text-[#F09000] mx-[3px]">uni-app/dist/build/mp-weixin</span>
                        <span>目录</span>
                    </div>
                    <div class="ml-[40px] w-[1085px] h-[40px]  bg-[#F9F9FB] rounded-[4px] mt-[10px] flex items-center justify-between border-[#F1F1F8] border-solid border-[1px] px-[10px]">
                        <span class="text-[14px] text-[#374151]">npm run build:mp-weixin</span>
                        <span class="iconfont iconfuzhiV6xx1 !text-[#252B3A] cursor-pointer" @click="copyEvent('npm run build:mp-weixin')"></span>
                    </div>
                </div>
                <div class="mt-[21px]">
                    <div class="ml-[40px] text-[14px] text-[#374151] italic">
                        <span class="text-[16px] italic">#前台web(pc)端打包:：</span>
                        <span>进入web目录下执行，执行后编译代码默认移动到系统niucloud下的</span>
                        <span class="text-[#F09000] mx-[3px]">public/web</span>
                        <span>目录下</span>
                    </div>
                    <div class="ml-[40px] w-[1085px] h-[40px]  bg-[#F9F9FB] rounded-[4px] mt-[10px] flex items-center justify-between border-[#F1F1F8] border-solid border-[1px] px-[10px]">
                        <span class="text-[14px] text-[#374151]">npm run generate</span>
                        <span class="iconfont iconfuzhiV6xx1 !text-[#252B3A] cursor-pointer" @click="copyEvent('npm run build')"></span>
                    </div>
                </div>
            </div>
        </div>
        <upgrade ref="upgradeRef" @cloudbuild="handleCloudBuild"/>
        <cloud-build ref="cloudBuildRef"/>
    </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { useClipboard } from '@vueuse/core'
import { t } from '@/lang'
import Upgrade from '@/app/components/upgrade/index.vue'
import CloudBuild from '@/app/components/cloud-build/index.vue'
import { connectTest, setLocalUrl, getLocalUrl } from '@/app/api/upgrade'
import Test from '@/utils/test'
const loading = ref<Boolean>(false)
const btnLoading = ref<Boolean>(false)
const testConnectLoading = ref(false)

// 云编译调用
const cloudBuildRef = ref<any>(null)
const handleCloudBuild = () => {
    ElMessageBox.confirm(t('cloudBuildTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        cloudBuildRef.value?.open()
    })
}

// 复制命令
const { copy, isSupported, copied } = useClipboard()

const copyEvent = (text: string) => {
    if (!isSupported.value) {
        ElMessage({
            message: t('notSupportCopy'),
            type: 'warning'
        })
        return
    }
    copy(text)
}

watch(copied, () => {
    if (copied.value) {
        ElMessage({
            message: t('copySuccess'),
            type: 'success'
        })
    }
})

const linkEvent = (url: string) => {
    window.open(url, '_blank')
}
const isCloudCompilation = ref(0)
const serverurl = ref('')
const connect = async () => {
    if (serverurl.value != '' && !Test.url(serverurl.value)) {
        ElMessage({
            message: '请输入正确的服务器地址，必须以http://或https://开头',
            type: 'warning'
        })
        return
    }
    testConnectLoading.value = true
    connectTest({
        url: serverurl.value
    }).then(res => {
        if (res.data == false) {
            ElMessage({
                message: '连通测试失败',
                type: 'error'
            })
        } else {
            ElMessage({
                message: '连通测试成功',
                type: 'success'
            })
        }
        testConnectLoading.value = false
    }).catch(() => {
        testConnectLoading.value = false
    })
}
const confirm = async () => {
    if (serverurl.value != '' && !Test.url(serverurl.value)) {
        ElMessage({
            message: '请输入正确的服务器地址，必须以http://或https://开头',
            type: 'warning'
        })
        return
    }
    btnLoading.value = true
    setLocalUrl({
        url: serverurl.value,
        is_open: isCloudCompilation.value
    }).then(res => {
        if (res.data == 1) {
            ElMessage({
                message: '保存成功',
                type: 'success'
            })
            getLocalUrlFn()
        }
    }).catch(() => {
        ElMessage({
            message: '保存失败',
            type: 'error'
        })
    }).finally(() => {
        btnLoading.value = false
    })
}
const getLocalUrlFn = async () => {
    getLocalUrl({}).then(res => {
        serverurl.value = res.data.baseUri
        isCloudCompilation.value = res.data.isOpen
    })
}
getLocalUrlFn()
</script>

<style lang="scss" scoped>
.btn-time {
    line-height: 36px;
    text-align: center;
}
:deep(.el-button){
    border-radius: 4px !important;
}
:deep(.el-timeline-item__node--normal){
    width: 16px !important;
    height: 16px !important;
}
:deep(.el-timeline-item__tail){
    left: 6px !important;
}
:deep(.el-timeline-item__node.is-hollow){
    background: #9699B6 !important;
    border-width: 3px !important;
}
</style>
