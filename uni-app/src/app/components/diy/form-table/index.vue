<template>
	<view :style="warpCss" class="form-item-frame">
	</view>
</template>

<script setup lang="ts">
	// 表单 表格组件
	import { ref, computed, watch, onMounted } from 'vue';
	import useDiyStore from '@/app/stores/diy';
	import { img, timeStampTurnTime, timeTurnTimeStamp } from '@/utils/common';
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
		var style = '';
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