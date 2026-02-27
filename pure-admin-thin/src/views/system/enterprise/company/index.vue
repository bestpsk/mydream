<template>
  <div class="company-container">
    <el-card class="main-card">
      <template #header>
        <div class="card-header">
          <span>公司管理</span>
        </div>
      </template>

      <!-- 搜索栏 -->
      <el-card v-if="hasAuth('company:view')" class="mb-4" shadow="never">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <span class="text-sm font-bold">公司名称</span>
            <el-input
              v-model="searchForm.companyName"
              placeholder="请输入公司名称"
              clearable
              style="width: 200px"
              @clear="handleSearch"
              @keyup.enter="handleSearch"
            />
            <span class="text-sm font-bold">企业类型</span>
            <el-select
              v-model="searchForm.companyType"
              placeholder="请选择企业类型"
              clearable
              style="width: 160px"
            >
              <el-option label="美容" value="美容" />
              <el-option label="美发" value="美发" />
              <el-option label="综合" value="综合" />
              <el-option label="养生" value="养生" />
            </el-select>
            <el-button type="primary" @click="handleSearch">
              <el-icon><Search /></el-icon>
              搜索
            </el-button>
            <el-button @click="resetSearch">
              <el-icon><Refresh /></el-icon>
              重置
            </el-button>
          </div>
          <el-button
            v-if="hasAuth('company:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增公司
          </el-button>
        </div>
      </el-card>

      <!-- 公司列表 -->
      <div v-if="hasAuth('company:view')" class="company-list">
        <el-table v-loading="loading" :data="companyList" style="width: 100%">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="companyName" label="公司名称" />
          <el-table-column prop="code" label="公司编码" width="120" />
          <el-table-column prop="boss" label="老板" />
          <el-table-column prop="phone" label="电话" />
          <el-table-column prop="address" label="地址" />
          <el-table-column prop="companyType" label="企业类型" />
          <el-table-column prop="storeCount" label="门店数量" />
          <el-table-column prop="servicePerson" label="服务人" />
          <el-table-column label="操作" width="180">
            <template #default="scope">
              <el-button
                v-if="hasAuth('company:edit')"
                type="primary"
                size="small"
                @click="handleEdit(scope.row)"
              >
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('company:delete')"
                type="danger"
                size="small"
                @click="handleDelete(scope.row.id)"
              >
                <el-icon><Delete /></el-icon>
                删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>

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
      </div>
      <!-- 无权限提示 -->
      <div v-else class="no-permission">
        <el-empty description="无权限查看数据" />
      </div>
    </el-card>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增公司' : '编辑公司'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules">
        <el-form-item label="公司名称" prop="companyName">
          <el-input v-model="form.companyName" placeholder="请输入公司名称" />
        </el-form-item>
        <el-form-item label="公司编码" prop="code">
          <el-input v-model="form.code" placeholder="请输入公司编码" />
        </el-form-item>
        <el-form-item label="老板" prop="boss">
          <el-input v-model="form.boss" placeholder="请输入老板姓名" />
        </el-form-item>
        <el-form-item label="电话" prop="phone">
          <el-input v-model="form.phone" placeholder="请输入联系电话" />
        </el-form-item>
        <el-form-item label="地址" prop="address">
          <el-input v-model="form.address" placeholder="请输入公司地址" />
        </el-form-item>
        <el-form-item label="企业类型" prop="companyType">
          <el-select v-model="form.companyType" placeholder="请选择企业类型">
            <el-option label="美容" value="美容" />
            <el-option label="美发" value="美发" />
            <el-option label="综合" value="综合" />
            <el-option label="养生" value="养生" />
          </el-select>
        </el-form-item>
        <el-form-item label="门店数量" prop="storeCount">
          <el-input-number
            v-model="form.storeCount"
            :min="0"
            placeholder="请输入门店数量"
          />
        </el-form-item>
        <el-form-item label="服务人" prop="servicePerson">
          <el-input
            v-model="form.servicePerson"
            placeholder="请输入服务人姓名"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button
            v-if="
              dialogType === 'add'
                ? hasAuth('company:add')
                : hasAuth('company:edit')
            "
            type="primary"
            @click="handleSubmit"
          >
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import { Plus, Edit, Delete, Search, Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import { hasAuth } from "@/router/utils";
import {
  getCompanies,
  addCompany,
  updateCompany,
  deleteCompany
} from "@/api/enterprise";

// 加载状态
const loading = ref(false);

// 搜索表单
const searchForm = reactive({
  companyName: "",
  companyType: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 公司列表
const companyList = ref([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 表单数据
const form = reactive({
  id: 0,
  companyName: "",
  code: "",
  boss: "",
  phone: "",
  address: "",
  companyType: "",
  storeCount: 0,
  servicePerson: ""
});

// 表单验证规则
const rules = reactive<FormRules>({
  companyName: [{ required: true, message: "请输入公司名称", trigger: "blur" }],
  code: [{ required: true, message: "请输入公司编码", trigger: "blur" }],
  boss: [{ required: true, message: "请输入老板姓名", trigger: "blur" }],
  phone: [{ required: true, message: "请输入联系电话", trigger: "blur" }],
  address: [{ required: true, message: "请输入公司地址", trigger: "blur" }],
  companyType: [
    { required: true, message: "请选择企业类型", trigger: "change" }
  ],
  storeCount: [
    { required: true, message: "请输入门店数量", trigger: "change" },
    {
      validator: (rule, value, callback) => {
        const num = Number(value);
        if (isNaN(num)) {
          callback(new Error("门店数量必须是数字"));
        } else if (num < 0) {
          callback(new Error("门店数量不能为负数"));
        } else {
          callback();
        }
      },
      trigger: "change"
    }
  ],
  servicePerson: [
    { required: true, message: "请输入服务人姓名", trigger: "blur" }
  ]
});

// 初始化数据
onMounted(() => {
  if (hasAuth("company:view")) {
    getCompanyList();
  }
});

/**
 * 获取公司列表
 * 从后端API获取数据并更新本地状态
 */
const getCompanyList = () => {
  if (!hasAuth("company:view")) {
    return;
  }

  loading.value = true;
  // 从后端API获取数据
  getCompanies({
    ...searchForm,
    page: pagination.current,
    page_size: pagination.pageSize
  })
    .then(response => {
      loading.value = false;
      if (response?.code === 200) {
        companyList.value = response.data || [];
        pagination.total = companyList.value.length;
      } else {
        ElMessage.error(response?.message || "获取公司列表失败");
        // 重置数据，避免显示旧数据
        companyList.value = [];
        pagination.total = 0;
      }
    })
    .catch(error => {
      loading.value = false;
      ElMessage.error("获取公司列表失败，请检查网络连接");
      console.error("getCompanyList error:", error);
      // 重置数据，避免显示旧数据
      companyList.value = [];
      pagination.total = 0;
    });
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getCompanyList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.companyName = "";
  searchForm.companyType = "";
  pagination.current = 1;
  getCompanyList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getCompanyList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getCompanyList();
};

// 新增公司
const handleAdd = () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  form.companyName = "";
  form.code = "";
  form.boss = "";
  form.phone = "";
  form.address = "";
  form.companyType = "";
  form.storeCount = 0;
  form.servicePerson = "";
  dialogVisible.value = true;
};

// 编辑公司
const handleEdit = (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.id;
  form.companyName = row.companyName;
  form.code = row.code;
  form.boss = row.boss;
  form.phone = row.phone;
  form.address = row.address;
  form.companyType = row.companyType;
  form.storeCount = row.storeCount;
  form.servicePerson = row.servicePerson;
  dialogVisible.value = true;
};

/**
 * 删除公司
 * @param id 公司ID
 * 弹出确认对话框，用户确认后调用后端API删除公司
 */
const handleDelete = (id: number) => {
  if (!id) {
    ElMessage.warning("公司ID不能为空");
    return;
  }

  ElMessageBox.confirm("确定要删除该公司吗？此操作不可恢复", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      loading.value = true;
      // 调用后端API删除公司
      deleteCompany(id)
        .then(response => {
          loading.value = false;
          if (response?.code === 200) {
            ElMessage.success(response?.message || "删除成功");
            getCompanyList();
          } else {
            ElMessage.error(response?.message || "删除失败");
          }
        })
        .catch(error => {
          loading.value = false;
          ElMessage.error("删除失败，请检查网络连接");
          console.error("handleDelete error:", error);
        });
    })
    .catch(() => {
      // 取消删除
    });
};

/**
 * 提交表单
 * 验证表单数据，然后调用后端API新增或编辑公司
 */
const handleSubmit = async () => {
  if (!formRef.value) return;
  await formRef.value.validate((valid: boolean) => {
    if (valid) {
      loading.value = true;
      // 直接使用camelCase字段名，与后端保持一致
      const request =
        dialogType.value === "add"
          ? addCompany(form)
          : updateCompany(form.id, form);

      request
        .then(response => {
          loading.value = false;
          if (response?.code === 200) {
            ElMessage.success(
              response?.message ||
                (dialogType.value === "add" ? "新增成功" : "编辑成功")
            );
            dialogVisible.value = false;
            getCompanyList();
          } else {
            ElMessage.error(
              response?.message ||
                (dialogType.value === "add" ? "新增失败" : "编辑失败")
            );
          }
        })
        .catch(error => {
          loading.value = false;
          ElMessage.error(
            dialogType.value === "add"
              ? "新增失败，请检查网络连接"
              : "编辑失败，请检查网络连接"
          );
          console.error("handleSubmit error:", error);
        });
    }
  });
};
</script>

<style scoped>
.company-container {
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.main-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.main-card :deep(.el-card__body) {
  padding: 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
  margin-bottom: 16px;
}

/* 公司列表区域 */
.company-list {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 公司列表表格 */
.company-list .el-table {
  flex: 1;
  min-height: 0;
}

.company-list .el-table__body-wrapper {
  overflow: auto;
}

.pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}
</style>
