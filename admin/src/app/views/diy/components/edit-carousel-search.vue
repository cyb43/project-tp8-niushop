<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselSearchShowPosition') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('carouselSearchShowWay')">
                    <el-radio-group v-model="diyStore.editComponent.positionWay">
                        <el-radio label="static">{{ t('carouselSearchShowWayStatic') }}</el-radio>
                        <el-radio label="fixed">{{ t('carouselSearchShowWayFixed') }}</el-radio>
                    </el-radio-group>
                    <div v-if="diyStore.editComponent.positionWay == 'fixed'" class="text-sm text-gray-400 mb-[10px]">滑动页面查看效果</div>
                </el-form-item>
                <el-form-item :label="t('carouselSearchFixedBgColor')" v-show="diyStore.editComponent.positionWay == 'fixed'">
                    <el-color-picker v-model="diyStore.editComponent.fixedBgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('carouselSearchBgGradient')">
                    <el-radio-group v-model="diyStore.editComponent.bgGradient">
                        <el-radio :label="true">{{ t('carouselSearchOpen') }}</el-radio>
                        <el-radio :label="false">{{ t('carouselSearchClose') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselSearchSet') }}</h3>
            <el-form label-width="100px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('selectStyle')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer" @click="showSearchStyle">{{ diyStore.editComponent.search.styleName }}</span>
                    <el-icon @click="showSearchStyle" class="cursor-pointer">
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
                <el-form-item :label="t('carouselSearchSubTitle')" v-if="diyStore.editComponent.search.style == 'style-2'">
                    <el-input v-model.trim="diyStore.editComponent.search.subTitle.text" :placeholder="t('carouselSearchSubTitlePlaceholder')" clearable maxlength="10" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('logo')">
                    <upload-image v-model="diyStore.editComponent.search.logo" :limit="1" />
                    <div class="text-sm text-gray-400 mb-[10px]">{{ t('carouselSearchLogoTips') }}</div>
                </el-form-item>
                <el-form-item :label="t('carouselSearchText')">
                    <div>
                        <el-input v-model.trim="diyStore.editComponent.search.text" :placeholder="t('carouselSearchPlaceholder')" clearable maxlength="20" show-word-limit />
                        <p class="text-sm text-gray-400 mt-[10px] leading-[1.5]">{{ t('carouselSearchTextTips') }}</p>
                    </div>
                </el-form-item>
                <el-form-item :label="t('link')">
                    <diy-link v-model="diyStore.editComponent.search.link" />
                </el-form-item>
            </el-form>

            <el-dialog v-model="showSearchDialog" :title="t('selectStyle')" width="500px">

                <div class="flex flex-wrap">
                    <template v-for="(item,index) in searchStyleList" :key="index">
                        <div :class="{ 'border-primary': selectSearchStyle.value == item.value }" @click="changeSearchStyle(item)" class="flex items-center justify-center overflow-hidden w-[200px] h-[100px] m-[6px] cursor-pointer border bg-[#eee]">
                            <img :src="img(item.url)" />
                        </div>
                    </template>
                </div>

                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="showSearchDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" @click="confirmSearchStyle">{{ t('confirm') }}</el-button>
                    </span>
                </template>

            </el-dialog>
        </div>

        <div class="edit-attr-item-wrap mb-[20px]">
            <h3 class="mb-[10px]">{{ t('carouselSearchHotWordSet') }}</h3>
            <el-form label-width="100px" class="px-[10px]">

                <el-form-item :label="t('carouselSearchHotWordInterval')">
                    <el-slider v-model="diyStore.editComponent.search.hotWord.interval" show-input size="small" class="ml-[10px] diy-nav-slider" :min="1" :max="10" />
                </el-form-item>

                <p class="text-sm text-gray-400 mb-[10px]">{{ t('dragMouseAdjustOrder') }}</p>

                <div ref="searchHotWordTabBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.search.hotWord.list" :key="item.id"
                         class="item-wrap p-[10px] relative border border-dashed border-gray-300 mb-[16px]">

                        <el-form-item :label="t('carouselSearchHotWordText')" class="!mb-0">
                            <el-input v-model.trim="item.text" :placeholder="t('carouselSearchHotWordTextPlaceholder')" clearable maxlength="4" show-word-limit />
                        </el-form-item>

                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]" @click="diyStore.editComponent.search.hotWord.list.splice(index,1)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>

                    </div>
                    <el-button v-show="diyStore.editComponent.search.hotWord.list.length < 50" class="w-full" @click="addHotWordItem">{{ t('carouselSearchAddHotWordItem') }}</el-button>
                </div>

            </el-form>
        </div>

        <el-collapse v-model="activeNames" @change="handleChange" class="collapse-wrap">
            <el-collapse-item :title="t('carouselSearchTabSet')" name="tab">
                <div class="edit-attr-item-wrap">
                    <el-form label-width="100px" class="px-[10px]" @submit.prevent>
                        <el-form-item :label="t('carouselSearchTabControl')">
                            <el-switch v-model="diyStore.editComponent.tab.control" />
                        </el-form-item>

                        <p class="text-sm text-gray-400 mb-[10px]">{{ t('dragMouseAdjustOrder') }}</p>

                        <div ref="tabBoxRef">
                            <div v-for="(item,index) in diyStore.editComponent.tab.list" :key="item.id" class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">

                                <el-form-item :label="t('carouselSearchTabCategoryText')">
                                    <el-input v-model.trim="item.text" :placeholder="t('carouselSearchTabCategoryTextPlaceholder')" clearable maxlength="4" show-word-limit />
                                </el-form-item>

                                <el-form-item :label="t('dataSources')">
                                    <el-input v-model.trim="item.diy_title" :placeholder="t('selectDiyPagePlaceholder')" readonly class="select-diy-page-input" @click="diyPageShowDialogOpen(index)">
                                        <template #suffix>
                                            <div @click.stop="tabClear(index)">
                                                <el-icon v-if="item.diy_title">
                                                    <Close />
                                                </el-icon>
                                                <el-icon v-else>
                                                    <ArrowRight />
                                                </el-icon>
                                            </div>
                                        </template>
                                    </el-input>
                                </el-form-item>

                                <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                                     v-show="diyStore.editComponent.tab.list.length > 1"
                                     @click="diyStore.editComponent.tab.list.splice(index,1)">
                                    <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                                </div>

                            </div>
                            <el-button v-show="diyStore.editComponent.tab.list.length < 50" class="w-full" @click="addTabItem">{{ t('carouselSearchAddTabItem') }}</el-button>
                        </div>

                        <!-- 选择微页面弹出框 -->
                        <el-dialog v-model="diyPageShowDialog" :title="t('selectSourcesDiyPage')" width="1000px" :close-on-press-escape="true" :destroy-on-close="true" :close-on-click-modal="false">
                            <el-table :data="diyPageTable.data" ref="diyPageTableRef" size="large"
                                      v-loading="diyPageTable.loading" height="490px"
                                      @current-change="handleCurrentDiyPageChange" row-key="id" highlight-current-row>
                                <template #empty>
                                    <span>{{ !diyPageTable.loading ? t('emptyData') : '' }}</span>
                                </template>
                                <el-table-column prop="page_title" :label="t('diyPageTitle')" min-width="120" />
                                <el-table-column prop="addon_name" :label="t('diyPageTypeName')" min-width="80" />
                                <el-table-column prop="type_name" :label="t('diyPageForAddon')" min-width="80" />
                            </el-table>
                            <div class="mt-[16px] flex justify-end">
                                <el-pagination v-model:current-page="diyPageTable.page"
                                               v-model:page-size="diyPageTable.limit"
                                               layout="total, sizes, prev, pager, next, jumper"
                                               :total="diyPageTable.total"
                                               @size-change="loadDiyPageList" @current-change="loadDiyPageList" />
                            </div>
                            <div class="flex items-center justify-end mt-[15px]">
                                <el-button type="primary" @click="saveDiyPageId">{{ t('confirm') }}</el-button>
                                <el-button @click="diyPageShowDialog = false">{{ t('cancel') }}</el-button>
                            </div>
                        </el-dialog>

                    </el-form>
                </div>
            </el-collapse-item>
            <el-collapse-item :title="t('carouselSearchSwiperSet')" name="swiper">
                <el-form label-width="100px" class="px-[10px]">
                    <el-form-item :label="t('carouselSearchSwiperControl')">
                        <el-switch v-model="diyStore.editComponent.swiper.control" />
                    </el-form-item>
                    <el-form-item :label="t('carouselSearchSwiperInterval')">
                        <el-slider v-model="diyStore.editComponent.swiper.interval" show-input size="small" class="ml-[10px] diy-nav-slider" :min="1" :max="10" />
                    </el-form-item>

                    <div class="text-sm text-gray-400 mb-[10px]">{{ t('carouselSearchSwiperTips') }}</div>

                    <div ref="imageBoxRef">
                        <div v-for="(item,index) in diyStore.editComponent.swiper.list" :key="item.id"
                             class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">
                            <el-form-item :label="t('image')">
                                <upload-image v-model="item.imageUrl" :limit="1" @change="selectImg" />
                            </el-form-item>

                            <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                                 v-show="diyStore.editComponent.swiper.list.length > 1"
                                 @click="diyStore.editComponent.swiper.list.splice(index,1)">
                                <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                            </div>

                            <el-form-item :label="t('link')">
                                <diy-link v-model="item.link" />
                            </el-form-item>
                        </div>
                    </div>

                    <el-button v-show="diyStore.editComponent.swiper.list.length < 10" class="w-full"
                               @click="addImageAd">{{ t('addImageAd') }}
                    </el-button>

                </el-form>
            </el-collapse-item>
        </el-collapse>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.search.style == 'style-2'">
            <h3 class="mb-[10px]">{{ t('carouselSearchPositionStyle') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('carouselSearchTextColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.positionColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.search.style == 'style-2'">
            <h3 class="mb-[10px]">{{ t('carouselSearchSubTitleStyle') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('carouselSearchTextColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.subTitle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('carouselSearchBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.subTitle.startColor" :predefine="diyStore.predefineColors" show-alpha />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.search.subTitle.endColor" :predefine="diyStore.predefineColors" show-alpha />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselSearchStyle') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('carouselSearchTextColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.color" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('carouselSearchBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.bgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('carouselSearchBtnColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.btnColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('carouselSearchBtnBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.search.btnBgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselSearchTabStyle') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('noColor')">
                    <el-color-picker v-model="diyStore.editComponent.tab.noColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('selectColor')">
                    <el-color-picker v-model="diyStore.editComponent.tab.selectColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('fixedNoColor')">
                    <el-color-picker v-model="diyStore.editComponent.tab.fixedNoColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('fixedSelectColor')">
                    <el-color-picker v-model="diyStore.editComponent.tab.fixedSelectColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselSearchSwiperSet') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('carouselSearchSwiperStyle')" @change="changeSwiperStyle">
                    <el-radio-group v-model="diyStore.editComponent.swiper.swiperStyle">
                        <el-radio label="style-1">{{ t('carouselSearchSwiperIndicatorStyle1') }}</el-radio>
                        <el-radio label="style-2">{{ t('carouselSearchSwiperIndicatorStyle2') }}</el-radio>
                        <el-radio label="style-3">{{ t('carouselSearchSwiperIndicatorStyle3') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('topRounded')">
                    <el-slider v-model="diyStore.editComponent.swiper.topRounded" show-input size="small"
                               class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('bottomRounded')">
                    <el-slider v-model="diyStore.editComponent.swiper.bottomRounded" show-input size="small"
                               class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselSearchSwiperIndicatorSet') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('carouselSearchSwiperIndicatorStyle')">
                    <el-radio-group v-model="diyStore.editComponent.swiper.indicatorStyle">
                        <el-radio label="style-1">{{ t('carouselSearchSwiperIndicatorStyle1') }}</el-radio>
                        <el-radio label="style-2">{{ t('carouselSearchSwiperIndicatorStyle2') }}</el-radio>
                        <el-radio label="style-3">{{ t('carouselSearchSwiperIndicatorStyle3') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('carouselSearchSwiperIndicatorAlign')">
                    <el-radio-group v-model="diyStore.editComponent.swiper.indicatorAlign">
                        <el-radio label="left">{{ t('alignLeft') }}</el-radio>
                        <el-radio label="center">{{ t('alignCenter') }}</el-radio>
                        <el-radio label="right">{{ t('alignRight') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('noColor')">
                    <el-color-picker v-model="diyStore.editComponent.swiper.indicatorColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('selectColor')">
                    <el-color-picker v-model="diyStore.editComponent.swiper.indicatorActiveColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件样式 -->
        <!-- <slot name="style"></slot> -->
    </div>

</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { img } from '@/utils/common'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive, watch, onMounted, nextTick } from 'vue'
import { ElTable } from 'element-plus'
import Sortable from 'sortablejs'
import { range, cloneDeep } from 'lodash-es'

import { getDiyPageListByCarouselSearch } from '@/app/api/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgColor', 'componentBgUrl', 'marginTop', 'marginBottom', 'topRounded', 'bottomRounded', 'pageBgColor', 'marginBoth'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    diyStore.value[index].search.hotWord.list.forEach((item: any) => {
        if (item.text == '') {
            res.code = false
            res.message = t('carouselSearchHotWordTextPlaceholder')
            return res
        }
    })

    diyStore.value[index].tab.list.forEach((item: any) => {
        if (item.text == '') {
            res.code = false
            res.message = t('carouselSearchTabCategoryTextPlaceholder')
            return res
        }
        // if(item.diy_id == ''){
        //     res.code = false
        //     res.message = t('selectDiyPagePlaceholder')
        //     return res
        // }
    })

    if (diyStore.value[index].swiper.control) {
        diyStore.value[index].swiper.list.forEach((item: any) => {
            if (item.imageUrl == '') {
                res.code = false
                res.message = t('imageUrlTip')
                return res
            }
        })
    }

    return res
}

/** ************ 搜索框样式选择-start ********************/
const selectSearchStyle = reactive({
    title: diyStore.editComponent.search.styleName,
    value: diyStore.editComponent.search.style
})

const showSearchDialog = ref(false)

const showSearchStyle = () => {
    showSearchDialog.value = true
    selectSearchStyle.title = diyStore.editComponent.search.styleName
    selectSearchStyle.value = diyStore.editComponent.search.style
}

const changeSearchStyle = (item: any) => {
    selectSearchStyle.title = item.title
    selectSearchStyle.value = item.value
}

const confirmSearchStyle = () => {
    diyStore.editComponent.search.styleName = selectSearchStyle.title
    diyStore.editComponent.search.style = selectSearchStyle.value
    showSearchDialog.value = false
}

const searchStyleList = reactive([
    {
        url: 'static/resource/images/diy/carousel_search/style_1.png',
        title: '风格1',
        value: 'style-1'
    },
    {
        url: 'static/resource/images/diy/carousel_search/style_2.png',
        title: '风格2',
        value: 'style-2'
    }
])

/** ************ 搜索框样式选择-end ********************/

diyStore.editComponent.search.hotWord.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

diyStore.editComponent.tab.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

diyStore.editComponent.swiper.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

const activeNames = ref(['tab', 'swiper'])
const handleChange = (val: string[]) => {
}

onMounted(() => {
    loadDiyPageList()
})

const addHotWordItem = () => {
    diyStore.editComponent.search.hotWord.list.push({
        id: diyStore.generateRandom(),
        text: '关键词'
    })
}

const tabClear = (index: any) => {
    diyStore.editComponent.tab.list[index].diy_id = 0
    diyStore.editComponent.tab.list[index].diy_title = ''
}

const addTabItem = () => {
    diyStore.editComponent.tab.list.push({
        id: diyStore.generateRandom(),
        text: '分类名称', // 最多4个字
        source: 'diy_page', // 数据源类型，微页面：diy_page
        diy_id: '',
        diy_title: ''
    })
}

const searchHotWordTabBoxRef = ref()

const tabBoxRef = ref()

const imageBoxRef = ref()

onMounted(() => {
    nextTick(() => {
        const hotWordSortable = Sortable.create(searchHotWordTabBoxRef.value, {
            group: 'item-wrap',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.search.hotWord.list[event.oldIndex!]
                diyStore.editComponent.search.hotWord.list.splice(event.oldIndex!, 1)
                diyStore.editComponent.search.hotWord.list.splice(event.newIndex!, 0, temp)
                tabSortable.sort(
                    range(diyStore.editComponent.search.hotWord.list.length).map(value => {
                        return value.toString()
                    })
                )
            }
        })

        const tabSortable = Sortable.create(tabBoxRef.value, {
            group: 'item-wrap',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.tab.list[event.oldIndex!]
                diyStore.editComponent.tab.list.splice(event.oldIndex!, 1)
                diyStore.editComponent.tab.list.splice(event.newIndex!, 0, temp)
                tabSortable.sort(
                    range(diyStore.editComponent.tab.list.length).map(value => {
                        return value.toString()
                    })
                )
            }
        })

        const imageSortable = Sortable.create(imageBoxRef.value, {
            group: 'item-wrap',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.swiper.list[event.oldIndex!]
                diyStore.editComponent.swiper.list.splice(event.oldIndex!, 1)
                diyStore.editComponent.swiper.list.splice(event.newIndex!, 0, temp)
                imageSortable.sort(
                    range(diyStore.editComponent.swiper.list.length).map(value => {
                        return value.toString()
                    })
                )
                handleHeight(true)
            }
        })
    })
})

const diyPageShowDialog = ref(false)

const diyPageTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {}
})
const diyPageTableRef = ref<InstanceType<typeof ElTable>>()

/**
 * 获取自定义页面列表
 */
const loadDiyPageList = (page: number = 1) => {
    diyPageTable.loading = true
    diyPageTable.page = page

    getDiyPageListByCarouselSearch({
        page: diyPageTable.page,
        limit: diyPageTable.limit,
        ...diyPageTable.searchParam
    }).then(res => {
        diyPageTable.loading = false
        const data = res.data.data
        let newData: any = []
        let isExistCount = 0

        // 排除当前编辑的微页面以及存在 置顶组件的数据
        if (diyStore.id) {
            for (let i = 0; i < data.length; i++) {
                if (data[i].id == diyStore.id) {
                    isExistCount++
                } else {
                    newData.push(data[i])
                }
            }
        } else {
            newData = cloneDeep(data) // 添加
        }
        if (isExistCount) {
            res.data.total = res.data.total - isExistCount
        }
        diyPageTable.data = newData
        diyPageTable.total = res.data.total
    }).catch(() => {
        diyPageTable.loading = false
    })
}

// 选择微页面
let currDiyPage: any = {}
let currTabIndexForDiyPage = 0
const handleCurrentDiyPageChange = (val: string | any[]) => {
    currDiyPage = val
}

const saveDiyPageId = () => {
    diyStore.editComponent.tab.list[currTabIndexForDiyPage].diy_id = currDiyPage.id
    diyStore.editComponent.tab.list[currTabIndexForDiyPage].diy_title = currDiyPage.title
    diyPageShowDialog.value = false
}

const diyPageShowDialogOpen = (index: any) => {
    diyPageShowDialog.value = true
    currTabIndexForDiyPage = index
    if (currDiyPage) {
        setTimeout(() => {
            diyPageTableRef.value!.setCurrentRow(currDiyPage)
        }, 200)
    }
}

watch(
    () => diyStore.editComponent.swiper.list,
    (newValue, oldValue) => {
        // 设置图片宽高
        handleHeight()
    },
    { deep: true }
)

const addImageAd = () => {
    diyStore.editComponent.swiper.list.push({
        id: diyStore.generateRandom(),
        imageUrl: '',
        imgWidth: 0,
        imgHeight: 0,
        link: { name: '' }
    })
}

const selectImg = (url: string) => {
    handleHeight(true)
}

const changeSwiperStyle = (value: any) => {
    handleHeight(true)
}

// 处理高度
const handleHeight = (isCalcHeight: boolean = false) => {
    diyStore.editComponent.swiper.list.forEach((item: any, index: number) => {
        const image = new Image()
        image.src = img(item.imageUrl)
        image.onload = async () => {
            item.imgWidth = image.width
            item.imgHeight = image.height
            // 计算第一张图片高度
            if (isCalcHeight && index == 0) {
                const ratio = item.imgHeight / item.imgWidth
                if (diyStore.editComponent.swiper.swiperStyle == 'style-1') {
                    item.width = 375 * 0.92 // 0.92：前端缩放比例
                } else {
                    item.width = 355
                }
                item.height = item.width * ratio
                diyStore.editComponent.swiper.imageHeight = parseInt(item.height)
            }
        }
    })
}

defineExpose({})
</script>

<style lang="scss" scoped></style>

<style lang="scss">
.select-diy-page-input .el-input__inner {
    cursor: pointer;
}

.collapse-wrap {
    .el-collapse-item__header {
        font-size: 16px;
    }
}
</style>
