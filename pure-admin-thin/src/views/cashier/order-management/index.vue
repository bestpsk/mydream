<template>
  <div class="order-management-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>订单管理</span>
          <el-button type="primary" @click="handleRefresh">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div class="search-bar mb-4">
        <el-form :inline="true" :model="searchForm" class="mb-4">
          <el-form-item label="订单号">
            <el-input v-model="searchForm.orderNo" placeholder="请输入订单号" />
          </el-form-item>
          <el-form-item label="客户姓名">
            <el-input
              v-model="searchForm.customerName"
              placeholder="请输入客户姓名"
            />
          </el-form-item>
          <el-form-item label="订单状态">
            <el-select v-model="searchForm.status" placeholder="请选择订单状态">
              <el-option label="全部" value="" />
              <el-option label="待支付" value="pending_payment" />
              <el-option label="已支付" value="paid" />
              <el-option label="已完成" value="completed" />
              <el-option label="已取消" value="cancelled" />
            </el-select>
          </el-form-item>
          <el-form-item label="下单时间">
            <el-date-picker
              v-model="searchForm.orderTime"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="resetSearch">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 订单列表 -->
      <div class="order-list">
        <el-table v-loading="loading" :data="orderList" style="width: 100%">
          <el-table-column prop="id" label="订单号" />
          <el-table-column prop="customerName" label="客户姓名" />
          <el-table-column prop="customerPhone" label="客户手机号" />
          <el-table-column prop="totalAmount" label="订单金额" />
          <el-table-column prop="paymentMethod" label="支付方式" />
          <el-table-column prop="status" label="订单状态">
            <template #default="scope">
              <el-tag :type="getStatusType(scope.row.status)">{{
                getStatusText(scope.row.status)
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="orderTime" label="下单时间" />
          <el-table-column prop="paidTime" label="支付时间" />
          <el-table-column label="操作" width="200">
            <template #default="scope">
              <el-button
                type="primary"
                size="small"
                @click="viewOrderDetail(scope.row)"
              >
                查看详情
              </el-button>
              <el-button
                v-if="canAuditOrder(scope.row.status)"
                type="success"
                size="small"
                @click="auditOrder(scope.row)"
              >
                审核
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <!-- 分页 -->
      <div class="pagination">
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
    </el-card>

    <!-- 订单详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="订单详情" width="700px">
      <div v-if="currentOrder" class="order-detail">
        <el-card shadow="hover" class="mb-4">
          <template #header>
            <span>基本信息</span>
          </template>
          <div class="grid grid-cols-2 gap-4">
            <div>订单号: {{ currentOrder.id }}</div>
            <div>客户姓名: {{ currentOrder.customerName }}</div>
            <div>客户手机号: {{ currentOrder.customerPhone }}</div>
            <div>下单时间: {{ currentOrder.orderTime }}</div>
            <div>支付方式: {{ currentOrder.paymentMethod }}</div>
            <div>
              订单状态:
              <el-tag :type="getStatusType(currentOrder.status)">{{
                getStatusText(currentOrder.status)
              }}</el-tag>
            </div>
            <div>订单金额: {{ currentOrder.totalAmount }}元</div>
            <div>支付时间: {{ currentOrder.paidTime || "未支付" }}</div>
          </div>
        </el-card>

        <el-card shadow="hover">
          <template #header>
            <span>服务项目</span>
          </template>
          <el-table :data="currentOrder.services" style="width: 100%">
            <el-table-column prop="name" label="服务名称" />
            <el-table-column prop="price" label="价格" />
            <el-table-column prop="quantity" label="数量" />
            <el-table-column prop="total" label="小计" />
          </el-table>
        </el-card>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 订单审核对话框 -->
    <el-dialog v-model="auditDialogVisible" title="订单审核" width="500px">
      <div v-if="auditOrderData" class="audit-form">
        <el-form ref="auditFormRef" :model="auditForm" :rules="auditRules">
          <el-form-item label="订单号">
            <el-input v-model="auditOrderData.id" readonly />
          </el-form-item>
          <el-form-item label="客户姓名">
            <el-input v-model="auditOrderData.customerName" readonly />
          </el-form-item>
          <el-form-item label="订单金额">
            <el-input v-model="auditOrderData.totalAmount" readonly />
          </el-form-item>
          <el-form-item label="审核状态" prop="status">
            <el-radio-group v-model="auditForm.status">
              <el-radio label="completed">通过</el-radio>
              <el-radio label="cancelled">拒绝</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="审核备注" prop="remark">
            <el-input
              v-model="auditForm.remark"
              type="textarea"
              placeholder="请输入审核备注"
            />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="auditDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleAuditConfirm">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import { Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";

// 搜索表单
const searchForm = reactive({
  orderNo: "",
  customerName: "",
  status: "",
  orderTime: []
});

// 加载状态
const loading = ref(false);

// 订单列表（模拟数据）
const orderList = ref([
  {
    id: "ORD202602030001",
    customerName: "张三",
    customerPhone: "13800138001",
    totalAmount: 298,
    paymentMethod: "支付宝",
    status: "pending_payment",
    orderTime: "2026-02-03 10:00:00",
    paidTime: null,
    services: [{ name: "面部护理", price: 298, quantity: 1, total: 298 }]
  },
  {
    id: "ORD202602030002",
    customerName: "李四",
    customerPhone: "13800138002",
    totalAmount: 696,
    paymentMethod: "微信支付",
    status: "paid",
    orderTime: "2026-02-03 11:00:00",
    paidTime: "2026-02-03 11:05:00",
    services: [
      { name: "身体按摩", price: 398, quantity: 1, total: 398 },
      { name: "美甲", price: 198, quantity: 1, total: 198 },
      { name: "美容咨询", price: 98, quantity: 1, total: 98 }
    ]
  },
  {
    id: "ORD202602030003",
    customerName: "王五",
    customerPhone: "13800138003",
    totalAmount: 268,
    paymentMethod: "现金",
    status: "completed",
    orderTime: "2026-02-03 12:00:00",
    paidTime: "2026-02-03 12:05:00",
    services: [{ name: "美发", price: 268, quantity: 1, total: 268 }]
  },
  {
    id: "ORD202602030004",
    customerName: "赵六",
    customerPhone: "13800138004",
    totalAmount: 596,
    paymentMethod: "会员卡",
    status: "cancelled",
    orderTime: "2026-02-03 13:00:00",
    paidTime: null,
    services: [{ name: "面部护理", price: 298, quantity: 2, total: 596 }]
  }
]);

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 4
});

// 订单详情
const detailDialogVisible = ref(false);
const currentOrder = ref<any>(null);

// 订单审核
const auditDialogVisible = ref(false);
const auditOrderData = ref<any>(null);
const auditFormRef = ref<FormInstance>();
const auditForm = reactive({
  status: "completed",
  remark: ""
});

const auditRules = reactive<FormRules>({
  status: [{ required: true, message: "请选择审核状态", trigger: "change" }],
  remark: [{ required: true, message: "请输入审核备注", trigger: "blur" }]
});

// 初始化数据
onMounted(() => {
  getOrderList();
});

// 获取订单列表
const getOrderList = () => {
  loading.value = true;
  // 模拟API请求
  setTimeout(() => {
    loading.value = false;
    // 这里可以根据搜索条件过滤数据
  }, 500);
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getOrderList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.orderNo = "";
  searchForm.customerName = "";
  searchForm.status = "";
  searchForm.orderTime = [];
  pagination.current = 1;
  getOrderList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getOrderList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getOrderList();
};

// 刷新
const handleRefresh = () => {
  getOrderList();
};

// 获取状态文本
const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    pending_payment: "待支付",
    paid: "已支付",
    completed: "已完成",
    cancelled: "已取消"
  };
  return statusMap[status] || status;
};

// 获取状态类型
const getStatusType = (status: string) => {
  const typeMap: Record<string, string> = {
    pending_payment: "warning",
    paid: "info",
    completed: "success",
    cancelled: "danger"
  };
  return typeMap[status] || "";
};

// 查看订单详情
const viewOrderDetail = (row: any) => {
  currentOrder.value = row;
  detailDialogVisible.value = true;
};

// 检查是否可以审核订单
const canAuditOrder = (status: string) => {
  // 只有已支付的订单可以审核
  return status === "paid";
};

// 审核订单
const auditOrder = (row: any) => {
  auditOrderData.value = row;
  auditForm.status = "completed";
  auditForm.remark = "";
  auditDialogVisible.value = true;
};

// 确认审核
const handleAuditConfirm = async () => {
  if (!auditFormRef.value) return;
  await auditFormRef.value.validate((valid: boolean) => {
    if (valid) {
      // 模拟API请求
      setTimeout(() => {
        // 更新订单状态
        const index = orderList.value.findIndex(
          item => item.id === auditOrderData.value.id
        );
        if (index !== -1) {
          orderList.value[index].status = auditForm.status;
        }

        auditDialogVisible.value = false;
        ElMessage.success("订单审核成功");
      }, 500);
    }
  });
};
</script>

<style scoped>
.order-management-container {
  min-height: calc(100vh - 120px);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
  margin-bottom: 20px;
}

.pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}

.order-detail {
  max-height: 500px;
  overflow-y: auto;
}

.audit-form {
  max-height: 400px;
  overflow-y: auto;
}
</style>
