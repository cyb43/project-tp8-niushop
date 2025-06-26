<template>
    <div class="min-w-[100px] min-h-[650px] p-[15px] bg-white tab-index mb-[150px]">
        <el-tabs v-model="activeName" class="demo-tabs" @tab-click="handleClick" type="card">
            <el-tab-pane name="first">
                <template #label>今日</template>
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[24.3%] border-[#eee] border-solid border-[1px] h-[85px] p-[15px] cursor-pointer" v-for="(item, index) in boxes"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">全部</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，全部站点总的访客人数" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[4px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[14px] mt-[16px] text-[#999]">访客数</div>
                            <div class="text-[14px] mt-[16px] text-[#999]">3</div>
                        </div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="昨日" name="second">
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[24.3%] border-[#eee] border-solid border-[1px] h-[85px] p-[15px] cursor-pointer" v-for="(item, index) in boxes"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">全部</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，全部站点总的访客人数" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[4px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[14px] mt-[16px] text-[#999]">访客数</div>
                            <div class="text-[14px] mt-[16px] text-[#999]">3</div>
                        </div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="7日内" name="third">
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[24.3%] border-[#eee] border-solid border-[1px] h-[85px] p-[15px] cursor-pointer" v-for="(item, index) in boxes"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">全部</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，全部站点总的访客人数" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[4px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[14px] mt-[16px] text-[#999]">访客数</div>
                            <div class="text-[14px] mt-[16px] text-[#999]">3</div>
                        </div>
                    </div>
                </div>
            </el-tab-pane>
            <el-tab-pane label="30日内" name="fourth">
                <div class="flex w-full justify-between flex-wrap">
                    <div class="w-[24.3%] border-[#eee] border-solid border-[1px] h-[85px] p-[15px] cursor-pointer" v-for="(item, index) in boxes"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                        <div class="flex items-center">
                            <div class="text-[14px]">全部</div>
                            <el-tooltip class="box-item" effect="light" content="统计时间内，全部站点总的访客人数" placement="top-start">
                              <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[4px]"></div>
                            </el-tooltip>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[14px] mt-[16px] text-[#999]">访客数</div>
                            <div class="text-[14px] mt-[16px] text-[#999]">3</div>
                        </div>
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
                    <div class="flex w-full justify-between flex-wrap">
                        <div class="w-[24.3%] border-[#eee] border-solid border-[1px] h-[85px] p-[15px] cursor-pointer" v-for="(item, index) in boxes"  @click="selectBox(item)" :class="{ 'border-[#105CFB]': currentIndex == item.id,'text-[#105CFB]': currentIndex == item.id }">
                            <div class="flex items-center">
                                <div class="text-[14px]">全部</div>
                                <el-tooltip class="box-item" effect="light" content="统计时间内，全部站点总的访客人数" placement="top-start">
                                <div class="nc-iconfont nc-icon-bangzhuV6xx wenHao text-[#666] ml-[4px]"></div>
                                </el-tooltip>
                            </div>
                            <div class="flex justify-between">
                                <div class="text-[14px] mt-[16px] text-[#999]">访客数</div>
                                <div class="text-[14px] mt-[16px] text-[#999]">3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-tab-pane>
        </el-tabs>
        <div ref="incomeChartRef" class="h-[400px] mt-[60px] ml-[-90px]"></div>
        <div class="w-full h-[120px] bg-[#f3f8ff] mt-[15px] flex">
            <div class="flex items-center justify-center flex-col w-[100px] h-[120px] bg-[#105CFB] text-[#fff]">
                <div class="nc-iconfont nc-icon-wodeV6xx-11 my_icon"></div>
                <div class="text-[14px]">浏览访问</div>
            </div>
            <div class="w-[237px] h-[120px] mx-[50px] flex flex-col justify-center">
                <div class="text-[14px]">访客数(人)</div>
                <div class="text-[26px]">3</div>
                <div class="flex">
                    <div class="text-[12px] text-[#666] pr-[2px]">比前日</div>
                    <div class="text-[12px] text-[#f97337] pr-[2px]">⬆</div>
                    <div class="text-[12px] text-[#f97337]">100%</div>
                </div>
            </div>
            <div class="w-[237px] h-[120px] mx-[50px] flex flex-col justify-center">
                <div class="text-[14px]">新增会员数(人)</div>
                <div class="text-[26px]">3</div>
                <div class="flex">
                    <div class="text-[12px] text-[#666] pr-[2px]">比前日</div>
                    <div class="text-[12px] text-[#f97337] pr-[2px]">⬆</div>
                    <div class="text-[12px] text-[#f97337]">0%</div>
                </div>
            </div>
        </div>
        <div class="w-full h-[120px] bg-[#fffaf3] mt-[15px] flex">
            <div class="flex items-center justify-center flex-col w-[100px] h-[120px] bg-[#ff9f15] text-[#fff]">
                <div class="nc-iconfont nc-icon-liebiaoV6xx my_icon"></div>
                <div class="text-[14px]">成交转化</div>
            </div>
            <div class="w-[237px] h-[120px] mx-[50px] flex flex-col justify-center">
                <div class="text-[14px]">支付人数(人)</div>
                <div class="text-[26px]">3</div>
                <div class="flex">
                    <div class="text-[12px] text-[#666] pr-[2px]">比前日</div>
                    <div class="text-[12px] text-[#f97337] pr-[2px]">⬆</div>
                    <div class="text-[12px] text-[#f97337]">100%</div>
                </div>
            </div>
            <div class="w-[237px] h-[120px] mx-[50px] flex flex-col justify-center">
                <div class="text-[14px]">访问-支付转化率(%)</div>
                <div class="text-[26px]">0.33</div>
                <div class="flex">
                    <div class="text-[12px] text-[#666] pr-[2px]">比前日</div>
                    <div class="text-[12px] text-[#f97337] pr-[2px]">⬆</div>
                    <div class="text-[12px] text-[#f97337]">0%</div>
                </div>
            </div>
        </div>
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
  { content: '盒子 1', id : 4 }
]);
const currentIndex = ref(-1); 

const selectBox = (item) => {
  currentIndex.value = item.id;
};

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
                data: ['全部']
            },
            xAxis: {
                type: 'category',
                data: xAxisData
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '全部',
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
});
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
