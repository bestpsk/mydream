<template>
  <div class="report-management-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>报单管理</span>
          <div class="header-buttons">
            <el-button type="primary" @click="handleAddReport">
              <el-icon><Plus /></el-icon>
              新增报单
            </el-button>
            <el-button @click="handleRefresh">
              <el-icon><Refresh /></el-icon>
              刷新
            </el-button>
          </div>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div class="search-bar mb-4">
        <el-form :inline="true" :model="searchForm" class="mb-4">
          <el-form-item label="报单号">
            <el-input
              v-model="searchForm.reportNo"
              placeholder="请输入报单号"
            />
          </el-form-item>
          <el-form-item label="员工姓名">
            <el-input
              v-model="searchForm.employeeName"
              placeholder="请输入员工姓名"
            />
          </el-form-item>
          <el-form-item label="报单状态">
            <el-select v-model="searchForm.status" placeholder="请选择报单状态">
              <el-option label="全部" value="" />
              <el-option label="待上级确认" value="pending_superior" />
              <el-option label="待前台审核" value="pending_front_desk" />
              <el-option label="待财务入账" value="pending_finance" />
              <el-option label="已完成" value="completed" />
              <el-option label="已拒绝" value="rejected" />
            </el-select>
          </el-form-item>
          <el-form-item label="报单时间">
            <el-date-picker
              v-model="searchForm.reportTime"
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

      <!-- 报单列表 -->
      <div class="report-list">
        <el-table v-loading="loading" :data="reportList" style="width: 100%">
          <el-table-column prop="id" label="报单号" />
          <el-table-column prop="employeeName" label="员工姓名" />
          <el-table-column prop="employeeId" label="员工ID" width="100" />
          <el-table-column prop="amount" label="报单金额" />
          <el-table-column prop="description" label="报单描述" />
          <el-table-column prop="status" label="报单状态">
            <template #default="scope">
              <el-tag :type="getStatusType(scope.row.status)">{{
                getStatusText(scope.row.status)
              }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="reportTime" label="报单时间" />
          <el-table-column prop="superiorConfirmTime" label="上级确认时间" />
          <el-table-column prop="frontDeskAuditTime" label="前台审核时间" />
          <el-table-column prop="financeEntryTime" label="财务入账时间" />
          <el-table-column label="操作" width="200">
            <template #default="scope">
              <el-button
                type="primary"
                size="small"
                @click="viewReportDetail(scope.row)"
              >
                查看详情
              </el-button>
              <el-button
                v-if="canHandleReport(scope.row.status)"
                type="success"
                size="small"
                @click="handleReport(scope.row)"
              >
                {{ getHandleButtonText(scope.row.status) }}
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

    <!-- 新增/编辑报单对话框 -->
    <el-dialog
      v-model="reportDialogVisible"
      :title="dialogType === 'add' ? '新增报单' : '编辑报单'"
      width="600px"
    >
      <el-form ref="reportFormRef" :model="reportForm" :rules="reportRules">
        <el-form-item label="员工姓名" prop="employeeId">
          <el-select v-model="reportForm.employeeId" placeholder="请选择员工">
            <el-option
              v-for="employee in employeeList"
              :key="employee.id"
              :label="employee.name"
              :value="employee.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="报单金额" prop="amount">
          <el-input-number v-model="reportForm.amount" :min="0" :step="0.01" />
        </el-form-item>
        <el-form-item label="报单类型" prop="type">
          <el-select v-model="reportForm.type" placeholder="请选择报单类型">
            <el-option label="服务提成" value="service_commission" />
            <el-option label="产品提成" value="product_commission" />
            <el-option label="其他收入" value="other_income" />
          </el-select>
        </el-form-item>
        <el-form-item label="报单描述" prop="description">
          <el-input
            v-model="reportForm.description"
            type="textarea"
            placeholder="请输入报单描述"
          />
        </el-form-item>
        <el-form-item label="附件" prop="attachments">
          <el-upload
            class="upload-demo"
            action="#"
            :on-change="handleAttachmentChange"
            :auto-upload="false"
            multiple
          >
            <el-button type="primary">
              <el-icon><Upload /></el-icon>
              上传附件
            </el-button>
            <template #tip>
              <div class="el-upload__tip">支持上传图片、PDF等文件</div>
            </template>
          </el-upload>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="reportDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleReportConfirm"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>

    <!-- 报单详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="报单详情" width="700px">
      <div v-if="currentReport" class="report-detail">
        <el-card shadow="hover" class="mb-4">
          <template #header>
            <span>基本信息</span>
          </template>
          <div class="grid grid-cols-2 gap-4">
            <div>报单号: {{ currentReport.id }}</div>
            <div>员工姓名: {{ currentReport.employeeName }}</div>
            <div>员工ID: {{ currentReport.employeeId }}</div>
            <div>报单金额: {{ currentReport.amount }}元</div>
            <div>报单类型: {{ getReportTypeText(currentReport.type) }}</div>
            <div>
              报单状态:
              <el-tag :type="getStatusType(currentReport.status)">{{
                getStatusText(currentReport.status)
              }}</el-tag>
            </div>
            <div>报单时间: {{ currentReport.reportTime }}</div>
            <div>报单描述: {{ currentReport.description }}</div>
            <div>
              上级确认时间: {{ currentReport.superiorConfirmTime || "未确认" }}
            </div>
            <div>
              前台审核时间: {{ currentReport.frontDeskAuditTime || "未审核" }}
            </div>
            <div>
              财务入账时间: {{ currentReport.financeEntryTime || "未入账" }}
            </div>
          </div>
        </el-card>

        <el-card
          v-if="
            currentReport.attachments && currentReport.attachments.length > 0
          "
          shadow="hover"
        >
          <template #header>
            <span>附件</span>
          </template>
          <div class="attachments-list">
            <el-link
              v-for="(attachment, index) in currentReport.attachments"
              :key="index"
              :href="attachment.url"
              target="_blank"
            >
              {{ attachment.name }}
            </el-link>
          </div>
        </el-card>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 报单处理对话框 -->
    <el-dialog
      v-model="handleDialogVisible"
      :title="getHandleDialogTitle"
      width="500px"
    >
      <div v-if="handleReportData" class="handle-form">
        <el-form ref="handleFormRef" :model="handleForm" :rules="handleRules">
          <el-form-item label="报单号">
            <el-input v-model="handleReportData.id" readonly />
          </el-form-item>
          <el-form-item label="员工姓名">
            <el-input v-model="handleReportData.employeeName" readonly />
          </el-form-item>
          <el-form-item label="报单金额">
            <el-input v-model="handleReportData.amount" readonly />
          </el-form-item>
          <el-form-item label="处理状态" prop="status">
            <el-radio-group v-model="handleForm.status">
              <el-radio label="approve">通过</el-radio>
              <el-radio label="reject">拒绝</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="处理备注" prop="remark">
            <el-input
              v-model="handleForm.remark"
              type="textarea"
              placeholder="请输入处理备注"
            />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="handleDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleHandleConfirm"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from "vue";
import { Plus, Refresh, Upload } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";

// 搜索表单
const searchForm = reactive({
  reportNo: "",
  employeeName: "",
  status: "",
  reportTime: []
});

// 加载状态
const loading = ref(false);

// 员工列表（模拟数据）
const employeeList = ref([
  { id: 1, name: "张美容师" },
  { id: 2, name: "李美容师" },
  { id: 3, name: "王美容师" },
  { id: 4, name: "赵美容师" },
  { id: 5, name: "钱美容师" }
]);

// 报单列表（模拟数据）
const reportList = ref([
  {
    id: "REP202602030001",
    employeeId: 1,
    employeeName: "张美容师",
    amount: 500,
    type: "service_commission",
    description: "面部护理服务提成",
    status: "pending_superior",
    reportTime: "2026-02-03 10:00:00",
    superiorConfirmTime: null,
    frontDeskAuditTime: null,
    financeEntryTime: null,
    attachments: []
  },
  {
    id: "REP202602030002",
    employeeId: 2,
    employeeName: "李美容师",
    amount: 300,
    type: "product_commission",
    description: "产品销售提成",
    status: "pending_front_desk",
    reportTime: "2026-02-03 11:00:00",
    superiorConfirmTime: "2026-02-03 12:00:00",
    frontDeskAuditTime: null,
    financeEntryTime: null,
    attachments: []
  },
  {
    id: "REP202602030003",
    employeeId: 3,
    employeeName: "王美容师",
    amount: 200,
    type: "other_income",
    description: "其他收入",
    status: "pending_finance",
    reportTime: "2026-02-03 13:00:00",
    superiorConfirmTime: "2026-02-03 14:00:00",
    frontDeskAuditTime: "2026-02-03 15:00:00",
    financeEntryTime: null,
    attachments: []
  },
  {
    id: "REP202602030004",
    employeeId: 4,
    employeeName: "赵美容师",
    amount: 600,
    type: "service_commission",
    description: "身体按摩服务提成",
    status: "completed",
    reportTime: "2026-02-03 14:00:00",
    superiorConfirmTime: "2026-02-03 15:00:00",
    frontDeskAuditTime: "2026-02-03 16:00:00",
    financeEntryTime: "2026-02-03 17:00:00",
    attachments: []
  }
]);

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 4
});

// 报单表单
const reportDialogVisible = ref(false);
const dialogType = ref("add");
const reportFormRef = ref<FormInstance>();
const reportForm = reactive({
  id: "",
  employeeId: 0,
  amount: 0,
  type: "service_commission",
  description: "",
  attachments: []
});

const reportRules = reactive<FormRules>({
  employeeId: [{ required: true, message: "请选择员工", trigger: "change" }],
  amount: [{ required: true, message: "请输入报单金额", trigger: "blur" }],
  type: [{ required: true, message: "请选择报单类型", trigger: "change" }],
  description: [{ required: true, message: "请输入报单描述", trigger: "blur" }]
});

// 报单详情
const detailDialogVisible = ref(false);
const currentReport = ref<any>(null);

// 报单处理
const handleDialogVisible = ref(false);
const handleReportData = ref<any>(null);
const handleFormRef = ref<FormInstance>();
const handleForm = reactive({
  status: "approve",
  remark: ""
});

const handleRules = reactive<FormRules>({
  status: [{ required: true, message: "请选择处理状态", trigger: "change" }],
  remark: [{ required: true, message: "请输入处理备注", trigger: "blur" }]
});

// 初始化数据
onMounted(() => {
  getReportList();
});

// 获取报单列表
const getReportList = () => {
  loading.value = true;
  // 模拟API请求
  setTimeout(() => {
    loading.value = false;
  }, 500);
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getReportList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.reportNo = "";
  searchForm.employeeName = "";
  searchForm.status = "";
  searchForm.reportTime = [];
  pagination.current = 1;
  getReportList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getReportList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getReportList();
};

// 刷新
const handleRefresh = () => {
  getReportList();
};

// 新增报单
const handleAddReport = () => {
  dialogType.value = "add";
  resetReportForm();
  reportDialogVisible.value = true;
};

// 重置报单表单
const resetReportForm = () => {
  reportForm.id = "";
  reportForm.employeeId = 0;
  reportForm.amount = 0;
  reportForm.type = "service_commission";
  reportForm.description = "";
  reportForm.attachments = [];
};

// 处理附件变化
const handleAttachmentChange = (file: any, fileList: any[]) => {
  reportForm.attachments = fileList;
};

// 确认报单
const handleReportConfirm = async () => {
  if (!reportFormRef.value) return;
  await reportFormRef.value.validate((valid: boolean) => {
    if (valid) {
      // 模拟API请求
      setTimeout(() => {
        if (dialogType.value === "add") {
          // 生成报单号
          const reportId = `REP${new Date().toISOString().slice(0, 10).replace(/-/g, "")}${String(reportList.value.length + 1).padStart(4, "0")}`;

          // 查找员工名称
          const employee = employeeList.value.find(
            e => e.id === reportForm.employeeId
          );
          const employeeName = employee ? employee.name : "";

          // 添加报单
          reportList.value.push({
            id: reportId,
            employeeId: reportForm.employeeId,
            employeeName: employeeName,
            amount: reportForm.amount,
            type: reportForm.type,
            description: reportForm.description,
            status: "pending_superior",
            reportTime: new Date().toISOString().slice(0, 19).replace("T", " "),
            superiorConfirmTime: null,
            frontDeskAuditTime: null,
            financeEntryTime: null,
            attachments: reportForm.attachments
          });
        } else {
          // 更新报单
          const index = reportList.value.findIndex(
            item => item.id === reportForm.id
          );
          if (index !== -1) {
            reportList.value[index].employeeId = reportForm.employeeId;
            reportList.value[index].amount = reportForm.amount;
            reportList.value[index].type = reportForm.type;
            reportList.value[index].description = reportForm.description;
            reportList.value[index].attachments = reportForm.attachments;
          }
        }

        reportDialogVisible.value = false;
        ElMessage.success(
          dialogType.value === "add" ? "报单添加成功" : "报单更新成功"
        );
      }, 500);
    }
  });
};

// 查看报单详情
const viewReportDetail = (report: any) => {
  currentReport.value = report;
  detailDialogVisible.value = true;
};

// 检查是否可以处理报单
const canHandleReport = (status: string) => {
  // 根据当前用户角色判断是否可以处理
  // 这里简化处理，假设当前用户可以处理所有待处理状态的报单
  return ["pending_superior", "pending_front_desk", "pending_finance"].includes(
    status
  );
};

// 获取处理按钮文本
const getHandleButtonText = (status: string) => {
  const textMap: Record<string, string> = {
    pending_superior: "上级确认",
    pending_front_desk: "前台审核",
    pending_finance: "财务入账"
  };
  return textMap[status] || "处理";
};

// 获取处理对话框标题
const getHandleDialogTitle = computed(() => {
  if (!handleReportData.value) return "报单处理";
  return getHandleButtonText(handleReportData.value.status);
});

// 处理报单
const handleReport = (report: any) => {
  handleReportData.value = report;
  handleForm.status = "approve";
  handleForm.remark = "";
  handleDialogVisible.value = true;
};

// 确认处理
const handleHandleConfirm = async () => {
  if (!handleFormRef.value) return;
  await handleFormRef.value.validate((valid: boolean) => {
    if (valid) {
      // 模拟API请求
      setTimeout(() => {
        // 更新报单状态
        const index = reportList.value.findIndex(
          item => item.id === handleReportData.value.id
        );
        if (index !== -1) {
          if (handleForm.status === "approve") {
            // 状态流转
            const statusMap: Record<string, string> = {
              pending_superior: "pending_front_desk",
              pending_front_desk: "pending_finance",
              pending_finance: "completed"
            };

            // 更新状态
            reportList.value[index].status =
              statusMap[handleReportData.value.status] ||
              handleReportData.value.status;

            // 更新时间
            const now = new Date().toISOString().slice(0, 19).replace("T", " ");
            if (handleReportData.value.status === "pending_superior") {
              reportList.value[index].superiorConfirmTime = now;
            } else if (handleReportData.value.status === "pending_front_desk") {
              reportList.value[index].frontDeskAuditTime = now;
            } else if (handleReportData.value.status === "pending_finance") {
              reportList.value[index].financeEntryTime = now;
            }
          } else {
            // 拒绝
            reportList.value[index].status = "rejected";
          }
        }

        handleDialogVisible.value = false;
        ElMessage.success("报单处理成功");
      }, 500);
    }
  });
};

// 获取状态文本
const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    pending_superior: "待上级确认",
    pending_front_desk: "待前台审核",
    pending_finance: "待财务入账",
    completed: "已完成",
    rejected: "已拒绝"
  };
  return statusMap[status] || status;
};

// 获取状态类型
const getStatusType = (status: string) => {
  const typeMap: Record<string, string> = {
    pending_superior: "warning",
    pending_front_desk: "info",
    pending_finance: "primary",
    completed: "success",
    rejected: "danger"
  };
  return typeMap[status] || "";
};

// 获取报单类型文本
const getReportTypeText = (type: string) => {
  const typeMap: Record<string, string> = {
    service_commission: "服务提成",
    product_commission: "产品提成",
    other_income: "其他收入"
  };
  return typeMap[type] || type;
};
</script>

<style scoped>
.report-management-container {
  min-height: calc(100vh - 120px);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.header-buttons {
  display: flex;
  gap: 10px;
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

.report-detail {
  max-height: 500px;
  overflow-y: auto;
}

.handle-form {
  max-height: 400px;
  overflow-y: auto;
}

.attachments-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.upload-demo {
  margin-bottom: 20px;
}
</style>
