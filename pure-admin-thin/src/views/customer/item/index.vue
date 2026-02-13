<template>
  <div class="customer-item-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>客户品项分析</span>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div v-if="hasAuth('customer:item:view')" class="search-bar mb-4">
        <el-form :inline="true" :model="searchForm">
          <el-form-item label="品项名称">
            <el-input
              v-model="searchForm.itemName"
              placeholder="请输入品项名称"
            />
          </el-form-item>
          <el-form-item label="品项类型">
            <el-select
              v-model="searchForm.itemType"
              placeholder="请选择品项类型"
            >
              <el-option label="服务项目" value="服务项目" />
              <el-option label="产品" value="产品" />
              <el-option label="套餐" value="套餐" />
            </el-select>
          </el-form-item>
          <el-form-item label="销售日期">
            <el-date-picker
              v-model="searchForm.saleDate"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              style="width: 280px"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="resetSearch">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 品项统计卡片 -->
      <div v-if="hasAuth('customer:item:view')" class="stats-cards mb-4">
        <el-row :gutter="20">
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">总销售额</div>
                <div class="stats-value">{{ totalSales.toFixed(2) }} 元</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">销售数量</div>
                <div class="stats-value">{{ totalQuantity }}</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">热门品项数</div>
                <div class="stats-value">{{ hotItemCount }}</div>
              </div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover" class="stats-card">
              <div class="stats-item">
                <div class="stats-label">平均单价</div>
                <div class="stats-value">{{ averagePrice.toFixed(2) }} 元</div>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </div>

      <!-- 品项销售分析图表 -->
      <div v-if="hasAuth('customer:item:view')" class="chart-container mb-4">
        <el-card shadow="hover">
          <template #header>
            <span>品项销售分析</span>
          </template>
          <div class="chart-content">
            <el-empty v-if="!chartData.length" description="暂无品项销售数据" />
            <div v-else class="item-chart">
              <!-- 这里可以使用图表库，如ECharts或Element Plus的图表组件 -->
              <div class="chart-placeholder">
                <div class="chart-title">热门品项销售排行</div>
                <div class="chart-bars">
                  <div
                    v-for="(item, index) in chartData"
                    :key="index"
                    class="chart-bar-item"
                  >
                    <div class="chart-bar-label">{{ item.name }}</div>
                    <div class="chart-bar">
                      <div
                        class="chart-bar-fill"
                        :style="{ width: (item.sales / maxSales) * 100 + '%' }"
                      />
                    </div>
                    <div class="chart-bar-value">
                      {{ item.sales.toFixed(2) }}元
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </el-card>
      </div>

      <!-- 品项记录表格 -->
      <div v-if="hasAuth('customer:item:view')">
        <el-table
          v-loading="loading"
          :data="itemList"
          border
          style="width: 100%"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="itemName" label="品项名称" />
          <el-table-column prop="itemType" label="品项类型" />
          <el-table-column prop="price" label="单价" width="100">
            <template #default="scope">
              <span>{{ scope.row.price.toFixed(2) }} 元</span>
            </template>
          </el-table-column>
          <el-table-column prop="quantity" label="销售数量" width="100" />
          <el-table-column prop="totalAmount" label="销售金额" width="120">
            <template #default="scope">
              <span class="amount"
                >{{ scope.row.totalAmount.toFixed(2) }} 元</span
              >
            </template>
          </el-table-column>
          <el-table-column prop="saleDate" label="销售日期" width="150" />
          <el-table-column prop="customerName" label="客户姓名" />
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
  itemName: "",
  itemType: "",
  saleDate: null
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 品项记录列表
const itemList = ref<any[]>([]);

// 统计数据
const totalSales = ref(0);
const totalQuantity = ref(0);
const hotItemCount = ref(0);
const averagePrice = ref(0);

// 图表数据
const chartData = ref<any[]>([]);
const maxSales = computed(() => {
  if (chartData.value.length === 0) return 1;
  return Math.max(...chartData.value.map(item => item.sales));
});

// 初始化数据
onMounted(() => {
  if (hasAuth("customer:item:view")) {
    getItemList();
    getItemStats();
    getItemChartData();
  }
});

// 获取品项记录列表
const getItemList = async () => {
  loading.value = true;
  try {
    // 构建请求参数
    const params = {
      page: pagination.current,
      pageSize: pagination.pageSize,
      itemName: searchForm.itemName || undefined,
      itemType: searchForm.itemType || undefined,
      startTime: searchForm.saleDate
        ? searchForm.saleDate[0]?.toISOString().split("T")[0]
        : undefined,
      endTime: searchForm.saleDate
        ? searchForm.saleDate[1]?.toISOString().split("T")[0]
        : undefined
    };

    // 这里使用模拟数据，实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/item", { params });
    // if (response.code === 200) {
    //   itemList.value = response.data.list;
    //   pagination.total = response.data.total;
    // }

    // 模拟数据
    const mockData = {
      list: [
        {
          id: 1,
          itemName: "面部护理",
          itemType: "服务项目",
          price: 298,
          quantity: 5,
          totalAmount: 1490,
          saleDate: "2026-01-01",
          customerName: "张三",
          remark: "面部深层清洁+补水"
        },
        {
          id: 2,
          itemName: "身体按摩",
          itemType: "服务项目",
          price: 598,
          quantity: 3,
          totalAmount: 1794,
          saleDate: "2026-01-02",
          customerName: "李四",
          remark: "全身精油按摩"
        },
        {
          id: 3,
          itemName: "护肤套装",
          itemType: "产品",
          price: 1280,
          quantity: 2,
          totalAmount: 2560,
          saleDate: "2026-01-03",
          customerName: "王五",
          remark: "高端护肤套装"
        },
        {
          id: 4,
          itemName: "美发造型",
          itemType: "服务项目",
          price: 398,
          quantity: 4,
          totalAmount: 1592,
          saleDate: "2026-01-04",
          customerName: "赵六",
          remark: "染发+烫发"
        },
        {
          id: 5,
          itemName: "肩颈按摩",
          itemType: "服务项目",
          price: 198,
          quantity: 6,
          totalAmount: 1188,
          saleDate: "2026-01-05",
          customerName: "孙七",
          remark: "肩颈舒缓按摩"
        }
      ],
      total: 5
    };

    itemList.value = mockData.list;
    pagination.total = mockData.total;
  } catch (error) {
    console.error("获取品项记录失败:", error);
    ElMessage.error("获取品项记录失败");
  } finally {
    loading.value = false;
  }
};

// 获取品项统计数据
const getItemStats = async () => {
  try {
    // 实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/item/stats");
    // if (response.code === 200) {
    //   totalSales.value = response.data.totalSales;
    //   totalQuantity.value = response.data.totalQuantity;
    //   hotItemCount.value = response.data.hotItemCount;
    //   averagePrice.value = response.data.averagePrice;
    // }

    // 模拟数据
    totalSales.value = 8624;
    totalQuantity.value = 20;
    hotItemCount.value = 3;
    averagePrice.value = 431.2;
  } catch (error) {
    console.error("获取品项统计数据失败:", error);
  }
};

// 获取品项图表数据
const getItemChartData = async () => {
  try {
    // 实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/item/chart");
    // if (response.code === 200) {
    //   chartData.value = response.data;
    // }

    // 模拟数据
    chartData.value = [
      { name: "面部护理", sales: 1490 },
      { name: "身体按摩", sales: 1794 },
      { name: "护肤套装", sales: 2560 },
      { name: "美发造型", sales: 1592 },
      { name: "肩颈按摩", sales: 1188 }
    ];
  } catch (error) {
    console.error("获取品项图表数据失败:", error);
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getItemList();
  getItemStats();
  getItemChartData();
};

// 重置搜索
const resetSearch = () => {
  searchForm.itemName = "";
  searchForm.itemType = "";
  searchForm.saleDate = null;
  pagination.current = 1;
  getItemList();
  getItemStats();
  getItemChartData();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getItemList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getItemList();
};
</script>

<style scoped>
.customer-item-container {
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

.item-chart {
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
  flex-direction: column;
  gap: 12px;
}

.chart-bar-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.chart-bar-label {
  font-size: 14px;
  color: #606266;
  width: 120px;
  flex-shrink: 0;
}

.chart-bar {
  flex-grow: 1;
  height: 30px;
  background-color: #f0f0f0;
  border-radius: 15px;
  overflow: hidden;
  position: relative;
}

.chart-bar-fill {
  height: 100%;
  background-color: #67c23a;
  border-radius: 15px;
  transition: width 0.5s ease;
}

.chart-bar-value {
  font-size: 14px;
  color: #303133;
  font-weight: bold;
  width: 100px;
  flex-shrink: 0;
  text-align: right;
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
