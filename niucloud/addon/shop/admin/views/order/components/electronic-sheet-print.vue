<template>
    <el-dialog v-model="showDialog" :title="t('electronicSheetPrintTitle')" :width="printDialogWidth" class="diy-dialog-wrap" :destroy-on-close="true" :close-on-click-modal="false">

        <el-alert type="warning" :closable="false" class="!mb-[10px]">
            <template #default>
                <p>注意事项：</p>
                <p>* 配送方式为物流配送的订单支持打印电子面单</p>
                <p>* 无需物流、虚拟发货不支持打印电子面单</p>
                <p>* 请对应物流公司选择面单模板</p>
                <p>* 批量打印时，只能选择一种面单模板</p>
                <p>* 单个打印时，如果有多个包裹，会展示商品信息，支持多包裹打印电子面单</p>
                <p>* 多包裹打印时，请不要重复选择面单模板</p>
            </template>
        </el-alert>

        <el-form :model="formData" ref="formRef" :rules="formRules" class="page-form es-form" v-loading="loading">

            <!-- 单个订单打印，更加详细，展示包裹 -->
            <template v-if="formData.print_type == 'single'">

                <template v-if="packageList.length">

                    <el-tabs v-model="currentPackageIndex" @tab-change="handleClick">
                        <el-tab-pane v-for="(item,index) in packageList" :label="'包裹' + (index + 1)" :name="index" />
                    </el-tabs>

                    <el-form-item :label="t('electronicSheetTemplate')">
                        <el-select v-model="formData.list[currentPackageIndex].electronic_sheet_id"
                                   :placeholder="t('electronicSheetTemplatePlaceholder')" clearable class="input-width">
                            <el-option v-for="(item) in electronicSheetData" :key="item.id" :label="item.template_name"
                                       :value="item.id" />
                        </el-select>
                    </el-form-item>

                    <el-form-item :label="t('company')">
                        <div class="truncate">{{ packageList[currentPackageIndex].company.company_name }}</div>
                    </el-form-item>

                    <el-form-item :label="t('expressNumber')">
                        <div class="truncate">{{ packageList[currentPackageIndex].express_number }}</div>
                    </el-form-item>

                    <el-table :data="packageList[currentPackageIndex].order_goods" size="large">
                        <el-table-column :label="t('goodsName')" align="left" width="300">
                            <template #default="{ row }">
                                <div class="flex">
                                    <div class="flex items-center w-[50px] h-[50px] mr-[10px]">
                                        <img class="w-[50px] h-[50px]" :src="img(row.goods_image_thumb_small)" />
                                    </div>
                                    <div class="flex flex-col flex-1">
                                        <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                        <span class="text-[12px] text-[#999]">{{ row.sku_name }}</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="price" :label="t('price')" min-width="50" align="left" />
                        <el-table-column prop="num" :label="t('num')" min-width="50" align="right" />
                    </el-table>

                </template>

                <template v-if="singlePrintData['delivery_id_' + currentPackageId] && singlePrintData['delivery_id_' + currentPackageId].length">

                    <h3 class="my-[15px]">{{ t('electronicSheetPrintResult') }}</h3>

                    <el-table :data="singlePrintData['delivery_id_' + currentPackageId]" size="large" class="table-top">
                        <el-table-column :label="t('deliveryPackageNo')" min-width="80">
                            <template #default="{ row }">
                                <div>{{ row.delivery_id }}</div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('printStatus')" min-width="80">
                            <template #default="{ row }">
                                <el-tag class="cursor-pointer" :type="row.success ? 'success' : 'danger'">{{ row.success ? '成功' : '失败' }}</el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('printResultCode')" min-width="80">
                            <template #default="{ row }">
                                <div>{{ row.result_code }}</div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('printRemark')" min-width="200">
                            <template #default="{ row }">
                                <div>{{ row.reason }}</div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('operation')" fixed="right" align="right" min-width="80">
                            <template #default="{ row }">
                                <el-button type="primary" link @click="printEvent(row)" v-if="row.success">{{ t('electronicSheetPrintOperation') }}</el-button>
                            </template>
                        </el-table-column>

                    </el-table>
                </template>

            </template>

            <!-- 多个订单打印 -->
            <template v-if="formData.print_type == 'multiple'">

                <el-form-item :label="t('electronicSheetTemplate')" prop="electronic_sheet_id">
                    <el-select v-model="formData.electronic_sheet_id" :placeholder="t('electronicSheetTemplatePlaceholder')" clearable class="input-width">
                        <el-option v-for="(item) in electronicSheetData" :key="item.id" :label="item.template_name" :value="item.id" />
                    </el-select>
                </el-form-item>

                <el-table :data="multiplePrintData" size="large" class="table-top" v-if="multiplePrintData.length > 1">
                    <el-table-column prop="order_no" :label="t('orderNo')" min-width="150" />
                    <el-table-column :label="t('printStatus')" min-width="80">
                        <template #default="{ row }">
                            <el-tag class="cursor-pointer" :type="row.success ? 'success' : 'danger'">{{ row.success ? '成功' : '失败' }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('printResultCode')" min-width="80">
                        <template #default="{ row }">
                            <div>{{ row.result_code }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('printRemark')" min-width="160">
                        <template #default="{ row }">
                            <div>{{ row.reason }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="80">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="printEvent(row)" v-if="row.success">{{ t('electronicSheetPrintOperation') }}</el-button>
                        </template>
                    </el-table-column>

                </el-table>
            </template>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="repeat" @click="confirm(formRef)">{{ t('confirm') }}</el-button>
            </span>
        </template>

    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import {
    getElectronicSheetConfig,
    getElectronicSheetList,
    printElectronicSheet
} from '@/addon/shop/api/electronic_sheet'
import { deliveryPackageList } from '@/addon/shop/api/order'
import { loadCLodop, getLodop } from '@/utils/lodop'
import { img } from '@/utils/common'
import { ElMessage, FormInstance } from 'element-plus'

const showDialog = ref(false)
const loading = ref(false)
const repeat = ref(false)

getElectronicSheetConfig().then((res: any) => {
    if (res.data) {
        loadCLodop(res.data)
    }
})

const electronicSheetData = ref([])
getElectronicSheetList({
    status: 1
}).then((res: any) => {
    if (res.data) {
        electronicSheetData.value = res.data;
    }
})

/**
 * 表单数据
 */
const initialFormData = {
    print_type: 'multiple',
    order_id: 0,
    electronic_sheet_id: '',
    list: []
}

const formData: Record<string, any> = reactive({ ...initialFormData })
const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        electronic_sheet_id: [
            { required: true, message: t('electronicSheetTemplatePlaceholder'), trigger: 'blur' }
        ]
    }
})

const emit = defineEmits(['complete'])

const printDialogWidth: any = ref('460px')

const packageList: any = ref([]) // 包裹列表
const currentPackageIndex: any = ref(0) // 当前选中包裹下标
const currentPackageId: any = ref(0) // 当前选中包裹id

const multiplePrintData: any = ref([]) // 多订单打印数据
const singlePrintData: any = ref({}) //  单订单打印数据

const handleClick = (index: any) => {
    currentPackageIndex.value = parseInt(index);
    currentPackageId.value = formData.list[index].delivery_id
}

const setFormData = async(data: any = null) => {
    loading.value = true;

    // 初始化数据
    Object.assign(formData, initialFormData);
    multiplePrintData.value = [];
    packageList.value = [];
    singlePrintData.value = {};
    currentPackageIndex.value = 0;
    currentPackageId.value = 0;

    if (!data) return;

    formData.order_id = data.order_id;
    formData.print_type = data.print_type;
    formData.list = [];

    // 打印单个订单，查询包裹
    if (data.print_type == 'single') {

        // 检测是否存在多个包裹的情况
        let tempDeliveryIdArr: any = [];
        data.order_goods.forEach((item: any) => {
            if (item.delivery_id && tempDeliveryIdArr.indexOf(item.delivery_id) == -1) {
                tempDeliveryIdArr.push(item.delivery_id)
            }
        });

        if (tempDeliveryIdArr.length > 1) {
            deliveryPackageList({
                order_id: formData.order_id
            }).then((res: any) => {
                if (res.data) {
                    let data = res.data.filter((item: any) => {
                        if (item.delivery_type == 'express' && item.sub_delivery_type == 'express') {
                            return item;
                        }
                    });

                    packageList.value.push(...data);
                    packageList.value.forEach((item: any) => {
                        formData.list.push({
                            delivery_id: item.id,
                            electronic_sheet_id: '',
                        })
                    })

                }
                loading.value = false;
                printDialogWidth.value = '800px';
            })
        } else {
            // 如果只有一个包裹，则不需要显示详细信息
            formData.print_type = 'multiple';
            printDialogWidth.value = '460px';
            loading.value = false;
        }

    } else {
        loading.value = false;
        printDialogWidth.value = '460px';
    }
};

/**
 * 确认
 * @param formEl
 */
const confirm = async(formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return;

    let LODOP = getLodop();
    if (!LODOP) return;

    await formEl.validate(async(valid) => {
        if (valid) {

            // 单订单打印，检测包裹有没有未选择面单模板
            let isFail = false;
            if (formData.print_type == 'single') {
                for (let i = 0; i < formData.list.length; i++) {
                    if (formData.list[i].electronic_sheet_id.length == 0) {
                        ElMessage({
                            type: 'warning',
                            message: `请选择[包裹${ i + 1 }]的面单模板`
                        });
                        currentPackageIndex.value = i;
                        isFail = true;
                        break;
                    }
                }
            }

            if (isFail) return;

            if (repeat.value) return;
            repeat.value = true;

            printElectronicSheet({
                print_type: formData.print_type,
                order_id: formData.order_id,
                electronic_sheet_id: formData.electronic_sheet_id,
                list: formData.list
            }).then((res: any) => {

                multiplePrintData.value = [];
                singlePrintData.value = {};

                if (res.data) {

                    let data = res.data;
                    let singleFailData: any = {}; // 单订单打印
                    let multipleFailData: any = []; // 多订单打印
                    let haveSuccessData = false;
                    LODOP.PRINT_INIT("打印电子面单"); // 只能初始化一次

                    if (formData.print_type == 'single') {

                        // 单订单打印
                        for (let i = 0; i < data.length; i++) {

                            if (!singlePrintData.value['delivery_id_' + data[i].delivery_id]) singlePrintData.value['delivery_id_' + data[i].delivery_id] = [];
                            if (!singleFailData['delivery_id_' + data[i].delivery_id]) singleFailData['delivery_id_' + data[i].delivery_id] = [];

                            if (data[i].success) {
                                // 调用打印机，开始打印
                                let html = data[i].print_template;
                                LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", html);
                                LODOP.NewPage(); // 批量打印，分页
                                haveSuccessData = true;
                                singlePrintData.value['delivery_id_' + data[i].delivery_id].push(data[i]);
                            } else {
                                singleFailData['delivery_id_' + data[i].delivery_id].push(data[i]);
                            }
                        }

                        for (let key in singlePrintData.value) {
                            if (singleFailData[key] && singleFailData[key].length) {
                                singlePrintData.value[key].push(...singleFailData[key])
                            }
                        }

                        if (Object.keys(singlePrintData.value).length > 1) {
                            printDialogWidth.value = '800px'
                        } else if (Object.keys(singlePrintData.value).length == 1) {
                            // 若打印单条数据并且只有一个包裹则关闭弹出框
                            showDialog.value = false
                        }

                    } else {

                        // 多订单打印
                        for (let i = 0; i < data.length; i++) {
                            if (data[i].success) {
                                // 调用打印机，开始打印
                                let html = data[i].print_template;
                                LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", html);
                                LODOP.NewPage(); // 批量打印，分页
                                haveSuccessData = true;
                                multiplePrintData.value.push(data[i]);
                            } else {
                                multipleFailData.push(data[i]);
                            }
                        }

                        if (multipleFailData.length) multiplePrintData.value.push(...multipleFailData);

                        if (multiplePrintData.value.length > 1) {
                            printDialogWidth.value = '800px'
                        } else if (multiplePrintData.value.length == 1) {
                            // 若打印单条数据并且只有一个包裹则关闭弹出框
                            showDialog.value = false
                        }
                    }

                    if (haveSuccessData) {
                        LODOP.PREVIEW(); // 预览
                        // LODOP.PRINT();  // 直接打印，会出现试用版提示
                        // LODOP.PRINTA(); // 选择打印机
                    }

                    repeat.value = false

                }

                emit('complete')
            }).catch(() => {
                repeat.value = false
            })

        }
    })
}

const printEvent = (data: any) => {
    let LODOP = getLodop();
    if (!LODOP) return;

    if (repeat.value) return
    repeat.value = true

    let html = data.print_template;
    LODOP.PRINT_INIT("打印电子面单");
    LODOP.ADD_PRINT_HTM(0, 0, "100%", "100%", html);
    LODOP.PREVIEW(); // 预览

    repeat.value = false
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
<style lang="scss">
.diy-dialog-wrap .el-form-item__label {
    height: auto !important;
}

.es-form .el-dialog__body .el-form-item {
    margin-bottom: 0px !important;
}
</style>
