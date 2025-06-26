<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="router.push(`/shop/goods/attr`)" />
        </el-card>
        
        <el-card class="box-card !border-none" shadow="never">

            <!-- 商品参数基础信息 -->
            <el-row :gutter="20" class="text-[14px]" v-if="Object.keys(attrInfo).length">
                <el-col :span="8">
                    <label>{{ t('attrName') }}：</label>
                    <span class="ml-[10px]">{{ attrInfo.attr_name }}</span>
                </el-col>
                <el-col :span="6">
                    <label>{{ t('sort') }}：</label>
                    <span class="ml-[10px]">{{ attrInfo.sort }}</span>
                </el-col>
                <el-col :span="6">
                    <el-button type="primary" link @click="editEvent">{{ t('edit') }}</el-button>
                </el-col>
            </el-row>

            <el-button type="primary" @click="openAttrValueEvent" class="my-[15px]">{{ t('addShopGoodsAttr') }}</el-button>

            <el-table :data="goodsAttrTable.data" size="large" v-loading="goodsAttrTable.loading">
                <template #empty>
                    <span>{{ !goodsAttrTable.loading ? t('emptyData') : '' }}</span>
                </template>

                <el-table-column prop="attr_value_name" :label="t('attrValueName')" min-width="200" :show-overflow-tooltip="true"/>

                <el-table-column prop="type" :label="t('attrValueType')" min-width="100" :show-overflow-tooltip="true">
                    <template #default="{ row }">
                        <template v-if="row.type == 'radio'">
                            <span>{{ t('attrValueTypeRadio') }}</span>
                        </template>
                        <template v-else-if="row.type == 'checkbox'">
                            <span>{{ t('attrValueTypeCheckbox') }}</span>
                        </template>
                        <template v-if="row.type == 'text'">
                            <span>{{ t('attrValueTypeText') }}</span>
                        </template>
                    </template>
                </el-table-column>

                <el-table-column prop="child" :label="t('attrValueChild')" min-width="320" :show-overflow-tooltip="true">
                    <template #default="{ row }">
                        <template v-if="row.type != 'text'">
                            <template v-for="(item,index) in row.child">
                                <span :class="{ 'mr-[5px]' : (index+1) != row.child.length }">{{ item.name }}</span>
                            </template>
                        </template>
                        <template v-else>
                            <span>-</span>
                        </template>
                    </template>
                </el-table-column>

                <el-table-column prop="sort" :label="t('sort')" min-width="120" sortable="custom">
                    <template #default="{ row,$index }">
                        <el-input v-model.trim="row.sort" class="w-[70px]" maxlength="8" @input="sortInputListener($event, row)" />
                    </template>
                </el-table-column>

                <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                   <template #default="{ row,$index }">
                       <el-button type="primary" link @click="editAttrValueEvent(row,$index)">{{ t('edit') }}</el-button>
                       <el-button type="primary" link @click="deleteEvent($index)">{{ t('delete') }}</el-button>
                   </template>
                </el-table-column>

            </el-table>

        </el-card>

        <!-- 编辑商品参数信息 -->
        <el-dialog v-model="showDialogByAttr" :title="t('updateAttr')" width="500px" :close-on-click-modal="false" :destroy-on-close="true">
            <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRulesByAttr" class="page-form" v-loading="loadingByAttr">
                <el-form-item :label="t('attrName')" prop="attr_name">
                    <el-input v-model.trim="formData.attr_name" clearable :placeholder="t('attrNamePlaceholder')" class="input-width"  maxlength="20" />
                </el-form-item>

                <el-form-item :label="t('sort')" >
                    <el-input v-model.trim="formData.sort"  maxlength="8" show-word-limit clearable :placeholder="t('sortPlaceholder')" class="input-width" @keyup="filterNumber($event)"/>
                </el-form-item>

            </el-form>

            <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialogByAttr = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loadingByAttr" @click="confirmAttr(formRef)">{{ t('confirm') }}</el-button>
            </span>
            </template>
        </el-dialog>

        <!-- 编辑商品参数值信息 -->
        <el-dialog v-model="showDialogByAttrValue" :title="attrValueTitleDialog" width="700px" :close-on-click-modal="false" :destroy-on-close="true">
            <el-form :model="formAttrValueData" label-width="120px" ref="formAttrValueRef" :rules="formRulesByAttrValue" class="page-form" v-loading="loadingByAttrValue">
                <el-form-item :label="t('attrValueName')" prop="attr_value_name">
                    <el-input v-model.trim="formAttrValueData.attr_value_name" clearable :placeholder="t('attrValueNamePlaceholder')" class="input-width" maxlength="20" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('attrValueType')">
                    <div class="flex flex-col">
                        <el-select v-model="formAttrValueData.type" class="!w-[150px]" :disabled="actionAttrValueId > -1">
                            <el-option v-for="item in attrValueTypeOptions" :label="item.label" :value="item.value" />
                        </el-select>
                        <div class="mt-[10px] text-[12px] text-[#999] leading-[1]">注意：编辑时参数类型不可更改</div>
                    </div>
                </el-form-item>
                <el-form-item :label="t('sort')" >
                    <el-input v-model.trim="formAttrValueData.sort" maxlength="8" show-word-limit clearable :placeholder="t('sortPlaceholder')" class="!w-[150px]" @keyup="filterNumber($event)"/>
                </el-form-item>

                <template v-if="formAttrValueData.type != 'text'">
                    <el-table :data="formAttrValueData.child" size="large">
                        <template #empty>
                            <span>{{ formAttrValueData.child.length == 0 ? t('emptyData') : '' }}</span>
                        </template>

                        <el-table-column prop="name" :label="t('attrValueName')" min-width="200">
                            <template #default="{ row }">
                                <el-input v-model.trim="row.name" class="input-width" maxlength="20" :placeholder="t('attrValueNamePlaceholder')" clearable show-word-limit />
                            </template>
                        </el-table-column>

                        <el-table-column prop="name" :label="t('sort')" min-width="120">
                            <template #default="{ row }">
                                <el-input v-model.trim="row.sort" class="!w-[150px]" maxlength="8" :placeholder="t('sortPlaceholder')" clearable show-word-limit @keyup="filterNumber($event)" />
                            </template>
                        </el-table-column>

                        <el-table-column :label="t('operation')" fixed="right" align="right" min-width="60">
                            <template #default="{ row,$index }">
                                <el-button type="primary" link @click="deleteAttrValueEvent(row,$index)">{{ t('delete') }}</el-button>
                            </template>
                        </el-table-column>

                    </el-table>

                    <el-button type="primary" plain @click="addAttrValueEvent" class="my-[10px]" v-show="formAttrValueData.child.length < maxLength">{{ t('addAttrValue') }}</el-button>
                </template>

            </el-form>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialogByAttrValue = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" :loading="loadingByAttrValue" @click="confirmAttrValue(formAttrValueRef)">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref,computed } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from "@element-plus/icons-vue"
import { ElMessage,ElMessageBox,FormInstance } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { debounce,filterNumber } from '@/utils/common'
import { getAttrInfo,editAttr, modifyAttrValue } from '@/addon/shop/api/goods'
import { cloneDeep } from 'lodash-es'

const route = useRoute()
const router = useRouter()

const pageName = route.meta.title;

route.query.attr_id = route.query.attr_id || 0

const attrId:any = ref(route.query.attr_id)
const attrInfo:any = reactive({})

const maxLength = ref(30) // 参数值最大数量控制

const goodsAttrTable = reactive({
    loading: true,
    data: []
})

const loadAttrInfo = ()=> {
    // 查询商品参数信息
    getAttrInfo(attrId.value).then((res: any) => {
        Object.assign(attrInfo, res.data)
        if (attrInfo.attr_value_format) {
            attrInfo.attr_value_format = JSON.parse(attrInfo.attr_value_format);
        } else {
            attrInfo.attr_value_format = []
        }

        goodsAttrTable.data = cloneDeep(attrInfo.attr_value_format)
        goodsAttrTable.data.sort((a: any, b: any) => {
            return b.sort - a.sort
        });

        goodsAttrTable.loading = false
    })
}

loadAttrInfo()

const showDialogByAttr = ref(false)
const loadingByAttr = ref(false)
const formRef = ref<FormInstance>()
const formData = reactive({
    attr_id: 0,
    attr_name: '',
    sort: 0
})

// 表单验证规则
const formRulesByAttr = computed(() => {
    return {
        attr_name: [
            { required: true, message: t('attrNamePlaceholder'), trigger: 'blur' }
        ]
    }
})

// 编辑商品参数
const editEvent = ()=>{
    formData.attr_id = attrInfo.attr_id;
    formData.attr_name = attrInfo.attr_name;
    formData.sort = attrInfo.sort
    showDialogByAttr.value = true
}

/**
 * 确认
 * @param formEl
 */
const confirmAttr = async (formEl: FormInstance | undefined) => {
    if (loadingByAttr.value || !formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            loadingByAttr.value = true
            editAttr(formData).then(res => {
                loadingByAttr.value = false
                showDialogByAttr.value = false
                loadAttrInfo()
            }).catch(err => {
                loadingByAttr.value = false
            })
        }
    })
}

// 删除商品参数
const deleteEvent = (index: any) => {
    ElMessageBox.confirm(t('goodsAttrDeleteTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning',
    }).then(() => {
        if (repeatAttrValue.value) return
        repeatAttrValue.value = true

        attrInfo.attr_value_format.splice(index, 1);
        let data = {
            attr_id: attrId.value,
            attr_value_format: JSON.stringify(attrInfo.attr_value_format)
        };
        modifyAttrValue(data).then(res => {
            repeatAttrValue.value = false
            loadAttrInfo()
        }).catch(err => {
            repeatAttrValue.value = false
        })
    })
}

const showDialogByAttrValue = ref(false)
const loadingByAttrValue = ref(false)
const formAttrValueRef = ref<FormInstance>()
const attrValueTitleDialog = ref('')
const formAttrValueData:any = reactive({
    attr_value_id: 0,
    attr_value_name: '',
    type: 'radio',
    sort: 0,
    child: <any>[]
})

// 参数类型下拉框
const attrValueTypeOptions = reactive([
    {
        label:'单选',
        value:'radio'
    },
    {
        label:'多选',
        value:'checkbox'
    },
    {
        label:'输入',
        value:'text'
    }
])

// 生成随机数，商品参数中添加的为正数，商品中为负数，方便区分
const generateRandom = () => {
    return (Math.floor(new Date().getSeconds()) + Math.floor(new Date().getMilliseconds()));
}

// 打开商品参数弹出框
const openAttrValueEvent = ()=> {
    formAttrValueData.attr_value_id = attrInfo.attr_value_format.length + generateRandom();
    formAttrValueData.attr_value_name = '';
    formAttrValueData.type = 'radio';
    formAttrValueData.sort = 0;
    formAttrValueData.child = [];

    showDialogByAttrValue.value = true;
    attrValueTitleDialog.value = t('addShopGoodsAttr');
    actionAttrValueId.value = -1
}

// 修改参数的排序号
const sortInputListener = debounce((sort:any, row:any) => {
    if (isNaN(sort) || !/^\d{0,10}$/.test(sort)) {
        ElMessage({
            type: 'warning',
            message: `${t('sortTips')}`
        })
        return
    }

    for (let i = 0; i < attrInfo.attr_value_format.length; i++) {
        if (attrInfo.attr_value_format[i].attr_value_id == row.attr_value_id) {
            attrInfo.attr_value_format[i].sort = sort ? sort : 0;
            break;
        }
    }

    let data = {
        attr_id: attrId.value,
        attr_value_format: JSON.stringify(attrInfo.attr_value_format)
    };
    modifyAttrValue(data).then(res => {
        loadAttrInfo()
    }).catch(err => {
    })
})

// 添加参数值
const addAttrValueEvent = ()=>{
    formAttrValueData.child.push({
        id: formAttrValueData.child.length + generateRandom(),
        name: '',
        sort: 0
    });
}

// 编辑商品参数值
const editAttrValueEvent = (data:any,index:any)=> {
    attrValueTitleDialog.value = t('updateShopGoodsAttr');
    actionAttrValueId.value = data.attr_value_id
    Object.assign(formAttrValueData,cloneDeep(data))
    showDialogByAttrValue.value = true;
}

// 表单验证规则
const formRulesByAttrValue = computed(() => {
    return {
        attr_value_name: [
            { required: true, message: t('attrValueNamePlaceholder'), trigger: 'blur' }
        ]
    }
})

const actionAttrValueId = ref(-1)
const repeatAttrValue = ref(false)
const confirmAttrValue = async (formEl: FormInstance | undefined) => {
    if (loadingByAttrValue.value || !formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            if(formAttrValueData.type != 'text'){
                if(formAttrValueData.child.length == 0){
                    ElMessage({type: 'warning', message: `${t('attrValueNamePlaceholder')}`})
                    return;
                }
                for(let i=0;i<formAttrValueData.child.length;i++){
                    if(formAttrValueData.child[i].name == ''){
                        ElMessage({type: 'warning', message: `${t('attrValueNamePlaceholder')}`})
                        break;
                    }
                }
            }

            if (repeatAttrValue.value) return
            repeatAttrValue.value = true

            formAttrValueData.child.forEach((item:any)=>{
                item.sort = item.sort ? item.sort : 0
            })

            formAttrValueData.child.sort((a: any, b: any) => {
                return b.sort - a.sort
            });

            loadingByAttrValue.value = true
            if(actionAttrValueId.value == -1) {
                attrInfo.attr_value_format.push(formAttrValueData)
            }else{
                for (let i = 0; i < attrInfo.attr_value_format.length; i++) {
                    if (attrInfo.attr_value_format[i].attr_value_id == actionAttrValueId.value) {
                        attrInfo.attr_value_format[i] = cloneDeep(formAttrValueData)
                        break;
                    }
                }
            }
            let data = {
                attr_id : attrId.value,
                attr_value_format : JSON.stringify(attrInfo.attr_value_format)
            };
            modifyAttrValue(data).then(res => {
                loadingByAttrValue.value = false
                showDialogByAttrValue.value = false
                repeatAttrValue.value = false
                loadAttrInfo()
            }).catch(err => {
                loadingByAttrValue.value = false
                repeatAttrValue.value = false
            })
        }
    })
}

// 删除参数值
const deleteAttrValueEvent = (item:any,index:any)=> {
    formAttrValueData.child.splice(index,1);
}
</script>

<style lang="scss" scoped>
</style>
