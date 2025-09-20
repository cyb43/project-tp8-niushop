import { reactive, ref, computed, nextTick } from 'vue'
import { t } from '@/lang'
import { TabsPaneContext, ElMessage } from 'element-plus'
import Sortable from 'sortablejs'
import { range, cloneDeep } from 'lodash-es'
import { debounce, deepClone } from '@/utils/common'
import { useRoute, useRouter } from 'vue-router'
import Test from '@/utils/test'
import {
    getGoodsType,
    getBrandList,
    getLabelList,
    getServeList,
    getSupplierList,
    getCategoryTree,
    getAttrList,
    goodsVerify,
    getGoodsInfoTemplate
} from '@/addon/shop/api/goods'
import { getPosterList } from '@/app/api/poster'
import { getDiyFormList } from '@/app/api/diy_form'

// 商品添加/编辑
export function useGoodsEdit(params: any = {}) {

    const route = useRoute()
    const router = useRouter()

    const repeat = ref(false)

    // 表单数据
    const formData: any = reactive({
        goods_id: '',
        goods_type: 'real',

        // 基础信息
        goods_name: '',
        sub_title: '',
        goods_image: '',
        goods_video: '',
        goods_category: '',
        brand_id: '',
        poster_id: '',
        diy_detail_id: '',
        form_id: '',
        label_ids: [],
        service_ids: [],
        supplier_id: '',
        status: '1',
        is_gift: 0,
        sort: '',

        addon_shop_supplier: [],

        // 价格库存
        spec_type: 'single',
        price: '',
        market_price: '',
        cost_price: '',
        stock: '',
        sku_no: '',
        unit: '',
        virtual_sale_num: '',
        member_discount: '',
        is_limit: 0,
        limit_type: 1,
        max_buy: '',
        min_buy: '',

        // 商品参数
        attr_ids: [],
        attr_format: [],

        // 商品详情
        goods_desc: '',
        skuCheckAll: false,// 是否全选
        skuIsIndeterminate: false,// 是否部分选中
        skuCheckedCities: [],// 选中的规格
    })

    Object.assign(formData, params.formData);

    formData.goods_id = ref(route.query.goods_id)

    // 追加表单数据
    const appendFormData = params.appendFormData;
    Object.assign(formData, appendFormData);

    // 追加刷新商品sku数据
    const appendRefreshGoodsSkuData = params.appendRefreshGoodsSkuData || {};

    // 追加单规格数据
    const appendSingleGoodsData = params.appendSingleGoodsData;

    const getFormRules = params.getFormRules;

    const formRefArr: any = reactive({});

    const getFormRef = params.getFormRef;

    const verifyArr: any = reactive([])

    nextTick(() => {
        let formRef = getFormRef();
        for (let key in formRef) {
            formRefArr[key] = formRef[key];
        }

        if (params.getVerify) {
            verifyArr.splice(0, verifyArr.length, ...params.getVerify())
        }

    });

    const editApi = params.editApi;
    const addApi = params.addApi;

    const activeName: any = ref('basic')
    const tabHandleClick = (tab: TabsPaneContext, event: Event) => {
        // console.log(tab, event)
    }

    // 商品类型
    const goodsType = reactive([])

    // 切换商品类型
    const changeGoodsType = (data: any) => {
        router.push(data.path)
    }

    // 商品类型
    getGoodsType().then((res) => {
        const data = res.data
        if (data) {
            for (const k in data) {
                goodsType.push(data[k])
            }
        }
    })

    // 商品分类
    const goodsCategoryOptions = reactive([])
    const goodsCategoryProps = {
        multiple: true
    }

    const categoryHandleChange = (value: any) => {
        // console.log(value, goodsEdit.formData.goods_category, goodsEdit.formData.goods_category.toString())
    }

    // 跳转到商品分类，添加分类
    const toGoodsCategoryEvent = () => {
        const url = router.resolve({
            path: '/shop/goods/category'
        })
        window.open(url.href)
    }

    // 刷新商品分类
    const refreshGoodsCategory = (bool = false) => {
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
                if (bool) {
                    ElMessage({
                        message: t('refreshSuccess'),
                        type: 'success'
                    })
                }
            }
        })
    }

    refreshGoodsCategory()

    // 品牌列表下拉框
    const brandOptions = reactive([])

    // 跳转到商品品牌，添加品牌
    const toGoodsBrandEvent = () => {
        const url = router.resolve({
            path: '/shop/goods/brand'
        })
        window.open(url.href)
    }

    // 商品品牌
    // const refreshGoodsBrand = (bool = false) => {
    //     getBrandList({}).then((res) => {
    //         const data = res.data
    //         if (data) {
    //             brandOptions.splice(0, brandOptions.length, ...data)
    //             if (bool) {
    //                 ElMessage({
    //                     message: t('refreshSuccess'),
    //                     type: 'success'
    //                 })
    //             }
    //         }
    //     })
    // }
    const refreshGoodsBrand = (query='', bool = false) => {
        getBrandList({brand_name: query}).then((res) => {
            const data = res.data
            if (data) {
                brandOptions.splice(0, brandOptions.length, ...data)
                if (bool) {
                    ElMessage({
                        message: t('refreshSuccess'),
                        type: 'success'
                    })
                }
            }
        })
    }
    // 不能注释，编辑时需要提前加载
    refreshGoodsBrand()

    // 海报列表下拉框
    const posterOptions = reactive([])

    // 跳转到海报列表，添加海报
    const toPosterEvent = () => {
        const url = router.resolve({
            path: '/poster/list'
        })
        window.open(url.href)
    }

    // 商品海报
    const refreshGoodsPoster = (bool = false) => {
        getPosterList({
            type: 'shop_goods'
        }).then((res) => {
            const data = res.data
            if (data) {
                posterOptions.splice(0, posterOptions.length, ...data)
                if (bool) {
                    ElMessage({
                        message: t('refreshSuccess'),
                        type: 'success'
                    })
                }
            }
        })
    }

    refreshGoodsPoster()

    // 商品详情列表下拉框
    const detailTemplateOptions = reactive([])

    // 跳转到微页面，添加商品详情模板
    const toDetailTemplateEvent = () => {
        const url = router.resolve({
            path: '/diy/list'
        })
        window.open(url.href)
    }

    // 刷新微页面
    const refreshDetailTemplate = (bool = false) => {
        getGoodsInfoTemplate({
            type: 'DIY_SHOP_GOODS_DETAIL'
        }).then((res) => {
            const data = res.data
            if (data) {
                detailTemplateOptions.splice(0, detailTemplateOptions.length, ...data)
                if (bool) {
                    ElMessage({
                        message: t('refreshSuccess'),
                        type: 'success'
                    })
                }
            }
        })
    }

    refreshDetailTemplate()


    // 万能表单列表下拉框
    const diyFormOptions = reactive([])

    // 跳转到万能表单列表，添加表单
    const toDiyFormEvent = () => {
        const url = router.resolve({
            path: '/diy_form/list'
        })
        window.open(url.href)
    }

    // 刷新万能表单
    const refreshDiyForm = (bool = false) => {
        getDiyFormList({
            type: 'DIY_FORM_GOODS_DETAIL',
            status: 1
        }).then((res) => {
            const data = res.data
            if (data) {
                diyFormOptions.splice(0, diyFormOptions.length, ...data)
                if (bool) {
                    ElMessage({
                        message: t('refreshSuccess'),
                        type: 'success'
                    })
                }
            }
        })
    }

    refreshDiyForm()

    // 标签组列表复选框
    const labelOptions = reactive([])

    // 跳转到商品标签，添加标签
    const toGoodsLabelEvent = () => {
        const url = router.resolve({
            path: '/shop/goods/label'
        })
        window.open(url.href)
    }

    // 商品标签
    const refreshGoodsLabel = () => {
        getLabelList({}).then((res) => {
            const data = res.data
            if (data) {
                labelOptions.splice(0, labelOptions.length, ...data)
            }
        })
    }

    refreshGoodsLabel()

    // 商品服务列表复选框
    const serviceOptions = reactive([])

    // 跳转到商品服务，添加服务
    const toGoodsServiceEvent = () => {
        const url = router.resolve({
            path: '/shop/goods/service'
        })
        window.open(url.href)
    }

    // 商品服务
    const refreshGoodsService = () => {
        getServeList({}).then((res) => {
            const data = res.data
            if (data) {
                serviceOptions.splice(0, serviceOptions.length, ...data)
            }
        })
    }

    refreshGoodsService()

    // 供应商列表下拉框
    const supplierOptions = reactive([])

    // 跳转到供应商
    const toSupplierEvent = () => {
        const url = router.resolve({
            path: '/shop_supplier/supplier'
        })
        window.open(url.href)
    }

    // 供应商
    const refreshSupplier = () => {
        getSupplierList({}).then((res) => {
            const data = res.data
            if (data) {
                supplierOptions.splice(0, supplierOptions.length, ...data)
            }
        })
    }

    const goodsSpecFormat: any = reactive([]) // 规格项/规格值
    const goodsSkuData: any = reactive({}) // 商品SKU规格数据
    const specData: any = reactive([]) // 商品规格值

    const activeGoodsCount: any = ref(0)

    const isDisabledPrice = () => {
        if (activeGoodsCount.value > 0) {
            return true;
        }
        return false;
    }

    const handleGoodsInit = (data: any) => {
        formData.addon_shop_supplier = data.addon_shop_supplier
        if (formData.addon_shop_supplier && formData.addon_shop_supplier.status == 1) {
            refreshSupplier()
        }

        if (data.default_sort) {
            formData.sort = data.default_sort;
        }

        if (formData.goods_id && data.goods_info) {

            // 商品参与营销活动的数量
            activeGoodsCount.value = data.goods_info.active_goods_count;

            // 基础信息
            formData.goods_name = data.goods_info.goods_name
            formData.sub_title = data.goods_info.sub_title
            formData.goods_type = data.goods_info.goods_type
            formData.goods_image = data.goods_info.goods_image
            formData.goods_video = data.goods_info.goods_video
            formData.goods_category = data.goods_info.goods_category
            formData.brand_id = data.goods_info.brand_id
            formData.poster_id = data.goods_info.poster_id
            formData.form_id = data.goods_info.form_id
            formData.diy_detail_id = data.goods_info.diy_detail_id
            formData.label_ids = data.goods_info.label_ids
            formData.service_ids = data.goods_info.service_ids
            formData.supplier_id = data.goods_info.supplier_id
            formData.status = data.goods_info.status
            formData.sort = data.goods_info.sort
            formData.is_gift = data.goods_info.is_gift

            /*************** 商品参数-start ****************/
            formData.attr_format = data.goods_info.attr_format
            formData.attr_ids = data.goods_info.attr_ids

            attrChange(formData.attr_ids, true)
            /*************** 商品参数-end ****************/

            // 价格库存
            formData.spec_type = data.goods_info.spec_type
            formData.stock = data.goods_info.stock

            if (formData.spec_type == 'single') {
                // 单规格
                const skuInfo = data.goods_info.sku_list[0]
                formData.price = skuInfo.price
                formData.market_price = skuInfo.market_price
                formData.cost_price = skuInfo.cost_price
                formData.sku_no = skuInfo.sku_no

                if (appendSingleGoodsData) Object.assign(formData, appendSingleGoodsData(skuInfo));
            } else if (formData.spec_type == 'multi') {
                // 多规格
                const specList = data.goods_info.spec_list
                specList.forEach((item: any) => {
                    const values: any = []
                    item.spec_values = item.spec_values.split(',')
                    item.spec_values.forEach((v: any) => {
                        values.push({
                            id: generateRandom(),
                            spec_value_name: v
                        })
                    })
                    goodsSpecFormat.push({
                        id: generateRandom(),
                        spec_id: item.spec_id,
                        goods_id: item.goods_id,
                        spec_name: item.spec_name,
                        values
                    })
                })

                refreshGoodsSkuData();

                const skuList = data.goods_info.sku_list

                for (let key in goodsSkuData) {
                    for (let i = 0; i < skuList.length; i++) {
                        let item = skuList[i];
                        if (goodsSkuData[key].spec_name == item.sku_spec_format.replace(/,/g, ' ')) {
                            goodsSkuData[key].sku_id = item.sku_id;
                            goodsSkuData[key].sku_image = item.sku_image;
                            goodsSkuData[key].price = item.price;
                            goodsSkuData[key].market_price = item.market_price;
                            goodsSkuData[key].cost_price = item.cost_price;

                            for (let field in appendRefreshGoodsSkuData) {
                                goodsSkuData[key][field] = item[field];
                            }

                            goodsSkuData[key].stock = item.stock;
                            goodsSkuData[key].sku_id = item.sku_id;
                            goodsSkuData[key].sku_no = item.sku_no;
                            goodsSkuData[key].is_default = item.is_default;
                            break;
                        }
                    }
                }

                nextTick(() => {
                    refreshSkuTable()
                    bindSpecValue()
                })
            }

            formData.member_discount = data.goods_info.member_discount

            formData.unit = data.goods_info.unit
            formData.virtual_sale_num = data.goods_info.virtual_sale_num
            formData.is_limit = data.goods_info.is_limit
            formData.limit_type = data.goods_info.limit_type
            formData.max_buy = data.goods_info.max_buy
            formData.min_buy = data.goods_info.min_buy

            // 商品详情
            formData.goods_desc = data.goods_info.goods_desc
        }
    }

    // 绑定拖拽规格值事件
    const bindSpecValue = () => {
        // 商品正在参与营销活动，禁止操作
        if (isDisabledPrice()) {
            return
        }
        if (!getFormRef().specValueRef) return

        for (let i = 0; i < getFormRef().specValueRef.length; i++) {
            const item = getFormRef().specValueRef[i]
            const sortable = Sortable.create(item, {
                group: 'draggable-element-' + i,
                animation: 200,
                // 结束拖拽
                onEnd: event => {
                    const temp = goodsSpecFormat[i].values[event.oldIndex!]
                    goodsSpecFormat[i].values.splice(event.oldIndex!, 1)
                    goodsSpecFormat[i].values.splice(event.newIndex!, 0, temp)

                    nextTick(() => {
                        sortable.sort(
                            range(goodsSpecFormat[i].values.length).map(value => {
                                return value.toString()
                            })
                        )

                        // 渲染商品规格数据、表格
                        refreshGoodsSkuData()
                        refreshSkuTable()
                    })
                }
            })
        }
    }

    // 生成随机数
    const generateRandom = (len: number = 5) => {
        return Number(Math.random().toString().substr(3, len) + Date.now()).toString(36)
    }

    // 添加规格项
    const addSpec = () => {
        // 商品正在参与营销活动，禁止操作
        if (isDisabledPrice()) {
            ElMessage({
                type: 'warning',
                message: `${ t('participateInActiveDisableTips') }`
            })
            return
        }
        if (goodsSpecFormat.length > 4) {
            ElMessage({
                type: 'warning',
                message: `${ t('maxAddSpecTips') }`
            })
            return
        }
        goodsSpecFormat.push({
            id: generateRandom(),
            spec_name: '',
            values: [
                {
                    id: generateRandom(),
                    spec_value_name: ''
                }
            ]
        })
    }

    // 删除规格项
    const deleteSpec = (index: number) => {
        // 商品正在参与营销活动，禁止操作
        if (isDisabledPrice()) {
            ElMessage({
                type: 'warning',
                message: `${ t('participateInActiveDisableTips') }`
            })
            return
        }
        goodsSpecFormat.splice(index, 1)
        // 渲染商品规格数据、表格、统计库存变化
        refreshGoodsSkuData()
        refreshSkuTable()
        specStockSum()
    }

    // 添加规格值
    const addSpecValue = (index: number) => {
        // 商品正在参与营销活动，禁止操作
        if (isDisabledPrice()) {
            ElMessage({
                type: 'warning',
                message: `${ t('participateInActiveDisableTips') }`
            })
            return
        }
        goodsSpecFormat[index].values.push({
            id: generateRandom(),
            spec_value_name: ''
        })
        bindSpecValue()
    }

    // 监听规格值变化
    const specValueNameInputListener = debounce((value) => {
        // 渲染商品规格数据、表格
        refreshGoodsSkuData()
        refreshSkuTable()

    })

    // 删除规格值
    const deleteSpecValue = (index: number, specIndex: number) => {
        // 商品正在参与营销活动，禁止操作
        if (isDisabledPrice()) {
            ElMessage({
                type: 'warning',
                message: `${ t('participateInActiveDisableTips') }`
            })
            return
        }
        goodsSpecFormat[index].values.splice(specIndex, 1)
        // 渲染商品规格数据、表格、统计库存变化
        refreshGoodsSkuData()
        refreshSkuTable()
        specStockSum()
    }

    // 设置默认规格
    const specValueIsDefaultChangeListener = (value: any, key: any) => {
        for (const k in goodsSkuData) {
            if (k == key) {
                goodsSkuData[k].is_default = value
            } else {
                goodsSkuData[k].is_default = 0
            }
        }
    }

    // 监听规格库存变化，统计总库存
    const specStockSum = debounce(() => {
        let stock = 0
        for (const k in goodsSkuData) {
            if (goodsSkuData[k].stock) stock += parseInt(goodsSkuData[k].stock)
        }
        formData.stock = stock
    })

    // 刷新商品规格数据
    const refreshGoodsSkuData = () => {
        const arr = goodsSpecFormat
        const tempGoodsSkuData = cloneDeep(goodsSkuData)// 记录原始数据，后续用作对比
        let skuData: any = {}
        let tempIndex = 0;
        for (const spec of arr) {
            let item_prop_arr: any = {}
            if (Object.keys(skuData).length > 0) {
                for (const ele_2 in skuData) {
                    for (let ele_3 of spec.values) {
                        let sku_spec = cloneDeep(skuData[ele_2].sku_spec)// 防止对象引用
                        sku_spec.push(ele_3)
                        item_prop_arr['sku_' + tempIndex] = {
                            spec_name: `${ skuData[ele_2].spec_name } ${ ele_3.spec_value_name }`,
                            sku_spec,
                            sku_image: '',
                            price: '',
                            market_price: '',
                            cost_price: '',
                            stock: '',
                            sku_no: '',
                            is_default: 0
                        }

                        for (let key in appendRefreshGoodsSkuData) {
                            item_prop_arr['sku_' + tempIndex][key] = appendRefreshGoodsSkuData[key].value;
                        }
                        tempIndex++;
                    }
                }
            } else {
                for (let ele_1 of spec.values) {
                    let spec_name = ele_1.spec_value_name
                    item_prop_arr['sku_' + tempIndex] = {
                        spec_name: spec_name,
                        sku_spec: [ele_1],
                        sku_image: '',
                        price: '',
                        market_price: '',
                        cost_price: '',
                        stock: '',
                        sku_no: '',
                        is_default: 0
                    }
                    for (let key in appendRefreshGoodsSkuData) {
                        item_prop_arr['sku_' + tempIndex][key] = appendRefreshGoodsSkuData[key].value;
                    }
                    tempIndex++;
                }
            }

            skuData = Object.keys(item_prop_arr).length > 0 ? item_prop_arr : skuData
        }

        // 比对已存在的规格项/值，并且赋值
        for (const tempKey in tempGoodsSkuData) {
            for (const key in skuData) {
                const count = matchSkuSpecCount(tempGoodsSkuData[tempKey].sku_spec, skuData[key].sku_spec)
                if (count === skuData[key].sku_spec.length) {
                    // 匹配成功后，要同步最新的规格项名称、规格值集合
                    const specName = skuData[key].spec_name
                    const skuSpec = skuData[key].sku_spec
                    Object.assign(skuData[key], tempGoodsSkuData[tempKey])
                    skuData[key].spec_name = specName
                    skuData[key].sku_spec = skuSpec
                    break
                }
            }
        }

        for (const item in goodsSkuData) {
            delete goodsSkuData[item]
        }

        let firstSpec = ''

        for (const key in skuData) {
            if (firstSpec == '') {
                firstSpec = key
                skuData[key].is_default = 1
            } else {
                skuData[key].is_default = 0
            }
            goodsSkuData[key] = skuData[key]
        }
        formData.skuCheckAll = false;// 是否全选
        formData.skuIsIndeterminate = false// 是否部分选中
        formData.skuCheckedCities = []// 选中的规格
    }

    // 匹配规格值
    const matchSkuSpecCount = (oVal: any, nVal: any) => {
        let count = 0// 匹配次数，与规格值相等时为匹配成功
        for (let i = 0; i < oVal.length; i++) {
            for (let j = 0; j < nVal.length; j++) {
                if (oVal[i].id === nVal[j].id) {
                    count++
                    break
                }
            }
        }
        return count
    }

    // 刷新商品规格表格
    const refreshSkuTable = () => {
        let length = 0
        // 统计有效规格数量
        for (let i = 0; i < goodsSpecFormat.length; i++) {
            if (goodsSpecFormat[i].spec_name != '' && goodsSpecFormat[i].values.length > 0) {
                length++
            }
        }

        let row = 1 // 跨行
        const tempSpecData = []

        // 规格值单元格合并
        for (let i = length - 1; i >= 0; i--) {
            for (let k = 0; k < Object.keys(goodsSkuData).length;) {
                if (goodsSpecFormat[i].values.length > 0) {
                    for (let ele of goodsSpecFormat[i].values) {
                        tempSpecData.push({
                            index: k,
                            colSpan: i,
                            rowSpan: row,
                            spec_value_name: ele.spec_value_name
                        })
                        k = k + row
                    }
                } else {
                    k++
                }
            }
            row = row * goodsSpecFormat[i].values.length
        }

        tempSpecData.reverse()
        specData.splice(0, specData.length, ...tempSpecData)
    }

    const batchOperation: any = reactive({
        spec: '', // 所选规格id，空为全部
        price: '', // 销售价
        market_price: '', // 划线价
        cost_price: '', // 成本价
        stock: '', // 库存
        sku_no: '' // 商品编码
    })

    const appendBatchOperation: any = {};
    for (let key in appendRefreshGoodsSkuData) {
        appendBatchOperation[key] = appendRefreshGoodsSkuData[key].value;
    }

    Object.assign(batchOperation, appendBatchOperation);

    const skuHandleCheckAllChange = (value: any) => {
        formData.skuIsIndeterminate = false
        if (value) {
            formData.skuCheckedCities = Object.keys(goodsSkuData)
        } else {
            formData.skuCheckedCities = []
        }
    }
    const handleCheckedCitiesChange = (value: any) => {
        const checkedCount = value.length
        formData.skuCheckAll = checkedCount === Object.keys(goodsSkuData).length
        formData.skuIsIndeterminate = checkedCount > 0 && checkedCount < Object.keys(goodsSkuData).length
    }
    // 批量设置确认
    const saveBatch = () => {
        if (formData.skuCheckedCities.length == 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('pleaseSelectSku') }`
            })
            return
        }
        // 验证输入内容
        if (batchOperation.price && (isNaN(batchOperation.price) || !regExp.digit.test(batchOperation.price))) {
            ElMessage({
                type: 'warning',
                message: `${ t('priceTips') }`
            })
            return
        }
        if (batchOperation.market_price && (isNaN(batchOperation.market_price) || !regExp.digit.test(batchOperation.market_price))) {
            ElMessage({
                type: 'warning',
                message: `${ t('marketPriceTips') }`
            })
            return
        }
        if (batchOperation.cost_price && (isNaN(batchOperation.cost_price) || !regExp.digit.test(batchOperation.cost_price))) {
            ElMessage({
                type: 'warning',
                message: `${ t('costPriceTips') }`
            })
            return
        }
        if (batchOperation.stock && (isNaN(batchOperation.stock) || !regExp.number.test(batchOperation.stock))) {
            ElMessage({
                type: 'warning',
                message: `${ t('stockTips') }`
            })
            return
        }

        for (let field in appendRefreshGoodsSkuData) {
            let reg = regExp[appendRefreshGoodsSkuData[field].regExp]
            let message = appendRefreshGoodsSkuData[field].message

            if (batchOperation[field] && (isNaN(batchOperation[field]) || !reg.test(batchOperation[field]))) {
                ElMessage({
                    type: 'warning',
                    message: message
                })
                return
            }
        }

        // 设置全部规格
        formData.skuCheckedCities.forEach((key: any) => {
            if (batchOperation.price) goodsSkuData[key].price = batchOperation.price
            if (batchOperation.market_price) goodsSkuData[key].market_price = batchOperation.market_price
            if (batchOperation.cost_price) goodsSkuData[key].cost_price = batchOperation.cost_price
            if (batchOperation.stock) goodsSkuData[key].stock = batchOperation.stock

            for (let field in appendRefreshGoodsSkuData) {
                if (batchOperation[field]) goodsSkuData[key][field] = batchOperation[field]
            }

            if (batchOperation.sku_no) goodsSkuData[key].sku_no = batchOperation.sku_no
        })

        // 保存完清空
        batchOperation.price = ''
        batchOperation.market_price = ''
        batchOperation.cost_price = ''
        batchOperation.stock = ''
        batchOperation.sku_no = ''

        for (let field in appendRefreshGoodsSkuData) {
            batchOperation[field] = '';
        }
    }

    // 正则表达式
    const regExp: any = {
        required: /[\S]+/,
        number: /^\d{0,10}$/,
        digit: /^\d{0,10}(.?\d{0,2})$/,
        special: /^\d{0,10}(.?\d{0,3})$/
    }

    // 表单验证规则
    const formRules = computed(() => {
        let rules = {
            goods_name: [
                {
                    required: true,
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (value === '') {
                            callback(new Error(t('goodsNamePlaceholder')))
                        }
                        if (value.length > 60) {
                            callback(new Error(t('goodsNameMaxLengthTips')))
                        } else {
                            callback()
                        }
                    }
                }
            ],
            sub_title: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (value.length > 100) {
                            callback(new Error(t('subTitleMaxLengthTips')))
                        } else {
                            callback()
                        }
                    }
                }
            ],
            goods_image: [
                { required: true, message: t('goodsImagePlaceholder'), trigger: 'blur' }
            ],
            goods_category: [
                { required: true, message: t('goodsCategoryPlaceholder'), trigger: 'blur' }
            ],
            sort: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (isNaN(value) || !regExp.number.test(value)) {
                            callback(new Error(t('sortTips')))
                        } else {
                            callback()
                        }
                    }
                }
            ],
            // unit: [
            //     {required: true, message: t('unitPlaceholder'), trigger: 'blur'}
            // ],
            // 单规格
            price: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.spec_type == 'single') {
                            if (value === '') {
                                callback(new Error(t('pricePlaceholder')))
                            } else if (isNaN(value) || !regExp.digit.test(value)) {
                                callback(new Error(t('priceTips')))
                            } else if (value < 0) {
                                callback(new Error(t('priceNotZeroTips')))
                            } else {
                                callback()
                            }
                        } else {
                            callback()
                        }
                    }
                }
            ],
            market_price: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.spec_type == 'single') {
                            if (isNaN(value) || !regExp.digit.test(value)) {
                                callback(new Error(t('marketPriceTips')))
                            } else if (value < 0) {
                                callback(new Error(t('marketPriceNotZeroTips')))
                            } else {
                                callback()
                            }
                        } else {
                            callback()
                        }
                    }
                }
            ],
            cost_price: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.spec_type == 'single') {
                            if (isNaN(value) || !regExp.digit.test(value)) {
                                callback(new Error(t('costPriceTips')))
                            } else if (value < 0) {
                                callback(new Error(t('costPriceNotZeroTips')))
                            } else {
                                callback()
                            }
                        } else {
                            callback()
                        }
                    }
                }
            ],
            stock: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.spec_type == 'single') {
                            if (value === '') {
                                callback(new Error(t('stockPlaceholder')))
                            } else if (isNaN(value) || !regExp.number.test(value)) {
                                callback(new Error(t('stockTips')))
                            } else if (value < 0) {
                                callback(new Error(t('stockNotZeroTips')))
                            } else {
                                callback()
                            }
                        } else {
                            callback()
                        }
                    }
                }
            ],
            virtual_sale_num: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.spec_type == 'single') {
                            if (isNaN(value) || !regExp.number.test(value)) {
                                callback(new Error(t('virtualSaleNumTips')))
                            } else if (value < 0) {
                                callback(new Error(t('virtualSaleNumNotZeroTips')))
                            } else {
                                callback()
                            }
                        } else {
                            callback()
                        }
                    }
                }
            ],
            // 多规格
            spec_type: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.spec_type == 'multi') {
                            if (Object.keys(goodsSkuData).length == 0) {
                                callback(new Error(t('pleaseEditSpecPlaceholder')))
                            }
                        }
                        callback()
                    }
                }
            ],
            max_buy: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (value === '') {
                            callback(new Error(t('maxBuyPlaceholder')))
                        } else if (isNaN(value) || !regExp.number.test(value)) {
                            callback(new Error(t('maxBuyTips')))
                        } else if (value < 1) {
                            callback(new Error(t('maxBuyNotZeroTips')))
                        } else {
                            callback()
                        }
                    }
                }
            ],
            min_buy: [
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (isNaN(value) || !regExp.number.test(value)) {
                            callback(new Error(t('minBuyFormatErrorTips')))
                        } else if (value < 0) {
                            callback(new Error(t('minBuyNotZeroTips')))
                        } else if (formData.is_limit == 1 && value > Number(formData.max_buy)) {
                            callback(new Error(t('minBuyGreaterThanMaxBuyTips')))
                        } else {
                            callback()
                        }
                    }
                }
            ],
            goods_desc: [
                {
                    required: true,
                    trigger: ['blur', 'change'],
                    validator: (rule: any, value: any, callback: any) => {
                        if (value === '') {
                            callback(new Error(t('goodsDescPlaceholder')))
                        } else if (value.length < 5 || value.length > 50000) {
                            callback(new Error(t('goodsDescMaxTips')))
                            return false
                        } else {
                            callback()
                        }
                    }
                }
            ]
        };

        if (getFormRules) {
            Object.assign(rules, getFormRules(formData, regExp));
        }

        return rules;
    })

    // 多规格，销售价 验证规则
    const skuPriceRules = () => {
        return [{
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (formData.spec_type == 'multi') {
                    if (value.length == 0) {
                        callback(t('pricePlaceholder'))
                    } else if (isNaN(value) || !regExp.digit.test(value)) {
                        callback(t('priceTips'))
                    } else if (value < 0) {
                        callback(t('priceNotZeroTips'))
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }

            }
        }]
    };

    // 多规格，划线价 验证规则
    const skuMarketPriceRules = () => {
        return [{
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (formData.spec_type == 'multi') {
                    if (isNaN(value) || !regExp.digit.test(value)) {
                        callback(t('marketPriceTips'))
                    } else if (value < 0) {
                        callback(t('marketPriceNotZeroTips'))
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            }
        }]
    }

    // 多规格，成本价 验证规则
    const skuCostPriceRules = () => {
        return [{
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (formData.spec_type == 'multi') {
                    if (isNaN(value) || !regExp.digit.test(value)) {
                        callback(t('costPriceTips'))
                    } else if (value < 0) {
                        callback(t('costPriceNotZeroTips'))
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            }
        }]
    }

    // 多规格，库存 验证规则
    const skuStockRules = () => {
        return [{
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (formData.spec_type == 'multi') {
                    if (value.length == 0) {
                        callback(t('stockPlaceholder'))
                    } else if (isNaN(value) || !regExp.number.test(value)) {
                        callback(t('stockTips'))
                    } else if (value < 0) {
                        callback(t('stockNotZeroTips'))
                    } else {
                        callback();
                    }

                } else {
                    callback();
                }

            }
        }]
    }

    const verify = (callback: any) => {
        let formRef = [
            {
                key: 'basic',
                verify: false,
                ref: formRefArr.basicFormRef
            },
            {
                key: 'price_stock',
                verify: false,
                ref: formRefArr.priceStockFormRef
            },
            {
                key: 'price_stock',
                verify: false,
                ref: formRefArr.skuFormRef
            },
            {
                key: 'price_stock',
                verify: false,
                ref: formRefArr.priceStockCommonFormRef
            }
        ];
        formRef = formRef.concat(verifyArr);

        // 调整验证顺序 -- 详情验证
        let obj = {
            key: 'detail',
            verify: false,
            ref: formRefArr.detailFormRef
        };
        formRef.push(obj)

        formRef.forEach((el: any, index) => {
            el.ref.validate((valid: any) => {
                el.verify = valid
            })
        })

        if (formData.spec_type == 'multi') {
            let specVerify = true
            let repeatSpecNameArr: any = [];
            let repeatSpecValueNameArr: any = [];
            for (let i = 0; i < goodsSpecFormat.length; i++) {
                const spec = goodsSpecFormat[i]
                if (Test.require(spec.spec_name)) {
                    specVerify = false
                    ElMessage({ type: 'warning', message: `${ t('specNameRequire') }` })
                    break
                }
                if (repeatSpecNameArr.indexOf(spec.spec_name) > -1) {
                    specVerify = false
                    ElMessage({ type: 'warning', message: `${ t('specNameRepeat') }` })
                    break
                } else {
                    repeatSpecNameArr.push(spec.spec_name);
                }

                if (spec.values.length) {
                    for (let v = 0; v < spec.values.length; v++) {
                        const value = spec.values[v]
                        if (Test.require(value.spec_value_name)) {
                            specVerify = false
                            ElMessage({ type: 'warning', message: `${ t('specValueRequire') }` })
                            break
                        }

                        if (repeatSpecValueNameArr.indexOf(value.spec_value_name) > -1) {
                            specVerify = false
                            ElMessage({ type: 'warning', message: `${ t('specValueNameRepeat') }` })
                            break
                        } else {
                            repeatSpecValueNameArr.push(value.spec_value_name);
                        }
                    }
                } else {
                    specVerify = false
                    ElMessage({ type: 'warning', message: `${ t('specValueRequire') }` })
                }

                if (!specVerify) break
            }

            if (!specVerify) {
                activeName.value = 'price_stock'
                return
            }

            let isHasDefaultSpec = false; // 是否存在默认规格
            for (const k in goodsSkuData) {
                if (goodsSkuData[k].is_default) {
                    isHasDefaultSpec = true;
                }
            }

            if (!isHasDefaultSpec) {
                activeName.value = 'price_stock'
                ElMessage({ type: 'warning', message: `${ t('lackDefaultSpec') }` })
                return
            }
        }

        setTimeout(() => {
            let verify = true
            // 检测验证，并且定位tab页面
            for (let i = 0; i < formRef.length; i++) {
                if (formRef[i].verify == false) {
                    activeName.value = formRef[i].key
                    verify = false
                    break
                }
            }
            if (verify && callback) callback()
        }, 10)
    }

    // 保存数据
    const save = (callback: any = null) => {
        verify(() => {

            if (repeat.value) return
            repeat.value = true

            const api = formData.goods_id ? editApi : addApi
            const data = cloneDeep(formData)

            if (data.spec_type == 'multi') {
                data.stock = 0
                for (const k in goodsSkuData) {
                    if (goodsSkuData[k].stock) data.stock += parseInt(goodsSkuData[k].stock)
                }
            }

            const goodsCategory: any = []
            data.goods_category.forEach((item: any) => {
                if (typeof item == 'object') {
                    item.forEach((second: any) => {
                        if (goodsCategory.indexOf(second) == -1) {
                            goodsCategory.push(second)
                        }
                    })
                } else {
                    if (goodsCategory.indexOf(item) == -1) {
                        goodsCategory.push(item)
                    }
                }
            })

            data.goods_category = goodsCategory
            data.goods_sku_data = goodsSkuData
            data.goods_spec_format = goodsSpecFormat

            /***** 商品参数-start ****/
            data.attr_format = [];
            let attrFormat: any = deepClone(attrTableData);
            attrFormat.forEach((item: any, index: any) => {
                if (item.attr_value_name && item.select_child_val || item.attr_value_id > 0) {
                    let obj: any = {};
                    obj.attr_id = item.attr_id
                    obj.attr_value_id = item.attr_value_id
                    obj.attr_value_name = item.attr_value_name
                    obj.type = item.type
                    obj.sort = item.sort ? item.sort : 0
                    obj.attr_child_value_id = item.select_child_name
                    obj.attr_child_value_name = item.select_child_val
                    data.attr_format.push(obj);
                }
            });
            data.attr_format.sort((a: any, b: any) => {
                return b.sort - a.sort
            });
            /***** 商品参数-end ****/

            if (callback) {
                Object.assign(data, callback(data));
            }

            api(data).then((res: any) => {
                repeat.value = false
                router.push('/shop/goods/list')
            }).catch(() => {
                repeat.value = false
            })
        })
    }

    const back = () => {
        router.push('/shop/goods/list')
    }

    const filterSpecial = (event: any) => {
        event.target.value = event.target.value.replace(/[^\u4e00-\u9fa5a-zA-Z0-9\s]/g, '')
        event.target.value = event.target.value.replace(/[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]·~！@#￥%……&*（）——\-+={}|《》？：“”【】、；‘’，。、]/g, '')
    }

    const handleBlur = (e: any) => {
        formRefArr.detailFormRef?.validateField('goods_desc')
    }

    const goodsVerifyFn = (data: any) => {
        if (!data.target.value) return false
        const obj = {
            goods_id: formData.goods_id,
            sku_no: data.target.value
        }
        goodsVerify(obj).then((res) => {

        })
    }

    /******************** 商品参数-start ***************************/
    const attrOptions = reactive([])
    const getAttrListFn = () => {
        getAttrList({}).then((res) => {
            attrOptions.splice(0, attrOptions.length, ...res.data)
        })
    }

    getAttrListFn();

    const attrTableData: any = reactive([])

    const attrChange = (attr_id_arr: any = [], isEdit: any = false) => {
        if (attr_id_arr.length == 0) {
            // 清空已选的商品参数模板
            let temporaryAttrData: any = deepClone(attrTableData);
            temporaryAttrData = temporaryAttrData.filter((item: any) => item.attr_value_id < 0);
            attrTableData.splice(0, attrTableData.length, ...temporaryAttrData);
        }

        // 编辑初始化赋值
        const editInit = () => {
            if (isEdit) {
                attrTableData.forEach((item: any) => {
                    for (let i = 0; i < formData.attr_format.length; i++) {
                        if (formData.attr_format[i].attr_value_id == item.attr_value_id) {
                            item.select_child_name = formData.attr_format[i].attr_child_value_id
                            item.select_child_val = formData.attr_format[i].attr_child_value_name
                            item.sort = formData.attr_format[i].sort
                            break
                        }
                    }
                })

                for (let i = 0; i < formData.attr_format.length; i++) {
                    if (formData.attr_format[i].attr_value_id < 0) {
                        let item = formData.attr_format[i]
                        let obj: any = {
                            attr_id: item.attr_id,
                            attr_value_id: item.attr_value_id,
                            attr_value_name: item.attr_value_name,
                            sort: item.sort ? item.sort : 0,
                            type: "text",
                            select_child_name: item.attr_child_value_id,
                            select_child_val: item.attr_child_value_name
                        };
                        attrTableData.push(obj);
                    }
                }

            }
        }

        if (attr_id_arr.length) {
            getAttrList({
                attr_id_arr
            }).then((res) => {
                let data = res.data;
                if (data && Object.keys(data).length) {
                    let temporaryData: any = deepClone(attrTableData);
                    temporaryData = temporaryData.filter((item: any) => {
                        if (item.attr_value_id > 0) {
                            return attr_id_arr.indexOf(item.attr_id) != -1
                        } else {
                            return true; // 保留自定义添加的商品参数模板
                        }
                    });

                    let formAttrIdArray = temporaryData.map((obj: any) => obj.attr_value_id);

                    data.forEach((item: any) => {
                        if (item.attr_value_format) {
                            let attr_value_format = JSON.parse(item.attr_value_format)

                            // 过滤已添加的商品参数模板
                            if (formAttrIdArray.length) {
                                formAttrIdArray.forEach((id: any) => {
                                    attr_value_format = attr_value_format.filter((child: any) => child.attr_value_id != id)
                                })
                            }

                            // 初始化
                            attr_value_format.filter((child: any) => {
                                child.attr_id = item.attr_id
                                child.select_child_name = child.type == "checkbox" ? [] : ''
                                child.select_child_val = child.type == "checkbox" ? [] : ''
                            });

                            attr_value_format.sort((a: any, b: any) => {
                                return b.sort - a.sort
                            });

                            temporaryData = temporaryData.concat(attr_value_format);
                            attrTableData.splice(0, attrTableData.length, ...temporaryData);
                        }
                    })
                    editInit();
                }
            })
        } else {
            editInit();
        }
    }

    // 添加商品参数
    const addAttr = () => {
        let obj: any = {
            attr_id: '',
            attr_value_id: "",
            attr_value_name: "",
            child: {
                id: 1,
                name: ''
            },
            sort: 0,
            type: "text",
            select_child_name: '',
            select_child_val: ''
        };
        obj.attr_value_id = -Math.floor((new Date().getSeconds()) + Math.floor(new Date().getMilliseconds()));
        obj.sort = attrTableData.length + 1;
        attrTableData.push(obj);
    }

    // 删除商品参数
    const delAttr = (index: any) => {
        attrTableData.splice(index, 1);
    }

    // 商品参数单选监听
    const attrRadioChange = (index: any, data: any) => {
        attrTableData.forEach((item: any, index: any, array: any) => {
            if (item.type == 'radio') {
                item.child.forEach((childItem: any, childIndex: any) => {
                    if (childItem.id == data) {
                        array[index].select_child_name = childItem.id;
                        array[index].select_child_val = childItem.name;
                    }
                });
            }
            return item;
        });
    }

    // 商品参数多选监听
    const attrCheckboxChange = (index: any, data: any) => {
        attrTableData[index].select_child_val = []
        attrTableData[index].child.forEach((childItem: any, childIndex: any) => {
            if (data.indexOf(childItem.id) > -1) {
                attrTableData[index].select_child_val.push(childItem.name);
            }
        });
    }

    return {
        formData,

        activeName,
        tabHandleClick,

        goodsType,
        changeGoodsType,

        goodsCategoryOptions,
        goodsCategoryProps,
        categoryHandleChange,
        toGoodsCategoryEvent,
        refreshGoodsCategory,

        brandOptions,
        toGoodsBrandEvent,
        refreshGoodsBrand,

        posterOptions,
        toPosterEvent,
        refreshGoodsPoster,

        detailTemplateOptions,
        toDetailTemplateEvent,
        refreshDetailTemplate,

        diyFormOptions,
        toDiyFormEvent,
        refreshDiyForm,

        labelOptions,
        toGoodsLabelEvent,
        refreshGoodsLabel,

        serviceOptions,
        toGoodsServiceEvent,
        refreshGoodsService,

        supplierOptions,
        toSupplierEvent,
        refreshSupplier,

        goodsSpecFormat,
        goodsSkuData,
        specData,

        generateRandom,

        isDisabledPrice,
        addSpec,
        deleteSpec,
        addSpecValue,
        specValueNameInputListener,
        deleteSpecValue,
        specValueIsDefaultChangeListener,
        specStockSum,

        batchOperation,
        skuHandleCheckAllChange,
        handleCheckedCitiesChange,
        saveBatch,

        regExp,

        formRules,
        skuPriceRules,
        skuMarketPriceRules,
        skuCostPriceRules,
        skuStockRules,

        handleGoodsInit,
        save,
        back,
        filterSpecial,
        handleBlur,

        attrOptions,
        attrChange,
        getAttrListFn,
        attrTableData,
        addAttr,
        delAttr,
        attrRadioChange,
        attrCheckboxChange,
        goodsVerifyFn
    }
}
