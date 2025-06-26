<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('activeCubeBlockContent') }}</h3>
            <el-form label-width="85px" class="px-[10px]">

                <div ref="blockBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.list" :key="item.id" class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">
                        <el-form-item :label="t('bgImage')">
                            <upload-image v-model="item.bgUrl" :limit="1" />
                        </el-form-item>

                        <el-form-item :label="t('listFrameColor')">
                            <el-color-picker v-model="item.listFrame.startColor" show-alpha :predefine="diyStore.predefineColors" />
                            <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                            <el-color-picker v-model="item.listFrame.endColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>

                        <el-form-item :label="t('rankingTitleIcon')">
                            <upload-image v-model="item.imgUrl" :limit="1" />
                        </el-form-item>

                        <el-form-item :label="t('rankName')">
                            <el-input v-model="item.text" clearable :placeholder="t('rankNamePlaceholder')" maxlength="8" show-word-limit />
                        </el-form-item>
                        <el-form-item :label="t('rankTextColor')">
                            <el-color-picker v-model="item.textColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>

                        <el-form-item :label="t('rankingSubTitle')">
                            <el-input v-model.trim="item.subTitle.text" :placeholder="t('activeCubeSubTitlePlaceholder')" clearable maxlength="6" show-word-limit />
                        </el-form-item>

                        <el-form-item :label="t('rankingSubTitleTextColor')">
                            <el-color-picker v-model="item.subTitle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>

                        <el-form-item :label="t('rankingSubTitleLink')">
                            <diy-link v-model="item.subTitle.link" />
                        </el-form-item>

                        <el-form-item :label="t('rankSelectPopupSelectRankButton')">
                            <el-radio-group v-model="item.source" :title="t('rankSelectPopupSelectRankButton')">
                                <el-radio label="default">{{ t('defaultSources') }}</el-radio>
                                <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="t('customGoods')" v-if="item.source == 'custom'">
                            <rank-select-popup ref="goodsSelectPopupRef" v-model="diyStore.editComponent.list[index].rankIds" :max="1" />
                        </el-form-item>
                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]" v-show="diyStore.editComponent.list.length > 1" @click="diyStore.editComponent.list.splice(index,1)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>
                    </div>
                </div>

                <el-button v-show="diyStore.editComponent.list.length < 10" class="w-full" @click="addItem">{{ t('activeCubeAddItem') }}</el-button>

            </el-form>
        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('rankingStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('topRounded')">
                    <el-slider v-model="diyStore.editComponent.topElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('bottomRounded')">
                    <el-slider v-model="diyStore.editComponent.bottomElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive } from 'vue'
import rankSelectPopup from '@/addon/shop/views/marketing/goods_rank/components/rank-select-popup.vue'

const diyStore:any = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    diyStore.value[index].list.forEach((item: any) => {
        if (item.source == 'custom') {
            if (item.rankIds.length == 0) {
                res.code = false
                res.message = t('请选择榜单')
            }
        }
    });
    return res
}

const addItem = () => {
    diyStore.editComponent.list.push({
        id: diyStore.generateRandom(),
        bgUrl: '',
        text: '榜单名称',
        textColor: "#FFFFFF",
        imgUrl: "",
        subTitle: {
            text: '查看更多',
            textColor: '#FFFFFF',
            link: {
                name: ''
            }
        },
        listFrame: {
            startColor: '#FEA715',
            endColor: '#FE1E00',
        },
        source: 'default',
        rankIds: []
    })
}

defineExpose({})
</script>

<style lang="scss" scoped></style>
