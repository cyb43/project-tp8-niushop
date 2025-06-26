<template>
    <div class="min-w-[100px] min-h-[650px] p-[15px] bg-white tab-index">
        <el-tabs v-model="activeName" class="demo-tabs" @tab-click="handleClick" type="card">
            <el-tab-pane name="first">
                <template #label>今日</template>
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[32%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] cursor-pointer" v-for="(item, index) in boxes.slice(0, 3)"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">预计收入</div>
                            <div class="ml-[5px] text-[14px]">(元)</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，店铺收入金额减去支出的金额" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0.00</div>
                    </div>
                    <div class="w-[13.5%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] mt-[20px] cursor-pointer" v-for="(item, index) in boxes.slice(3)" @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">访客人数</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，站点访问人数" placement="top-start">
                                <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0</div>
                    </div>
                </div>

            </el-tab-pane>
            <el-tab-pane label="昨日" name="second">
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[32%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] cursor-pointer" v-for="(item, index) in boxes.slice(0, 3)"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">预计收入</div>
                            <div class="ml-[5px] text-[14px]">(元)</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，店铺收入金额减去支出的金额" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0.00</div>
                    </div>
                    <div class="w-[13.5%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] mt-[20px] cursor-pointer" v-for="(item, index) in boxes.slice(3)" @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">访客人数</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，站点访问人数" placement="top-start">
                                <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0</div>
                    </div>
                </div>
                <!-- <div ref="incomeChartRef1"  class="w-full h-[400px] mt-[60px]"></div> -->
            </el-tab-pane>
            <el-tab-pane label="7日内" name="third">
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[32%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] cursor-pointer" v-for="(item, index) in boxes.slice(0, 3)"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">预计收入</div>
                            <div class="ml-[5px] text-[14px]">(元)</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，店铺收入金额减去支出的金额" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0.00</div>
                    </div>
                    <div class="w-[13.5%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] mt-[20px] cursor-pointer" v-for="(item, index) in boxes.slice(3)" @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">访客人数</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，站点访问人数" placement="top-start">
                                <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0</div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="30日内" name="fourth">
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[32%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] cursor-pointer" v-for="(item, index) in boxes.slice(0, 3)"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">预计收入</div>
                            <div class="ml-[5px] text-[14px]">(元)</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，店铺收入金额减去支出的金额" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0.00</div>
                    </div>
                    <div class="w-[13.5%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] mt-[20px] cursor-pointer" v-for="(item, index) in boxes.slice(3)" @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">访客人数</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，站点访问人数" placement="top-start">
                                <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0</div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="自定义" name="fifth">
                <el-dialog v-model="dialogVisible" width="520" draggable="true">
                    <template #header>
                        <div class="text-[14px] color-[#333] h-[42px] leading-[42px]">自定义时间选择</div>
                    </template>
                    <div class="flex items-center">
                        <span class="mr-[10px]">选择时间:</span>
                        <el-date-picker v-model="value2" class="w-[100px]" type="datetimerange" start-placeholder="开始时间" end-placeholder="结束时间" format="YYYY-MM-DD HH:mm:ss" date-format="YYYY/MM/DD ddd" time-format="A hh:mm:ss"/>
                    </div>
                    <template #footer>
                    <div class="dialog-footer">
                        <el-button type="primary" @click="dialogVisible = false">确认</el-button>
                        <el-button @click="dialogVisible = false">取消</el-button>
                    </div>
                    </template>
                </el-dialog>
                <div class="flex w-full justify-between flex-wrap" >
                    <div class="w-[32%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] cursor-pointer" v-for="(item, index) in boxes.slice(0, 3)"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">预计收入</div>
                            <div class="ml-[5px] text-[14px]">(元)</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，店铺收入金额减去支出的金额" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0.00</div>
                    </div>
                    <div class="w-[13.5%] border-[#eee] border-solid border-[1px] h-[90px] p-[15px] mt-[20px] cursor-pointer" v-for="(item, index) in boxes.slice(3)" @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">访客人数</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，站点访问人数" placement="top-start">
                                <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[12px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="text-[25px] font-bold">0</div>
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
        <div class="w-full flex justify-between mt-[20px]">
            <div class="w-[68%] h-[115px] bg-[#f2f3f5] flex justify-between">
                <div class="flex flex-col justify-center   h-76px">
                    <div class="text-[14px] ml-[15px]">收入总额</div>
                    <div class="text-[24px] ml-[15px]">433.00</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px text-[18px]">=</div>
                <div class="flex flex-col justify-center  h-76px">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px">+</div>
                <div class="flex flex-col justify-center   h-76px">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px text-[18px]">+</div>
                <div class="flex flex-col justify-center   h-76px">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px text-[18px]">+</div>
                <div class="flex flex-col justify-center   h-76px">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px text-[18px]">+</div>
                <div class="flex flex-col justify-center   h-76px mr-[20px]">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
            </div>
            <div class="w-[30%] h-[115px] bg-[#f2f3f5] flex">
                    <div class="flex flex-col justify-center  w-[149px] h-76px">
                    <div class="text-[14px] ml-[15px]">收入总额</div>
                    <div class="text-[24px] ml-[15px]">433.00</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px text-[18px]">=</div>
                <div class="flex flex-col justify-center w-[149px] h-76px">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
                <div class="flex flex-col justify-center items-center w-[50px] h-76px">+</div>
                <div class="flex flex-col justify-center  w-[149px] h-76px">
                    <div class="text-[14px] ml-[15px] mt-[10px]">收入总额</div>
                    <div class="text-[24px] ml-[15px] mb-[10px] mt-[5px]">433.00</div>
                    <div class="text-[#105CFB] text-[14px] cursor-pointer ml-[15px] mt-[-5px]">明细</div>
                </div>
            </div>
        </div>
        <div ref="incomeChartRef" class="h-[400px] mt-[40px] ml-[-90px]"></div>
    </div>
</template>
<script lang="ts" setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import type { TabsPaneContext} from 'element-plus'
import * as echarts from 'echarts'

const activeName = ref('first')
const dialogVisible = ref(false)
const value2 = ref('')

const handleClick = (tab: TabsPaneContext, event: Event) => {
    if (tab.props.name == 'fifth') {
        dialogVisible.value = true
    }
    nextTick(() => {
        initIncomeChart()
    })

}

const boxes = reactive([
  { content: '盒子 1', id : 1 },
  { content: '盒子 2', id : 2 },
  { content: '盒子 3', id : 3 },
  { content: '盒子 1', id : 4  },
  { content: '盒子 2', id : 5  },
  { content: '盒子 3', id : 6  },
  { content: '盒子 3', id : 8  },
  { content: '盒子 3', id : 9  },
  { content: '盒子 3', id : 10  },
  { content: '盒子 3', id : 11  },
]);
const currentIndex = ref(-1);

const selectBox = (item) => {
    currentIndex.value = item.id;
}

// 折线图
const incomeChartRef = ref(null);

const initIncomeChart = () => {
    if (incomeChartRef.value !== null) {
        const incomeChart = echarts.init(incomeChartRef.value);

        // 准备数据
        const xAxisData = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        const yAxisData = [0, -20, -50, 134, 90, 230, 210];

        // 配置项
        const option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['预计收入']
            },
            xAxis: {
                type: 'category',
                data: xAxisData
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '预计收入',
                type: 'line',
                data: yAxisData
            }]
        };

        // 使用配置项初始化图表
        incomeChart.setOption(option);
        incomeChart.resize({
            width: 'auto'
        });
    }
}

onMounted(() => {
    initIncomeChart();
})
</script>
<style lang="scss" scoped>
.wenHao{
    font-size: 14px;
}
.tab-index :deep(.el-tabs__item.is-active){
    background-color: #105CFB;
    color: white;
}
.tab-index :deep(.el-tabs__item):hover{
    background-color: #105CFB;
    color: white;
}
.tab-index :deep(.el-tabs__item){
    line-height: 34px;
    height: 34px !important;
}
.tab-index :deep(.el-tabs__nav){
    border: 1px solid #D2D2D2;
    height: 34px;
    border-radius: 0;
}
.tab-index :deep(.el-tabs__header) {
  border: none;
}
</style>
