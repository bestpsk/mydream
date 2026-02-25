<template>
  <div class="time-card">
    <div class="mb-4 flex justify-between items-center">
      <div class="search-bar flex-grow">
        <el-form :inline="true" :model="searchForm" class="w-full">
          <el-form-item label="卡名称">
            <el-input v-model="searchForm.cardName" placeholder="请输入卡名称" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">
              <el-icon><Search /></el-icon>
              搜索
            </el-button>
            <el-button @click="resetSearch">
              <el-icon><Refresh /></el-icon>
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </div>
      <el-button v-if="hasAuth('card:time:add')" type="primary" class="ml-4" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        新增时效卡
      </el-button>
    </div>

    <div class="flex-1 min-h-0">
      <el-table v-loading="loading" :data="list" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="cardName" label="卡名称" />
        <el-table-column prop="validDays" label="有效期(天)" />
        <el-table-column prop="price" label="价格" />
        <el-table-column prop="createTime" label="创建时间" />
        <el-table-column label="操作" width="180">
          <template #default="scope">
            <el-button v-if="hasAuth('card:time:edit')" type="primary" size="small" @click="handleEdit(scope.row)">
              <el-icon><Edit /></el-icon>
              编辑
            </el-button>
            <el-button v-if="hasAuth('card:time:delete')" type="danger" size="small" @click="handleDelete(scope.row.id)">
              <el-icon><Delete /></el-icon>
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

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

    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
        label-width="120px"
      >
        <el-form-item label="时效卡名称" prop="cardName">
          <el-input v-model="formData.cardName" placeholder="请输入时效卡名称" />
        </el-form-item>
        <el-form-item label="有效期" prop="validDays">
          <div class="flex items-center">
            <el-input-number v-model="formData.validDays" :min="1" :step="1" class="w-full" />
            <span class="ml-2 text-gray-500">天</span>
          </div>
        </el-form-item>
        <el-form-item label="价格" prop="price">
          <div class="flex items-center">
            <el-input-number v-model="formData.price" :min="0" :step="0.01" :precision="2" class="w-full" />
            <span class="ml-2 text-gray-500">元</span>
          </div>
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="formData.description"
            type="textarea"
            placeholder="请输入描述"
            rows="3"
          />
        </el-form-item>
        <el-form-item label="备注" prop="remark">
          <el-input
            v-model="formData.remark"
            type="textarea"
            placeholder="请输入备注"
            rows="2"
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
import { ref, reactive, onMounted } from "vue";
import { Plus, Edit, Delete, Search, Refresh, Close, Check } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import { hasAuth } from "@/router/utils";

interface Props {
  storeList: any[];
  departmentList: any[];
}

defineProps<Props>();

const loading = ref(false);
const dialogVisible = ref(false);
const dialogTitle = ref('新增时效卡');
const formRef = ref<any>(null);

const searchForm = reactive({
  cardName: ""
});

const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

const list = ref<any[]>([]);

const formData = reactive({
  id: '',
  cardName: '',
  validDays: 30,
  price: 0,
  description: '',
  remark: ''
});

const rules = reactive({
  cardName: [{ required: true, message: '请输入时效卡名称', trigger: ['blur', 'change'] }],
  validDays: [{ required: true, message: '请输入有效期', trigger: 'blur' }],
  price: [{ required: true, message: '请输入价格', trigger: 'blur' }]
});

onMounted(() => {
  getList();
});

const getList = async () => {
  loading.value = true;
  try {
    // TODO: 调用API获取时效卡列表
    // const response = await getTimeCards(params);
    // if (response.code === 200) {
    //   list.value = response.data;
    //   pagination.total = response.total || 0;
    // }
    
    // 暂时使用模拟数据
    setTimeout(() => {
      loading.value = false;
      list.value = [];
      pagination.total = 0;
    }, 500);
  } catch (error) {
    console.error('获取时效卡列表失败:', error);
    ElMessage.error('获取时效卡列表失败');
    loading.value = false;
  }
};

const handleSearch = () => {
  pagination.current = 1;
  getList();
};

const resetSearch = () => {
  searchForm.cardName = "";
  pagination.current = 1;
  getList();
};

const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getList();
};

const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getList();
};

const handleAdd = () => {
  dialogTitle.value = '新增时效卡';
  Object.assign(formData, {
    id: '',
    cardName: '',
    validDays: 30,
    price: 0,
    description: '',
    remark: ''
  });
  dialogVisible.value = true;
};

const handleEdit = (row: any) => {
  dialogTitle.value = '编辑时效卡';
  Object.assign(formData, {
    id: row.id,
    cardName: row.cardName,
    validDays: row.validDays,
    price: row.price,
    description: row.description,
    remark: row.remark
  });
  dialogVisible.value = true;
};

const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该时效卡吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        // TODO: 调用API删除时效卡
        // const response = await deleteTimeCard(id);
        // if (response.code === 200) {
        //   ElMessage.success("删除成功");
        //   getList();
        // } else {
        //   ElMessage.error(response.message || "删除失败");
        // }
        
        // 暂时使用模拟数据
        setTimeout(() => {
          loading.value = false;
          ElMessage.success("删除成功");
          getList();
        }, 500);
      } catch (error) {
        console.error('删除时效卡失败:', error);
        ElMessage.error('删除时效卡失败');
        loading.value = false;
      }
    })
    .catch(() => {});
};

const handleSubmit = async () => {
  if (!formRef.value) return;
  
  try {
    await formRef.value.validate();
    
    // TODO: 调用API新增/更新时效卡
    // if (formData.id) {
    //   const response = await updateTimeCard(formData.id, formData);
    //   if (response.code === 200) {
    //     ElMessage.success('更新成功');
    //   } else {
    //     ElMessage.error(response.message || '更新失败');
    //     return;
    //   }
    // } else {
    //   const response = await addTimeCard(formData);
    //   if (response.code === 200) {
    //     ElMessage.success('新增成功');
    //   } else {
    //     ElMessage.error(response.message || '新增失败');
    //     return;
    //   }
    // }
    
    // 暂时使用模拟数据
    ElMessage.success(formData.id ? '更新成功' : '新增成功');
    dialogVisible.value = false;
    getList();
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};
</script>

<style scoped>
.time-card {
  height: 100%;
  display: flex;
  flex-direction: column;
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
