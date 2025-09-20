<template>
    <div class="main-container">

        <el-card class="box-card !border-none" shadow="never">
            <el-page-header :content="goodsEdit.formData.goods_id ? t('updateGoods') : t('addGoods')" :icon="ArrowLeft" @back="goodsEdit.back()" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">

            <el-tabs v-model="goodsEdit.activeName" @tab-click="goodsEdit.tabHandleClick">
                <el-tab-pane :label="t('basicInfoTab')" name="basic">

                    <el-form :model="goodsEdit.formData" label-width="120px" ref="basicFormRef" :rules="goodsEdit.formRules" class="page-form">
                        <el-form-item :label="t('goodsType')" v-if="goodsEdit.formData.goods_id">
                            <template v-for="(item) in goodsEdit.goodsType" :key="item.type">
                                <div class="goods-type-wrap" :class="[goodsEdit.formData.goods_type == item.type ? 'selected' : 'disabled']">
                                    <div class="goods-type-name">{{ item.name }}</div>
                                    <div class="goods-type-desc">({{ item.desc }})</div>
                                    <template v-if="goodsEdit.formData.goods_type == item.type">
                                        <div class="triangle"></div>
                                        <div class="selected">✓</div>
                                    </template>
                                </div>
                            </template>
                        </el-form-item>
                        <el-form-item :label="t('goodsType')" v-else>
                            <div class="goods-type-wrap" :class="{ 'selected': goodsEdit.formData.goods_type == item.type }" v-for="(item) in goodsEdit.goodsType" :key="item.type" @click="goodsEdit.changeGoodsType(item)">
                                <div class="goods-type-name">{{ item.name }}</div>
                                <div class="goods-type-desc">({{ item.desc }})</div>
                                <template v-if="goodsEdit.formData.goods_type == item.type">
                                    <div class="triangle"></div>
                                    <div class="selected">✓</div>
                                </template>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('goodsName')" prop="goods_name">
                            <el-input v-model.trim="goodsEdit.formData.goods_name" clearable :placeholder="t('goodsNamePlaceholder')" class="input-width" maxlength="60" show-word-limit />
                        </el-form-item>
                        <el-form-item :label="t('subTitle')" prop="sub_title">
                            <el-input v-model.trim="goodsEdit.formData.sub_title" clearable :placeholder="t('subTitlePlaceholder')" class="input-width" maxlength="100" show-word-limit />
                        </el-form-item>
                        <el-form-item :label="t('goodsImage')" prop="goods_image">
                            <upload-image v-model="goodsEdit.formData.goods_image" :limit="10" />
                        </el-form-item>
                        <el-form-item :label="t('goodsVideo')">
                            <div class="flex flex-col">
                                <upload-video v-model="goodsEdit.formData.goods_video" :limit="1" />
                                <el-input v-model="goodsEdit.formData.goods_video" clearable :placeholder="t('在此输入外链视频地址')" class="input-width mt-[10px]" />
                                <div class="flex flex-col mt-[10px]">
                                    <div class="text-[12px] text-[#999] leading-[20px]">{{ t('goodsVideoTipTile') }}</div>
                                    <div class="mt-[5px] text-[12px] text-[var(--el-color-primary)] leading-[20px]">{{ t('goodsVideoTipOne') }}</div>
                                    <div class="mt-[5px] text-[12px] text-[var(--el-color-primary)] leading-[20px]">{{ t('goodsVideoTipTwo') }}</div>
                                    <div class="mt-[5px] text-[12px] text-[var(--el-color-primary)] leading-[20px]">{{ t('goodsVideoTipThree') }}</div>
                                    <div class="mt-[5px] text-[12px] text-[var(--el-color-primary)] leading-[20px]">{{ t('goodsVideoTipFour') }}</div>
                                    <div class="mt-[5px] text-[12px] text-[var(--el-color-primary)] leading-[20px]">{{ t('goodsVideoTipFive') }}</div>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('goodsCategory')" prop="goods_category">
                            <el-cascader v-model="goodsEdit.formData.goods_category" :options="goodsEdit.goodsCategoryOptions" :props="goodsEdit.goodsCategoryProps" clearable filterable @change="goodsEdit.categoryHandleChange" />
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshGoodsCategory(true)">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toGoodsCategoryEvent">{{ t('addGoodsCategory') }}</span>
                            </div>
                        </el-form-item>

                        <el-form-item :label="t('brand')">
                            <el-select v-model="goodsEdit.formData.brand_id" :placeholder="t('brandPlaceholder')" clearable filterable remote reserve-keyword :remote-method="goodsEdit.refreshGoodsBrand">
                                <el-option v-for="item in goodsEdit.brandOptions" :key="item.brand_id" :label="item.brand_name" :value="item.brand_id" />
                            </el-select>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshGoodsBrand(true)">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toGoodsBrandEvent">{{ t('addGoodsBrand') }}</span>
                            </div>
                        </el-form-item>

                        <el-form-item :label="t('label')">
                            <el-checkbox-group v-model="goodsEdit.formData.label_ids">
                                <el-checkbox :label="item.label_id" v-for="(item, index) in goodsEdit.labelOptions" :key="index" >{{ item.label_name }}</el-checkbox>
                            </el-checkbox-group>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshGoodsLabel">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toGoodsLabelEvent">{{ t('addGoodsLabel') }}</span>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('goodsService')">
                            <el-checkbox-group v-model="goodsEdit.formData.service_ids">
                                <el-checkbox :label="item.service_id" v-for="(item, index) in goodsEdit.serviceOptions" :key="index" >{{ item.service_name }}</el-checkbox>
                            </el-checkbox-group>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshGoodsService">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toGoodsServiceEvent">{{ t('addGoodsService') }}</span>
                            </div>
                        </el-form-item>

                        <el-form-item :label="t('supplier')" v-if="goodsEdit.formData.addon_shop_supplier && goodsEdit.formData.addon_shop_supplier.status == 1">
                            <el-select v-model="goodsEdit.formData.supplier_id" :placeholder="t('supplierPlaceholder')" clearable>
                                <el-option v-for="item in goodsEdit.supplierOptions" :key="item.supplier_id" :label="item.supplier_name" :value="item.supplier_id" />
                            </el-select>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshSupplier">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toSupplierEvent">{{ t('addSupplier') }}</span>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('status')">
                            <el-radio-group v-model="goodsEdit.formData.status">
                                <el-radio label="1">{{ t('statusOn') }}</el-radio>
                                <el-radio label="0">{{ t('statusOff') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="t('isGive')">
                            <div>
                                <el-radio-group v-model="goodsEdit.formData.is_gift">
                                    <el-radio :label= "1">{{ t('yes') }}</el-radio>
                                    <el-radio :label= "0">{{ t('no') }}</el-radio>
                                </el-radio-group>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('giftTips') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('unit')" prop="unit">
                            <el-input v-model.trim="goodsEdit.formData.unit" clearable :placeholder="t('unitPlaceholder')" class="input-width" show-word-limit maxlength="6" />
                        </el-form-item>
                        <el-form-item :label="t('virtualSaleNum')" prop="virtual_sale_num">
                            <div>
                                <el-input v-model.trim="goodsEdit.formData.virtual_sale_num" clearable :placeholder="t('virtualSaleNumPlaceholder')" class="input-width" show-word-limit maxlength="8" @keyup="filterNumber($event)" @blur="goodsEdit.formData.virtual_sale_num = $event.target.value">
                                    <template #append>{{ goodsEdit.formData.unit ? goodsEdit.formData.unit : '件' }}</template>
                                </el-input>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('virtualSaleNumDesc') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('setDeliverGoods')">
                            <el-radio-group v-model="goodsEdit.formData.virtual_auto_delivery">
                                <el-radio label="1" :disabled="isDisabledVirtual">{{ t('autoDeliverGoods') }}</el-radio>
                                <el-radio label="0" :disabled="isDisabledVirtual">{{ t('handDeliverGoods') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="t('setTakeGoods')">
                            <div>
                                <el-radio-group v-model="goodsEdit.formData.virtual_receive_type">
                                    <el-radio label="auto" :disabled="isDisabledVirtual">{{ t('autoTakeGoods') }}</el-radio>
                                    <el-radio label="artificial" :disabled="isDisabledVirtual">{{ t('handTakeGoods') }}</el-radio>
                                    <el-radio label="verify" :disabled="isDisabledVirtual">{{ t('verifyTakeGoods') }}</el-radio>
                                </el-radio-group>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]" v-show="goodsEdit.formData.virtual_receive_type == 'verify'">{{ t('virtualSetTips') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('verifyVirtualIndate')" v-if="goodsEdit.formData.virtual_receive_type == 'verify'">
                            <el-radio-group v-model="goodsEdit.formData.virtual_verify_type">
                                <el-radio label="0">{{ t('verifyVirtualIndateOne') }}</el-radio>
                                <el-radio label="1">{{ t('verifyVirtualIndateTwo') }}</el-radio>
                                <el-radio label="2">{{ t('verifyVirtualIndateThree') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <el-form-item :label="t('virtualIndate')" v-if="goodsEdit.formData.virtual_receive_type == 'verify' && goodsEdit.formData.virtual_verify_type == 1" prop="virtual_indate_day">
                            <el-input v-model.trim="goodsEdit.formData.virtual_indate_day" clearable class="input-width" show-word-limit maxlength="5" @keyup="filterNumber($event)">
                                <template #append>{{t('sky')}}</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item :label="t('virtualIndate')" v-if="goodsEdit.formData.virtual_receive_type == 'verify' && goodsEdit.formData.virtual_verify_type == 2" prop="virtual_indate">
                             <div>
                                 <el-date-picker
                                    class="input-width"
                                    value-format="YYYY-MM-DD HH:mm:ss"
                                    v-model="goodsEdit.formData.virtual_indate"
                                    type="datetime"
                                    :disabled-date="disabledPastDates"
                                />
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{t('verifyHint')}}</div>
                             </div>
                        </el-form-item>
                        <el-form-item :label="t('sort')" prop="sort">
                            <el-input v-model.trim="goodsEdit.formData.sort" clearable :placeholder="t('sortPlaceholder')" class="input-width" show-word-limit maxlength="8" @keyup="filterNumber($event)" />
                        </el-form-item>

                    </el-form>

                </el-tab-pane>
                <el-tab-pane :label="t('priceStockTab')" name="price_stock">
                    <el-form :model="goodsEdit.formData" label-width="120px" ref="priceStockFormRef" :rules="goodsEdit.formRules" class="page-form">
                        <el-form-item :label="t('specType')" prop="spec_type">
                            <div>
                                <el-radio-group v-model="goodsEdit.formData.spec_type">
                                    <el-radio label="single" :disabled="goodsEdit.isDisabledPrice()">{{ t('singleSpec') }}</el-radio>
                                    <el-radio label="multi" :disabled="goodsEdit.isDisabledPrice()">{{ t('multiSpec') }}</el-radio>
                                </el-radio-group>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]" v-if="goodsEdit.isDisabledPrice()">{{ t('participateInActiveDisableTips') }}</div>
                            </div>
                        </el-form-item>

                        <template v-if="goodsEdit.formData.spec_type == 'single'">
                            <el-form-item :label="t('price')" prop="price">
                                <el-input v-model.trim="goodsEdit.formData.price" clearable placeholder="0.00" class="input-width" maxlength="8" :disabled="goodsEdit.isDisabledPrice()">
                                    <template #append>{{ t('yuan') }}</template>
                                </el-input>
                            </el-form-item>

                            <el-form-item :label="t('marketPrice')" prop="market_price">
                                <el-input v-model.trim="goodsEdit.formData.market_price" clearable placeholder="0.00" class="input-width" maxlength="8">
                                    <template #append>{{ t('yuan') }}</template>
                                </el-input>
                            </el-form-item>

                            <el-form-item :label="t('costPrice')" prop="cost_price">
                                <el-input v-model.trim="goodsEdit.formData.cost_price" clearable placeholder="0.00" class="input-width" maxlength="8">
                                    <template #append>{{ t('yuan') }}</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item :label="t('goodsStock')" prop="stock">
                                <el-input v-model.trim="goodsEdit.formData.stock" clearable :placeholder="t('goodsStockPlaceholder')" class="input-width" maxlength="8" @keyup="filterNumber($event)" :disabled="goodsEdit.isDisabledPrice()">
                                    <template #append>{{ goodsEdit.formData.unit ? goodsEdit.formData.unit : t('defaultUnit') }}</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item :label="t('skuNo')">
                                <el-input v-model.trim="goodsEdit.formData.sku_no" clearable :placeholder="t('skuNoPlaceholder')" class="input-width" maxlength="50" @keyup="goodsEdit.filterSpecial($event)"  @blur="goodsEdit.goodsVerifyFn($event)" />
                            </el-form-item>
                        </template>

                    </el-form>

                    <!-- 多规格 -->
                    <el-form :model="goodsEdit.goodsSkuData" label-width="120px" ref="skuFormRef" class="page-form">

                        <div class="el-form-item asterisk-left" v-show="goodsEdit.formData.spec_type == 'multi'">
                            <div class="el-form-item__label w-[120px]">{{ t('goodsSku') }}</div>
                            <div class="spec-wrap">
                                <!--规格项/规格值-->
                                <div class="spec-edit-list">

                                    <div class="spec-item" v-for="(item, index) in goodsEdit.goodsSpecFormat" :key="item.id">
                                        <div class="spec-name-wrap">
                                            <el-input v-model.trim="item.spec_name" clearable :placeholder="t('specNamePlaceholder')" class="input-width" maxlength="30" />
                                        </div>
                                        <div class="spec-value-wrap">
                                            <ul ref="specValueRef">
                                                <li :class="'draggable-element' + index" v-for="(specValue, specIndex) in item.values" :key="specValue.id">

                                                    <el-input v-model.trim="specValue.spec_value_name" clearable
                                                              :placeholder="t('specValueNamePlaceholder')" class="input-width"
                                                              :suffix-icon="Rank" maxlength="30"
                                                              @input="goodsEdit.specValueNameInputListener">

                                                    </el-input>
                                                    <el-icon class="icon" :size="20" color="#7b7b7b" @click="goodsEdit.deleteSpecValue(index, specIndex)">
                                                        <CircleCloseFilled />
                                                    </el-icon>

                                                </li>
                                            </ul>
                                            <span class="text-primary text-[14px] add-spec-value" @click="goodsEdit.addSpecValue(index)">{{ t('addSpecValue') }}</span>
                                            <div class="box"></div>
                                        </div>

                                        <el-icon class="del-spec" :size="20" color="#7b7b7b" @click="goodsEdit.deleteSpec(index)">
                                            <CircleCloseFilled />
                                        </el-icon>
                                    </div>

                                </div>

                                <div class="add-spec">
                                    <el-button type="primary" @click="goodsEdit.addSpec">{{ t('addSpec') }}</el-button>
                                </div>

                                <!-- 批量设置 -->
                                <div class="batch-operation-sku" v-show="Object.keys(goodsEdit.goodsSkuData).length">
                                    <label>{{ t('batchOperationSku') }}</label>

<!--                                    <el-select v-model="goodsEdit.batchOperation.spec" class="set-spec-select" :placeholder="t('all')">-->
<!--                                        <el-option :label="t('all')" value="" />-->
<!--                                        <template v-for="(item, key) in goodsEdit.goodsSkuData" :key="key">-->
<!--                                            <el-option v-if="item.spec_name" :label="item.spec_name" :value="key" />-->
<!--                                        </template>-->
<!--                                    </el-select>-->

                                    <el-input v-if="!goodsEdit.isDisabledPrice()" v-model.trim="goodsEdit.batchOperation.price" clearable :placeholder="t('price')" class="set-input" maxlength="8" />
                                    <el-input v-model.trim="goodsEdit.batchOperation.market_price" clearable :placeholder="t('marketPrice')" class="set-input" maxlength="8" />
                                    <el-input v-model.trim="goodsEdit.batchOperation.cost_price" clearable :placeholder="t('costPrice')" class="set-input" maxlength="8" />
                                    <el-input v-if="!goodsEdit.isDisabledPrice()" v-model.trim="goodsEdit.batchOperation.stock" clearable :placeholder="t('stock')" class="set-input" maxlength="8" />
                                    <el-input v-model.trim="goodsEdit.batchOperation.sku_no" clearable maxlength="50" :placeholder="t('skuNo')" class="set-input" @blur="goodsEdit.goodsVerifyFn($event)" />
                                    <el-button type="primary" @click="goodsEdit.saveBatch">{{ t('confirm') }}</el-button>
                                </div>

                                <!--sku列表-->
                                <div class="sku-table" v-show="Object.keys(goodsEdit.goodsSkuData).length">

                                    <div class="el-table--fit el-table--default el-table" style="width: 100%;">
                                        <div class="el-table__inner-wrapper">
                                            <div class="el-table__header-wrapper">
                                                <table class="el-table__header" border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                                    <thead>
                                                        <tr>
                                                            <th class="el-table__cell w-[50px]">
                                                                <div class="cell">
                                                                    <el-checkbox
                                                                        v-model="goodsEdit.formData.skuCheckAll"
                                                                        :indeterminate="goodsEdit.formData.skuIsIndeterminate"
                                                                        @change="goodsEdit.skuHandleCheckAllChange"
                                                                    ></el-checkbox>
                                                                </div>
                                                            </th>
                                                            <template v-for="(item, index) in goodsEdit.goodsSpecFormat" :key="index">
                                                                <th class="el-table__cell" v-if="item.spec_name">
                                                                    <div class="cell">{{ item.spec_name }}</div>
                                                                </th>
                                                            </template>

                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('image') }}</div>
                                                            </th>
                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('price') }}</div>
                                                            </th>
                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('marketPrice') }}</div>
                                                            </th>
                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('costPrice') }}</div>
                                                            </th>
                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('stock') }}</div>
                                                            </th>
                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('skuNo') }}</div>
                                                            </th>
                                                            <th class="el-table__cell">
                                                                <div class="cell">{{ t('defaultSku') }}</div>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                            <el-checkbox-group v-model="goodsEdit.formData.skuCheckedCities" @change="goodsEdit.handleCheckedCitiesChange">
                                                <div class="el-table__body-wrapper text-[14px]">
                                                    <div class="el-scrollbar">
                                                        <div class="el-scrollbar__wrap el-scrollbar__wrap--hidden-default">
                                                            <div class="el-scrollbar__view" style="display: inline-block; vertical-align: middle;">
                                                                <table class="el-table__body" cellspacing="0" cellpadding="0" border="0" style="table-layout: fixed; width: 100%;">
                                                                    <tbody tabindex="-1">
                                                                        <tr class="el-table__row" v-for="(item, key, index) in goodsEdit.goodsSkuData" :key="key">
                                                                            <td class="el-table__cell w-[50px]">
                                                                                <div class="cell">
                                                                                    <el-checkbox :label="key" :key="key">{{ '' }}</el-checkbox>
                                                                                </div>
                                                                            </td>
                                                                            <template v-for="(c, cIndex) in goodsEdit.specData" :key="cIndex">
                                                                                <td class="el-table__cell" :rowspan="c.rowSpan" v-if="c.index == index">
                                                                                    <div class="cell">{{ c.spec_value_name }}</div>
                                                                                </td>
                                                                            </template>

                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <upload-image v-model="item.sku_image" :limit="1" width="50px" height="50px" />
                                                                                </div>
                                                                            </td>

                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <el-form-item :prop="key + '.price'" :rules="goodsEdit.skuPriceRules()" class="sku-form-item-wrap">
                                                                                        <el-input v-model.trim="item.price" clearable placeholder="0.00" maxlength="8" :disabled="goodsEdit.isDisabledPrice()" />
                                                                                    </el-form-item>
                                                                                </div>
                                                                            </td>
                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <el-form-item :prop="key + '.market_price'" :rules="goodsEdit.skuMarketPriceRules()" class="sku-form-item-wrap">
                                                                                        <el-input v-model.trim="item.market_price" clearable placeholder="0.00" maxlength="8" />
                                                                                    </el-form-item>
                                                                                </div>
                                                                            </td>
                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <el-form-item :prop="key + '.cost_price'" :rules="goodsEdit.skuCostPriceRules()" class="sku-form-item-wrap">
                                                                                        <el-input v-model.trim="item.cost_price" clearable placeholder="0.00" maxlength="8" />
                                                                                    </el-form-item>
                                                                                </div>
                                                                            </td>
                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <el-form-item :prop="key + '.stock'" :rules="goodsEdit.skuStockRules()" class="sku-form-item-wrap">
                                                                                        <el-input v-model.trim="item.stock" clearable placeholder="0" @input="goodsEdit.specStockSum" maxlength="8" :disabled="goodsEdit.isDisabledPrice()" />
                                                                                    </el-form-item>
                                                                                </div>
                                                                            </td>
                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <el-input v-model.trim="item.sku_no" clearable maxlength="50" @blur="goodsEdit.goodsVerifyFn($event)"/>
                                                                                </div>
                                                                            </td>
                                                                            <td class="el-table__cell">
                                                                                <div class="cell">
                                                                                    <el-switch v-model="item.is_default" :active-value="1" :inactive-value="0" @change="goodsEdit.specValueIsDefaultChangeListener($event, key)" />
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </el-checkbox-group>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <el-form-item :label="t('memberDiscount')">
                            <div>
                                <el-radio-group v-model="goodsEdit.formData.member_discount">
                                    <el-radio label="">{{ t('nonparticipation') }}</el-radio>
                                    <el-radio label="discount">{{ t('discount') }}</el-radio>
                                    <el-radio label="fixed_price">{{ t('fixedPrice') }}</el-radio>
                                </el-radio-group>
                                <div class="text-[12px] text-[#999] leading-[20px]" v-if="goodsEdit.formData.member_discount == 'discount'">{{t('discountHint')}}</div>
                                <div class="text-[12px] text-[#999] leading-[20px]" v-if="goodsEdit.formData.member_discount == 'fixed_price'">{{t('fixedPriceHint')}}</div>
                            </div>
                        </el-form-item>
                    </el-form>

                    <el-form :model="goodsEdit.formData" label-width="120px" ref="priceStockCommonFormRef" :rules="goodsEdit.formRules" class="page-form">
                        <el-form-item :label="t('isLimit')">
                            <div>
                                <el-switch v-model="goodsEdit.formData.is_limit" :active-value="1" :inactive-value="0" />
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('isLimitTips') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('limitType')" prop="limit_type" v-if="goodsEdit.formData.is_limit == '1'">
                            <div>
                                <el-radio-group v-model="goodsEdit.formData.limit_type">
                                    <el-radio :label="1">{{ t('singleTime') }}</el-radio>
                                    <el-radio :label="2">{{ t('singlePerson') }}</el-radio>
                                </el-radio-group>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('limitTypeTips') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('maxBuy')" prop="max_buy" v-if="goodsEdit.formData.is_limit == '1'">
                            <div>
                                <el-input v-model.trim="goodsEdit.formData.max_buy" clearable :placeholder="t('maxBuyPlaceholder')" class="input-width" maxlength="8" @keyup="filterNumber($event)">
                                    <template #append>{{ goodsEdit.formData.unit ? goodsEdit.formData.unit : t('defaultUnit') }}</template>
                                </el-input>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('maxBuyWarnTips') }}</div>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('minBuy')" prop="min_buy">
                            <div>
                                <el-input v-model.trim="goodsEdit.formData.min_buy" clearable class="input-width" maxlength="8" @keyup="filterNumber($event)">
                                    <template #append>{{ goodsEdit.formData.unit ? goodsEdit.formData.unit : t('defaultUnit') }}</template>
                                </el-input>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('minBuyTips') }}</div>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :label="t('goodsArguments')" name="goods_arguments">
                    <el-form :model="goodsEdit.formData" label-width="120px" ref="goodsArgumentsFormRef" :rules="goodsEdit.formRules" class="page-form">

                        <el-form-item :label="t('goodsArgumentsTemp')">
                            <div>
                                <el-select v-model="goodsEdit.formData.attr_ids" :placeholder="t('goodsArgumentsTempPlaceholder')" clearable multiple @change="goodsEdit.attrChange" @clear="goodsEdit.attrChange">
                                    <el-option v-for="item in goodsEdit.attrOptions" :key="item.attr_id" :label="item.attr_name" :value="item.attr_id" />
                                </el-select>
                                <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{t('goodsArgumentsTempHint')}}</div>
                                <table class="attr-table mt-[10px]">
                                    <colgroup>
                                        <col width="30%">
                                        <col width="40%">
                                        <col width="20%">
                                        <col width="10%">
                                    </colgroup>
                                    <thead class="bg-[#f7f7f7]">
                                        <tr>
                                            <th>{{t('argumentsName')}}</th>
                                            <th>{{t('argumentsValue')}}</th>
                                            <th class="prompt-block">
                                                <div class="flex items-center">
                                                    <span>{{t('sort')}}</span>
                                                    <el-tooltip class="box-item" effect="dark" :content="t('argumentsSortHint')" placement="right">
                                                        <span class="iconfont iconwenhao cursor-pointer ml-[3px] mt-[3px] !text-[14px]"></span>
                                                    </el-tooltip>
                                                </div>
                                            </th>
                                            <th>{{t('operation')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="attr-new">
                                        <template v-if="goodsEdit.attrTableData.length">
                                            <tr class="goods-attr-tr goods-new-attr-tr" v-for="(item,index) in goodsEdit.attrTableData" :key="index">
                                                <td v-if="item.attr_value_id > 0">{{item.attr_value_name}}</td>
                                                <td v-else>
                                                    <el-input  maxlength="30" show-word-limit v-model.trim="item.attr_value_name" clearable/>
                                                </td>
                                                <td>
                                                    <el-input maxlength="30" show-word-limit v-if="item.type == 'text'" v-model.trim="item.select_child_val" clearable/>
                                                    <div v-else-if="item.type == 'radio'">
                                                        <el-radio-group v-model="item.select_child_name" @change="goodsEdit.attrRadioChange(index,$event)">
                                                            <el-radio v-for="(childItem,childIndex) in item.child" :key="childIndex" :label="childItem.id">{{ childItem.name }}</el-radio>
                                                        </el-radio-group>
                                                    </div>
                                                    <div v-else-if="item.type == 'checkbox'">
                                                        <el-checkbox-group v-model="item.select_child_name" @change="goodsEdit.attrCheckboxChange(index,$event)">
                                                            <el-checkbox v-for="(childItem,childIndex) in item.child" :key="childIndex" :label="childItem.id">{{ childItem.name }}</el-checkbox>
                                                        </el-checkbox-group>
                                                    </div>
                                                </td>
                                                <td>
                                                    <el-input maxlength="6" show-word-limit v-model.trim="item.sort" clearable  @keyup="filterNumber($event)"/>
                                                </td>
                                                <td>
                                                    <span class="cursor-pointer text-[var(--el-color-primary)]" @click="goodsEdit.delAttr(index)">{{t('delAttr')}}</span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-else>
                                            <td colspan="4" class="text-center">{{t('noData')}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <el-button class="mt-[15px]" @click="goodsEdit.addAttr">{{t('addGoodsArguments')}}</el-button>
                            </div>
                        </el-form-item>

                    </el-form>
                </el-tab-pane>
                <el-tab-pane :label="t('goodsDesc')" name="detail">
                    <el-form :model="goodsEdit.formData" label-width="120px" ref="detailFormRef" :rules="goodsEdit.formRules" class="page-form">
                        <el-form-item :label="t('goodsDesc')" prop="goods_desc">
                            <editor v-model="goodsEdit.formData.goods_desc" :height="600" class="editor-width" @handleBlur="goodsEdit.handleBlur" />
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
                <el-tab-pane :label="t('advancedSetup')" name="diy">
                    <el-form :model="goodsEdit.formData" label-width="120px" class="page-form">
                        <el-form-item :label="t('diyDetailTemplate')">
                            <el-select v-model="goodsEdit.formData.diy_detail_id" :placeholder="t('diyDetailTemplatePlaceholder')" clearable>
                                <el-option v-for="item in goodsEdit.detailTemplateOptions" :key="item.id" :label="item.page_title" :value="item.id" />
                            </el-select>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshDetailTemplate(true)">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toDetailTemplateEvent">{{t('addDetailTemp')}}</span>
                            </div>
                        </el-form-item>
                        <div class="ml-[120px] mb-[10px] text-[12px] -mt-[10px] text-[#999] leading-[20px]">{{t('detailTempTip')}}</div>
                        <el-form-item :label="t('poster')">
                            <el-select v-model="goodsEdit.formData.poster_id" :placeholder="t('posterPlaceholder')" clearable>
                                <el-option v-for="item in goodsEdit.posterOptions" :key="item.id" :label="item.name" :value="item.id" />
                            </el-select>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshGoodsPoster(true)">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toPosterEvent">{{ t('addGoodsPoster') }}</span>
                            </div>
                        </el-form-item>
                        <div class="-mt-[10px] ml-[120px] mb-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('posterTips') }}</div>
                        <el-form-item :label="t('diyForm')">
                            <el-select v-model="goodsEdit.formData.form_id" :placeholder="t('diyFormPlaceholder')" clearable>
                                <el-option v-for="item in goodsEdit.diyFormOptions" :key="item.form_id" :label="item.page_title" :value="item.form_id" />
                            </el-select>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="goodsEdit.refreshDiyForm(true)">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="goodsEdit.toDiyFormEvent">{{ t('addDiyForm') }}</span>
                            </div>
                        </el-form-item>
                    </el-form>
                </el-tab-pane>
            </el-tabs>

        </el-card>
        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="save()">{{ t('save') }}</el-button>
                <el-button @click="goodsEdit.back()">{{ t('back') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed, onMounted ,nextTick} from 'vue'
import { t } from '@/lang'
import { FormInstance } from 'element-plus'
import { Rank, ArrowLeft } from '@element-plus/icons-vue'
import { filterNumber, timeStampTurnTime } from '@/utils/common'
import {
    addVirtualGoods,
    editVirtualGoods,
    getVirtualGoodsInit
} from '@/addon/shop/api/goods'
import { useGoodsEdit } from './public/js/useGoodsEdit'

const basicFormRef = ref<FormInstance>()
const priceStockFormRef = ref<FormInstance>()
const goodsArgumentsFormRef = ref<FormInstance>()
const detailFormRef = ref<FormInstance>()
const skuFormRef = ref<FormInstance>()
const priceStockCommonFormRef = ref<FormInstance>()

// 拖拽规格值
const specValueRef = ref()

const goodsEdit = useGoodsEdit({
    getFormRef () {
        return {
            basicFormRef: basicFormRef.value,
            priceStockFormRef: priceStockFormRef.value,
            goodsArgumentsFormRef: goodsArgumentsFormRef.value,
            detailFormRef: detailFormRef.value,
            skuFormRef: skuFormRef.value,
            specValueRef: specValueRef.value,
            priceStockCommonFormRef: priceStockCommonFormRef.value,
        }
    },
    addApi: addVirtualGoods,
    editApi: editVirtualGoods,
    formData: {
        goods_type: 'virtual'
    },
    // 追加表单数据
    appendFormData: {
        virtual_auto_delivery: '0',
        virtual_receive_type: 'auto',
        virtual_verify_type: '0',
        virtual_indate: '0',
        virtual_indate_day: '0' // 几日有效的有效期
    },
    // 表单验证规则
    getFormRules (formData: any, regExp: any) {
        return {
            virtual_indate_day: [
                { required: true, message: t('virtualIndatePlaceholder'), trigger: 'blur' },
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.virtual_receive_type == 'verify' && formData.virtual_verify_type == 1 && value < 1) {
                            callback(new Error(t('virtualIndateErrorHint')))
                        }
                        callback()
                    }
                }
            ],
            virtual_indate: [
                { required: true, message: t('virtualIndatePlaceholder'), trigger: 'blur' },
                {
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (formData.virtual_receive_type == 'verify' && formData.virtual_verify_type == 2) {
                            const date = new Date(value)
                            const time = date.getTime()
                            const date1 = new Date()
                            const time1 = date1.getTime()
                            if (time <= time1) { callback(new Error(t('virtualIndateErrorOneHint'))) }
                        }
                        callback()
                    }
                }
            ]
        }
    }
})

const orderGoodsCount = ref(0)

const isDisabledVirtual = computed(() => {
    if (goodsEdit.formData.virtual_receive_type == 'verify' && orderGoodsCount.value > 0) {
        // 虚拟商品，并且设置为店内核销，若存在订单，则禁用，无法编辑
        return true
    }
    return false
})

// 加载初始化数据
getVirtualGoodsInit({
    goods_id: goodsEdit.formData.goods_id
}).then((res) => {
    const data = res.data
    if (data) {
        goodsEdit.handleGoodsInit(data)

        // 虚拟商品 收发货设置
        if (goodsEdit.formData.goods_id && data.goods_info) {
            orderGoodsCount.value = data.goods_info.order_goods_count

            goodsEdit.formData.virtual_auto_delivery = String(data.goods_info.virtual_auto_delivery)
            goodsEdit.formData.virtual_receive_type = data.goods_info.virtual_receive_type
            goodsEdit.formData.virtual_verify_type = String(data.goods_info.virtual_verify_type)
            if (data.goods_info.virtual_receive_type == 'verify' && data.goods_info.virtual_verify_type == 2) {
                goodsEdit.formData.virtual_indate = timeStampTurnTime(data.goods_info.virtual_indate)
            } else if (data.goods_info.virtual_receive_type == 'verify' && data.goods_info.virtual_verify_type == 1) {
                goodsEdit.formData.virtual_indate_day = data.goods_info.virtual_indate
            }
        }
    }
})

// 保存数据
const save = () => {
    goodsEdit.save((data: any) => {
        // 收货设置
        if (data.virtual_receive_type == 'verify' && data.virtual_verify_type == 2) {
            const date = new Date(data.virtual_indate)
            data.virtual_indate = Math.floor(date.getTime() / 1000)
        }
        if (data.virtual_receive_type == 'verify' && data.virtual_verify_type == 1) {
            data.virtual_indate = data.virtual_indate_day
        }
        if (data.virtual_receive_type == 'verify' && data.virtual_verify_type == 0) {
            data.virtual_indate = 0
        }
    })
}

const disabledPastDates = (date:any) => {
    return date.valueOf() < Date.now()
}
onMounted(() => {
    nextTick(() => {
        document.addEventListener("click", (event) => {
            // 检查点击的是否是 Cascader 选项文本
            const labelNode = event.target.closest(".el-cascader-node__label");
            if (labelNode) {
                // 找到最近的 checkbox 并触发点击
                const checkbox = labelNode.parentNode.querySelector(".el-checkbox");
                if (checkbox) {
                    checkbox.click();
                }
            }
        });
    });
});

</script>

<style lang="scss" scoped>
    @import 'public/css/goods_edit.scss';
</style>
<style lang="scss">
    .edui-default .edui-editor{
        z-index: 1 !important;
    }

    .el-cascader__tags.is-validate{
        right: 30px !important;
    }
</style>
