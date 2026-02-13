<template>
  <div class="customer-visit-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>客户到店管理</span>
          <el-button
            v-if="hasAuth('customer:visit:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增到店记录
          </el-button>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div v-if="hasAuth('customer:visit:view')" class="search-bar mb-4">
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
          <el-form-item label="到店日期">
            <el-date-picker
              v-model="searchForm.visitDate"
              type="date"
              placeholder="请选择到店日期"
              style="width: 180px"
            />
          </el-form-item>
          <el-form-item label="服务项目">
            <el-input
              v-model="searchForm.serviceItem"
              placeholder="请输入服务项目"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="resetSearch">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 到店记录表格 -->
      <div v-if="hasAuth('customer:visit:view')">
        <el-table
          v-loading="loading"
          :data="visitList"
          border
          style="width: 100%"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="customerName" label="客户姓名" />
          <el-table-column prop="phone" label="手机号码" />
          <el-table-column prop="visitDate" label="到店日期" width="150" />
          <el-table-column prop="visitTime" label="到店时间" width="120" />
          <el-table-column prop="leaveTime" label="离店时间" width="120" />
          <el-table-column prop="serviceItem" label="服务项目" />
          <el-table-column prop="serviceStaff" label="服务人员" />
          <el-table-column prop="remark" label="备注" min-width="150" />
          <el-table-column label="操作" width="200">
            <template #default="scope">
              <el-button
                v-if="hasAuth('customer:visit:edit')"
                type="primary"
                size="small"
                @click="handleEdit(scope.row)"
              >
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('customer:visit:delete')"
                type="danger"
                size="small"
                @click="handleDelete(scope.row.id)"
              >
                删除
              </el-button>
            </template>
          </el-table-column>
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

      <!-- 新增/编辑对话框 -->
      <el-dialog
        v-model="dialogVisible"
        :title="dialogType === 'add' ? '新增到店记录' : '编辑到店记录'"
        width="600px"
      >
        <el-form ref="formRef" :model="form" :rules="rules">
          <el-form-item label="客户姓名" prop="customerName">
            <el-input
              v-model="form.customerName"
              placeholder="请输入客户姓名"
            />
          </el-form-item>
          <el-form-item label="手机号码" prop="phone">
            <el-input v-model="form.phone" placeholder="请输入手机号码" />
          </el-form-item>
          <el-form-item label="到店日期" prop="visitDate">
            <el-date-picker
              v-model="form.visitDate"
              type="date"
              placeholder="请选择到店日期"
              style="width: 100%"
            />
          </el-form-item>
          <el-form-item label="到店时间" prop="visitTime">
            <el-time-picker
              v-model="form.visitTime"
              placeholder="请选择到店时间"
              style="width: 100%"
              value-format="HH:mm"
            />
          </el-form-item>
          <el-form-item label="离店时间" prop="leaveTime">
            <el-time-picker
              v-model="form.leaveTime"
              placeholder="请选择离店时间"
              style="width: 100%"
              value-format="HH:mm"
            />
          </el-form-item>
          <el-form-item label="服务项目" prop="serviceItem">
            <el-input v-model="form.serviceItem" placeholder="请输入服务项目" />
          </el-form-item>
          <el-form-item label="服务人员" prop="serviceStaff">
            <el-input
              v-model="form.serviceStaff"
              placeholder="请输入服务人员"
            />
          </el-form-item>
          <el-form-item label="备注" prop="remark">
            <el-input
              v-model="form.remark"
              type="textarea"
              placeholder="请输入备注信息"
              :rows="3"
            />
          </el-form-item>
        </el-form>
        <template #footer>
          <span class="dialog-footer">
            <el-button @click="dialogVisible = false">取消</el-button>
            <el-button type="primary" @click="handleSubmit">确定</el-button>
          </span>
        </template>
      </el-dialog>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import { Plus } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import { http } from "@/utils/http";
import { hasAuth } from "@/router/utils";

// 加载状态
const loading = ref(false);

// 搜索表单
const searchForm = reactive({
  customerName: "",
  phone: "",
  visitDate: null,
  serviceItem: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 到店记录列表
const visitList = ref<any[]>([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 表单数据
const form = reactive({
  id: 0,
  customerName: "",
  phone: "",
  visitDate: null,
  visitTime: "",
  leaveTime: "",
  serviceItem: "",
  serviceStaff: "",
  remark: ""
});

// 表单验证规则
const rules = reactive<FormRules>({
  customerName: [
    { required: true, message: "请输入客户姓名", trigger: "blur" }
  ],
  phone: [
    { required: true, message: "请输入手机号码", trigger: "blur" },
    {
      pattern: /^1[3-9]\d{9}$/,
      message: "请输入正确的手机号码",
      trigger: "blur"
    }
  ],
  visitDate: [{ required: true, message: "请选择到店日期", trigger: "blur" }],
  visitTime: [{ required: true, message: "请选择到店时间", trigger: "blur" }],
  serviceItem: [{ required: true, message: "请输入服务项目", trigger: "blur" }],
  serviceStaff: [{ required: true, message: "请输入服务人员", trigger: "blur" }]
});

// 初始化数据
onMounted(() => {
  if (hasAuth("customer:visit:view")) {
    getVisitList();
  }
});

// 获取到店记录列表
const getVisitList = async () => {
  loading.value = true;
  try {
    // 构建请求参数
    const params = {
      page: pagination.current,
      pageSize: pagination.pageSize,
      customerName: searchForm.customerName || undefined,
      phone: searchForm.phone || undefined,
      visitDate: searchForm.visitDate
        ? searchForm.visitDate.toISOString().split("T")[0]
        : undefined,
      serviceItem: searchForm.serviceItem || undefined
    };

    // 这里使用模拟数据，实际项目中需要替换为真实的API调用
    // const response = await http.request("get", "/api/customer/visit", { params });
    // if (response.code === 200) {
    //   visitList.value = response.data.list;
    //   pagination.total = response.data.total;
    // }

    // 模拟数据
    const mockData = {
      list: [
        {
          id: 1,
          customerName: "张三",
          phone: "13800138000",
          visitDate: "2026-01-01",
          visitTime: "10:00",
          leaveTime: "12:00",
          serviceItem: "面部护理",
          serviceStaff: "李护士",
          remark: "首次到店"
        },
        {
          id: 2,
          customerName: "李四",
          phone: "13900139000",
          visitDate: "2026-01-02",
          visitTime: "14:00",
          leaveTime: "16:00",
          serviceItem: "身体按摩",
          serviceStaff: "王技师",
          remark: "定期护理"
        },
        {
          id: 3,
          customerName: "王五",
          phone: "13700137000",
          visitDate: "2026-01-03",
          visitTime: "09:00",
          leaveTime: "11:00",
          serviceItem: "美发造型",
          serviceStaff: "张造型师",
          remark: "染发+烫发"
        }
      ],
      total: 3
    };

    visitList.value = mockData.list;
    pagination.total = mockData.total;
  } catch (error) {
    console.error("获取到店记录失败:", error);
    ElMessage.error("获取到店记录失败");
  } finally {
    loading.value = false;
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getVisitList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.customerName = "";
  searchForm.phone = "";
  searchForm.visitDate = null;
  searchForm.serviceItem = "";
  pagination.current = 1;
  getVisitList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getVisitList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getVisitList();
};

// 新增到店记录
const handleAdd = () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  form.customerName = "";
  form.phone = "";
  form.visitDate = null;
  form.visitTime = null;
  form.leaveTime = null;
  form.serviceItem = "";
  form.serviceStaff = "";
  form.remark = "";
  dialogVisible.value = true;
};

// 编辑到店记录
const handleEdit = (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.id;
  form.customerName = row.customerName;
  form.phone = row.phone;
  form.visitDate = row.visitDate ? new Date(row.visitDate) : null;
  form.visitTime = row.visitTime || "";
  form.leaveTime = row.leaveTime || "";
  form.serviceItem = row.serviceItem;
  form.serviceStaff = row.serviceStaff;
  form.remark = row.remark || "";
  dialogVisible.value = true;
};

// 删除到店记录
const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该到店记录吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        // 实际项目中需要替换为真实的API调用
        // const response = await http.request("delete", `/api/customer/visit/${id}`);
        // if (response.code === 200) {
        //   ElMessage.success("删除成功");
        //   getVisitList();
        // }

        // 模拟删除成功
        ElMessage.success("删除成功");
        getVisitList();
      } catch (error) {
        console.error("删除到店记录失败:", error);
        ElMessage.error("删除到店记录失败");
      } finally {
        loading.value = false;
      }
    })
    .catch(() => {
      // 取消删除
    });
};

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid: boolean) => {
    if (valid) {
      loading.value = true;
      try {
        // 准备提交数据
        const submitData = {
          customerName: form.customerName,
          phone: form.phone,
          visitDate: form.visitDate
            ? form.visitDate.toISOString().split("T")[0]
            : "",
          visitTime: form.visitTime,
          leaveTime: form.leaveTime,
          serviceItem: form.serviceItem,
          serviceStaff: form.serviceStaff,
          remark: form.remark
        };

        // 实际项目中需要替换为真实的API调用
        // let response;
        // if (dialogType.value === "add") {
        //   response = await http.request("post", "/api/customer/visit", { data: submitData });
        // } else {
        //   response = await http.request("put", `/api/customer/visit/${form.id}`, { data: submitData });
        // }
        // if (response.code === 200) {
        //   ElMessage.success(dialogType.value === "add" ? "新增成功" : "编辑成功");
        //   dialogVisible.value = false;
        //   getVisitList();
        // }

        // 模拟提交成功
        ElMessage.success(dialogType.value === "add" ? "新增成功" : "编辑成功");
        dialogVisible.value = false;
        getVisitList();
      } catch (error) {
        console.error("提交失败:", error);
        ElMessage.error(dialogType.value === "add" ? "新增失败" : "编辑失败");
      } finally {
        loading.value = false;
      }
    }
  });
};
</script>

<style scoped>
.customer-visit-container {
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

.pagination {
  display: flex;
  justify-content: flex-end;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}

.no-permission {
  padding: 40px 0;
  text-align: center;
}
</style>
