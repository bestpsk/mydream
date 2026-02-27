<template>
  <div class="employee-container">
    <el-card class="main-card">
      <template #header>
        <div class="card-header">
          <span>员工管理</span>
        </div>
      </template>

      <!-- 搜索栏 -->
      <el-card v-if="hasAuth('employee:view')" class="mb-4" shadow="never">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <span class="text-sm font-bold">员工姓名</span>
            <el-input
              v-model="searchForm.employeeName"
              placeholder="请输入员工姓名"
              clearable
              style="width: 200px"
              @clear="handleSearch"
              @keyup.enter="handleSearch"
            />
            <span class="text-sm font-bold">所属门店</span>
            <el-select
              v-model="searchForm.storeId"
              placeholder="请选择所属门店"
              clearable
              style="width: 200px"
            >
              <el-option label="全部" :value="''" />
              <el-option
                v-for="store in storeList"
                :key="store.id"
                :label="store.storeName"
                :value="store.id"
              />
              <el-option label="其他" :value="'other'" />
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
            v-if="hasAuth('employee:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增员工
          </el-button>
        </div>
      </el-card>

      <!-- 员工列表 -->
      <div v-if="hasAuth('employee:view')" class="employee-list">
        <el-table v-loading="loading" :data="employeeList" style="width: 100%">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column label="员工姓名">
            <template #default="scope">
              {{ scope.row.employeeName || "无" }}
            </template>
          </el-table-column>
          <el-table-column label="账号">
            <template #default="scope">
              {{ scope.row.username || scope.row.user?.username || "无" }}
            </template>
          </el-table-column>
          <el-table-column label="所属公司">
            <template #default="scope">
              {{
                scope.row.companyName ||
                (scope.row.company_id ? scope.row.company_id : "无")
              }}
            </template>
          </el-table-column>
          <el-table-column label="所属门店">
            <template #default="scope">
              {{
                scope.row.storeName ||
                (scope.row.store_id ? scope.row.store_id : "无")
              }}
            </template>
          </el-table-column>
          <el-table-column label="部门">
            <template #default="scope">
              {{
                scope.row.deptName ||
                (scope.row.department_id ? scope.row.department_id : "无")
              }}
            </template>
          </el-table-column>
          <el-table-column label="职位">
            <template #default="scope">
              {{
                scope.row.positionName ||
                (scope.row.position_id ? scope.row.position_id : "无")
              }}
            </template>
          </el-table-column>
          <el-table-column label="上级员工">
            <template #default="scope">
              {{
                scope.row.superiorName ||
                (scope.row.superior_id ? scope.row.superior_id : "无")
              }}
            </template>
          </el-table-column>
          <el-table-column label="拥有角色">
            <template #default="scope">
              <template
                v-if="scope.row.roleNames && scope.row.roleNames.length"
              >
                <el-tag
                  v-for="(roleName, index) in scope.row.roleNames"
                  :key="index"
                  size="small"
                  type="primary"
                  style="margin-right: 5px"
                >
                  {{ roleName }}
                </el-tag>
              </template>
              <template v-else> 无 </template>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="180">
            <template #default="scope">
              <el-button
                v-if="hasAuth('employee:edit')"
                type="primary"
                size="small"
                @click="handleEdit(scope.row)"
              >
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('employee:delete')"
                type="danger"
                size="small"
                @click="handleDelete(scope.row.employeeId || scope.row.id)"
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
      :title="dialogType === 'add' ? '新增员工' : '编辑员工'"
      width="800px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="员工姓名" prop="employeeName">
              <el-input
                v-model="form.employeeName"
                placeholder="请输入员工姓名"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="用户账号" prop="username">
              <el-input v-model="form.username" placeholder="请输入登录账号" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="所属公司" prop="companyId">
              <el-select
                v-model="form.companyId"
                placeholder="请选择所属公司"
                :disabled="!isSuperAdmin"
              >
                <template v-if="isSuperAdmin">
                  <el-option
                    v-for="company in companyList"
                    :key="company.id"
                    :label="company.companyName"
                    :value="company.id"
                  />
                </template>
                <template v-else>
                  <el-option
                    :label="
                      userStore.companyName &&
                      userStore.companyName !== '未知公司'
                        ? userStore.companyName
                        : '未知公司'
                    "
                    :value="userStore.companyId || 0"
                  />
                </template>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="所属门店" prop="storeId">
              <el-select v-model="form.storeId" placeholder="请选择所属门店">
                <el-option
                  v-for="store in storeList"
                  :key="store.id"
                  :label="store.storeName"
                  :value="store.id"
                />
                <el-option label="其他" :value="'other'" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="部门" prop="deptId">
              <el-select v-model="form.deptId" placeholder="请选择部门">
                <el-option
                  v-for="dept in deptList"
                  :key="dept.id"
                  :label="dept.deptName"
                  :value="dept.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="职位" prop="positionId">
              <el-select v-model="form.positionId" placeholder="请选择职位">
                <el-option
                  v-for="position in positionList"
                  :key="position.id"
                  :label="position.positionName"
                  :value="position.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="上级员工" prop="superiorId">
              <el-select v-model="form.superiorId" placeholder="请选择上级员工">
                <el-option
                  v-for="employee in filteredEmployeeList"
                  :key="employee.employeeId || employee.id"
                  :label="employee.employeeName"
                  :value="employee.employeeId || employee.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="拥有角色" prop="roles">
              <el-select
                v-model="form.roles"
                multiple
                placeholder="请选择拥有角色"
                tag-type="primary"
              >
                <el-option
                  v-for="role in roleList"
                  :key="role.id"
                  :label="role.roleName"
                  :value="role.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="可查看门店" prop="storeIds">
              <el-select
                v-model="form.storeIds"
                multiple
                placeholder="请选择可查看门店"
                tag-type="primary"
              >
                <el-option
                  v-for="store in storeList"
                  :key="store.id"
                  :label="store.storeName"
                  :value="store.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmit">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, computed } from "vue";
import { Plus, Edit, Delete, Search, Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import {
  getEmployees,
  addEmployee,
  updateEmployee,
  deleteEmployee,
  getCompanies,
  getStores,
  getDepartments,
  getPositions,
  getRoles
} from "@/api/enterprise";
import { hasAuth } from "@/router/utils";
import { useUserStoreHook } from "@/store/modules/user";

// 加载状态
const loading = ref(false);

// 获取用户store
const userStore = useUserStoreHook();

// 计算属性：判断当前用户是否为超级管理员
const isSuperAdmin = computed(() => userStore.isSuper);

// 计算属性：根据选择的门店过滤员工列表
const filteredEmployeeList = computed(() => {
  const currentStoreId = form.storeId;
  const currentSuperiorId = form.superiorId;
  const currentEmployeeId = form.id;

  let filteredList;

  if (currentStoreId === "other") {
    // 如果选择了"其他"，显示没有对应门店的员工
    filteredList = employeeList.value.filter(employee => {
      const employeeStoreId = employee.storeId || employee.store_id;
      const employeeId = employee.employeeId || employee.id;
      // 排除当前员工自己
      if (
        currentEmployeeId &&
        String(employeeId) === String(currentEmployeeId)
      ) {
        return false;
      }
      return (
        !employeeStoreId || employeeStoreId === 0 || employeeStoreId === "0"
      );
    });
  } else if (currentStoreId) {
    // 如果选择了具体门店，显示对应门店的员工
    filteredList = employeeList.value.filter(employee => {
      const employeeStoreId = employee.storeId || employee.store_id;
      const employeeId = employee.employeeId || employee.id;
      // 排除当前员工自己
      if (
        currentEmployeeId &&
        String(employeeId) === String(currentEmployeeId)
      ) {
        return false;
      }
      return String(employeeStoreId) === String(currentStoreId);
    });
  } else {
    // 如果没有选择门店，显示所有员工
    filteredList = employeeList.value.filter(employee => {
      const employeeId = employee.employeeId || employee.id;
      // 排除当前员工自己
      if (
        currentEmployeeId &&
        String(employeeId) === String(currentEmployeeId)
      ) {
        return false;
      }
      return true;
    });
  }

  // 在编辑模式下，如果当前设置的上级员工不在过滤列表中，添加进去
  if (currentSuperiorId && dialogType.value === "edit") {
    const hasSuperior = filteredList.some(
      employee =>
        String(employee.employeeId || employee.id) === String(currentSuperiorId)
    );
    if (!hasSuperior) {
      const superiorEmployee = employeeList.value.find(
        employee =>
          String(employee.employeeId || employee.id) ===
          String(currentSuperiorId)
      );
      if (superiorEmployee) {
        const superiorId = superiorEmployee.employeeId || superiorEmployee.id;
        // 确保添加的上级员工不是当前员工自己
        if (
          !currentEmployeeId ||
          String(superiorId) !== String(currentEmployeeId)
        ) {
          filteredList = [superiorEmployee, ...filteredList];
        }
      }
    }
  }

  return filteredList;
});

// 搜索表单
const searchForm = reactive({
  employeeName: "",
  storeId: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 员工列表
const employeeList = ref([]);

// 下拉选项数据
const companyList = ref([]);
const storeList = ref([]);
const deptList = ref([]);
const positionList = ref([]);
const roleList = ref([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 表单数据
const form = reactive({
  id: 0,
  employeeName: "",
  username: "",
  companyId: "",
  storeId: "",
  deptId: "",
  positionId: "",
  superiorId: "",
  roles: [],
  storeIds: []
});

// 监听公司ID变化，重新获取门店和部门列表
watch(
  () => form.companyId,
  newCompanyId => {
    if (newCompanyId) {
      getStoreList(newCompanyId);
      getDeptList(newCompanyId);
    } else {
      storeList.value = [];
      deptList.value = [];
      positionList.value = [];
    }
    // 清空相关选择
    form.storeId = "";
    form.storeIds = [];
    form.deptId = "";
    form.positionId = "";
  }
);

// 监听部门ID变化，重新获取职位列表
watch([() => form.deptId, () => form.storeId], ([newDeptId, newStoreId]) => {
  if (newDeptId && form.companyId) {
    getPositionList(form.companyId, newDeptId, newStoreId);
  } else {
    positionList.value = [];
  }
  // 清空职位选择
  form.positionId = "";
});

// 表单验证规则
const rules = reactive<FormRules>({
  employeeName: [
    { required: true, message: "请输入员工姓名", trigger: "blur" }
  ],
  username: [{ required: true, message: "请输入用户账号", trigger: "blur" }],
  companyId: [{ required: true, message: "请选择所属公司", trigger: "change" }],
  storeId: [{ required: true, message: "请选择所属门店", trigger: "change" }],
  deptId: [{ required: true, message: "请选择部门", trigger: "change" }],
  positionId: [{ required: true, message: "请选择职位", trigger: "change" }],
  roles: [{ required: true, message: "请选择拥有角色", trigger: "change" }],
  storeIds: [{ required: true, message: "请选择可查看门店", trigger: "change" }]
});

// 初始化数据
onMounted(() => {
  getCompanyList();
  getStoreList();
  getDeptList();
  getPositionList();
  getRolesList();
  // 检查是否有查看权限
  if (hasAuth("employee:view")) {
    getEmployeeList();
  }
  // 确保用户信息已加载
  if (!userStore.companyId) {
    userStore.getUserInfo();
  }
});

// 获取角色列表
const getRolesList = () => {
  getRoles()
    .then(response => {
      if (response?.code === 200) {
        // 转换角色数据，确保角色名称正确映射到roleName属性
        roleList.value = (response.data || []).map((role: any) => {
          return {
            ...role,
            roleName: role.roleName || role.role_name || role.name || role.id
          };
        });
      } else {
        ElMessage.error(response?.message || "获取角色列表失败");
      }
    })
    .catch(error => {
      ElMessage.error("获取角色列表失败");
      console.error("getRolesList error:", error);
    });
};

// 获取公司列表
const getCompanyList = () => {
  getCompanies()
    .then(response => {
      if (response?.code === 200) {
        companyList.value = response.data || [];
      } else {
        ElMessage.error(response?.message || "获取公司列表失败");
      }
    })
    .catch(error => {
      ElMessage.error("获取公司列表失败");
      console.error("getCompanyList error:", error);
    });
};

// 获取门店列表
const getStoreList = (companyId = "") => {
  return new Promise((resolve, reject) => {
    getStores({ company_id: companyId })
      .then(response => {
        if (response?.code === 200) {
          storeList.value = response.data || [];
          resolve(response.data);
        } else {
          ElMessage.error(response?.message || "获取门店列表失败");
          reject(response?.message || "获取门店列表失败");
        }
      })
      .catch(error => {
        ElMessage.error("获取门店列表失败");
        console.error("getStoreList error:", error);
        reject(error);
      });
  });
};

// 获取部门列表
const getDeptList = (companyId = "") => {
  return new Promise((resolve, reject) => {
    getDepartments({ company_id: companyId })
      .then(response => {
        if (response?.code === 200) {
          deptList.value = response.data || [];
          resolve(response.data);
        } else {
          ElMessage.error(response?.message || "获取部门列表失败");
          reject(response?.message || "获取部门列表失败");
        }
      })
      .catch(error => {
        ElMessage.error("获取部门列表失败");
        console.error("getDeptList error:", error);
        reject(error);
      });
  });
};

// 获取职位列表
const getPositionList = (companyId = "", deptId = "", storeId = "") => {
  return new Promise((resolve, reject) => {
    getPositions({
      company_id: companyId,
      department_id: deptId,
      store_id: storeId
    })
      .then(response => {
        if (response?.code === 200) {
          positionList.value = response.data || [];
          resolve(response.data);
        } else {
          ElMessage.error(response?.message || "获取职位列表失败");
          reject(response?.message || "获取职位列表失败");
        }
      })
      .catch(error => {
        ElMessage.error("获取职位列表失败");
        console.error("getPositionList error:", error);
        reject(error);
      });
  });
};

// 获取员工列表
const getEmployeeList = () => {
  // 检查是否有查看权限
  if (!hasAuth("employee:view")) {
    return;
  }
  loading.value = true;
  // 从后端API获取数据
  getEmployees({
    ...searchForm,
    page: pagination.current,
    page_size: pagination.pageSize
  })
    .then(response => {
      loading.value = false;
      if (response?.code === 200) {
        // 转换员工数据，确保员工姓名正确显示
        const data = response.data || [];
        // 确保data是数组
        const employeeArray = Array.isArray(data) ? data : Object.values(data);
        employeeList.value = employeeArray.map((employee: any) => {
          return {
            ...employee,
            // 确保employeeName字段存在且为字符串
            employeeName:
              employee.employeeName ||
              employee.employee_name ||
              employee.name ||
              String(employee.id),
            // 确保superiorName字段存在且为字符串
            superiorName:
              employee.superiorName ||
              employee.superior_name ||
              employee.superior ||
              (employee.superior_id ? employee.superior_id : "")
          };
        });
        pagination.total = employeeList.value.length;
      } else {
        ElMessage.error(response?.message || "获取员工列表失败");
      }
    })
    .catch(error => {
      loading.value = false;
      ElMessage.error("获取员工列表失败");
      console.error("getEmployeeList error:", error);
    });
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getEmployeeList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.employeeName = "";
  searchForm.storeId = "";
  pagination.current = 1;
  getEmployeeList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getEmployeeList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getEmployeeList();
};

// 新增员工
const handleAdd = () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  form.employeeName = "";
  form.username = "";
  // 普通用户默认设置为自己的公司ID
  form.companyId = isSuperAdmin.value ? "" : userStore.companyId || 0;
  form.storeId = "";
  form.deptId = "";
  form.positionId = "";
  form.superiorId = "";
  form.roles = [];
  form.storeIds = [];
  dialogVisible.value = true;
};

// 编辑员工
const handleEdit = async (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.employeeId || row.id;
  form.employeeName = row.employeeName;
  form.username = row.username || row.user?.username || "";
  // 普通用户只能编辑自己公司的员工，默认设置为自己的公司ID
  form.companyId = isSuperAdmin.value
    ? row.companyId || row.company_id
    : userStore.companyId || 0;

  // 先设置公司ID，触发watch获取门店和部门列表
  if (form.companyId) {
    await getStoreList(form.companyId);
    await getDeptList(form.companyId);

    // 设置其他表单数据
    const storeId = row.storeId || row.store_id;
    form.storeId = storeId || "other";
    form.deptId = row.deptId || row.department_id;

    // 获取职位列表
    if (form.deptId) {
      await getPositionList(form.companyId, form.deptId);
    }
  }

  form.positionId = row.positionId || row.position_id;
  form.superiorId = row.superiorId || row.superior_id;
  // 处理角色数据：如果是字符串则分割，否则直接使用数组
  form.roles = Array.isArray(row.roles)
    ? row.roles
    : row.roles
      ? row.roles.split(",")
      : [];
  form.storeIds = row.storeIds || [];

  // 显示模态框
  dialogVisible.value = true;
};

// 删除员工
const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该员工吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      loading.value = true;
      // 调用删除API
      deleteEmployee(id)
        .then(response => {
          loading.value = false;
          if (response?.code === 200) {
            ElMessage.success("删除成功");
            getEmployeeList();
          } else {
            ElMessage.error(response?.message || "删除失败");
          }
        })
        .catch(error => {
          loading.value = false;
          ElMessage.error("删除失败");
          console.error("handleDelete error:", error);
        });
    })
    .catch(() => {
      // 取消删除
    });
};

// 格式化角色显示
const formatRoles = (roles: any) => {
  if (!roles) return "";
  const roleArray = Array.isArray(roles) ? roles : roles.split(",");
  return roleArray
    .map(roleId => {
      const role = roleList.value.find(r => String(r.id) === String(roleId));
      return role ? role.roleName : roleId;
    })
    .join(",");
};

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return;
  await formRef.value.validate((valid: boolean) => {
    if (valid) {
      loading.value = true;
      // 直接使用camelCase字段名，与后端保持一致
      const submitData = {
        ...form,
        roles: form.roles.join(",")
      };
      // 处理门店ID：如果选择了"其他"，则传递0
      if (submitData.storeId === "other") {
        submitData.storeId = 0;
      }
      // 处理上级员工ID：如果为空字符串，设置为0
      if (submitData.superiorId === "") {
        submitData.superiorId = 0;
      }
      // 打印提交的数据，用于调试
      console.log("提交的编辑数据:", submitData);

      const apiCall =
        dialogType.value === "add"
          ? addEmployee(submitData)
          : updateEmployee(form.id, submitData);

      apiCall
        .then(response => {
          loading.value = false;
          if (response?.code === 200) {
            ElMessage.success(
              dialogType.value === "add" ? "新增成功" : "编辑成功"
            );
            dialogVisible.value = false;
            getEmployeeList();
          } else {
            ElMessage.error(
              response?.message ||
                (dialogType.value === "add" ? "新增失败" : "编辑失败")
            );
          }
        })
        .catch(error => {
          loading.value = false;
          ElMessage.error(dialogType.value === "add" ? "新增失败" : "编辑失败");
          console.error("handleSubmit error:", error);
        });
    }
  });
};
</script>

<style scoped>
.employee-container {
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

/* 员工列表区域 */
.employee-list {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 员工列表表格 */
.employee-list .el-table {
  flex: 1;
  min-height: 0;
}

.employee-list .el-table__body-wrapper {
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
