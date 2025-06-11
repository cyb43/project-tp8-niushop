<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <el-tabs v-model="activeName" @tab-click="tabHandleClick">
                <el-tab-pane :label="t('goodsSearch')" name="search">
                    <el-form :model="formData" label-width="140px" ref="formRef"  class="page-form" v-loading="loading">
                        <div class="flex justify-between items-center mb-[20px]">
                            <span class="text-[14px]">{{ t('defaultSearch') }}</span>
                        </div>
                        <el-form-item :label="t('defaultWord')" >
                            <div>
                                <el-input v-model.trim="formData.default_word" type="textarea" clearable :placeholder="t('defaultWordPlaceholder')" class="input-width" maxlength="12"/>
                                <div class="text-[12px] text-[#999]">{{ t('defaultWordTips') }}</div>
                            </div>
                        </el-form-item>
                        <div class="flex justify-between items-center mb-[20px]">
                            <span class="text-[14px]">{{ t('hotSearch') }}</span>
                        </div>
                        <el-form-item :label="t('indexKeyword')" >
                            <div class="search-wrap">
                                <ul ref="searchRef">
                                    <li class="draggable-element" v-for="(item, index) in searchList" :key="item.id">
                                        <el-input v-model.trim="item.name" clearable :placeholder="t('searchPlaceholder')" class="input-width"  :suffix-icon="Rank" maxlength="12" @blur="searchHandleBlur(item.name,index)"></el-input>
                                        <el-icon class="icon" :size="20" color="#7b7b7b" @click="deleteSearch(index)">
                                            <CircleCloseFilled />
                                        </el-icon>
                                    </li>
                                </ul>
                                <span class="text-primary text-[14px] cursor-pointer" @click="addSearch" v-if="searchList.length < 10">{{ t('addSearch') }}</span>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :label="t('goodsCode')" name="code">
                    <el-form  label-width="140px"   class="page-form" v-loading="codeLoading">
                        <el-form-item :label="t('enable')">
                            <div>
                                <el-switch v-model="isEnable" :active-value="1" :inactive-value="0" />
                                <div class="text-[12px] text-[#999]">{{ t('enableTips') }}</div>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :label="t('goodSort')" name="sort">
                    <el-form  label-width="140px" class="page-form" v-loading="sortLoading">
                        <el-form-item :label="t('sortType')">
                            <div>
                                <el-select v-model="sortData.sort_type" :placeholder="t('sortTypePlaceholder')">
                                    <el-option  v-for="(label, key) in sortData.init.sort_type"  :key="key" :label="label" :value="key"></el-option>
                                </el-select>
                                <div class="text-[12px] text-[#999] flex flex-col leading-[20px] mt-1">
                                    <span>{{ t('sortTypeTips') }}</span>
                                </div>

                            </div>
                        </el-form-item>
                        <el-form-item :label="t('sortColumn')">
                            <div>
                                <el-select v-model="sortData.sort_column" :placeholder="t('sortColumnPlaceholder')">
                                    <el-option  v-for="(label, key) in sortData.init.sort_column"  :key="key"  :label="label" :value="key"></el-option>
                                </el-select>
                                <div class="text-[12px] text-[#999]">{{ t('sortColumnTips') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('defaultSort')">
                            <div>
                                <el-input v-model.trim="sortData.default_sort" clearable :placeholder="t('defaultSortPlaceholder')" class="input-width-short"  maxlength="8" />
                                <div class="text-[12px] text-[#999]">{{ t('defaultSortTips') }}</div>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>
        </el-card>
        <div class="fixed-footer-wrap" v-if="!loading || !codeLoading || !sortLoading">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(formRef)">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue'
import { t } from '@/lang'
import { ElMessage, FormInstance } from 'element-plus'
import { Rank } from '@element-plus/icons-vue'
import Sortable from 'sortablejs'
import { range } from 'lodash-es'
import { getGoodsConfigSearch, setGoodsConfigSearch, getGoodsConfigUnique, setGoodsConfigUnique, setGoodsConfigSort, getGoodsConfigSort } from '@/addon/shop/api/goods'

const activeName = ref('search')
const loading = ref(false)
const codeLoading = ref(false)
const sortLoading = ref(false)

const formData = ref({
    default_word: '',
    search_words: []
})

const sortData = ref({
    default_sort: '',
    sort_type: '',
    sort_column: '0',
    init: {
        sort_type: {},
        sort_column: {}
    }
})

const isEnable = ref<any>(-1)
const formRef = ref<FormInstance>()
const searchList = ref<any>([]) // 热门搜索

onMounted(() => {
    nextTick(() => {
        bindSearch()
    })
})

// 搜索配置
const getGoodsConfigSearchFn = () => {
    loading.value = true
    getGoodsConfigSearch().then((res) => {
        formData.value = res.data
        if (formData.value.search_words && formData.value.search_words.length) {
            searchList.value = []
            formData.value.search_words.forEach((item: any) => {
                searchList.value.push({
                    id: generateRandom(),
                    name: item
                })
            })
        }
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

getGoodsConfigSearchFn()

// 唯一值配置
const getGoodsConfigUniqueFn = () => {
    getGoodsConfigUnique().then((res) => {
        isEnable.value = Number(res.data.is_enable)
    })
}
getGoodsConfigUniqueFn()

// 排序
const getGoodsConfigSortFn = () => {
    getGoodsConfigSort().then((res) => {
        sortData.value = res.data
    })
}
getGoodsConfigSortFn()

const tabHandleClick = (tab: any) => {
    activeName.value = tab.props.name
}

const searchHandleBlur = (data: any, index: any) => {
    if (data) {
        const isDuplicate = searchList.value.some((item: any, i: number) => i !== index && item.name === data)
        if (isDuplicate) {
            ElMessage.error(t('searchTips'))
            searchList.value[index].name = ''
        }
    }
}

// 拖拽
const searchRef = ref()

// 绑定拖拽规事件
const bindSearch = () => {
    if (!searchRef.value) return

    const sortable = Sortable.create(searchRef.value, {
        group: 'draggable-element',
        animation: 200,
        // 结束拖拽
        onEnd: event => {
            const temp = searchList.value[event.oldIndex!]
            searchList.value.splice(event.oldIndex!, 1)
            searchList.value.splice(event.newIndex!, 0, temp)
            nextTick(() => {
                sortable.sort(
                    range(searchList.value.length).map(value => {
                        return value.toString()
                    })
                )
            })
        }
    })
}
// 生成随机数
const generateRandom = (len: number = 5) => {
    return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36)
}

const addSearch = () => {
    searchList.value.push({
        id: generateRandom(),
        name: ''
    })
    bindSearch()
}
// 删除规格值
const deleteSearch = (index: number) => {
    searchList.value.splice(index, 1)
}

const repeat = ref(false)
const onSave = async (formEl: any) => {
    await formEl.validate(async (valid:any) => {
        if (valid) {
            if (activeName.value === 'search') {
                for (let i = 0; i < searchList.value.length; i++) {
                    if (!searchList.value[i].name) {
                        ElMessage.error(t('keyWordTips'))
                        return
                    }
                }

                if (repeat.value) return
                repeat.value = true

                loading.value = true
                const obj = searchList.value.map((item: any) => {
                    return item.name
                })
                formData.value.search_words = obj.join(',')
                setGoodsConfigSearch(formData.value).then((res: any) => {
                    repeat.value = false
                    getGoodsConfigSearchFn()
                }).catch(() => {
                    loading.value = false
                    repeat.value = false
                })
            } else  if (activeName.value === 'code')  {
                if (repeat.value) return
                repeat.value = true

                codeLoading.value = true
                setGoodsConfigUnique({
                    is_enable: isEnable.value
                }).then((res: any) => {
                    getGoodsConfigUniqueFn()
                    repeat.value = false
                    codeLoading.value = false
                }).catch(() => {
                    repeat.value = false
                    codeLoading.value = false
                })
            }else{
                if (repeat.value) return
                repeat.value = true

                sortLoading.value = true
                setGoodsConfigSort(sortData.value).then((res: any) => {
                    getGoodsConfigSortFn()
                    repeat.value = false
                    sortLoading.value = false
                }).catch(() => {
                    repeat.value = false
                    sortLoading.value = false
                })
            }
        }
    })
}
</script>

<style  lang="scss" scoped>
.input-width-short{
    width: 190px;
}
.search-wrap {
    position: relative;

    ul {

        display: flex;
        flex-wrap: wrap;
        flex: 1;
        align-items: baseline;

        li {
            margin: 0 10px 20px 0;
            position: relative;

            .input-width {
                width: 200px;
            }

            .icon {
                width: 32px;
                padding: 0;
                display: none;
                position: absolute;
                top: -12px;
                right: -20px;
                cursor: pointer;
            }

            &:hover {
                .icon {
                    display: block;
                }
            }
        }

    }
}
</style>
