<template>
	<view :style="warpCss" class="form-item-frame">
		<view class="relative base-layout-one">
			<view class="p-[10rpx] flex items-center">
				<view class="w-[27%] mr-[10rpx] flex items-center">
					<text class="text-overflow-ellipsis"
						:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
					<text class="text-[#ec0003]">{{ diyComponent.field.required ? '*' : '' }}</text>
				</view>
			</view>
			<view>
<!--				<uni-table border>
					<uni-tr v-for="(column, columnIndex) in diyComponent.columnList" :key="columnIndex">
						<uni-th>{{ column.name }}</uni-th>
						<uni-td>
							<view class="layout-two-content"
								v-if="column.type === 'text' || column.type === 'number' || column.type === 'idcard'">
								<input :type="column.type" class="layout-one-content"
									placeholderClass="layout-one-input-placeholder"
									:placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
									:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
									v-model="column.value" :disabled="isDisabled" />
							</view>
							<view class="layout-two-content"
								v-else-if="column.type === 'mobile'">
								<input type="tel" class="layout-one-content"
									placeholderClass="layout-one-input-placeholder"
									:placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
									:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
									v-model="column.value" :disabled="isDisabled" />
							</view>
							<view v-else-if="column.type === 'gender'">
								<view @click="openSex(column)"
									class="px-[16rpx] box-border h-[80rpx] flex items-center justify-between border-solid border-[2rpx] border-[#e6e6e6] rounded-[10rpx] w-[100%]">
									<view>
										<text class="mr-[10rpx] text-[28rpx]"
											:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">
											{{ getSexName(column) }}
										</text>
									</view>
									<text class="nc-iconfont nc-icon-xiaV6xx pull-down-arrow text-[#666]"
										:class="{'selected': selectShow[columnIndex]}"
										:style="{'font-size': (diyComponent.fontSize * 2 + 2) + 'rpx !important'}"></text>
								</view>
							</view>
							<view v-else-if="column.type === 'date'">
								<view class="layout-one-content" @click="openCalendar(column)">
									<view
										class="nc-iconfont nc-icon-a-riliV6xx-36 !text-[32rpx] text-[#999] mr-[16rpx]">
									</view>
									<view class="flex-1 text-overflow-ellipsis flex"
										:class="{'!text-[#999]' : !diyComponent.field.value.date && !diyComponent.defaultControl}"
										:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' }">
										{{ startDate }}
									</view>
								</view>
							</view>
							<view v-else-if="column.type === 'address'">
								<view class="flex layout-one-content justify-between items-center">
									<input type="text" class="flex-1" :placeholder="inputPlaceholder(column)" 
								       placeholderClass="layout-one-input-placeholder"
								       :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
								       :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
								       v-model="column.value" :disabled="isDisabled" @click="selectArea(column)" />
									   <view class="text-[var(&#45;&#45;primary-color)]" v-if="column.value" @click="column.value=''">
									   	清除
									   </view>
								</view>	
								<textarea v-if="column.addressFormat=='province/city/district/address'" type="textarea" class="layout-one-content mt-2 w-full" placeholderClass="layout-one-input-placeholder" :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }" placeholder="详细地址(如小区门牌号)" :disabled="isDisabled"></textarea>
							</view>

							&lt;!&ndash; 仅当字段类型为 'radio' 时才显示 &ndash;&gt;
							<view class="layout-two-content" v-else-if="column.type === 'radio'">
								<view @click="openPicker(columnIndex)"
									class="px-[16rpx] box-border h-[80rpx] flex items-center justify-between border-solid border-[2rpx] border-[#e6e6e6] rounded-[10rpx] w-[100%]">
									<view v-if="column.value">
										<text class="mr-[10rpx] text-[28rpx]"
											:style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">
											{{ getSelectRadioName(columnIndex) }}
										</text>
									</view>
									<text v-else class="text-[28rpx] text-[#999]"
										:style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}">请选择</text>
									<text class="nc-iconfont nc-icon-xiaV6xx pull-down-arrow text-[#666]"
										:class="{'selected': selectShow[columnIndex]}"
										:style="{'font-size': (diyComponent.fontSize * 2 + 2) + 'rpx !important'}"></text>
								</view>
							</view>
						</uni-td>
					</uni-tr>
				</uni-table>-->
			</view>
		</view>

		<!-- 下拉弹窗 -->
		<u-popup v-for="(column, columnIndex) in diyComponent.columnList" :key="'popup-' + columnIndex"
			:show="selectShow[columnIndex]" mode="bottom" @close="selectShow[columnIndex] = false">
			<view class="p-[15rpx]">
				<scroll-view scroll-y="true" class="max-h-[450rpx] px-[14rpx] box-border">
					<u-radio-group v-model="column.value" placement="column" @change="groupChange(columnIndex)"
						iconPlacement="right">
						<view class="border-solid border-[0] border-b-[2rpx] border-[#e6e6e6] py-[20rpx]"
							v-for="(option, optionIndex) in column.options" :key="optionIndex"
							@click.stop="pullDownConfirmFn(columnIndex, option)">
							<u-radio activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'"
								:labelColor="diyComponent.textColor" :style="{'width': '100%'}" :label="option.label"
								:name="option.id">
							</u-radio>
						</view>
					</u-radio-group>
				</scroll-view>
			</view>
		</u-popup>

		<!-- 修改性别 -->
		<u-action-sheet :actions="sexList" :show="sexSheetShow" :closeOnClickOverlay="true"
			:safeAreaInsetBottom="true" @close="sexSheetShow = false" @select="updateSex"></u-action-sheet>
		<area-select ref="areaRef" @complete="areaSelectComplete" :area-id="formData.district_id" />
		<view class="calendar-wrap">
			<u-calendar :show="calendarShow" mode="single" @confirm="confirm" @close="calendarShow=false"
				closeOnClickOverlay="true" :formatter="formatter" confirmDisabledText="禁止选择"
				color="var(--primary-color)" ref="calendar" :maxDate="maxDate"></u-calendar>
			<u-datetime-picker :show="show" mode="datetime" @cancel="show=false"
				closeOnClickOverlay="true" @confirm="calendarConfirm" @close="show=false"
				:minDate="minDate"></u-datetime-picker>
		</view>
	</view>
</template>

<script setup lang="ts">
	// 表单 表格组件
	import { ref, computed, watch, onMounted } from 'vue';
	import useDiyStore from '@/app/stores/diy';
	import { img, timeStampTurnTime, timeTurnTimeStamp } from '@/utils/common';
	// import uniTable from '@/addon/o2o/components/uni-table/components/uni-table/uni-table.vue'
	// import uniTr from '@/addon/o2o/components/uni-table/components/uni-tr/uni-tr.vue'
	// import uniTh from '@/addon/o2o/components/uni-table/components/uni-th/uni-th.vue'
	// import uniTd from '@/addon/o2o/components/uni-table/components/uni-td/uni-td.vue'
	const props = defineProps(['component', 'index', 'global']);
	const diyStore = useDiyStore();
	const formData: any = ref({
	    id: 0,
	    name: '',
	    mobile: '',
	    province_id: 0,
	    city_id: 0,
	    district_id: 0,
	    lat: '',
	    lng: '',
	    address: '',
	    address_name: '',
	    full_address: '',
	    is_default: 0,
	    area: ''
	})

	const selectShow = ref<Array<boolean>>(Array(10).fill(false)); // 用于控制多个弹窗的开关状态
	const diyComponent = computed(() => {
		if (diyStore.mode == 'decorate') {
			return diyStore.value[props.index];
		} else {
			return props.component;
		}
	})

	const isDisabled = computed(() => {
		return diyStore.mode === 'decorate';
	});

	// 打开下拉选择框
	const openPicker = (columnIndex : number) => {
		if (isDisabled.value) return;
		selectShow.value[columnIndex] = true;
	};

	// 获取选中的单选项名称
	const getSelectRadioName = (columnIndex : number) => {
		const column = diyComponent.value.columnList[columnIndex];
		if (!column || !column.value) return '';
		const selectedOption = column.options.find(opt => opt.id === column.value);
		return selectedOption ? selectedOption.label : '';
	};

	// 确认选择
	const pullDownConfirmFn = (columnIndex : number, option : any) => {
		diyComponent.value.columnList[columnIndex].value = option.id;
		selectShow.value[columnIndex] = false;
	};

	// 监听表单值变化
	const groupChange = (columnIndex : number) => {
		const column = diyComponent.value.columnList[columnIndex];
		if (!column || !column.value) return;
		selectShow.value[columnIndex] = false;
	};
	const currentColumn = ref(null); // 记录当前点击的列
	/**
	   * 性别
	   */
	const sexSheetShow = ref(false); // 控制性别选择框显示


	// 性别选项
	const sexList = computed(() => [
		{ name: '男', value: 1 },
		{ name: '女', value: 2 }
	]);

	// 获取当前列的性别名称
	const getSexName = (column : any) => {
		if (!column || !column.value) return '请选择';
		return column.value === 1 ? '男' : column.value === 2 ? '女' : '请选择';
	};

	// 打开性别选择框，并记录当前列
	const openSex = (column : any) => {
		if (isDisabled.value) return;
		currentColumn.value = column;
		sexSheetShow.value = true;
	};

	// 选择性别并更新
	const updateSex = (e : any) => {
		if (currentColumn.value) {
			currentColumn.value.value = e.value; // 更新当前列的 value
		}
		sexSheetShow.value = false; // 关闭弹窗
	};

	//日期

	let currentDate = new Date();
	currentDate.setFullYear(currentDate.getFullYear() + 1);
	const maxDate = ref(currentDate.getTime());

	let currentDateTime = new Date();
	const minDate = ref(currentDateTime.getTime());
	const calendarShow = ref(false);
	const show = ref(false);

	// 日期
	const startDate = computed(() => {
		if (!currentColumn.value || !currentColumn.value.value) {
			return diyComponent.value.placeholder || '请选择日期';
		}
		return getDateFn(currentColumn.value.value, currentColumn.value.dateFormat);
	});

	const openCalendar = (column : any) => {
		if (diyStore.mode === 'decorate') return;
		currentColumn.value = column;

		if (column.dateFormat == 'YYYY-MM-DD HH:mm') {
			show.value = true;
		} else {
			calendarShow.value = true;
		}
	};

	const confirm = (e : any) => {
		if (currentColumn.value) {
			currentColumn.value.value = e[0]; // 赋值给 column
		}
		
		calendarShow.value = false;
	};

	const calendarConfirm = (e : any) => {
		if (currentColumn.value) {
			currentColumn.value.value = e.value; // 赋值给 column
		}
		show.value = false;
	};


	const formatter = (day) => {
		const time = timeStampTurnTime(Date.parse(day.date) / 1000, "year_month_day");
		return day
	}

	const getDateFn = (data : any = '', type : any) => {
		console.log(data,type)
		let timestamp = type == 'YYYY-MM-DD HH:mm' ? data : timeTurnTimeStamp(data);
		let date = timestamp > 9999999999 ? new Date(timestamp) : new Date(timestamp * 1000);
		let year = date.getFullYear();
		let month = String(date.getMonth() + 1).padStart(2, '0');
		let day = String(date.getDate()).padStart(2, '0');
		console.log(year)
		const hours = String(date.getHours()).padStart(2, '0');
		const minutes = String(date.getMinutes()).padStart(2, '0');
		
		let str = '';
		if (type == 'YYYY年M月D日') {
			str = `${year}年${month}月${day}日`;
		} else if (type == 'YYYY-MM-DD') {
			str = `${year}-${month}-${day}`;
		} else if (type == 'YYYY/MM/DD') {
			str = `${year}/${month}/${day}`;
		} else if (type == 'YYYY-MM-DD HH:mm') {
			str = `${year}-${month}-${day} ${hours}:${minutes}`;
		}
		return str;
	}

	const areaRef = ref()
	const isSelectAddress = ref(false)
	const selectArea = (column : any)  => {
		currentColumn.value = column;
	    isSelectAddress.value = true
	    areaRef.value.open()
	}
	const areaSelectComplete = (event: any) => {
		if (isSelectAddress.value && (formData.value.province_id == event.province.id || formData.value.city_id != event.city.id || formData.value.district_id != event.district.id)) {
			formData.value.lat = '';
			formData.value.lng = '';
		}
		formData.value.province_id = event.province.id || 0
		formData.value.city_id = event.city.id || 0
		formData.value.district_id = event.district.id || 0
		formData.value.area = `${ event.province.name || '' }${ event.city.name || '' }${ event.district.name || '' }`
		currentColumn.value.value = formData.value.area
		isSelectAddress.value = false;
	}

	const inputPlaceholder = (column: any) => {
		if (!column) return "请选择";
		const format = column.addressFormat || "province/city/district";

		if (format === "province/city/district/address") return "请选择省/市/区/街道";
		if (format === "province/city/district/street") return "请选择省/市/区/街道(镇)";
		if (format === "province/city/district") return "请选择省/市/区(县)";
		if (format === "province/city") return "请选择省/市";
		return "请选择省份";
	};



	const warpCss = computed(() => {
        let style = '';
        style += 'position:relative;';
		if (diyComponent.value.componentStartBgColor) {
			if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${diyComponent.value.componentGradientAngle},${diyComponent.value.componentStartBgColor},${diyComponent.value.componentEndBgColor});`;
			else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
		}

		if (diyComponent.value.componentBgUrl) {
			style += `background-image:url('${img(diyComponent.value.componentBgUrl)}');`;
			style += 'background-size: cover;background-repeat: no-repeat;';
		}

		if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
		if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
		if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
		if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
		return style;
	})

	onMounted(() => {
		refresh();
		// 装修模式下刷新
		if (diyStore.mode == 'decorate') {
			watch(
				() => diyComponent.value,
				(newValue, oldValue) => {
					if (newValue && newValue.componentName == 'FormTable') {
						refresh();
					}
				}
			)
		} else {
		}

		console.log(diyComponent.value)
	});

	const refresh = () => {
	}

	// 表单组件验证
	const verify = () => {
		const res = { code: true, message: '' }
		// todo 验证组件，diyComponent.value.field.value
		return res;
	}

	// 重置表单组件数据
	const reset = () => {
		// todo 清空组件数据，diyComponent.value.field.value = '';
	}

	defineExpose({
		verify,
		reset
	})
</script>

<style lang="scss" scoped>
	@import '@/styles/diy_form.scss';

	.text-overflow-ellipsis {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
</style>