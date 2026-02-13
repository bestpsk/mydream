<template>
  <div class="customer-consumption-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>客户消费分析</span>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div v-if="hasAuth('customer:consumption:view')" class="search-bar mb-4">
        <el-form :inline="true" :model="searchForm">
          <el-form-item label="客户姓名">
            <el-input
              v-model="searchForm.customerName"
              placeholder="请输入客户姓名"
            />
          </el-form-item>
          <el-form-item label="手机号码">
            <el-input v-model="searchForm.phone" placeholder="请输入手机号码" />
          </el-form-item>
          <el-form-item label="消费日期">
            <el-date-picker
              v-model="searchForm.consumptionDate"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              style="width: 280px"
            />
          </el-form-item>
          <el-form-item label="消费类型">
            <el-select
              v-model="searchForm.consumptionType"
              placeholder="请选择消费类型"
            >
              <el-option label="服务消费" value="服务消费" />
              <el-option label="产品购买" value="产品购买" />
              <el-option label="会员卡充值" value="会员卡充值" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="resetSearch">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 消费统计卡片 -->
      <div v-if="hasAuth('customer:consumption:view')" class="stats-cards mb-4">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">总消费金额</div>
                <div class="stats-value">{{ totalAmount.toFixed(2) }} 元</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">消费次数</div>
                <div class="stats-value">{{ totalCount }}</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">平均消费</div>
                <div class="stats-value">{{ averageAmount.toFixed(2) }} 元</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">会员充值</div>
                <div class="stats-value">
                  {{ rechargeAmount.toFixed(2) }} 元
                </div>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <!-- 消费趋势图表 -->
      <div
        v-if="hasAuth('customer:consumption:view')"
        class="chart-container mb-4"
      >
        <el-card shadow="hover">
          <template #header>
            <span>消费趋势</span>
          </template>
          <div class="chart-content">
            <el-empty v-if="!chartData.length" description="暂无消费趋势数据" />
            <div v-else class="consumption-chart">
              <!-- 这里可以使用图表库，如ECharts或Element Plus的图表组件 -->
              <div class="chart-placeholder">
                <div class="chart-title">近7天消费趋势</div>
                <div class="chart-bars">
                  <div
                    v-for="(item, index) in chartData"
                    :key="index"
                    class="chart-bar-item"
                  >
                    <div class="chart-bar-label">{{ item.date }}</div>
                    <div class="chart-bar">
                      <div
                        class="chart-bar-fill"
                        :style="{
                          height: (item.amount / maxAmount) * 100 + '%'
                        }"
                      />
                    </div>
                    <div class="chart-bar-value">
                      {{ item.amount.toFixed(2) }}元
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </el-card>
      </div>

      <!-- 消费记录表格 -->
      <div v-if="hasAuth('customer:consumption:view')">
        <el-table
          v-loading="loading"
          :data="consumptionList"
          border
          style="width: 100%"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="customerName" label="客户姓名" />
          <el-table-column prop="phone" label="手机号码" />
          <el-table-column
            prop="consumptionDate"
            label="消费日期"
            width="150"
          />
          <el-table-column prop="amount" label="消费金额" width="120">
            <template #default="scope">
              <span class="amount">{{ scope.row.amount.toFixed(2) }} 元</span>
            </template>
          </el-table-column>
          <el-table-column prop="consumptionType" label="消费类型" />
          <el-table-column prop="serviceItem" label="服务项目" />
          <el-table-column prop="paymentMethod" label="支付方式" />
          <el-table-column prop="remark" label="备注" min-width="150" />
        </el-table>

        <!-- 分页 -->
        <div class="pagination mt-4">
          <el-pagination
            v-model:current-page="pagination.current"
            v-model:page-size="pagination.pageSize"
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            :total="pagination.total"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>

      <!-- 无权限提示 -->
      <div v-else class="no-permission">
        <el-empty description="无权限查看数据" />
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from "vue";
import { ElMessage } from "element-plus";
import { http } from "@/utils/http";
import { hasAuth } from "@/router/utils";

// 加载状态
const loading = ref(false);

// 搜索表单
const searchForm = reactive({
  customerName: "",
  phone: "",
  consumptionDate: null,
  consumptionType: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 消费记录列表
const consumptionList = ref<any[]>([]);

// 统计数据
const totalAmount = ref(0);
const totalCount = ref(0);
const averageAmount = ref(0);
const rechargeAmount = ref(0);

// 图表数据
const chartData = ref<any[]>([]);
const maxAmount = computed(() => {
  if (chartData.value.length === 0) return 1;
  return Math.max(...chartData.value.map(item => item.amount));
});

// 初始化数据
onMounted(() => {
  if (hasAuth("customer:consumption:view")) {
    getConsumptionList();
    getConsumptionStats();
    getConsumptionChartData();
  }
});

// 获取消费记录列表
const getConsumptionList = async () => {
  loading.value = true;
  try {
    // 构建请求参数
    const params = {
      page: pagination.current,
      pageSize: pagination.pageSize,
      customerName: searchForm.customerName || undefined,
      phone: searchForm.phone || undefined,
      startTime: searchForm.consumptionDate
        ? searchForm.consumptionDate[0]?.toISOString().split("T")[0]
        : undefined,
      endTime: searchForm.consumptionDate
        ? searchForm.consumptionDate[1]?.toISOString().split("T")[0]
        : undefined,
      consumptionType: searchForm.consumptionType || undefined
    };

    // 这里使用模拟数据，实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/consumption", { params });
    // if (response.code === 200) {
    //   consumptionList.value = response.data.list;
    //   pagination.total = response.data.total;
    // }

    // 模拟数据
    const mockData = {
      list: [
        {
          id: 1,
          customerName: "张三",
          phone: "13800138000",
          consumptionDate: "2026-01-01",
          amount: 298,
          consumptionType: "服务消费",
          serviceItem: "面部护理",
          paymentMethod: "微信支付",
          remark: "首次消费"
        },
        {
          id: 2,
          customerName: "李四",
          phone: "13900139000",
          consumptionDate: "2026-01-02",
          amount: 598,
          consumptionType: "服务消费",
          serviceItem: "身体按摩",
          paymentMethod: "支付宝",
          remark: "会员折扣"
        },
        {
          id: 3,
          customerName: "王五",
          phone: "13700137000",
          consumptionDate: "2026-01-03",
          amount: 1280,
          consumptionType: "产品购买",
          serviceItem: "护肤套装",
          paymentMethod: "银行卡",
          remark: "大额消费"
        },
        {
          id: 4,
          customerName: "张三",
          phone: "13800138000",
          consumptionDate: "2026-01-04",
          amount: 1000,
          consumptionType: "会员卡充值",
          serviceItem: "会员卡",
          paymentMethod: "微信支付",
          remark: "充值1000元"
        },
        {
          id: 5,
          customerName: "李四",
          phone: "13900139000",
          consumptionDate: "2026-01-05",
          amount: 398,
          consumptionType: "服务消费",
          serviceItem: "肩颈按摩",
          paymentMethod: "支付宝",
          remark: "定期护理"
        }
      ],
      total: 5
    };

    consumptionList.value = mockData.list;
    pagination.total = mockData.total;
  } catch (error) {
    console.error("获取消费记录失败:", error);
    ElMessage.error("获取消费记录失败");
  } finally {
    loading.value = false;
  }
};

// 获取消费统计数据
const getConsumptionStats = async () => {
  try {
    // 实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/consumption/stats");
    // if (response.code === 200) {
    //   totalAmount.value = response.data.totalAmount;
    //   totalCount.value = response.data.totalCount;
    //   averageAmount.value = response.data.averageAmount;
    //   rechargeAmount.value = response.data.rechargeAmount;
    // }

    // 模拟数据
    totalAmount.value = 3574;
    totalCount.value = 5;
    averageAmount.value = 714.8;
    rechargeAmount.value = 1000;
  } catch (error) {
    console.error("获取消费统计数据失败:", error);
  }
};

// 获取消费图表数据
const getConsumptionChartData = async () => {
  try {
    // 实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/consumption/chart");
    // if (response.code === 200) {
    //   chartData.value = response.data;
    // }

    // 模拟数据
    chartData.value = [
      { date: "01-01", amount: 298 },
      { date: "01-02", amount: 598 },
      { date: "01-03", amount: 1280 },
      { date: "01-04", amount: 1000 },
      { date: "01-05", amount: 398 },
      { date: "01-06", amount: 0 },
      { date: "01-07", amount: 0 }
    ];
  } catch (error) {
    console.error("获取消费图表数据失败:", error);
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getConsumptionList();
  getConsumptionStats();
  getConsumptionChartData();
};

// 重置搜索
const resetSearch = () => {
  searchForm.customerName = "";
  searchForm.phone = "";
  searchForm.consumptionDate = null;
  searchForm.consumptionType = "";
  pagination.current = 1;
  getConsumptionList();
  getConsumptionStats();
  getConsumptionChartData();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getConsumptionList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getConsumptionList();
};
</script>

<style scoped>
.customer-consumption-container {
  min-height: calc(100vh - 120px);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
  background-color: #f9fafc;
  padding: 16px;
  border-radius: 8px;
}

.stats-cards {
  margin-bottom: 20px;
}

.stats-card {
  transition: all 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-2px);
}

.stats-item {
  text-align: center;
}

.stats-label {
  font-size: 14px;
  color: #606266;
  margin-bottom: 8px;
}

.stats-value {
  font-size: 24px;
  font-weight: bold;
  color: #303133;
}

.chart-container {
  margin-bottom: 20px;
}

.chart-content {
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.consumption-chart {
  width: 100%;
  height: 300px;
}

.chart-placeholder {
  width: 100%;
  height: 100%;
  padding: 20px;
}

.chart-title {
  text-align: center;
  margin-bottom: 20px;
  font-size: 16px;
  font-weight: bold;
  color: #303133;
}

.chart-bars {
  display: flex;
  align-items: flex-end;
  justify-content: space-around;
  height: 200px;
}

.chart-bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 80px;
}

.chart-bar-label {
  font-size: 12px;
  color: #606266;
  margin-bottom: 8px;
}

.chart-bar {
  width: 40px;
  background-color: #f0f0f0;
  border-radius: 4px 4px 0 0;
  overflow: hidden;
  margin-bottom: 8px;
  min-height: 10px;
}

.chart-bar-fill {
  width: 100%;
  background-color: #409eff;
  border-radius: 4px 4px 0 0;
  transition: height 0.5s ease;
}

.chart-bar-value {
  font-size: 12px;
  color: #303133;
  font-weight: bold;
}

.pagination {
  display: flex;
  justify-content: flex-end;
}

.no-permission {
  padding: 40px 0;
  text-align: center;
}

.amount {
  font-weight: bold;
  color: #f56c6c;
}
</style>
