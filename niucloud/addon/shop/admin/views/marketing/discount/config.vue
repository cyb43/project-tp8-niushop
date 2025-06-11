<template>
    <div class="main-container" v-loading="loading">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <div class="flex mt-[20px]">
                <div class="relative overflow-hidden w-[340px] h-[680px] z-0 bg-[#f5f5f5]">
                    <img class="absolute top-0 left-0 z-10 pointer-events-none" src="@/addon/shop/assets/discount_config.png"/>
                    <div class="absolute top-0 left-0 w-[340px]">
                        <img class="w-full h-[256px]" v-if="isShow" src="@/addon/shop/assets/discount_banner.png">
                        <el-carousel height="256px" arrow="never" v-else>
                            <template v-for="(item,index) in formData.list" :key="'img'+index">
                                <el-carousel-item v-if="item.imageUrl">
                                    <img class="w-full h-full" :src="img(item.imageUrl)">
                                </el-carousel-item>
                            </template>
                        </el-carousel>
                    </div>
                </div>
                <div class="ml-[20px]">
                    <h3 class="panel-title !text-sm">{{t('headTitle')}}</h3>

                    <el-form class="page-form" :model="formData" label-width="120px" ref="formRef">
                        <div v-for="(item,index) in formData.list" class="border-[1px] border-[var(--el-border-color)] border-dashed w-[500px] pt-[15px] mb-[15px] relative item" :key="index">
                            <el-form-item :label="t('image')" :prop="`list.${index}.imageUrl`"  :rules="[{
                                required: true,
                                trigger: 'change',
                                validator: (rule: any, value: any, callback: any) => {
                                    if (!value) {
                                        callback(t('imagePlaceholder'))
                                    }
                                    callback()
                                }
                            }]">
                                <upload-image v-model="item.imageUrl"  :limit="1"/>
                            </el-form-item>
                            <el-form-item :label="t('toLink')" :prop="`list.${index}.toLink.name`"  :rules="[{
                                required: true,
                                trigger: 'change',
                                validator: (rule: any, value: any, callback: any) => {
                                    if (!value) {
                                        callback(t('toLinkPlaceholder'))
                                    }
                                    callback()
                                }
                            }]">
                                <diy-link v-model="item.toLink"/>
                            </el-form-item>
                            <span v-if="formData.list.length>1" class="cursor-pointer absolute top-[-8px] right-[-8px] delete" @click="deleteConfigList(index)"><el-icon color="#bbbbbb" size="20px"><CircleCloseFilled /></el-icon></span>
                        </div>
                        <div class="flex w-full justify-center">
                            <el-button class="w-[400px]" @click="addConfigList">{{ t('addConfigList') }}</el-button>
                        </div>
                    </el-form>
                </div>
            </div>
        </el-card>

        <!-- 提交按钮 -->
        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(formRef)">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref,computed } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import {getActiveDiscountConfig,editActiveDiscountConfig} from "@/addon/shop/api/marketing";
import { FormInstance } from 'element-plus'
import { useRoute } from 'vue-router'

const route = useRoute()
const pageName = route.meta.title
const loading = ref(false)
const isShow = computed(()=>{
    return formData.value.list.every((el:any)=>el.imageUrl=='')
})

const formData: Record<string, any> = ref({
    list: [
        {imageUrl: '', toLink: {name: ''}},
    ]
})

const formRef = ref<FormInstance>()
const getActiveDiscountConfigFn=()=>{
    loading.value = true
    getActiveDiscountConfig().then((res:any)=>{
        if(res.data.length) formData.value.list=res.data
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

getActiveDiscountConfigFn()
const addConfigList = ()=>{
    formData.value.list.push({imageUrl:'',toLink:{name: ''}})
}
const deleteConfigList = (index:number)=>{
    formData.value.list.splice(index,1)
}
const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            editActiveDiscountConfig(formData.value).then((res:any) => {
                loading.value = false
                getActiveDiscountConfigFn()
            }).catch(() => {
                loading.value = false
            })
        }
    })
}
</script>

<style lang="scss" scoped>
    .item {
        .delete {
            display:none;
        }
        &:hover {
            .delete {
                display:block;
            }
        }
    }
</style>
