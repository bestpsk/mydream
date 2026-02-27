<!--
  供应商管理页面
  
  功能说明：
  管理供应商信息，包括供应商名称、联系人、联系电话、地址、银行账户、预存余额、配送余额等
  
  页面结构：
  - 搜索栏：按供应商品牌、联系人搜索
  - 数据表格：展示供应商列表
  - 操作按钮：新增、编辑、删除供应商
  
  权限控制：
  - project:supplier:view - 查看供应商
  - project:supplier:add - 新增供应商
  - project:supplier:edit - 编辑供应商
  - project:supplier:delete - 删除供应商
  
  数据表：
  - card_supplier - 供应商表
-->
<template>
  <div class="supplier-container">
    <el-card class="h-full flex flex-col">
      <!-- 搜索栏和操作按钮区域 -->
      <el-card class="mb-4" shadow="never">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <span class="text-sm font-bold">供应商品牌</span>
            <el-input
              v-model="searchForm.supplierName"
              placeholder="请输入供应商品牌"
              clearable
              style="width: 200px"
              @clear="handleSearch"
              @keyup.enter="handleSearch"
            />
            <span class="text-sm font-bold">联系人</span>
            <el-input
              v-model="searchForm.contact"
              placeholder="请输入联系人"
              clearable
              style="width: 200px"
              @clear="handleSearch"
              @keyup.enter="handleSearch"
            />
            <el-button type="primary" @click="handleSearch">
              <el-icon><Search /></el-icon>
              搜索
            </el-button>
            <el-button @click="resetSearch">
              <el-icon><Refresh /></el-icon>
              重置
            </el-button>
          </div>
          <!-- 新增供应商按钮 -->
          <el-button
            v-if="hasAuth('project:supplier:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增供应商
          </el-button>
        </div>
      </el-card>

      <!-- 供应商数据表格 -->
      <div class="flex-1 min-h-0">
        <el-table
          v-loading="loading"
          :data="list"
          style="width: 100%"
          class="h-full"
          :max-height="`calc(100vh - 320px)`"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="supplierName" label="供应商名称" />
          <el-table-column prop="contact" label="联系人" />
          <el-table-column prop="phone" label="联系电话" />
          <el-table-column prop="address" label="地址" />
          <el-table-column prop="bank" label="开户银行" />
          <el-table-column prop="bankCard" label="银行卡号" />
          <el-table-column prop="email" label="邮箱" />
          <el-table-column prop="prepayBalance" label="预存余额" />
          <el-table-column prop="deliveryBalance" label="配送余额" />
          <el-table-column prop="createTime" label="创建时间" />
          <el-table-column label="操作" width="180">
            <template #default="scope">
              <el-button
                v-if="hasAuth('project:supplier:edit')"
                type="primary"
                size="small"
                @click="handleEdit(scope.row)"
              >
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('project:supplier:delete')"
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

        <!-- 分页组件 -->
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
    </el-card>

    <!-- 新增/编辑供应商对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="500px"
    >
      <!-- 供应商表单 -->
      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="供应商名称" prop="supplierName">
          <el-input
            v-model="formData.supplierName"
            placeholder="请输入供应商名称"
          />
        </el-form-item>
        <el-form-item label="联系人" prop="contact">
          <el-input v-model="formData.contact" placeholder="请输入联系人" />
        </el-form-item>
        <el-form-item label="联系电话" prop="phone">
          <el-input v-model="formData.phone" placeholder="请输入联系电话" />
        </el-form-item>
        <el-form-item label="地址" prop="address">
          <el-input v-model="formData.address" placeholder="请输入地址" />
        </el-form-item>
        <el-form-item label="开户银行">
          <el-input v-model="formData.bank" placeholder="请输入开户银行" />
        </el-form-item>
        <el-form-item label="银行卡号">
          <el-input
            v-model="formData.bankCard"
            placeholder="请输入银行卡号"
          />
        </el-form-item>
        <el-form-item label="邮箱">
          <el-input v-model="formData.email" placeholder="请输入邮箱" />
        </el-form-item>
        <el-form-item label="预存余额">
          <el-input
            v-model.number="formData.prepayBalance"
            type="number"
            placeholder="请输入预存余额"
          />
        </el-form-item>
        <el-form-item label="配送余额">
          <el-input
            v-model.number="formData.deliveryBalance"
            type="number"
            placeholder="请输入配送余额"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmit">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
/**
 * 供应商管理页面脚本
 * 
 * 主要功能：
 * 1. 供应商列表展示 - 支持搜索和分页
 * 2. 供应商CRUD操作 - 新增、编辑、删除
 */
import { ref, reactive, onMounted } from "vue";
import { Plus, Edit, Delete, Search, Refresh, Close, Check } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox, type FormInstance } from "element-plus";
import { hasAuth } from "@/router/utils";
import http from "@/utils/http";

// ==================== 状态定义 ====================

/** 加载状态 */
const loading = ref(false);

/** 对话框可见性 */
const dialogVisible = ref(false);

/** 对话框标题 */
const dialogTitle = ref("");

/** 表单引用 */
const formRef = ref<FormInstance>();

/** 当前编辑的供应商ID */
const currentId = ref<number | null>(null);

/** 搜索表单数据 */
const searchForm = reactive({
  supplierName: "",
  contact: ""
});

/** 分页信息 */
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

/** 供应商列表数据 */
const list = ref<any[]>([]);

/** 供应商表单数据 */
const formData = reactive({
  supplierName: "",
  contact: "",
  phone: "",
  address: "",
  bank: "",
  bankCard: "",
  email: "",
  prepayBalance: 0,
  deliveryBalance: 0
});

/** 表单验证规则 */
const rules = reactive({
  supplierName: [
    { required: true, message: "请输入供应商名称", trigger: "blur" }
  ]
});

// ==================== 生命周期 ====================

/**
 * 组件挂载时初始化数据
 * 根据权限加载供应商列表
 */
onMounted(() => {
  if (hasAuth("project:supplier:view")) {
    getList();
  }
});

// ==================== 数据获取方法 ====================

/**
 * 获取供应商列表
 * 支持按供应商名称和联系人筛选，支持分页
 */
const getList = async () => {
  if (!hasAuth("project:supplier:view")) {
    return;
  }

  loading.value = true;
  try {
    const response = await http.get("/api/card-item/get-suppliers", {
      params: {
        supplierName: searchForm.supplierName,
        contact: searchForm.contact,
        page: pagination.current,
        pageSize: pagination.pageSize
      }
    });

    if (response.code === 200) {
      list.value = response.data || [];
      pagination.total = response.data?.length || 0;
    }
  } catch (error) {
    console.error("获取供应商列表失败", error);
  } finally {
    loading.value = false;
  }
};

// ==================== 搜索方法 ====================

/** 执行搜索 */
const handleSearch = () => {
  pagination.current = 1;
  getList();
};

/** 重置搜索条件 */
const resetSearch = () => {
  searchForm.supplierName = "";
  searchForm.contact = "";
  pagination.current = 1;
  getList();
};

// ==================== 分页方法 ====================

/**
 * 处理分页大小变化
 * @param size - 新的分页大小
 */
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getList();
};

/**
 * 处理当前页变化
 * @param current - 新的当前页码
 */
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getList();
};

// ==================== 表单操作方法 ====================

/** 重置表单 */
const resetForm = () => {
  formRef.value?.resetFields();
  Object.assign(formData, {
    supplierName: "",
    contact: "",
    phone: "",
    address: "",
    bank: "",
    bankCard: "",
    email: "",
    prepayBalance: 0,
    deliveryBalance: 0
  });
};

/** 打开新增供应商对话框 */
const handleAdd = () => {
  resetForm();
  dialogTitle.value = "新增供应商";
  currentId.value = null;
  dialogVisible.value = true;
};

/**
 * 打开编辑供应商对话框
 * @param row - 供应商数据行
 */
const handleEdit = (row: any) => {
  Object.assign(formData, {
    supplierName: row.supplierName,
    contact: row.contact,
    phone: row.phone,
    address: row.address,
    bank: row.bank || "",
    bankCard: row.bankCard || "",
    email: row.email || "",
    prepayBalance: row.prepayBalance || 0,
    deliveryBalance: row.deliveryBalance || 0
  });
  dialogTitle.value = "编辑供应商";
  currentId.value = row.id;
  dialogVisible.value = true;
};

/**
 * 删除供应商
 * 弹出确认框，确认后调用API删除
 * @param id - 供应商ID
 */
const handleDelete = async (id: number) => {
  ElMessageBox.confirm("确定要删除该供应商吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        const response = await http.delete(
          `/api/card-item/delete-supplier/${id}`
        );

        if (response.code === 200) {
          loading.value = false;
          ElMessage.success("删除成功");
          getList();
        } else {
          loading.value = false;
          ElMessage.error(response.message || "删除失败");
        }
      } catch (error) {
        loading.value = false;
        console.error("删除供应商失败", error);
        ElMessage.error("网络错误，请稍后重试");
      }
    })
    .catch(() => {});
};

/**
 * 提交供应商表单
 * 验证表单后调用新增或更新API
 */
const handleSubmit = async () => {
  if (!formRef.value) return;

  try {
    await formRef.value.validate();

    loading.value = true;

    const submitData = {
      ...formData,
      companyId: 2
    };

    let response;
    if (currentId.value) {
      response = await http.put(
        `/api/card-item/update-supplier/${currentId.value}`,
        {
          data: submitData
        }
      );
    } else {
      response = await http.post("/api/card-item/add-supplier", {
        data: submitData
      });
    }

    if (response.code === 200) {
      loading.value = false;
      dialogVisible.value = false;
      ElMessage.success(currentId.value ? "编辑成功" : "新增成功");
      getList();
    } else {
      loading.value = false;
      ElMessage.error(response.message || "操作失败");
    }
  } catch (error) {
    loading.value = false;
    console.error("操作失败", error);
    ElMessage.error("网络错误，请稍后重试");
  }
};
</script>

<style scoped>
.supplier-container {
  min-height: calc(100vh - 120px);
}

.pagination {
  display: flex;
  justify-content: flex-end;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}
</style>
