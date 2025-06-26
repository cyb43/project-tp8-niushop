<template>
    <u-popup :show="show" mode="bottom" :round="10" closeable @close="show = false">
        <view class="box h-[728rpx]">
            <view class="title px-[30rpx] box-border text-center text-[28rpx] font-bold h-[90rpx] leading-[90rpx] border-0 border-solid border-[#f7f4f4] border-b-[2rpx]">
                <text>{{ rules.type=='subscribe' ? '请选择配送时间'  : '请选择自提时间'}}</text>
            </view>
            <view class="body flex h-[calc(100%-90rpx)] box-border" v-if="dateArr.length">
                <!-- 左侧日期选择 -->
                <scroll-view :scroll-y="true" class="left bg-[#f8f8f8] shrink-0 w-[230rpx]" scroll-with-animation :scroll-into-view="'id' + (dateActive ? dateActive - 1 : 0)">
                    <template v-for="(item,index) in dateArr" :key="index">
                        <view class="date-box flex px-[30rpx] py-[16rpx] box-border text-[24rpx] items-center"
                              :id="'id' + index" @click="selectDateEvent(index,item)"
                              :class="{ 'bg-[#fff]': index == dateActive }">
                            <view class="text-[24rpx] leading-[58rpx]">{{ item.md }}</view>
                            <view class="text-[24rpx] leading-[58rpx]">({{ item.week }})</view>
                        </view>
                    </template>
                </scroll-view>
                <!-- 右侧时间选择 -->
                <scroll-view :scroll-y="true" class="right w-[calc(100%-230rpx)] px-[30rpx] box-border" scroll-with-animation :scroll-into-view="'id' + (timeActive ? timeActive - 1 : 0)">
                    <!-- 时间选项 -->
                    <template v-for="(el,key) in dateArr[dateActive].children" :key="key">
                        <view class=" h-[72rpx] flex  border-0 border-solid border-b-[2rpx] border-[#eee] justify-between items-center"
                            v-if="!el.disable" @click="selectTimeEvent(key,el)"
                            :class="{'text-[var(--primary-color)]':key == timeActive}" :id="'id' + key">
                            <view class="text-[24rpx]" :class="{'text-[var(--primary-color)]':key == timeActive}">
								<text v-if="el.value=='immediate'">{{ el.begin }}{{ el.end }}</text>
                                <text v-else>{{ el.begin }}-{{ el.end }}</text>
                            </view>
                            <text v-if="key == timeActive" class="nc-iconfont nc-icon-duihaoV6mm mr-[30rpx] text-[38rpx]"></text>
                        </view>
                    </template>
                </scroll-view>
            </view>
            <view class="h-[80%] flex items-center flex-col justify-center" v-else>
                <u-empty :text="rules.type=='subscribe' ? '没有可选择的配送时间'  : '没有可选择的自提时间'" width="214" :icon="img('static/resource/images/empty.png')" />
            </view>
        </view>
    </u-popup>
</template>

<script>
import { initData, initTime, currentTime, timestampTransition, timeTransition ,initAppointmentData,initTodayData} from './date.js'
import { cloneDeep } from 'lodash-es'
import { img } from "@/utils/common";

export default {
    name: 'times',
    model: {
        prop: "showPop",
        event: "change"
    },
    props: {
        rules: {
            type: Object,
            default() {
                return {}
            }
        },
        isQuantum: {
            type: Boolean,
            default: false
        },
		isOpen: {
		    type: Boolean,
		    default: true
		}

    },
    data() {
        return {
            orderDateTime: '', // 选中时间
            orderDateStamp: '', // 选中时间年-月-日
            dateArr: [], //日期数据
            timeArr: [], //时间数据
            nowDate: "", // 当前日期(月,日)
            dateActive: 0, //选中的日期索引
            timeActive: 0, //选中的时间索引
            selectDate: "", //选择的日期
            show: false,
        }
    },
    created() {
        this.nowDate = currentTime().md
        this.initOnload()
    },
    watch: {
        rules: {
            handler: function (newVal) {
                if (Object.keys(newVal).length) {
                    this.initOnload(); // 重新初始化
                }
            },
            deep: true,
            immediate: true
        }
    },
    methods: {
        img,
        initOnload() {
            if (!this.rules.trade_time_json || !this.rules.time_week) return;
            // 只有同城配送时，advance_day 和 most_day 有效
            const useLimit =
				this.rules.type=='subscribe'&&
                'advance_day' in this.rules &&
                'most_day' in this.rules &&
                this.rules.advance_day !== null &&
                this.rules.most_day !== null;

			let dateObj = [];
			   
			if (!this.isOpen && this.rules.type === 'subscribe') {
			    dateObj = initTodayData()
			} else if (useLimit) {
				dateObj = initAppointmentData(this.rules.advance_day, this.rules.most_day);
			} else {
				dateObj = initData(); // 最近 7 天
			}


            this.timeArr = initTime(
                this.rules.trade_time_json,
                Number(this.rules.time_interval) / 60,
                this.isQuantum
            );
        
            const time = Math.floor(timeTransition(currentTime().time));
            const now = new Date();
        

            let advanceStart = null;
            let advanceEnd = null;
            if (useLimit) {
                advanceStart = new Date(now);
                advanceStart.setDate(now.getDate() + this.rules.advance_day);
                advanceStart.setHours(0, 0, 0, 0);

                advanceEnd = new Date(now);
                advanceEnd.setDate(now.getDate() + this.rules.most_day);
                advanceEnd.setHours(23, 59, 59, 999);
            }
        
            this.dateArr = [];
            dateObj.forEach((item) => {
                const itemDate = new Date(item.date);
                const isWithinRange = !useLimit || (itemDate >= advanceStart && itemDate <= advanceEnd);
				const now = new Date();
				const isToday = item.mdTime === this.nowDate;
				const isAllowWeek = this.rules.time_week.includes(item.dayNum);
			    if ((isAllowWeek && isWithinRange) || (useLimit && isToday && isAllowWeek) ||(!this.isOpen && isToday)) {
				   const needAdvanceToday = isToday && !this.isOpen;
				   
				   if (needAdvanceToday) {
					   // 今天但需要提前预约 → 不允许选择具体时间段
					   item.children = [];
				   } else {
					   // 正常日期或今天无需提前预约 → 加所有时间段
					   item.children = cloneDeep(this.timeArr);
				   }
					// 判断当前时间是否在配送时段内
					const nowTime = time; // 秒数
					const isInTradeTime = this.rules.trade_time_json.some(slot => {
						return nowTime >= slot.start_time && nowTime < slot.end_time;
					});
					if ((isToday && useLimit && isInTradeTime) ||(isToday && useLimit && !this.isOpen)  ) {
					
						item.children.unshift({
							begin: '立即',
							end: '配送',
							value: 'immediate',
							disable: false
						});
					}

				    // item.children = item.children.filter((el) => {
					   // if (el.value === 'immediate') return true;
					   // if (!el.end) return false;
					   // let elTime = timeTransition(el.end);
					   // return !(item.mdTime === this.nowDate && elTime < time);
				    // });
					item.children = item.children.filter((el) => {
					    if (el.value === 'immediate') return true;
					    if (!el.end || !el.begin) return false;
					
					    const elStart = timeTransition(el.begin);
					    const elEnd = timeTransition(el.end);
					    const nowTime = time;
					
					    const hasImmediate = item.children.some(c => c.value === 'immediate');
					
					    // 有立即配送时，过滤掉当前时间在 begin ~ end 范围内的项
					    if (hasImmediate) {
					        if (item.mdTime === this.nowDate && nowTime >= elStart && nowTime < elEnd) {
					            return false;
					        }
					    }
					
					    // 原有逻辑：过滤掉已过时间段
					    return !(item.mdTime === this.nowDate && elEnd < nowTime);
					});

				   if (item.children.length > 0) {
					   this.dateArr.push(item);
				   }
			   }
		
            });
        
            let flag = true;
            this.dateArr.forEach((item, index) => {
                item.children.forEach((el, key) => {
                    if (!el.disable && flag) {
                        flag = false;
                        this.timeActive = key;
                        this.dateActive = index;
                        this.selectDate = item.mdTime;
                        this.orderDateStamp = item.date;
						if (el.value === 'immediate') {
						    this.orderDate = `${this.selectDate}(立即配送)`;
						    this.orderDateTime = `${item.date} 立即配送`;
						} else {
						    this.orderDate = `${this.selectDate}(${el.begin}~${el.end})`;
						    this.orderDateTime = `${item.date} ${el.begin}~${el.end}`;
						}

                        // this.orderDate = `${this.selectDate}(${el.begin}~${el.end})`;
                        // this.orderDateTime = `${item.date} ${el.begin}~${el.end}`;
                        this.$emit("change", this.orderDateTime);
                        this.$emit("getDate", this.orderDate);
                        this.$emit("getStamp", this.orderDateStamp);
                    }
                });
            });
        },

        // 日期选择事件
        selectDateEvent(index, item) {
            this.dateActive = index
            this.selectDate = item.mdTime
            this.orderDateStamp = item.date
            this.timeActive = 0
            // this.orderDate = `${ this.selectDate }(${ item.children[this.timeActive].begin }~${ item.children[this.timeActive].end })`
            // this.orderDateTime = `${ item.date } ${ item.children[this.timeActive].begin }~${ item.children[this.timeActive].end }`
			 const firstTime = item.children[this.timeActive];

			if (firstTime.value === 'immediate') {
				this.orderDate = `${this.selectDate}(立即配送)`;
				this.orderDateTime = `${item.date} 立即配送`;
			} else {
				this.orderDate = `${this.selectDate}(${firstTime.begin}~${firstTime.end})`;
				this.orderDateTime = `${item.date} ${firstTime.begin}~${firstTime.end}`;
			}
            this.$emit('change', this.orderDateTime)
            this.$emit('getDate', this.orderDate);
            this.$emit('getStamp', this.orderDateStamp)
        },
        // 时间选择事件
        selectTimeEvent(index, item) {
            this.handleSelectQuantum(index, item)
            this.show = false
            this.$emit('change', this.orderDateTime)
            this.$emit('getDate', this.orderDate);
        },
        handleSelectQuantum(index, item) {
            this.timeActive = index
            // this.orderDate = `${ this.selectDate }(${ item.begin }~${ item.end })`
            // this.orderDateTime = `${ this.orderDateStamp } ${ item.begin }~${ item.end }`
			 if (item.value === 'immediate') {
				this.orderDate = `${this.selectDate}(立即配送)`;
				this.orderDateTime = `${this.orderDateStamp} 立即配送`;
			} else {
				this.orderDate = `${this.selectDate}(${item.begin}~${item.end})`;
				this.orderDateTime = `${this.orderDateStamp} ${item.begin}~${item.end}`;
			}
        }
    }
}
</script>
