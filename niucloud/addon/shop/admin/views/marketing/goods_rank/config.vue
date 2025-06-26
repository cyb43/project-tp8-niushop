<template>
    <div>
        <!-- 榜单设置 -->
        <el-card class="box-card !border-none main-container" shadow="never">
            <div class="flex justify-between items-center mb-[10px]">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form">
                <el-card class="box-card !border-none" shadow="never">
                    <!-- <el-form-item :label="t('rankName')" prop="rank_name">
                        <div class="flex items-center mx-[5px]">
                            <el-input v-model.trim="formData.rank_name" clearable class="input-width" :placeholder="t('rankNamePlaceholder')" maxlength="20" show-word-limit />
                        </div>
                    </el-form-item> -->
                    <el-form-item :label="t('rankImages')" prop="rank_images" :rules="[{
                        required: true,
                        trigger: 'change',
                        validator: (rule: any, value: any, callback: any) => {
                        if (!value) {
                            callback(t('imagePlaceholder'))
                        }
                        callback()
                        }
                    }]">
                        <upload-image v-model="formData.rank_images" :limit="1" />
                    </el-form-item>
                    <el-form-item :label="t('noColor')">
                        <el-color-picker v-model="formData.no_color" show-alpha  />
                    </el-form-item>
                    <el-form-item :label="t('selectedColor')">
                        <el-color-picker v-model="formData.select_color" show-alpha  />
                    </el-form-item>
                    <el-form-item :label="t('selectedBgColor')">
                        <el-color-picker v-model="formData.select_bg_color_start" show-alpha />
                        <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                        <el-color-picker v-model="formData.select_bg_color_end" show-alpha />
                    </el-form-item>

                    <!-- <el-form-item :label="t('rankRemark')" prop="rank_remark">
                        <el-input v-model="formData.rank_remark" :placeholder="t('rankRemarkPlaceholder')" type="textarea" maxlength="500" show-word-limit rows="5" class="!w-[400px]" clearable />
                        <el-button class="ml-[20px]" type="primary" @click="defaultRankRemarkEvent()" plain>{{ t('useDefaultRankRemark') }}</el-button>
                    </el-form-item> -->
                </el-card>
            </el-form>
        </el-card>

        <!-- 提交按钮 -->
        <div class="fixed-footer-wrap">
            <div class="fixed-footer h-[48px]">
                <el-button type="primary" @click="onSave">{{ t("save") }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed } from "vue";
import { t } from "@/lang";
import { useRoute } from "vue-router";
import { FormInstance } from "element-plus";
import { getRankConfig, setRankConfig } from "@/addon/shop/api/marketing";

const route = useRoute();
const pageName = route.meta.title;

const formData = ref({
    // rank_name: "",
    // rank_remark: "",
    no_color: "#FFFCF5",
    select_color: "#FF4142",
    select_bg_color_start: "#FFFFFF",
    select_bg_color_end: "#FFEBD7",
    rank_images: "",
})

const formRef = ref<FormInstance>();

const formRules = computed(() => {
    return {
        // rank_name: [
        //     { required: true, message: t("rankNamePlaceholder"), trigger: "blur" },
        // ],
        rank_images: [
            {
                required: true,
                message: t("imagePlaceholder"),
                trigger: "change",
            },
        ],
    };
});

const getRankConfigFn = () => {
    getRankConfig().then((res: any) => {
        Object.keys(formData.value).forEach((key) => {
            if (res.data[key]) formData.value[key] = res.data[key];
        });
    })
};

getRankConfigFn();

/**
 * 使用默认说明
 */
// const defaultRankRemarkEvent = () => {
//     formData.value.rank_remark = `精选商城内销量最高的商品，涵盖多个品类，为用户提供当前最受欢迎的商品参考。\n本榜单以销量为主要排名依据，保证推荐商品的高质量和高热度。`;
// }

/**** 提交 ****/
const preventDuplication = ref(false);
const onSave = async () => {
    if (preventDuplication.value) return;
    await formRef.value?.validate(async(valid) => {
        if (valid) {
            preventDuplication.value = true;

            setRankConfig(formData.value).then(() => {
                getRankConfigFn();
                preventDuplication.value = false;
            }).catch(() => {
                preventDuplication.value = false;
            });
        }
    });
};
</script>

<style lang="scss" scoped></style>
