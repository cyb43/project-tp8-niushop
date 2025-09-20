<template>
    <div>
        <el-form :inline="true" :model="goodsTable.searchParam" ref="searchFormRef">
            <el-form-item :label="t('goodsSelectPopupGoodsName')" prop="keyword" class="form-item-wrap">
                <el-input v-model.trim="goodsTable.searchParam.keyword" :placeholder="t('goodsSelectPopupGoodsNamePlaceholder')" maxlength="60" />
            </el-form-item>
            <el-form-item :label="t('goodsSelectPopupGoodsCategory')" prop="goods_category" class="form-item-wrap">
                <el-cascader v-model="goodsTable.searchParam.goods_category" :options="goodsCategoryOptions" :placeholder="t('goodsSelectPopupGoodsCategoryPlaceholder')" clearable :props="{ value: 'value', label: 'label', emitPath:false }" />
            </el-form-item>
            <el-form-item :label="t('goodsSelectPopupGoodsType')" prop="goods_type" class="form-item-wrap">
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
                <div class="w-[7%]"></div>
                <div class="w-[50%]">商品信息</div>
                <div class="w-[20%]">商品价格</div>
                <div class="w-[20%]">库存</div>
            </div>
            <div class="table-body h-[350px] overflow-y-auto">
                <div v-for="(row,rowIndex) in goodsTable.data" :key="rowIndex" class="flex flex-col">
                    <!-- 内容 -->
                    <div class="flex items-center border-solid border-[#e5e7eb] py-[5px] border-b-[1px]">
                        <div class="w-[3%]"></div>
                        <div class="w-[7%]">
                            <el-checkbox v-model="row.secondLevelCheckAll" @change="secondLevelHandleCheckAllChange($event,row)" />
                        </div>
                        <div class="flex items-center cursor-pointer w-[50%]">
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
                        <div class="w-[20%]">{{ row.stock }}</div>
                    </div>
                </div>

                <div v-if="!goodsTable.data.length && !goodsTable.loading" class="h-[60px] flex items-center justify-center border-solid border-[#e5e7eb] py-[12px] border-b-[1px]">暂无数据</div>
            </div>
        </div>

        <div class="mt-[16px] flex">
            <div class="flex items-center flex-1"></div>
            <el-pagination v-model:current-page="goodsTable.page" v-model:page-size="goodsTable.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="goodsTable.total"
                           @size-change="loadGoodsList()" @current-change="loadGoodsList" />
        </div>

    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive, computed, nextTick } from 'vue'
import { cloneDeep } from 'lodash-es'
import { img, deepClone } from '@/utils/common'
import { ElMessage } from 'element-plus'
import { getGoodsSelectPageList, getGoodsSkuNoPageList, getCategoryTree, getGoodsType } from '@/addon/shop/api/goods'

const prop = defineProps({
    goodsIds: {
        type: Array,
        default: () => {
            return []
        }
    },
    max: {
        type: Number,
        default: 1
    },
    min: {
        type: Number,
        default: 1
    },
})

const replacePrefix = 'goods_';

const goodsIds: any = computed(() => {
    return prop.goodsIds;
})

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
        goods_ids: '',
        verify_goods_ids: '',
        goods_type: '',
        is_gift: 0
    }
})

const searchFormRef = ref()

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

// 二级复选框
const secondLevelHandleCheckAllChange = (isSelect, row) => {

    if (isSelect) {
        selectGoodsId.push(row.goods_id)
        selectGoods[replacePrefix + row.goods_id] = deepClone(row)
    } else {
        selectGoodsId.splice(selectGoodsId.indexOf(row.goods_id), 1)
        // 未选中，删除当前商品
        delete selectGoods[replacePrefix + row.goods_id]
    }

    // 当所选数量超出限制数量【prop.max】时，添加一个就会删除开头的第一个或多个，最终保证所选的数量小于等于prop.max
    if (prop.max && prop.max > 0 && Object.keys(selectGoods).length > 0 && Object.keys(selectGoods).length > prop.max) {
        let len = Object.keys(selectGoods).length;
        len = len - prop.max;

        let goodsIdCopy = cloneDeep(selectGoodsId);
        goodsIdCopy.forEach((item, index, arr) => {
            if (index < len) {
                let indent = selectGoodsId.indexOf(item)
                delete selectGoods[replacePrefix + selectGoodsId[indent]]
                selectGoodsId.splice(indent, 1)
            }
        });
        setGoodsSelected();
    }
}

/**
 * 获取商品列表
 */
const loadGoodsList = (page: number = 1, callback: any = null) => {
    goodsTable.loading = true;
    goodsTable.data = [];
    goodsTable.page = page

    const searchData = cloneDeep(goodsTable.searchParam);

    getGoodsSelectPageList({
        page: goodsTable.page,
        limit: goodsTable.limit,
        ...searchData
    }).then(res => {
        let goodsTableData = cloneDeep(res.data.data);
        goodsTableData.forEach((item: any) => {
            item.secondLevelCheckAll = false;
        })

        if (callback) callback(res.data.verify_goods_ids, res.data.select_goods_list)
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
        for (let i = 0; i < goodsTable.data.length; i++) {
            goodsTable.data[i].secondLevelCheckAll = false;
            if (selectGoods[replacePrefix + goodsTable.data[i].goods_id]) {
                goodsTable.data[i].secondLevelCheckAll = true;
            }
        }
    });
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadGoodsList()
}

const init = () => {
    for (let k in selectGoods) {
        delete selectGoods[k];
    }

    // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
    goodsTable.searchParam.verify_goods_ids = goodsIds.value;

    getGoodsSkuNoPageListFn();

    loadGoodsList(1, (verify_ids: any) => {

        // 第一次打开弹出框时，纠正数据，并且赋值已选商品
        if (goodsIds.value && goodsIds.value.length) {
            goodsIds.value.splice(0, goodsIds.value.length, ...verify_ids)
            selectGoodsId.splice(0, selectGoodsId.length, ...verify_ids)
            if (Object.keys(selectGoods).length) {
                for (let key in selectGoods) {
                    let num = Number(key.split(replacePrefix)[1]);
                    if (goodsIds.value.indexOf(num) == -1) {
                        delete selectGoods[key];
                    }
                }
            }
        }
    })
}

const getGoodsSkuNoPageListFn = () => {
    const searchData = cloneDeep(goodsTable.searchParam);
    getGoodsSkuNoPageList({ ...searchData }).then((res: any) => {
        const selectGoodsData = res.data;
        // 赋值已选择的商品
        for (let i = 0; i < selectGoodsData.length; i++) {
            if (goodsIds.value.indexOf(selectGoodsData[i].goods_id) != -1) {
                selectGoods[replacePrefix + selectGoodsData[i].goods_id] = selectGoodsData[i];
            }
        }

        if (Object.keys(selectGoods).length && goodsIds.value.length) {
            for (let key in selectGoods) {
                let num = Number(key.split(replacePrefix)[1]);
                if (goodsIds.value.indexOf(num) == -1) {
                    delete selectGoods[key];
                }
            }
        }

        setGoodsSelected();
    })
}

initData()
init();

const getData = () => {
    if (prop.min && selectGoodsNum.value < prop.min) {
        ElMessage({
            type: 'warning',
            message: `${ t('goodsSelectPopupGoodsMinTip') }${ prop.min }${ t('goodsSelectPopupPiece') }`,
        });
        return;
    }

    if (prop.max && prop.max > 0 && selectGoodsNum.value && selectGoodsNum.value > prop.max) {
        ElMessage({
            type: 'warning',
            message: `${ t('goodsSelectPopupGoodsMaxTip') }${ prop.max }${ t('goodsSelectPopupPiece') }`,
        });
        return;
    }

    let ids: any = [];
    for (let k in selectGoods) {
        ids.push(parseInt(k.replace(replacePrefix, '')));
    }

    goodsIds.value.splice(0, goodsIds.value.length, ...ids)
    let goodsInfo = selectGoods['goods_' + goodsIds.value];
    return {
        name: 'SHOP_GOODS_DETAIL',
        title: goodsInfo.goods_name,
        url: `/addon/shop/pages/goods/detail?goods_id=${ goodsInfo.goods_id }`,
        action: '',
        goodsIds: goodsIds.value
    };

}

defineExpose({
    getData
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
</style>
