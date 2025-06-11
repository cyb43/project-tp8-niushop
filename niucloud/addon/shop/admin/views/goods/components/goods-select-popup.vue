<template>
    <div>
        <div class="leading-[1]" @click="show">
            <slot>
                <el-button :disabled="prop.disabled">{{ t('goodsSelectPopupSelectGoodsButton') }}</el-button>
                <div class="inline-block ml-[10px] text-[14px]" v-show="goodsIds.length">
                    <span>{{ t('goodsSelectPopupSelect') }}</span>
                    <span class="text-primary mx-[2px]">{{ goodsIds.length }}</span>
                    <span>{{ t('goodsSelectPopupPiece') }}</span>
                </div>
            </slot>
        </div>
        <el-dialog v-model="showDialog" :title="t('goodsSelectPopupSelectGoodsDialog')" width="1000px" :destroy-on-close="true" :close-on-click-modal="false">

            <el-form :inline="true" :model="goodsTable.searchParam" ref="searchFormRef">
                <el-form-item prop="select_type" class="form-item-wrap">
                    <el-select v-model="goodsTable.searchParam.select_type" @change="handleSelectTypeChange">
                        <el-option :label="t('goodsSelectPopupAllGoods')" value="all" />
                        <el-option :label="t('goodsSelectPopupSelectedGoods')" value="selected" />
                    </el-select>
                </el-form-item>
                <el-form-item :label="t('goodsSelectPopupGoodsName')" prop="keyword" class="form-item-wrap">
                    <el-input v-model.trim="goodsTable.searchParam.keyword" :placeholder="t('goodsSelectPopupGoodsNamePlaceholder')" maxlength="60" />
                </el-form-item>
                <el-form-item :label="t('goodsSelectPopupGoodsCategory')" prop="goods_category" class="form-item-wrap">
                    <el-cascader  v-model="goodsTable.searchParam.goods_category"
                        :options="goodsCategoryOptions" :placeholder="t('goodsSelectPopupGoodsCategoryPlaceholder')"
                        clearable :props="{ value: 'value', label: 'label', emitPath:false }" />
                </el-form-item>
                <el-form-item :label="t('goodsSelectPopupGoodsType')" prop="goods_type" class="form-item-wrap" v-if="!prop.goodsType">
                    <el-select v-model="goodsTable.searchParam.goods_type" :placeholder="t('goodsSelectPopupGoodsTypePlaceholder')" clearable>
                        <el-option v-for="item in goodsType" :key="item.type" :label="item.name" :value="item.type" />
                    </el-select>
                </el-form-item>
                <el-form-item class="form-item-wrap">
                    <el-button type="primary" @click="loadGoodsList()">{{ t('search') }}</el-button>
                    <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                </el-form-item>
            </el-form>

            <div class="table w-[100%]" v-loading="goodsTable.loading">
                <div class="table-head flex items-center bg-[#f5f7f9] py-[8px]">
                    <div class="w-[3%]"></div>
                    <div class="w-[7%]">
                        <el-checkbox v-model="staircheckAll" v-if="prop.max>1" :indeterminate="isStairIndeterminate" @change="handleCheckAllChange" />
                    </div>
                    <div class="w-[50%]">商品信息</div>
                    <div class="w-[20%]">商品价格</div>
                    <div class="w-[20%]">库存</div>
                </div>
                <div class="table-body h-[350px] overflow-y-auto">
                    <div v-for="(row,rowIndex) in goodsTable.data" :key="rowIndex" class="flex flex-col">
                        <!-- 内容 -->
                        <div class="flex items-center border-solid border-[#e5e7eb] py-[8px] border-b-[1px]">
                            <div v-if="prop.mode == 'spu'" class="w-[3%]"></div>
                            <div v-if="prop.mode == 'sku' && row.skuList.length > 1" class="w-[3%] cursor-pointer text-center !text-[10px]" @click="secondLevelArrowChange(row)" :class="{'iconfont iconxiangyoujiantou': row.skuList.length, 'arrow-show': row.isShow}"></div>
                            <div v-if="prop.mode == 'sku' && row.skuList.length <= 1" class="w-[3%]"></div>
                            <div class="w-[7%]">
                                <el-checkbox v-model="row.secondLevelCheckAll" :indeterminate="row.isSecondLevelIndeterminate" @change="secondLevelHandleCheckAllChange($event,row)" />
                            </div>
                            <div class="flex items-center cursor-pointer w-[50%] pr-[25px]">
                                <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                    <el-image v-if="row.goods_cover_thumb_small" class="w-[60px] h-[60px]" :src="img(row.goods_cover_thumb_small)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" />
                                            </div>
                                        </template>
                                    </el-image>
                                    <img v-else class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                </div>
                                <div class="ml-2 flex flex-col items-start">
                                    <span :title="row.goods_name" class="multi-hidden leading-[1.4]">{{ row.goods_name }}</span>
                                    <span class="text-primary text-[12px]">{{ row.goods_type_name }}</span>
                                    <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.is_gift == 1">赠品</span>
                                </div>
                            </div>
                            <div class="w-[20%]">￥{{ row.goodsSku.price }}</div>
                            <div class="w-[20%]">{{row.stock}}</div>
                        </div>

                        <div v-show="prop.mode == 'sku' && row.skuList.length > 1">
                            <!-- 子级 -->
                            <div v-for="(item,index) in row.skuList" :key="index" class="flex items-center py-[8px] border-solid border-transparent border-b-[1px]" :class="{'hidden': !row.isShow, 'border-[#e5e7eb]': index == (row.skuList.length-1)}">
                                <div class="w-[6%]"></div>
                                <div class="w-[4%]">
                                    <el-checkbox v-model="item.threeLevelCheckAll"  @change="subChildHandleCheckAllChange($event,row,item)" />
                                </div>
                                <div class="flex items-center cursor-pointer w-[50%]">
                                    <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                        <el-image v-if="item.sku_image" class="w-[60px] h-[60px]" :src="img(item.sku_image)" fit="contain">
                                            <template #error>
                                                <div class="image-slot">
                                                    <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" />
                                                </div>
                                            </template>
                                        </el-image>
                                        <img v-else class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                    </div>
                                    <div class="ml-2">
                                        <span :title="item.sku_name || row.goods_name" class="multi-hidden leading-[1.4]">{{ item.sku_name || row.goods_name }}</span>
                                        <span class="text-primary text-[12px]">{{ row.goods_type_name }}</span>
                                    </div>
                                </div>
                                <div class="w-[20%] flex">￥{{ item.price }}</div>
                                <div class="w-[20%] flex">{{ item.stock }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!goodsTable.data.length && !goodsTable.loading" class="h-[60px] flex items-center justify-center border-solid border-[#e5e7eb] py-[12px] border-b-[1px]">暂无数据</div>
                </div>
            </div>

            <div class="mt-[16px] flex">
                <div class="flex items-center flex-1">
                    <div class="mr-[10px]" v-show="selectGoodsNum">
                        <span>{{ t('goodsSelectPopupBeforeTip') }}</span>
                        <span class="text-primary mx-[2px]">{{ selectGoodsNum }}</span>
                        <span>{{ t('goodsSelectPopupAfterTip') }}</span>
                    </div>
                    <el-button type="primary" link @click="clear" v-show="selectGoodsNum">{{ t('goodsSelectPopupClearGoods') }}</el-button>
                </div>
                <el-pagination v-model:current-page="goodsTable.page" v-model:page-size="goodsTable.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="goodsTable.total"
                    @size-change="loadGoodsList()" @current-change="loadGoodsList" />
            </div>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="save">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive, computed, nextTick } from 'vue'
import { cloneDeep } from 'lodash-es'
import { img,deepClone } from '@/utils/common'
import { ElMessage } from 'element-plus'
import { getGoodsSelectPageList,getGoodsSkuNoPageList, getCategoryTree, getGoodsType } from '@/addon/shop/api/goods'

const prop = defineProps({
    modelValue: {
        type: [String, Array],
        default: ''
    },
    max: {
        type: Number,
        default: 0
    },
    min: {
        type: Number,
        default: 0
    },
    mode: {
        type: String,
        default: 'spu' // spu：按商品，sku：按多规格
    },
    way: {
        type: String,
        default: '' // 选择方式，空：代表全部， single：单一
    },
    isGift: {
        type: [String,Number],
        default: 0 // 查询是否赠品，0：不查赠品，1：查询赠品
    },
    disabled: {
        type: Boolean,
        default: false
    },
    goodsType: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue','goodsSelect'])

// 通过prop.mode来决定 数据前缀是sku_还是goods_
let replacePrefix = prop.mode == "sku" ? 'sku_' : 'goods_';

const isStairIndeterminate = ref(false);
const staircheckAll = ref(false);

const goodsIds: any = computed({
    get () {
        return prop.modelValue
    },
    set (value) {
        emit('update:modelValue', value)
    }
})

const showDialog = ref(false)

// 已选商品列表
const selectGoods: any = reactive({})

// 已选商品列表id
const selectGoodsId: any = reactive([])

// 已选商品数量
const selectGoodsNum: any = computed(() => {
    return Object.keys(selectGoods).length
})

const goodsTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        keyword: '',
        goods_category: [],
        select_type: 'all',
        goods_ids: '',
        verify_goods_ids: '',
        verify_sku_ids: '',
        goods_type: '',
        is_gift: 0
    }
})

goodsTable.searchParam.is_gift = prop.isGift ? prop.isGift : 0;
goodsTable.searchParam.goods_type = prop.goodsType ? prop.goodsType : '';

const searchFormRef = ref()

// 查询全部/已选商品
const handleSelectTypeChange = (value: any) => {
    loadGoodsList()
}

// 商品分类
const goodsCategoryOptions: any = reactive([])

// 商品类型
const goodsType: any = reactive([])

// 初始化数据
const initData = () => {
// 查询商品分类树结构
    getCategoryTree().then((res) => {
        const data = res.data
        if (data) {
            const goodsCategoryTree: any = []
            data.forEach((item: any) => {
                const children: any = []
                if (item.child_list) {
                    item.child_list.forEach((childItem: any) => {
                        children.push({
                            value: childItem.category_id,
                            label: childItem.category_name
                        })
                    })
                }
                goodsCategoryTree.push({
                    value: item.category_id,
                    label: item.category_name,
                    children
                })
            })
            goodsCategoryOptions.splice(0, goodsCategoryOptions.length, ...goodsCategoryTree)
        }
    })

    // 商品类型
    getGoodsType().then((res) => {
        const data = res.data
        if (data) {
            for (const k in data) {
                goodsType.push(data[k])
            }
        }
    })
}

initData()

const goodsListTableRef = ref()

// 箭头选择事件
const secondLevelArrowChange = (data)=>{
    data.isShow = !data.isShow;
}

// 一级复选框
const handleCheckAllChange = (isSelect) =>{
    isStairIndeterminate.value = false;
    goodsTable.data.forEach((item,index) => {
        item.secondLevelCheckAll = isSelect;
        item.skuList.forEach((subItem, subIndex) => {
            subItem.threeLevelCheckAll = isSelect;
        });
    });
    if (isSelect) {
        goodsTable.data.forEach((item: any) => {
            if(prop.mode == 'spu') {
                selectGoods[replacePrefix + item.goods_id] = item
                selectGoodsId.push(item.goods_id)
            }else{
                item.skuList.forEach((skuItem:any)=>{
                    selectGoodsId.push(skuItem.sku_id);
                    selectGoods[replacePrefix + skuItem.sku_id] = deepClone(skuItem);
                    selectGoods[replacePrefix + skuItem.sku_id].goods_name = item.goods_name; // 多规格模式，要增加商品名称、商品类型，后续还会增加，满足不同业务场景
                    selectGoods[replacePrefix + skuItem.sku_id].goods_type_name = item.goods_type_name;
                    selectGoods[replacePrefix + skuItem.sku_id].goods_type = item.goods_type;
                })

            }
        })
    } else {
        // 未选中，删除当前页面的数据
        goodsTable.data.forEach((item: any) => {
            if(prop.mode == 'spu') {
                selectGoodsId.splice(selectGoodsId.indexOf(item.goods_id), 1)
                delete selectGoods[replacePrefix + item.goods_id]
            }else{
                item.skuList.forEach((skuItem:any)=>{
                    selectGoodsId.splice(selectGoodsId.indexOf(skuItem.sku_id), 1)
                    delete selectGoods[replacePrefix + skuItem.sku_id]
                })
            }
        })
    }
}

// 二级复选框
const secondLevelHandleCheckAllChange = (isSelect,row)=>{

    row.skuList.forEach((item,index) => {
        item.threeLevelCheckAll = isSelect;
    });
    detectionAllSelect();
    if(prop.mode == 'spu'){
        if (isSelect) {
            selectGoodsId.push(row.goods_id)
            selectGoods[replacePrefix + row.goods_id] = deepClone(row)
        } else {
            selectGoodsId.splice(selectGoodsId.indexOf(row.goods_id),1)
            // 未选中，删除当前商品
            delete selectGoods[replacePrefix + row.goods_id]
        }
    }else{
        if (isSelect) {
            row.skuList.forEach((item,index) => {
                selectGoodsId.push(item.sku_id);
                selectGoods[replacePrefix + item.sku_id] = deepClone(item);
                selectGoods[replacePrefix + item.sku_id].goods_name = row.goods_name; // 多规格模式，要增加商品名称、商品类型，后续还会增加，满足不同业务场景
                selectGoods[replacePrefix + item.sku_id].goods_type_name = row.goods_type_name;
                selectGoods[replacePrefix + item.sku_id].goods_type = row.goods_type;
            });
        } else {
            row.skuList.forEach((item,index) => {
                selectGoodsId.splice(selectGoodsId.indexOf(item.sku_id),1)
                // 未选中，删除当前商品
                delete selectGoods[replacePrefix + item.sku_id]
            });
        }
    }

    // 用于可扩展的表格或树表格，如果某行被扩展，则切换。 使用第二个参数，您可以直接设置该行应该被扩展或折叠。
    // setTimeout(() => {
    //     goodsListTableRef.value.toggleRowExpansion(...Object.values(spreadTableData),true)
    // }, 0);
    // 当所选数量超出限制数量【prop.max】时，添加一个就会删除开头的第一个或多个，最终保证所选的数量小于等于prop.max
    if(prop.max && prop.max > 0 && Object.keys(selectGoods).length > 0 && Object.keys(selectGoods).length > prop.max){
        let len = Object.keys(selectGoods).length;
        len = len - prop.max;

        let goodsIdCopy = cloneDeep(selectGoodsId);
        goodsIdCopy.forEach((item,index,arr) => {
            if(index < len){
                let indent = selectGoodsId.indexOf(item)
                delete selectGoods[replacePrefix+selectGoodsId[indent]]
                selectGoodsId.splice(indent,1)
            }
        });
        setGoodsSelected();
    }
}

// 三级复选框
const subChildHandleCheckAllChange  = (selected: any,parentData: any,data: any)=>{
    let selectNum = 0;
    parentData.skuList.forEach((item,index) => {
        if(item.threeLevelCheckAll){
            selectNum++;
        }
    });
    if(selectNum > 0 && selectNum != parentData.skuList.length){
        parentData.secondLevelCheckAll = false;
        parentData.isSecondLevelIndeterminate = true;
    }else if(selectNum == parentData.skuList.length){
        parentData.isSecondLevelIndeterminate = false;
        parentData.secondLevelCheckAll = true
    }else{
        parentData.isSecondLevelIndeterminate = false;
        parentData.secondLevelCheckAll = false;
    }

    detectionAllSelect();

    let currSku = deepClone(data)

    if(selected){
        selectGoodsId.push(currSku.sku_id);

        currSku.goods_name = parentData.goods_name; // 多规格模式，要增加商品名称、商品类型，后续还会增加，满足不同业务场景
        currSku.goods_type_name = parentData.goods_type_name;
        currSku.goods_type = parentData.goods_type;
        selectGoods[replacePrefix + currSku.sku_id] = currSku;
    }else{
        selectGoodsId.splice(selectGoodsId.indexOf(currSku.sku_id),1)
        // 未选中，删除当前商品
        delete selectGoods[replacePrefix + currSku.sku_id]
    }
}

// 检测是否选中
const detectionAllSelect = ()=> {
    let selectNum = 0;
    goodsTable.data.forEach((item,index) => {
        if(item.secondLevelCheckAll){
            selectNum++;
        }
    });

    if(selectNum > 0 && selectNum != goodsTable.data.length){
        staircheckAll.value = false;
        isStairIndeterminate.value = true;
    }else if(selectNum > 0 && selectNum == goodsTable.data.length){
        isStairIndeterminate.value = false;
        staircheckAll.value = true
    }else{
        isStairIndeterminate.value = false;
        staircheckAll.value = false;
    }
}

/**
 * 获取商品列表
 */
const loadGoodsList = (page: number = 1, callback: any = null) => {
    isStairIndeterminate.value = false;
    staircheckAll.value = false;
    goodsTable.loading = true;
    goodsTable.data = [];
    goodsTable.page = page

    const searchData = cloneDeep(goodsTable.searchParam);

    if (searchData.select_type == 'selected') {
        const goods_ids = <any>[]
        for (let k in selectGoods) {
            goods_ids.push(parseInt(k.replace(replacePrefix, '')))
        }
        searchData[replacePrefix+'ids'] = goods_ids
    } else {
        searchData[replacePrefix+'ids'] = '';
    }

    getGoodsSelectPageList({
        page: goodsTable.page,
        limit: goodsTable.limit,
        ...searchData
    }).then(res => {
        let goodsTableData = cloneDeep(res.data.data);
        goodsTableData.forEach((item: any) => {
            item.isShow = false;
            item.isSecondLevelIndeterminate = false;
            item.secondLevelCheckAll = false;
        })

        if(prop.mode == "sku") {
            goodsTableData.forEach((item: any) => {
                if (item.skuList.length) {
                    item.skuList.forEach((skuItem: any) => {
                        skuItem.threeLevelCheckAll = false;
                        skuItem.goods_type = item.goods_type;
                    })
                }
            })
        }
        if (callback) callback(prop.mode == "spu" ? res.data.verify_goods_ids : res.data.verify_sku_ids, res.data.select_goods_list)
        setGoodsSelected();

        goodsTable.data = goodsTableData
        goodsTable.total = res.data.total
        goodsTable.loading = false

    }).catch(() => {
        goodsTable.loading = false
    })

}

// 表格设置选中状态 spu
const setGoodsSelected = () => {
    nextTick(() => {
        if(prop.mode == "spu"){
            for (let i = 0; i < goodsTable.data.length; i++) {
                goodsTable.data[i].secondLevelCheckAll = false;
                if (selectGoods[replacePrefix + goodsTable.data[i].goods_id]) {
                    goodsTable.data[i].secondLevelCheckAll = true;
                }
            }
        }else{
            let isAllSelectSku = true;
            for (let i = 0; i < goodsTable.data.length; i++) {
                goodsTable.data[i].secondLevelCheckAll = false;

                isAllSelectSku = true;

                goodsTable.data[i].isSecondLevelIndeterminate = false;
                goodsTable.data[i].skuList.forEach((item,index) => {
                    item.threeLevelCheckAll = false;
                    if (selectGoods[replacePrefix + item.sku_id]) {
                        goodsTable.data[i].isSecondLevelIndeterminate = true;
                        item.threeLevelCheckAll = true;
                    }else{
                        isAllSelectSku = false;
                    }
                });
                if(isAllSelectSku){
                    goodsTable.data[i].isSecondLevelIndeterminate = false;
                    goodsTable.data[i].secondLevelCheckAll = true;
                }
            }
        }
        detectionAllSelect();
    });
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()

    loadGoodsList()
}

const show = () => {
    for (let k in selectGoods) {
        delete selectGoods[k];
    }

    replacePrefix = prop.mode == "sku" ? 'sku_' : 'goods_';

    // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
    if (prop.mode == 'sku') {
        goodsTable.searchParam.verify_sku_ids = goodsIds.value;
    } else {
        goodsTable.searchParam.verify_goods_ids = goodsIds.value;
    }

    getGoodsSkuNoPageListFn();

    loadGoodsList(1, (verify_ids: any) => {

        // 第一次打开弹出框时，纠正数据，并且赋值已选商品
        if (goodsIds.value && goodsIds.value.length) {
            goodsIds.value.splice(0, goodsIds.value.length, ...verify_ids)
            selectGoodsId.splice(0, selectGoodsId.length, ...verify_ids)
            if(Object.keys(selectGoods).length){
                for(let key in selectGoods){
                    let num = Number(key.split(replacePrefix)[1]);
                    if(goodsIds.value.indexOf(num) == -1){
                        delete selectGoods[key];
                    }
                }
            }
        }
    })

    showDialog.value = true
}

const getGoodsSkuNoPageListFn = () =>{
    const searchData = cloneDeep(goodsTable.searchParam);
    // 改变类型，防止下面比对因为类型原因，导致错误
    goodsIds.value.forEach((item:any, index:any, arr)=>{
        arr[index] = Number(item)
    })
    getGoodsSkuNoPageList({...searchData}).then((res:any)=>{
        const selectGoodsData = res.data;
        // 赋值已选择的商品
        if (prop.mode == 'sku') {
            for (let i = 0; i < selectGoodsData.length; i++) {
                selectGoodsData[i].skuList.forEach((item: any) => {
                    if (goodsIds.value.indexOf(item.sku_id) != -1) {
                        item.goods_name = selectGoodsData[i].goods_name; // 多规格模式，要增加商品名称、商品类型，后续还会增加，满足不同业务场景
                        item.goods_type_name = selectGoodsData[i].goods_type_name;
                        item.goods_type = selectGoodsData[i].goods_type;
                        selectGoods[replacePrefix + item.sku_id] = item;
                    }
                });
            }
        } else {
            for (let i = 0; i < selectGoodsData.length; i++) {
                if (goodsIds.value.indexOf(selectGoodsData[i].goods_id) != -1) {
                    selectGoods[replacePrefix + selectGoodsData[i].goods_id] = selectGoodsData[i];
                }
            }
        }

        if(Object.keys(selectGoods).length && goodsIds.value.length){
            for(let key in selectGoods){
                let num = Number(key.split(replacePrefix)[1]);
                if(goodsIds.value.indexOf(num) == -1){
                    delete selectGoods[key];
                }
            }
        }

        setGoodsSelected();
    })
}

// 清空已选商品
const clear = () => {
    for (let k in selectGoods) {
        delete selectGoods[k];
    }
    setGoodsSelected();
}

const save = () => {
    if (prop.min && selectGoodsNum.value < prop.min) {
        ElMessage({
            type: 'warning',
            message: `${t('goodsSelectPopupGoodsMinTip')}${prop.min}${t('goodsSelectPopupPiece')}`,
        });
        return;
    }

    if (prop.max && prop.max > 0 && selectGoodsNum.value && selectGoodsNum.value > prop.max) {
        ElMessage({
            type: 'warning',
            message: `${t('goodsSelectPopupGoodsMaxTip')}${prop.max}${t('goodsSelectPopupPiece')}`,
        });
        return;
    }

    if(prop.way == 'single'){
        let realTypeNum = 0;
        let virtualTypeNum = 0;
        for (let k in selectGoods) {
            if(selectGoods[k].goods_type == "virtual"){
                virtualTypeNum++;
            }else if(selectGoods[k].goods_type == "real"){
                realTypeNum++;
            }
        }

        if (realTypeNum != Object.keys(selectGoods).length && virtualTypeNum != Object.keys(selectGoods).length) {
            ElMessage({
                type: 'warning',
                message: `${t('wayPlaceholder')}`,
            });
            return;
        }
    }

    let ids: any = [];
    for (let k in selectGoods) {
        ids.push(parseInt(k.replace(replacePrefix, '')));
    }

    goodsIds.value.splice(0, goodsIds.value.length, ...ids)
    emit('goodsSelect',selectGoods)
    initSearchParam();
    showDialog.value = false
}

// 重置表单搜索
const initSearchParam = ()=>{
    goodsTable.searchParam.keyword = '';
    goodsTable.searchParam.goods_category = [];
    goodsTable.searchParam.select_type = 'all';
    goodsTable.searchParam.goods_ids = '';
    goodsTable.searchParam.verify_goods_ids = '';
    goodsTable.searchParam.verify_sku_ids = '';
    goodsTable.searchParam.goods_type = '';
}

defineExpose({
    showDialog,
    selectGoods,
    selectGoodsNum
})
</script>

<style lang="scss" scoped>
.form-item-wrap {
    margin-right: 10px !important;
    margin-bottom: 10px !important;

    &.last-child {
        margin-right: 0 !important;
    }
}
.arrow-show{
    transform: rotate(90deg);
}
</style>
