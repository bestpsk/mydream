<template>
  <div class="expense-management-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>店务开支</span>
          <div class="header-buttons">
            <el-button type="primary" @click="handleAddExpense">
              <el-icon><Plus /></el-icon>
              新增开支
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
          <el-form-item label="开支编号">
            <el-input
              v-model="searchForm.expenseNo"
              placeholder="请输入开支编号"
            />
          </el-form-item>
          <el-form-item label="门店">
            <el-select v-model="searchForm.storeId" placeholder="请选择门店">
              <el-option label="全部" value="" />
              <el-option
                v-for="store in storeList"
                :key="store.id"
                :label="store.name"
                :value="store.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="开支分类">
            <el-select
              v-model="searchForm.categoryId"
              placeholder="请选择开支分类"
            >
              <el-option label="全部" value="" />
              <el-option
                v-for="category in categoryList"
                :key="category.id"
                :label="category.name"
                :value="category.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="开支时间">
            <el-date-picker
              v-model="searchForm.expenseTime"
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

      <!-- 开支列表 -->
      <div class="expense-list">
        <el-table v-loading="loading" :data="expenseList" style="width: 100%">
          <el-table-column prop="id" label="开支编号" />
          <el-table-column prop="storeName" label="门店" />
          <el-table-column prop="categoryName" label="开支分类" />
          <el-table-column prop="amount" label="开支金额" />
          <el-table-column prop="description" label="开支描述" />
          <el-table-column prop="expenseTime" label="开支时间" />
          <el-table-column prop="creatorName" label="创建人" />
          <el-table-column label="操作" width="180">
            <template #default="scope">
              <el-button
                type="primary"
                size="small"
                @click="viewExpenseDetail(scope.row)"
              >
                查看详情
              </el-button>
              <el-button
                type="success"
                size="small"
                @click="editExpense(scope.row)"
              >
                编辑
              </el-button>
              <el-button
                type="danger"
                size="small"
                @click="deleteExpense(scope.row.id)"
              >
                删除
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

      <!-- 分类管理 -->
      <div class="category-management mt-4">
        <el-card shadow="hover">
          <template #header>
            <div class="category-header">
              <span>开支分类管理</span>
              <el-button type="primary" size="small" @click="handleAddCategory">
                <el-icon><Plus /></el-icon>
                新增分类
              </el-button>
            </div>
          </template>
          <el-table :data="categoryList" style="width: 100%">
            <el-table-column prop="id" label="分类ID" width="80" />
            <el-table-column prop="name" label="分类名称" />
            <el-table-column prop="description" label="分类描述" />
            <el-table-column label="操作" width="180">
              <template #default="scope">
                <el-button
                  type="success"
                  size="small"
                  @click="editCategory(scope.row)"
                >
                  编辑
                </el-button>
                <el-button
                  type="danger"
                  size="small"
                  @click="deleteCategory(scope.row.id)"
                >
                  删除
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </div>
    </el-card>

    <!-- 新增/编辑开支对话框 -->
    <el-dialog
      v-model="expenseDialogVisible"
      :title="dialogType === 'add' ? '新增开支' : '编辑开支'"
      width="600px"
    >
      <el-form ref="expenseFormRef" :model="expenseForm" :rules="expenseRules">
        <el-form-item label="门店" prop="storeId">
          <el-select v-model="expenseForm.storeId" placeholder="请选择门店">
            <el-option
              v-for="store in storeList"
              :key="store.id"
              :label="store.name"
              :value="store.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="开支分类" prop="categoryId">
          <el-select
            v-model="expenseForm.categoryId"
            placeholder="请选择开支分类"
          >
            <el-option
              v-for="category in categoryList"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="开支金额" prop="amount">
          <el-input-number v-model="expenseForm.amount" :min="0" :step="0.01" />
        </el-form-item>
        <el-form-item label="开支时间" prop="expenseTime">
          <el-date-picker
            v-model="expenseForm.expenseTime"
            type="datetime"
            placeholder="选择开支时间"
          />
        </el-form-item>
        <el-form-item label="开支描述" prop="description">
          <el-input
            v-model="expenseForm.description"
            type="textarea"
            placeholder="请输入开支描述"
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
          <el-button @click="expenseDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleExpenseConfirm"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>

    <!-- 开支详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="开支详情" width="700px">
      <div v-if="currentExpense" class="expense-detail">
        <el-card shadow="hover" class="mb-4">
          <template #header>
            <span>基本信息</span>
          </template>
          <div class="grid grid-cols-2 gap-4">
            <div>开支编号: {{ currentExpense.id }}</div>
            <div>门店: {{ currentExpense.storeName }}</div>
            <div>开支分类: {{ currentExpense.categoryName }}</div>
            <div>开支金额: {{ currentExpense.amount }}元</div>
            <div>开支时间: {{ currentExpense.expenseTime }}</div>
            <div>创建人: {{ currentExpense.creatorName }}</div>
            <div>开支描述: {{ currentExpense.description }}</div>
          </div>
        </el-card>

        <el-card
          v-if="
            currentExpense.attachments && currentExpense.attachments.length > 0
          "
          shadow="hover"
        >
          <template #header>
            <span>附件</span>
          </template>
          <div class="attachments-list">
            <el-link
              v-for="(attachment, index) in currentExpense.attachments"
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

    <!-- 新增/编辑分类对话框 -->
    <el-dialog
      v-model="categoryDialogVisible"
      :title="categoryDialogType === 'add' ? '新增分类' : '编辑分类'"
      width="500px"
    >
      <el-form
        ref="categoryFormRef"
        :model="categoryForm"
        :rules="categoryRules"
      >
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="categoryForm.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="分类描述" prop="description">
          <el-input
            v-model="categoryForm.description"
            type="textarea"
            placeholder="请输入分类描述"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="categoryDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleCategoryConfirm"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import { Plus, Refresh, Upload } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";

// 搜索表单
const searchForm = reactive({
  expenseNo: "",
  storeId: "",
  categoryId: "",
  expenseTime: []
});

// 加载状态
const loading = ref(false);

// 门店列表（模拟数据）
const storeList = ref([
  { id: 1, name: "旗舰店" },
  { id: 2, name: "中心店" },
  { id: 3, name: "分店" }
]);

// 分类列表（模拟数据）
const categoryList = ref([
  { id: 1, name: "租金", description: "门店租金开支" },
  { id: 2, name: "水电费", description: "门店水电费开支" },
  { id: 3, name: "员工薪资", description: "员工薪资开支" },
  { id: 4, name: "产品采购", description: "产品采购开支" },
  { id: 5, name: "设备维护", description: "设备维护开支" },
  { id: 6, name: "其他", description: "其他开支" }
]);

// 开支列表（模拟数据）
const expenseList = ref([
  {
    id: "EXP202602030001",
    storeId: 1,
    storeName: "旗舰店",
    categoryId: 1,
    categoryName: "租金",
    amount: 10000,
    description: "2月份门店租金",
    expenseTime: "2026-02-01 00:00:00",
    creatorId: 1,
    creatorName: "管理员",
    attachments: []
  },
  {
    id: "EXP202602030002",
    storeId: 1,
    storeName: "旗舰店",
    categoryId: 2,
    categoryName: "水电费",
    amount: 800,
    description: "1月份水电费",
    expenseTime: "2026-02-02 00:00:00",
    creatorId: 1,
    creatorName: "管理员",
    attachments: []
  },
  {
    id: "EXP202602030003",
    storeId: 2,
    storeName: "中心店",
    categoryId: 3,
    categoryName: "员工薪资",
    amount: 15000,
    description: "1月份员工薪资",
    expenseTime: "2026-02-03 00:00:00",
    creatorId: 1,
    creatorName: "管理员",
    attachments: []
  },
  {
    id: "EXP202602030004",
    storeId: 3,
    storeName: "分店",
    categoryId: 4,
    categoryName: "产品采购",
    amount: 5000,
    description: "产品采购",
    expenseTime: "2026-02-04 00:00:00",
    creatorId: 1,
    creatorName: "管理员",
    attachments: []
  }
]);

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 4
});

// 开支表单
const expenseDialogVisible = ref(false);
const dialogType = ref("add");
const expenseFormRef = ref<FormInstance>();
const expenseForm = reactive({
  id: "",
  storeId: 0,
  categoryId: 0,
  amount: 0,
  expenseTime: new Date(),
  description: "",
  attachments: []
});

const expenseRules = reactive<FormRules>({
  storeId: [{ required: true, message: "请选择门店", trigger: "change" }],
  categoryId: [
    { required: true, message: "请选择开支分类", trigger: "change" }
  ],
  amount: [{ required: true, message: "请输入开支金额", trigger: "blur" }],
  expenseTime: [
    { required: true, message: "请选择开支时间", trigger: "change" }
  ],
  description: [{ required: true, message: "请输入开支描述", trigger: "blur" }]
});

// 开支详情
const detailDialogVisible = ref(false);
const currentExpense = ref<any>(null);

// 分类表单
const categoryDialogVisible = ref(false);
const categoryDialogType = ref("add");
const categoryFormRef = ref<FormInstance>();
const categoryForm = reactive({
  id: 0,
  name: "",
  description: ""
});

const categoryRules = reactive<FormRules>({
  name: [{ required: true, message: "请输入分类名称", trigger: "blur" }],
  description: [{ required: true, message: "请输入分类描述", trigger: "blur" }]
});

// 初始化数据
onMounted(() => {
  getExpenseList();
});

// 获取开支列表
const getExpenseList = () => {
  loading.value = true;
  // 模拟API请求
  setTimeout(() => {
    loading.value = false;
  }, 500);
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getExpenseList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.expenseNo = "";
  searchForm.storeId = "";
  searchForm.categoryId = "";
  searchForm.expenseTime = [];
  pagination.current = 1;
  getExpenseList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getExpenseList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getExpenseList();
};

// 刷新
const handleRefresh = () => {
  getExpenseList();
};

// 新增开支
const handleAddExpense = () => {
  dialogType.value = "add";
  resetExpenseForm();
  expenseDialogVisible.value = true;
};

// 重置开支表单
const resetExpenseForm = () => {
  expenseForm.id = "";
  expenseForm.storeId = 0;
  expenseForm.categoryId = 0;
  expenseForm.amount = 0;
  expenseForm.expenseTime = new Date();
  expenseForm.description = "";
  expenseForm.attachments = [];
};

// 处理附件变化
const handleAttachmentChange = (file: any, fileList: any[]) => {
  expenseForm.attachments = fileList;
};

// 编辑开支
const editExpense = (expense: any) => {
  dialogType.value = "edit";
  expenseForm.id = expense.id;
  expenseForm.storeId = expense.storeId;
  expenseForm.categoryId = expense.categoryId;
  expenseForm.amount = expense.amount;
  expenseForm.expenseTime = new Date(expense.expenseTime);
  expenseForm.description = expense.description;
  expenseForm.attachments = expense.attachments || [];
  expenseDialogVisible.value = true;
};

// 确认开支
const handleExpenseConfirm = async () => {
  if (!expenseFormRef.value) return;
  await expenseFormRef.value.validate((valid: boolean) => {
    if (valid) {
      // 模拟API请求
      setTimeout(() => {
        if (dialogType.value === "add") {
          // 生成开支编号
          const expenseId = `EXP${new Date().toISOString().slice(0, 10).replace(/-/g, "")}${String(expenseList.value.length + 1).padStart(4, "0")}`;

          // 查找门店名称
          const store = storeList.value.find(s => s.id === expenseForm.storeId);
          const storeName = store ? store.name : "";

          // 查找分类名称
          const category = categoryList.value.find(
            c => c.id === expenseForm.categoryId
          );
          const categoryName = category ? category.name : "";

          // 添加开支
          expenseList.value.push({
            id: expenseId,
            storeId: expenseForm.storeId,
            storeName: storeName,
            categoryId: expenseForm.categoryId,
            categoryName: categoryName,
            amount: expenseForm.amount,
            description: expenseForm.description,
            expenseTime: expenseForm.expenseTime
              .toISOString()
              .slice(0, 19)
              .replace("T", " "),
            creatorId: 1,
            creatorName: "管理员",
            attachments: expenseForm.attachments
          });
        } else {
          // 更新开支
          const index = expenseList.value.findIndex(
            item => item.id === expenseForm.id
          );
          if (index !== -1) {
            // 查找门店名称
            const store = storeList.value.find(
              s => s.id === expenseForm.storeId
            );
            const storeName = store ? store.name : "";

            // 查找分类名称
            const category = categoryList.value.find(
              c => c.id === expenseForm.categoryId
            );
            const categoryName = category ? category.name : "";

            expenseList.value[index].storeId = expenseForm.storeId;
            expenseList.value[index].storeName = storeName;
            expenseList.value[index].categoryId = expenseForm.categoryId;
            expenseList.value[index].categoryName = categoryName;
            expenseList.value[index].amount = expenseForm.amount;
            expenseList.value[index].description = expenseForm.description;
            expenseList.value[index].expenseTime = expenseForm.expenseTime
              .toISOString()
              .slice(0, 19)
              .replace("T", " ");
            expenseList.value[index].attachments = expenseForm.attachments;
          }
        }

        expenseDialogVisible.value = false;
        ElMessage.success(
          dialogType.value === "add" ? "开支添加成功" : "开支更新成功"
        );
      }, 500);
    }
  });
};

// 查看开支详情
const viewExpenseDetail = (expense: any) => {
  currentExpense.value = expense;
  detailDialogVisible.value = true;
};

// 删除开支
const deleteExpense = (id: string) => {
  ElMessageBox.confirm("确定要删除该开支吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  }).then(() => {
    expenseList.value = expenseList.value.filter(item => item.id !== id);
    ElMessage.success("开支已删除");
  });
};

// 新增分类
const handleAddCategory = () => {
  categoryDialogType.value = "add";
  resetCategoryForm();
  categoryDialogVisible.value = true;
};

// 重置分类表单
const resetCategoryForm = () => {
  categoryForm.id = 0;
  categoryForm.name = "";
  categoryForm.description = "";
};

// 编辑分类
const editCategory = (category: any) => {
  categoryDialogType.value = "edit";
  categoryForm.id = category.id;
  categoryForm.name = category.name;
  categoryForm.description = category.description;
  categoryDialogVisible.value = true;
};

// 确认分类
const handleCategoryConfirm = async () => {
  if (!categoryFormRef.value) return;
  await categoryFormRef.value.validate((valid: boolean) => {
    if (valid) {
      // 模拟API请求
      setTimeout(() => {
        if (categoryDialogType.value === "add") {
          // 添加分类
          categoryList.value.push({
            id: categoryList.value.length + 1,
            name: categoryForm.name,
            description: categoryForm.description
          });
        } else {
          // 更新分类
          const index = categoryList.value.findIndex(
            item => item.id === categoryForm.id
          );
          if (index !== -1) {
            categoryList.value[index].name = categoryForm.name;
            categoryList.value[index].description = categoryForm.description;
          }
        }

        categoryDialogVisible.value = false;
        ElMessage.success(
          categoryDialogType.value === "add" ? "分类添加成功" : "分类更新成功"
        );
      }, 500);
    }
  });
};

// 删除分类
const deleteCategory = (id: number) => {
  // 检查是否有开支使用该分类
  const hasExpense = expenseList.value.some(item => item.categoryId === id);
  if (hasExpense) {
    ElMessage.warning("该分类已被使用，无法删除");
    return;
  }

  ElMessageBox.confirm("确定要删除该分类吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  }).then(() => {
    categoryList.value = categoryList.value.filter(item => item.id !== id);
    ElMessage.success("分类已删除");
  });
};
</script>

<style scoped>
.expense-management-container {
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

.category-management {
  margin-top: 20px;
}

.category-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}

.expense-detail {
  max-height: 500px;
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
