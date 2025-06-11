<template>
    <div class="table w-[100%] mt-[15px]" v-loading="categoryTable.loading">
        <div class="table-head flex items-center bg-[#f5f7f9] py-[10px] text-[14px]" :style="{ paddingRight: scrollBarWidth + 'px' }">
            <div class="w-[6%]"></div>
            <div class="w-[10%]">
                <!-- <el-checkbox v-model="staircheckAll" :indeterminate="isStairIndeterminate" @change="handleCheckAllChange" /> -->
            </div>
            <div class="w-[50%]">{{ t('categoryName') }}</div>
            <div class="w-[34%]">{{ t('categoryImage') }}</div>
        </div>
        <div class="table-body max-h-[500px] overflow-y-auto" ref="tableBodyRef">
           <!-- 遍历一级分类 -->
            <div v-for="(row, rowIndex) in categoryTable.data" :key="rowIndex" class="flex flex-col">
                <div class="flex items-center border-solid border-[#e5e7eb] py-[10px] border-b-[1px]">
                    <!-- 图标：展开/收起子级 -->
                    <div v-if="row.child_list && row.child_list.length" class="w-[6%] cursor-pointer text-center !text-[10px]" @click="secondLevelArrowChange(row)" :class="{ 'iconfont iconxiangyoujiantou': row.child_list.length, 'arrow-show': row.isShow }"></div>
                    <div v-else class="w-[6%]"></div>
                    <!-- 一级分类复选框 -->
                    <div class="w-[10%]">
                        <el-checkbox v-model="row.secondLevelCheckAll" :indeterminate="row.isSecondLevelIndeterminate" @change="handleCheckboxChange($event, row)" />
                    </div>
                    <!-- 一级分类名称 -->
                    <div class="ml-2 flex flex-col items-start w-[50%]">
                        <span :title="row.category_name" class="multi-hidden leading-[1.4] mr-5 text-[14px] text-[#666]">
                            {{ row.category_name }}
                        </span>
                    </div>
                    <!-- 一级分类图片 -->
                    <div class="flex items-center cursor-pointer w-[34%]">
                        <div class="min-w-[30px] h-[30px] flex items-center justify-center">
                            <el-image v-if="row.img" class="w-[30px] h-[30px]" :src="img(row.img)" fit="contain">
                                <template #error>
                                    <div class="image-slot">
                                        <img class="w-[30px] h-[30px]" src="@/addon/shop/assets/category_default.png" />
                                    </div>
                                </template>
                            </el-image>
                            <img v-else class="w-[30px] h-[30px]" src="@/addon/shop/assets/category_default.png" fit="contain" />
                        </div>
                    </div>
                </div>
                <!-- 子级分类 -->
                <div v-show="row.child_list && row.isShow">
                    <div v-for="(item, index) in row.child_list" :key="index" class="flex items-center py-[10px] border-solid border-b-[1px]" :class="{ 'hidden': !row.isShow, 'border-[#e5e7eb]': index == (row.child_list.length - 1) }">
                        <div class="w-[9%]"></div>
                        <!-- 子级分类复选框 -->
                        <div class="w-[7%]">
                            <el-checkbox v-model="item.threeLevelCheckAll" @change="handleCheckboxChange($event, item, row)" />
                        </div>
                        <!-- 子级分类名称 -->
                        <div class="ml-2 flex flex-col items-start w-[50%]">
                            <span :title="item.category_name" class="multi-hidden leading-[1.4] mr-5 text-[14px] text-[#666]">
                                {{ item.category_name }}
                            </span>
                        </div>
                        <!-- 子级分类图片 -->
                        <div class="flex items-center cursor-pointer w-[34%]">
                            <div class="min-w-[30px] h-[30px] flex items-center justify-center">
                                <el-image v-if="row.img" class="w-[30px] h-[30px]" :src="img(row.img)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[30px] h-[30px]" src="@/addon/shop/assets/category_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                                <img v-else class="w-[30px] h-[30px]" src="@/addon/shop/assets/category_default.png" fit="contain" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="!categoryTable.data.length && !categoryTable.loading" class="h-[60px] flex items-center justify-center border-solid border-[#e5e7eb] py-[12px] border-b-[1px]">{{ t('emptyData') }}</div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive, computed, onMounted,nextTick } from 'vue'
import { img } from '@/utils/common'
import { ElMessage } from 'element-plus'
import { getCategoryTree } from '@/addon/shop/api/goods'
import { cloneDeep } from 'lodash-es'

const prop = defineProps({
    categoryId: {
        type: Number || String,
        default: ''
    }
})

const categoryId: any = computed(() => {
    return prop.categoryId
})

// 已选商品分类
let currCategoryData: any = null

const categoryTable = reactive({
    loading: true,
    data: []
})

const scrollBarWidth = ref(0);
const tableBodyRef = ref(null);

onMounted(() => {
    window.addEventListener("resize", getScrollBarWidth);
})

const getScrollBarWidth = () => {
    nextTick(() => {
        if (tableBodyRef.value) {
            scrollBarWidth.value = tableBodyRef.value.offsetWidth - tableBodyRef.value.clientWidth;
        }
    })
}

// 选择商品分类
// 方法：切换子级展开/收起
const secondLevelArrowChange = (row:any) => {
    row.isShow = !row.isShow;
    nextTick(() => getScrollBarWidth());
};

const loadCategoryList = () => {
    categoryTable.loading = true

    getCategoryTree().then(res => {
        categoryTable.loading = false
        categoryTable.data = res.data
        categoryTable.data.forEach((item: any) => {
            // 初始化一级分类的字段
            item.isShow = false; // 控制子级是否展开
            item.isSecondLevelIndeterminate = false; // 级分类不确定状态
            item.secondLevelCheckAll = false; // 级分类复选框状态

            // 如果有子分类（child_list），初始化子分类的字段
            if (item.child_list && item.child_list.length) {
                item.child_list.forEach((childItem: any) => {
                    childItem.threeLevelCheckAll = false; // 子分类复选框状态
                });
            }
        });
        if (categoryId.value) {
            let obj = {}
            categoryTable.data.forEach((row: any) => {
                if (row.category_id === categoryId.value) {
                    row.secondLevelCheckAll = true;
                    row.isShow = true; // 展开选中的一级分类
                    obj = cloneDeep(row)
                }
                if (row.child_list) {
                    row.child_list.forEach((child: any) => {
                        if (child.category_id === categoryId.value) {
                            child.threeLevelCheckAll = true;
                            row.isShow = true;
                            obj = cloneDeep(child)
                        }
                    });
                }
            });
            currCategoryData = cloneDeep(obj)
        }
    }).catch(() => {
        categoryTable.loading = false
    })
}

const clearAllSelections = () => {
    categoryTable.data.forEach((row: any) => {
        row.secondLevelCheckAll = false;
        if (row.child_list) {
            row.child_list.forEach((child: any) => {
                child.threeLevelCheckAll = false;
            });
        }
    });
}

// 处理复选框变化
const handleCheckboxChange = (checked: any, target: any, parentRow: any) => {
    clearAllSelections(); // 清空所有复选框的选中状态
    if (checked) {
        // 设置当前选中的分类
        if (parentRow) {
            // 如果是子分类
            target.threeLevelCheckAll = checked;
            currCategoryData = target;
            parentRow.isShow = true; // 展开父级分类
        } else {
            // 如果是一级分类
            target.secondLevelCheckAll = checked;
            currCategoryData = target;
            target.isShow = true; // 展开选中的一级分类
        }
    } else {
        // 取消勾选时，清空选中的分类 ID
        currCategoryData = null;
    }
};
loadCategoryList()

const getData = () => {
    if (!currCategoryData) {
        ElMessage({
            type: 'warning',
            message: `${ t('goodsCategorySelectContentPlaceholder') }`
        })
        return
    }

    return {
        name: 'SHOP_GOODS_CATEGORY',
        title: currCategoryData.category_name,
        url: `/addon/shop/pages/goods/list?curr_goods_category=${ currCategoryData.category_id }`,
        action: '',
        categoryId: currCategoryData.category_id
    }
}

defineExpose({
    getData
})
</script>

<style lang="scss" scoped>
.arrow-show {
  transform: rotate(90deg) !important; /* 提高优先级 */
}
</style>
