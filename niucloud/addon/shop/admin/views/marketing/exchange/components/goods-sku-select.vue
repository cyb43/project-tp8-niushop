<template>
    <div>
        <el-dialog v-model="showDialog" :title="t('goodsSkuTitle')" width="60%" :destroy-on-close="true">
            <el-table class="!max-w-[100%]" :data="tableData" size="large" ref="tableRef" @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55" />
                <el-table-column :label="t('goodsSelectPopupGoodsInfo')" min-width="300">
                    <template #default="{ row }">
                        <div class="flex items-center cursor-pointer">
                            <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                <el-image v-if="row.sku_image" class="w-[60px] h-[60px]" :src="img(row.sku_image)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                                <img v-else class="w-[70px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                            </div>
                            <div class="ml-2">
                                <span :title="row.sku_name" class="multi-hidden">{{ row.sku_name || row.goods?.goods_name }}</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="goods_price" :label="t('price')" min-width="170" />
                <el-table-column prop="goods_stock" :label="t('goodsStock')" min-width="170" />
            </el-table>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialog = false">{{ t("cancel") }}</el-button>
                    <el-button type="primary" @click="save">{{ t("confirm") }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { t } from "@/lang";
import { img } from "@/utils/common";
import { getGoodsSkuList } from '@/addon/shop/api/goods'
import { ref, nextTick } from "vue";

const showDialog = ref(false);
const loading = ref(false)
const tableRef = ref();
const tableData = ref();
const prop = defineProps({
    id: {
        type: String,
        default: '',
    },
});
const emit = defineEmits(["skuSelect"]);
const show = (data: any) => {
    showDialog.value = true;
    loading.value = true
    let sku_ids = data.map((el: any) => el.sku_id);
    getGoodsSkuList({
        goods_id: prop.id
    }).then(res => {
        tableData.value = res.data.map((el: any) => {
            el.goods_stock = el.stock + ''
            el.goods_price = el.price + ''
            el.limit_num = ''
            el.stock = ''
            el.point = ''
            el.price = ''
            return el
        })
        nextTick(() => {
            res.data.forEach((el: any) => {
                tableRef.value?.toggleRowSelection(el, false);
                if (sku_ids.includes(el.sku_id))
                    tableRef.value.toggleRowSelection(el, true);
            });
            loading.value = false
        })

    })

};
// 选中数据
const multipleSelection: any = ref([]);

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val;
};

const save = () => {
    emit("skuSelect", multipleSelection.value);
    showDialog.value = false;
    if (tableRef.value) tableRef.value.clearSelection();
};
defineExpose({
    show
});
</script>

<style lang="scss" scoped></style>
