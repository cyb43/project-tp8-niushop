<template>
    <div class="main-container">
        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="id ? t('editFullDiscountBonus') : t('addFullDiscountBonus')" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <!-- 表单 -->
        <el-card class="box-card mt-[15px] !border-none" shadow="never" v-loading="loading">
            <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" scroll-to-error class="page-form">
                <!-- 活动名称： -->
                <el-form-item :label="t('activeName')" prop="manjian_name">
                    <div>
                        <el-input v-model.trim="formData.manjian_name" clearable :placeholder="t('namePlaceholder')" class="input-width" :maxlength="20" show-word-limit />
                    </div>
                </el-form-item>
                <!-- 活动时间 -->
                <el-form-item :label="t('activityTime')" prop="manjian_time">
                    <div class="w-[180px]">
                        <el-date-picker v-model="formData.manjian_time" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"/>
                    </div>
                </el-form-item>
                <!-- 条件类型 -->
                <el-form-item :label="t('conditionType')" prop="condition_type" required>
                    <el-radio-group v-model="formData.condition_type" :disabled="disableSubmit">
                        <el-radio label="over_n_yuan">{{ t('overNyuan') }}</el-radio>
                        <el-radio label="over_n_piece">{{ t('overNgoods') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <!-- 优惠规格 -->
                <el-form-item :label="t('ruleType')" prop="rule_type" required>
                    <el-radio-group v-model="formData.rule_type" :disabled="disableSubmit">
                        <el-radio label="ladder">{{t('ladder')}}</el-radio>
                        <el-radio label="cycle">{{t('cycle')}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <div v-if="formData.rule_type === 'ladder'" class="ml-[120px] mb-[10px] mt-[-10px] text-[12px] text-[#999] leading-[20px]">{{ t('ruleTypeTips') }}</div>
                <div v-if="formData.rule_type === 'cycle'" class="ml-[120px] mb-[10px] mt-[-10px] text-[12px] text-[#999] leading-[20px]">{{ t('ruleTypeTipsTwo') }}</div>

                <!-- 优惠设置 -->
                <el-form-item :label="t('ruleJson')" prop="rule_json" required>
                <!-- 活动层级 -->
                <div class="el-form-item__content">
                    <div v-for="(level, index) in (formData.rule_type === 'cycle' ? [formData.rule_json[0]] : formData.rule_json)"  :key="index" class="activity-level">
                        <div class="level-header">
                            <div class="font-bold text-[#333]">{{t('activityLevel')}}{{ index + 1 }}</div>
                            <span class="cursor-pointer text-primary mr-[10px]" v-if="formData.rule_json.length > 1 && index !== 0 &&!disableSubmit" @click="removeSetting(index)">{{ t('delete') }}</span>
                        </div>
                        <!-- 优惠门槛 -->
                        <div class="level-section flex">
                            <el-form-item class="limit-input" :label="t('discountThreshold')" :prop="`rule_json.${index}.limit`" :rules="[{
                                required: true,
                                trigger: 'blur',
                                validator: (rule: any, value: any, callback: any) => {
                                            if (value === null || value === '') {
                                                callback(t('limitPlaceholder'))
                                            }else if (isNaN(value) || !regExp.digit.test(value) && formData.condition_type=='over_n_yuan') {
                                                callback(t('limitTips'))
                                            }else if (isNaN(value) || !regExp.number.test(value) && formData.condition_type=='over_n_piece') {
                                                callback(t('limitTips'))
                                            }else if (value <= 0) {
                                                callback(t('valueMustBeGreaterThanZero'))
                                            } else {
                                                const previousLimit = formData.rule_json[index - 1]?.limit;
                                                if (previousLimit !== undefined && value <=Number(previousLimit)) {
                                                callback(t('limitTipsThree'));
                                                } else {
                                                const limits = formData.rule_json.map((level, idx) => idx !== index && level.limit);
                                                if (limits.includes(value)) {
                                                    callback(t('limitTipsTwo'));
                                                } else {
                                                    callback();
                                                }
                                                }
                                            }
                                            }
                            }]">
                            <div class="flex items-center ml-[10px]">
                                <span class="mr-[10px]">满</span>
                                    <el-input  clearable class="input-width-short" maxlength="8" v-model="level.limit"  :placeholder="formData.condition_type === 'over_n_yuan' ? '0.00' : '0'" :disabled="disableSubmit" />
                                <span class="ml-[10px]">{{ formData.condition_type === 'over_n_yuan' ? '元' : '件' }}</span>
                            </div>
                        </el-form-item>

                        </div>

                        <!-- 优惠内容 -->
                        <div class="level-section">
                            <el-form-item class="limit-input" :label="t('discountContent')" required>
                                <div  class="vertical-checkbox-group">
                                    <!-- 优惠内容：订单金额优惠 -->
                                    <div class="condition-item">
                                        <el-checkbox  v-model="level.is_discount" :disabled="disableSubmit" label="is_discount">{{t('discountMoney')}}</el-checkbox>
                                        <el-form-item class="limit-input">
                                            <div class="dynamic-content" v-if="level.is_discount">
                                                <el-radio-group  v-model="level.discount_type" :disabled="disableSubmit" v-if="formData.rule_type=='ladder'">
                                                    <el-radio :label="1">{{ t('reduce') }}</el-radio>
                                                    <el-radio :label="2">{{ t('discountRate') }}</el-radio>
                                                </el-radio-group>
                                                <el-form-item :prop="`rule_json.${index}.discount_money`" :rules="[{
                                                    required: true,
                                                    trigger: 'blur',
                                                    validator: (rule: any, value: any, callback: any) => {
                                                                if (value === null || value === '') {
                                                                    callback(t('discountsPlaceholder'))
                                                                }else if (isNaN(value) || !regExp.digit.test(value)) {
                                                                    callback(t('limitTips'))
                                                                }else if (value <= 0) {
                                                                    callback(t('discountMustBeGreaterThanZero'))
                                                                }else if (level.discount_type === 1 && formData.condition_type=='over_n_yuan' && value > Number(level.limit)) {
                                                                    callback(t('discountLimit'))
                                                                }else if (level.discount_type === 2) {
                                                                    if(value >9.9 || value < 0.1 ){
                                                                        callback(t('discountMustBeGreaterThanNine'))
                                                                    }else {
                                                                        callback()
                                                                    }
                                                                }  else {
                                                                    callback();
                                                                }
                                                            }
                                                }]" >
                                                    <div class="flex items-center">
                                                        <span class="mr-[10px]">{{ level.discount_type === 1 ? '减' : '打' }}</span>
                                                            <el-input :disabled="disableSubmit" v-if="level.discount_type === 1"   clearable class="input-width-short" maxlength="8" v-model="level.discount_money"  placeholder="0.00" />
                                                            <el-input :disabled="disableSubmit" v-else  v-model="level.discount_money"   clearable class="input-width-short" maxlength="8" :max="9.9"  placeholder="0.00" />
                                                        <span class="ml-[10px]">{{ level.discount_type === 1 ? '元' : '折' }}</span>
                                                    </div>
                                                </el-form-item>
                                            </div>
                                        </el-form-item>
                                    </div>

                                    <!-- 优惠内容：包邮 -->
                                    <div class="condition-item">
                                        <el-checkbox v-model="level.is_free_shipping" :disabled="disableSubmit" label="is_free_shipping">{{t('freeShipping')}}</el-checkbox>
                                        <div class="mt-[-10px] text-[#999] text-[12px]">{{t('freeTips')}}</div>
                                    </div>

                                    <!-- 优惠内容：送积分 -->
                                    <div class="condition-item">
                                        <el-checkbox :disabled="disableSubmit"  v-model="level.is_give_point" label="is_give_point">{{t('givePoint')}}</el-checkbox>
                                        <el-form-item v-if="level.is_give_point" class="limit-input" :prop="`rule_json.${index}.point`" :rules="[{
                                            required: true,
                                            trigger: 'blur',
                                            validator: (rule: any, value: any, callback: any) => {
                                                        if (value === null || value === '') {
                                                            callback(t('pointPlaceholder'))
                                                        }else if (isNaN(value) || !regExp.number.test(value)) {
                                                            callback(t('limitTips'))
                                                        }else if (value <= 0) {
                                                            callback(t('pointMustBeGreaterThanZero'))
                                                        } else {
                                                            callback();
                                                        }
                                                    }
                                        }]">
                                            <div  class="dynamic-content flex items-center">
                                                <span class="mr-[10px]">送</span>
                                                <el-input :disabled="disableSubmit"  clearable class="input-width-short" maxlength="8" v-model="level.point"  placeholder="0" />
                                                <span class="ml-[10px]">积分</span>
                                            </div>
                                        </el-form-item>
                                    </div>
                                    <!-- 优惠内容：送余额 -->
                                    <div class="condition-item">
                                        <el-checkbox :disabled="disableSubmit" v-model="level.is_give_balance" label="is_give_balance">{{t('giveBalance')}}</el-checkbox>
                                        <el-form-item v-if="level.is_give_balance" class="limit-input" :prop="`rule_json.${index}.balance`" :rules="[{
                                            required: true,
                                            trigger: 'blur',
                                            validator: (rule: any, value: any, callback: any) => {
                                                        if (value === null || value === '') {
                                                            callback(t('balancePlaceholder'))
                                                        }else if (isNaN(value) || !regExp.digit.test(value)) {
                                                            callback(t('limitTips'))
                                                        }else if (value <= 0) {
                                                            callback(t('balanceMustBeGreaterThanZero'))
                                                        } else {
                                                            callback();
                                                        }
                                                    }
                                        }]">
                                            <div class="dynamic-content flex items-center">
                                                <span class="mr-[10px]">送</span>
                                                <el-input :disabled="disableSubmit" clearable class="input-width-short" maxlength="8"  v-model="level.balance"  placeholder="0.00" />
                                                <span class="ml-[10px]">余额</span>
                                            </div>
                                        </el-form-item>
                                    </div>
                                    <!-- 优惠内容：送优惠券 -->
                                    <div class="condition-item">
                                        <el-checkbox label="is_give_coupon" :disabled="disableSubmit" v-model="level.is_give_coupon">{{t('giveCoupon')}}</el-checkbox>
                                        <div class="mt-[-10px] text-[#999] text-[12px]">{{ t('giveCouponTips') }}</div>
                                        <el-form-item :prop="`rule_json.${index}.coupon`" v-if="level.is_give_coupon &&!disableSubmit" class="dynamic-content" :rules="[{ required: true, message: t('couponPlaceholder'), trigger: 'blur' }]">
                                            <coupon-select-popup ref="couponSelectPopupRef" @couponSelect="couponSelect($event, index)" v-model="level.couponIds" :min="1" :max="10" :is-gift="1" />
                                        </el-form-item>
                                        <el-form-item class="mt-[15px]" v-if="level.coupon && level.coupon.length && level.is_give_coupon">
                                            <div class="w-full sku_list">
                                                <el-table :data="level.coupon" size="large" max-height="400" :row-style="{ height: '80px' }">
                                                    <el-table-column prop="title" :label="t('name')" min-width="120" >
                                                        <template #default="{ row }">
                                                            <div>{{ row.title }}</div>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="type_name" :label="t('type')" min-width="120">
                                                        <template #default="{ row }">
                                                            <div>{{ row.type_name }}</div>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="price" :label="t('couponPrice')" min-width="120">
                                                        <template #default="{ row }">
                                                            <div>￥{{ row.price }}</div>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="num" :label="t('giveNum')" min-width="140">
                                                        <template #default="{ row, $index }">
                                                            <el-form-item
                                                            :prop="'rule_json[' + index + '].coupon[' + $index + '].num'"
                                                            :rules="[{ required: true, trigger: 'blur', validator: (rule: any, value: any, callback: any) => {
                                                                    if (value === null || value === '') {
                                                                        callback(t('giveNumPlaceholder'))
                                                                    }else if (isNaN(value) || !regExp.number.test(value)) {
                                                                        callback(t('limitTips'))
                                                                    }else if (value <= 0) {
                                                                        callback(t('giveNumMustBeGreaterThanZero'))
                                                                    }else {
                                                                        callback();
                                                                    }
                                                                } }]">
                                                                <el-input :disabled="disableSubmit" v-model.number ="row.num" class="w-[70px]" clearable maxlength="5"  />
                                                            </el-form-item>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column :label="t('operation')" align="right" min-width="160">
                                                        <template #default="{ row, $index }">
                                                            <el-button type="primary" link @click="deleteCouponEvents(row, index)" :disabled="disableSubmit">{{ t("delete") }}</el-button>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>
                                        </el-form-item>
                                    </div>

                                    <!-- 优惠内容：送赠品 -->
                                    <div class="condition-item">
                                        <el-checkbox :disabled="disableSubmit" label="is_give_goods" v-model="level.is_give_goods">{{ t("giveGoods") }}</el-checkbox>
                                        <div class="mt-[-10px] text-[#999] text-[12px]">{{ t('giveGoodsTips') }}</div>
                                        <el-form-item :prop="`rule_json.${index}.goods`" v-if="level.is_give_goods &&!disableSubmit" class="dynamic-content" :rules="[{ required: true, message: t('goodsJsonEmpty'), trigger: 'blur' }]">
                                            <goods-select-popup ref="goodsSelectPopupRef" v-model="level.goods_ids" mode="sku" @goodsSelect="goodsSelects($event, index)" :min="1" :max="10" :is-gift="1" />
                                        </el-form-item>
                                        <el-form-item class="mt-[15px]" v-if="level.goods && level.goods.length && level.is_give_goods ">
                                            <div class="w-full sku_list">
                                                <el-table :data="level.goods" size="large" max-height="400">
                                                    <el-table-column prop="goods_id" :label="t('goodsSelectPopupGoodsInfo')" min-width="300">
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
                                                                    <span :title="row.sku_name" class="multi-hidden">{{row.sku_name ? row.goods_name + " " + row.sku_name: row.goods_name}}</span>
                                                                    <span class="text-primary text-[12px]">{{row.goods_type_name}}</span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </el-table-column>

                                                    <el-table-column prop="price" :label="t('goodsSelectPopupPrice')" min-width="120">
                                                        <template #default="{ row }">
                                                            <div>￥{{ row.price }}</div>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="stock" :label="t('stock')" min-width="120">
                                                        <template #default="{ row }">
                                                            <div>{{ row.stock }}</div>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column prop="num" :label="t('giveNum')" min-width="180">
                                                        <template #default="{ row, $index }">
                                                            <el-form-item
                                                            :prop="'rule_json[' + index + '].goods[' + $index + '].num'"
                                                            :rules="[{ required: true, trigger: 'blur', validator: (rule: any, value: any, callback: any) => {
                                                                    if (value === null || value === '') {
                                                                        callback(t('giveNumPlaceholder'))
                                                                    }else if (isNaN(value) || !regExp.number.test(value)) {
                                                                        callback(t('limitTips'))
                                                                    }else if (value <= 0) {
                                                                        callback(t('giveNumMustBeGreaterThanZero'))
                                                                    }else if (value > row.stock) {
                                                                        callback(t('giveNumMustBeGreaterThanStock'))
                                                                    }else {
                                                                        callback();
                                                                    }
                                                                } }]">
                                                                <el-input :disabled="disableSubmit" v-model.number ="row.num" class="w-[70px]" clearable />
                                                            </el-form-item>
                                                        </template>
                                                    </el-table-column>
                                                    <el-table-column :label="t('operation')" align="right" min-width="160">
                                                        <template #default="{ row, $index }">
                                                            <el-button type="primary" link @click="deleteGoodsEvents(row, index)" :disabled="disableSubmit">{{ t("delete") }}</el-button>
                                                        </template>
                                                    </el-table-column>
                                                </el-table>
                                            </div>

                                        </el-form-item>
                                    </div>
                                </div>
                            </el-form-item>
                        </div>
                    </div>
                    <!-- 添加活动层级 -->
                    <el-button :disabled="disableSubmit" v-if="formData.rule_type=='ladder' && formData.rule_json.length < 5"  type="primary" plain @click="addSetting" class="mt-[20px]">{{ t('addActivityLevel') }}</el-button>
                </div>
                </el-form-item>
                <!-- 活动对象 -->
                <el-form-item :label="t('joinMemberType')" prop="join_member_type" required>
                    <el-radio-group v-model="formData.join_member_type">
                        <el-radio label="all_member">{{t('allMember')}}</el-radio>
                        <el-radio label="selected_member_level">{{t('selectedMemberLevel')}}</el-radio>
                        <el-radio label="selected_member_label">{{t('selectedMemberLabel')}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <!-- 会员标签 -->
                <el-form-item :label="t('memberLabel')" prop="label_ids" v-if="formData.join_member_type=='selected_member_label'" :rules="[{ required: true, message: t('labelTips'), trigger: 'blur' }]">
                    <el-select v-model="formData.label_ids" clearable multiple :placeholder="t('memberLabelPlaceholder')" class="input-width">
                        <el-option :label="item['label_name']" :value="item['label_id']" v-for="(item, index) in labelSelectData" :key="index" />
                    </el-select>
                </el-form-item>
                <!-- 会员等级 -->
                <el-form-item :label="t('memberLevel')" prop="level_ids" v-if="formData.join_member_type=='selected_member_level'" :rules="[{ required: true, message: t('levelTips'), trigger: 'blur' }]">
                    <el-select v-model="formData.level_ids" clearable multiple :placeholder="t('memberLevelPlaceholder')" class="input-width">
                        <el-option :label="item['level_name']" :value="item['level_id']" v-for="(item, index) in levelSelectData" :key="index" />
                    </el-select>
                </el-form-item>
                <!-- 参与商品 -->
                <el-form-item :label="t('goodsType')" prop="goods_type" required>
                    <el-radio-group v-model="formData.goods_type" :disabled="disableSubmit">
                        <el-radio label="all_goods">{{ t('allGoods') }}</el-radio>
                        <el-radio label="selected_goods">{{ t('selectedGoods') }}</el-radio>
                        <el-radio label="selected_goods_not">{{ t('selectedGoodsNot') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <!-- 选择商品 -->
                <el-form-item :label="t('selectGoods')" prop="goods_data" v-if="formData.goods_type != 'all_goods' ">
                    <goods-select-popup ref="goodsSelectPopupRef" v-model="formData.goods_ids" mode="sku" @goodsSelect="goodsSelect" :min="1" :max="99" />
                </el-form-item>
                <el-form-item class="mt-[15px]" v-if="formData.goods_data && formData.goods_data.length && formData.goods_type != 'all_goods'">
                    <el-table :data="formData.goods_data" size="large" max-height="400" >
                        <el-table-column prop="goods_id" :label="t('goodsSelectPopupGoodsInfo')" min-width="300">
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
                                    <span :title="row.sku_name" class="multi-hidden">{{row.sku_name ? row.goods_name + " " + row.sku_name: row.goods_name}}</span>
                                    <span class="text-primary text-[12px]">{{row.goods_type_name}}</span>
                                </div>
                                 <!-- 错误信息提示 -->
                                <div v-if="row.error_msg" class="text-red-500 text-sm ml-[10px] mt-5">{{ row.error_msg }}</div>
                                  <!-- 下架信息提示 -->
                                <div v-if="row.status==0" class="text-red-500 text-sm ml-[10px] mt-5">{{ t('goodsOffTips') }}</div>

                            </div>
                        </template>
                        </el-table-column>

                        <el-table-column prop="price" :label="t('goodsSelectPopupPrice')" min-width="120">
                        <template #default="{ row }">
                            <div>￥{{ row.price }}</div>
                        </template>
                        </el-table-column>
                        <el-table-column :label="t('operation')" align="right" min-width="160">
                            <template #default="{ row, $index }">
                                <el-button type="primary" link @click="deleteGoodsEvent(row, $index)">{{ t("delete") }}</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-form-item>
                <!-- 备注 -->
                <el-form-item :label="t('remark')" prop="remark">
                    <el-input v-model="formData.remark" :placeholder="t('rankRemarkPlaceholder')" type="textarea" maxlength="500" show-word-limit rows="5" class="!w-[400px]" clearable />
                </el-form-item>

            </el-form>
        </el-card>

        <!-- 提交按钮 -->
        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave()">{{ t('save') }}</el-button>
                <el-button @click="back()">{{ t('cancel') }}</el-button>
            </div>
        </div>
    </div>
</template>
<script lang="ts" setup>
import {ref, computed, reactive} from 'vue'
import {t} from '@/lang'
import {useRoute, useRouter} from 'vue-router'
import {editManjian,addManjian,getManjianInfo,goodsCheck} from "@/addon/shop/api/marketing";
import {FormInstance, ElMessage} from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import {getMemberLabelAll,getMemberLevelAll } from '@/app/api/member'
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'
import couponSelectPopup from '@/addon/shop/views/goods/components/coupon-select-popup.vue'
import {deepClone, img} from '@/utils/common'

const router = useRouter()
const route = useRoute()
const id = route.query.id;
const start_time = new Date()
const end_time = new Date(new Date().setMinutes(new Date().getMinutes() + 10));
const loading = ref(false)
const goodsSelectPopupRef: any = ref(null)
const initialFormData = reactive({
    manjian_name: '', // 名称
    start_time: '',
    goods_type: 'all_goods',
    condition_type: 'over_n_yuan',
    rule_type: 'ladder',
    join_member_type: 'all_member',
    end_time: '',
    goods_data: [],
    goods_ids: [],
    rule_json: [
        {
            limit: "", // 优惠门槛
            is_discount: false,          // 是否订单金额优惠
            is_free_shipping: false,     // 是否包邮
            is_give_point: false,        // 是否送积分
            is_give_coupon: false,       // 是否送优惠券
            is_give_goods: false,        // 是否送赠品
            is_give_balance: false,        // 是否送余额
            discount_type: 1,
            discount_money: '', // 优惠金额
            point: "", // 积分
            balance: "", // 余额
            goods_ids: [],
            goods: [],
            couponIds: [],
            coupon: [],
        },
    ],
    manjian_time: [start_time, end_time]
})

const formData: Record<string, any> = reactive({...initialFormData})
const formRef = ref<FormInstance>()
const formRules = computed(() => {
    const rules = {
        manjian_name: [
            { required: true, message: t('namePlaceholder'), trigger: 'blur' },
        ],
        manjian_time: [
            {required: true, validator: receiveTime, trigger: 'change'}
        ],
        goods_data: [
            {
                required: true,
                trigger: 'blur',
                validator: (rule: any, value: any, callback: any) => {
                    if (formData.goods_type != 'all_goods') {
                        if (formData.goods_data.length == 0) {
                            callback(new Error(t('goodsJsonEmpty')))
                        } else {
                            callback()
                        }
                    }
                }
            }
        ],
    };

    return rules;
});

const receiveTime = (rule: any, value: any, callback: any) => {
    if (!formData.manjian_time || (formData.manjian_time && !formData.manjian_time[0] && !formData.manjian_time[1])) {
        callback(new Error(t('selectActivityTime')))
    } else if (!formData.manjian_time[0]) {
        callback(new Error(t('selectActivityStartTime')))
    } else if (!formData.manjian_time[1]) {
        callback(new Error(t('selectActivityEndTime')))
    } else if (formData.manjian_time[1] <= formData.manjian_time[0]) {
        callback(new Error(t('selectActivityTimeTips')))
    }

    callback()
}

// 添加优惠设置
const addSetting = () => {
    if (formData.rule_json.length >= 5) {
    ElMessage.error(t("addLevelLimit"));
    return;
    }
    formData.rule_json.push({
        limit: "", // 优惠门槛
        is_discount: false,          // 是否订单金额优惠
        is_free_shipping: false,     // 是否包邮
        is_give_point: false,        // 是否送积分
        is_give_coupon: false,       // 是否送优惠券
        is_give_goods: false,        // 是否送赠品
        is_give_balance: false,        // 是否送余额
        discount_type:1,
        discount_money: '', // 优惠金额
        point: "", // 积分
        balance: "", // 余额
        goods_ids: [],
        goods: [],
        couponIds: [],
        coupon: [],
    });
};

// 正则表达式
const regExp: any = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}

// 删除优惠设置
const removeSetting = (index: number) => {
    formData.rule_json.splice(index, 1);
};
const levelSelectData = ref([])
const labelSelectData = ref([])
const disableSubmit = ref(false)
const getInfo = () => {

    // 获取全部标签
    getMemberLabelAll().then(({ data }) => {
        labelSelectData.value = data
    })

    getMemberLevelAll().then(({ data }) => {
        levelSelectData.value = data
    })

    if(id){
        disableSubmit.value = true
        loading.value = true
        const data = {
            manjian_id: id
        }
        getManjianInfo(data).then((res)=>{
            const data = res.data.manjian_info;
            formData.goods_data =res.data.manjian_goods;
            formData.goods_ids.splice(0, formData.goods_ids.length);
            formData.goods_data.forEach((item: any) => {
                formData.goods_ids.push( item.sku_id)
             })
            Object.assign(formData, data);
            formData.manjian_time = [data.start_time, data.end_time]
            loading.value = false;
        })
    }

}

getInfo()

// 删除商品
const deleteGoodsEvent = (row: any, index: any) => {
    formData.goods_data.splice(index, 1);
    formData.goods_ids.splice(formData.goods_ids.indexOf(row.sku_id), 1);
};
// 删除赠品
const deleteGoodsEvents = (row: any, levelIndex: number) => {
    const rule = formData.rule_json[levelIndex];
    const goodsIndex = rule.goods.findIndex((el: any) => el.sku_id === row.sku_id);
    if (goodsIndex !== -1) {
        rule.goods.splice(goodsIndex, 1);
    }

    const goodsIdIndex = rule.goods_ids.indexOf(row.sku_id);
    if (goodsIdIndex !== -1) {
        rule.goods_ids.splice(goodsIdIndex, 1);
    }
};
// 删除优惠券
const deleteCouponEvents = (row: any, levelIndex: number) => {
    const rule = formData.rule_json[levelIndex];
    const couponsIndex = rule.coupon.findIndex((el: any) => el.coupon_id === row.coupon_id);
    if (couponsIndex !== -1) {
        rule.coupon.splice(couponsIndex, 1);
    }

    const couponsIdIndex = rule.couponIds.indexOf(row.coupon_id);
    if (couponsIndex !== -1) {
        rule.couponIds.splice(couponsIdIndex, 1);
    }
};

//选择商品
const goodsSelect = (value: any) => {
    let arr = [];
    for (let key in value) {
        let goods_sku: any = value[key];
        let sku: any = {
            goods_id: goods_sku.goods_id,
            sku_id: goods_sku.sku_id,
            goods_type_name: goods_sku.goods_type_name,
            price: goods_sku.price,
            sku_image: goods_sku.sku_image,
            goods_name: goods_sku.goods_name,
            sku_name: goods_sku.sku_name,
            stock: goods_sku.stock,
        };

        if (formData.goods_data.length) {
            formData.goods_data.forEach((el: any) => {
                if (el.sku_id == sku.sku_id) {
                    sku = Object.assign(sku, el)
                }
            })

        }
        arr.push(deepClone(sku))
    }

    formData.goods_data = arr;

    let data = {
        manjian_id: id ? Number(id) : "",
        goods_ids: formData.goods_data.map((el: any) => el.goods_id),
        goods_type: formData.goods_type,
        start_time: formData.manjian_time[0] ? formData.manjian_time[0] : start_time,
        end_time: formData.manjian_time[1] ? formData.manjian_time[1] : end_time
    }
    goodsCheck(data).then((res: any) => {
        if (res.data.code == -1) {
            ElMessage({
                message: t("goodOnlyOne"),
                type: "error",
            });
        }
        const errorList = res.data.data;
        formData.goods_data.forEach((item: any) => {
            const error = errorList.find((err) => err.goods_id === item.goods_id);
            if (error) {
                item.error_msg = error.error_msg;
            }
        })

    })
};

//选择赠品
const goodsSelects = (value: any, levelIndex: number) => {
    const rule = formData.rule_json[levelIndex];
    let arr = [];
    for (let key in value) {
        let goods_sku: any = value[key];
        let sku: any = {
            goods_id: goods_sku.goods_id,
            sku_id: goods_sku.sku_id,
            goods_type_name: goods_sku.goods_type_name,
            price: goods_sku.price,
            sku_image: goods_sku.sku_image,
            goods_name: goods_sku.goods_name,
            sku_name: goods_sku.sku_name,
            stock: goods_sku.stock,
            num: 1
        };
        if (rule.goods.length) {
            rule.goods.forEach((el: any) => {
                if (el.sku_id == sku.sku_id) {
                    sku = Object.assign(sku, el)
                }
            })

        }
        arr.push(deepClone(sku))
    }

    rule.goods = arr;
};

// 选择优惠券
const couponSelect = (selectedCoupons: any, levelIndex: number) => {
    const rule = formData.rule_json[levelIndex];
    let arr = [];
    for (let key in selectedCoupons) {
        let coupons: any = selectedCoupons[key];
        let coupon: any = {
            price: coupons.price,
            title: coupons.title,
            type_name: coupons.type_name,
            coupon_id: coupons.id,
            num:1
        };

        if (rule.coupon.length) {
            rule.coupon.forEach((el: any) => {
            if (el.coupon_id == coupon.coupon_id) {
                coupon = Object.assign(coupon, el)
            }
        })
        }
        arr.push(deepClone(coupon))
    }
    rule.coupon = arr;

}

const onSave = async () => {
    // 防止失败丢失
    const originalRuleJson = JSON.parse(JSON.stringify(formData.rule_json));
    const originalGoodsData = JSON.parse(JSON.stringify(formData.goods_data));

    // 校验每个层级的优惠内容
    const hasInvalidRule = formData.rule_json.some((level: any, index: number) => {
        const noDiscount =
            !level.is_discount &&
            !level.is_free_shipping &&
            !level.is_give_point &&
            !level.is_give_coupon &&
            !level.is_give_goods &&
            !level.is_give_balance;

        if (noDiscount) {
            ElMessage.error(t(`第 ${ index + 1 } 层级的优惠内容至少选择一项`));
            return true;
        }
        return false;
    });
    if (hasInvalidRule) {
        return;
    }
    await formRef.value?.validate(async(valid) => {
        if (valid) {
            loading.value = true
            formData.start_time = formData.manjian_time[0];
            formData.end_time = formData.manjian_time[1];
            formData.rule_json.forEach((rule: any) => {
                if (rule.goods) {
                    rule.goods = rule.goods.map((item: any) => {
                        return {
                            ...item,
                            num: item.num,
                        };
                    });
                }
                if (rule.coupon) {
                    rule.coupon = rule.coupon.map((item: any) => {
                        return {
                            ...item,
                            num: item.num,
                        };
                    });
                }
            });
            if (id) {
                formData.id = id;
                editManjian(formData).then((res) => {
                    if (res.data.code == 1) {
                        loading.value = false;
                        back()
                        ElMessage({
                            message: t("updateSuccess"),
                            type: "success",
                        });
                    } else {
                        loading.value = false;
                        ElMessage({
                            message: t("goodOnlyOne"),
                            type: "error",
                        });
                    }
                }).catch(() => {
                    formData.rule_json = originalRuleJson;
                    formData.goods_data = originalGoodsData;
                    loading.value = false;
                });
            } else {
                addManjian(formData).then((res) => {
                    if (res.data.code == 1) {
                        loading.value = false;
                        back()
                        ElMessage({
                            message: t("addSuccess"),
                            type: "success",
                        });
                    } else {
                        loading.value = false;
                        ElMessage({
                            message: t("goodOnlyOne"),
                            type: "error",
                        });
                    }
                }).catch(() => {
                    formData.rule_json = originalRuleJson;
                    formData.goods_data = originalGoodsData;
                    loading.value = false;
                });
            }
        }else{
            var iserror = document.getElementsByClassName('is-error')[0]
            iserror.scrollIntoView()
        }
    });
};

const back = () => {
    router.push('/shop/marketing/manjian/list')
}

</script>

<style lang="scss" scoped>
.activity-level{
    width: 100%;
}
.input-width-short{
  width: 120px;
}
.level-header{
    background-color: #f5f5f5;
    display: flex;
    padding-left: 10px;
    justify-content: space-between;
}
.level-section{
    margin-bottom: 10px;
    margin-top: 10px;
}
.vertical-checkbox-group {
    margin-left: 10px;
}
.condition-item:first-child{
    margin-top: 3px;
}
.condition-item{
    margin-top: 8px;
}
.dynamic-content{
    margin: 5px 20px;
}
.sku_list :deep(.cell) {
    // min-height: 35px !important;
    overflow: initial !important;
}
.sku_list :deep(.el-form-item__error ) {
    padding-top: 6px;
    left: 2px;
}
</style>
