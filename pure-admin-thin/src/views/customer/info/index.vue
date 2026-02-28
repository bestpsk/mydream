<template>
  <div class="customer-info-container">
    <el-card class="h-full flex flex-col">
      <template #header>
        <div class="card-header">
          <h2 class="text-lg">客户信息列表</h2>
          <el-button
            v-if="hasAuth('customer:info:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增客户
          </el-button>
        </div>
      </template>

      <div class="flex flex-col h-full">
        <div
          v-if="hasAuth('customer:info:view')"
          :class="['flex flex-col h-full', { 'full-screen': isFullScreen }]"
        >
          <!-- 搜索栏 -->
          <el-card class="mb-4" shadow="never">
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-4">
                <!-- 部门分段控制器 -->
                <div class="segmented-container">
                  <el-segmented
                    v-model="activeDepartment"
                    :options="departmentOptions"
                    @change="handleDepartmentChange"
                  />
                </div>
                <span class="text-sm font-bold">客户姓名</span>
                <el-input
                  v-model="searchForm.customerName"
                  placeholder="请输入客户姓名"
                  clearable
                  style="width: 120px"
                  @clear="handleSearch"
                  @keyup.enter="handleSearch"
                />
                <span class="text-sm font-bold">手机号码</span>
                <el-input
                  v-model="searchForm.phone"
                  placeholder="请输入手机号码"
                  clearable
                  style="width: 120px"
                  @clear="handleSearch"
                  @keyup.enter="handleSearch"
                />
                <span class="text-sm font-bold">客户等级</span>
                <el-select
                  v-model="searchForm.level"
                  placeholder="请选择客户等级"
                  clearable
                  style="width: 100px"
                >
                  <el-option label="普通客户" value="普通客户" />
                  <el-option label="银卡客户" value="银卡客户" />
                  <el-option label="金卡客户" value="金卡客户" />
                  <el-option label="钻石客户" value="钻石客户" />
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
            </div>
          </el-card>

          <!-- 客户信息表格 -->
          <div class="flex-1 min-h-0">
            <el-table
              v-loading="loading"
              :data="customerList"
              style="width: 100%"
              class="h-full table-no-vertical-lines"
              :size="tableDensity"
              :max-height="
                isFullScreen ? `calc(100vh - 150px)` : `calc(100vh - 350px)`
              "
            >
              <el-table-column prop="id" label="ID" width="60" />
              <el-table-column v-if="visibleFields.storeName" label="分店">
                <template #default="scope">
                  {{ scope.row.storeName }}
                </template>
              </el-table-column>
              <el-table-column
                v-if="visibleFields.memberCard"
                prop="memberCard"
                label="会员卡号"
              />
              <el-table-column
                v-if="visibleFields.name"
                label="姓名"
                width="280"
              >
                <template #default="scope">
                  {{ scope.row.name }}
                  <span v-if="scope.row.departmentName">
                    <el-tag
                      v-for="(dept, index) in scope.row.departmentName.split(
                        ','
                      )"
                      :key="index"
                      size="small"
                      style="margin-left: 8px"
                    >
                      {{ dept.trim() }}
                    </el-tag>
                  </span>
                  <span v-else>
                    <el-tag size="small" style="margin-left: 8px" type="info"
                      >未分配</el-tag
                    >
                  </span>
                </template>
              </el-table-column>
              <el-table-column
                v-if="visibleFields.gender"
                prop="gender"
                label="性别"
                width="80"
              >
                <template #default="scope">
                  {{ scope.row.gender === 1 ? "男" : "女" }}
                </template>
              </el-table-column>
              <el-table-column
                v-if="visibleFields.phone"
                prop="phone"
                label="电话"
              />
              <el-table-column
                v-if="visibleFields.birthday"
                prop="birthday"
                label="生日"
              />
              <el-table-column
                v-if="visibleFields.birthdayType"
                prop="birthdayType"
                label="生日类别"
                width="100"
              >
                <template #default="scope">
                  {{ scope.row.birthdayType === 1 ? "阳历" : "阴历" }}
                </template>
              </el-table-column>
              <el-table-column
                v-if="visibleFields.points"
                prop="points"
                label="积分"
                width="80"
              />
              <el-table-column
                v-if="visibleFields.registerTime"
                prop="registerTime"
                label="注册时间"
                width="180"
              />
              <el-table-column
                v-if="visibleFields.source"
                prop="source"
                label="来源"
              />
              <el-table-column
                v-if="visibleFields.avatar"
                prop="avatar"
                label="头像"
                width="100"
              >
                <template #default="scope">
                  <el-avatar
                    v-if="scope.row.avatar"
                    :src="scope.row.avatar"
                    size="small"
                  />
                  <el-avatar v-else size="small">{{
                    scope.row.name.charAt(0)
                  }}</el-avatar>
                </template>
              </el-table-column>
              <el-table-column
                v-if="visibleFields.archiveNumber"
                prop="archiveNumber"
                label="档案编号"
              />
              <el-table-column
                v-if="visibleFields.level"
                prop="level"
                label="级别"
              />
              <el-table-column
                v-if="visibleFields.carePreference"
                prop="carePreference"
                label="护理喜好"
              />
              <el-table-column
                v-if="visibleFields.serviceRequirement"
                prop="serviceRequirement"
                label="服务要求"
              />
              <el-table-column
                v-if="visibleFields.sixPeriodManagement"
                prop="sixPeriodManagement"
                label="六期管理"
              />
              <el-table-column
                v-if="visibleFields.serviceTaboo"
                prop="serviceTaboo"
                label="服务禁忌"
              />
              <el-table-column
                v-if="visibleFields.serviceStaffName"
                prop="serviceStaffName"
                label="服务人"
              />
              <el-table-column
                v-if="visibleFields.managerName"
                prop="managerName"
                label="管理人"
              />
              <el-table-column
                v-if="visibleFields.lastConsumeTime"
                prop="lastConsumeTime"
                label="最近消费时间"
                width="180"
              />
              <el-table-column
                v-if="visibleFields.lastDepleteTime"
                prop="lastDepleteTime"
                label="最近消耗时间"
                width="180"
              />
              <el-table-column
                v-if="visibleFields.remark"
                prop="remark"
                label="备注"
              />
              <el-table-column label="操作" width="200">
                <template #header>
                  <div class="toolbar-buttons">
                    <el-tooltip content="刷新" placement="top">
                      <div class="tooltip-wrapper">
                        <el-button circle size="small" @click="handleRefresh">
                          <el-icon><Refresh /></el-icon>
                        </el-button>
                      </div>
                    </el-tooltip>
                    <el-tooltip content="密度" placement="top">
                      <div class="tooltip-wrapper">
                        <el-dropdown @command="changeDensity">
                          <el-button circle size="small">
                            <el-icon><ArrowDown /></el-icon>
                          </el-button>
                          <template #dropdown>
                            <el-dropdown-menu>
                              <el-dropdown-item command="large"
                                >宽松</el-dropdown-item
                              >
                              <el-dropdown-item command="default"
                                >默认</el-dropdown-item
                              >
                              <el-dropdown-item command="small"
                                >紧凑</el-dropdown-item
                              >
                            </el-dropdown-menu>
                          </template>
                        </el-dropdown>
                      </div>
                    </el-tooltip>
                    <el-tooltip content="列设置" placement="top">
                      <div class="tooltip-wrapper">
                        <el-popover
                          placement="bottom"
                          :width="200"
                          trigger="click"
                        >
                          <template #reference>
                            <el-button circle size="small">
                              <el-icon><Edit /></el-icon>
                            </el-button>
                          </template>
                          <div class="field-popover">
                            <div class="flex justify-between items-center mb-2">
                              <span class="font-bold">列显示</span>
                              <el-button
                                size="small"
                                text
                                @click="resetFieldSetting"
                                >重置</el-button
                              >
                            </div>
                            <div
                              v-for="field in allFields"
                              :key="field.value"
                              class="field-item"
                            >
                              <el-checkbox
                                v-model="tempVisibleFields[field.value]"
                                :label="field.label"
                              />
                            </div>
                            <div class="mt-4 flex justify-end">
                              <el-button
                                size="small"
                                type="primary"
                                @click="confirmFieldChange"
                              >
                                <el-icon><Check /></el-icon>
                                确认
                              </el-button>
                            </div>
                          </div>
                        </el-popover>
                      </div>
                    </el-tooltip>
                    <el-tooltip content="导出Excel" placement="top">
                      <div class="tooltip-wrapper">
                        <el-button
                          circle
                          size="small"
                          @click="handleExportExcel"
                        >
                          <el-icon><Download /></el-icon>
                        </el-button>
                      </div>
                    </el-tooltip>
                    <el-tooltip
                      :content="isFullScreen ? '退出全屏' : '全屏'"
                      placement="top"
                    >
                      <div class="tooltip-wrapper">
                        <el-button
                          circle
                          size="small"
                          @click="handleFullScreen"
                        >
                          <el-icon><FullScreen /></el-icon>
                        </el-button>
                      </div>
                    </el-tooltip>
                  </div>
                </template>
                <template #default="scope">
                  <el-button
                    v-if="hasAuth('customer:info:edit')"
                    type="primary"
                    size="small"
                    @click="handleEdit(scope.row)"
                  >
                    <el-icon><Edit /></el-icon>
                    编辑
                  </el-button>
                  <el-button
                    v-if="hasAuth('customer:info:delete')"
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
            <div class="pagination mt-4">
              <el-pagination
                v-model:current-page="pagination.current"
                v-model:page-size="pagination.pageSize"
                :page-sizes="[10, 20, 50, 100]"
                layout="total, sizes, prev, pager, next, jumper"
                :total="pagination.total"
                size="small"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
              />
            </div>
          </div>
        </div>
        <div
          v-else
          class="no-permission flex-1 flex items-center justify-center"
        >
          <!-- 无权限提示 -->
          <el-empty description="无权限查看数据" />
        </div>
      </div>

      <!-- 新增/编辑对话框 -->
      <el-dialog
        v-model="dialogVisible"
        :title="dialogType === 'add' ? '新增客户' : '编辑客户'"
        width="800px"
      >
        <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="客户姓名" prop="name">
                <el-input v-model="form.name" placeholder="请输入客户姓名" />
              </el-form-item>
              <el-form-item label="手机号码" prop="phone">
                <el-input v-model="form.phone" placeholder="请输入手机号码" />
              </el-form-item>
              <el-form-item label="性别" prop="gender">
                <el-select v-model="form.gender" placeholder="请选择性别">
                  <el-option label="男" value="1" />
                  <el-option label="女" value="2" />
                </el-select>
              </el-form-item>
              <el-form-item label="生日" prop="birthday">
                <el-date-picker
                  v-model="form.birthday"
                  type="date"
                  placeholder="请选择生日"
                  style="width: 100%"
                />
              </el-form-item>
              <el-form-item label="生日类别" prop="birthdayType">
                <el-select
                  v-model="form.birthdayType"
                  placeholder="请选择生日类别"
                >
                  <el-option label="阳历" value="1" />
                  <el-option label="阴历" value="2" />
                </el-select>
              </el-form-item>
              <el-form-item label="积分" prop="points">
                <el-input-number
                  v-model="form.points"
                  :min="0"
                  placeholder="请输入积分"
                />
              </el-form-item>
              <el-form-item label="注册时间" prop="registerTime">
                <el-date-picker
                  v-model="form.registerTime"
                  type="datetime"
                  placeholder="请选择注册时间"
                  style="width: 100%"
                />
              </el-form-item>
              <el-form-item label="来源" prop="source">
                <el-input v-model="form.source" placeholder="请输入客户来源" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="头像" prop="avatar">
                <el-upload
                  class="avatar-uploader"
                  action="/api/upload"
                  :show-file-list="false"
                  :on-success="handleAvatarSuccess"
                  :before-upload="beforeAvatarUpload"
                >
                  <el-avatar
                    v-if="form.avatar"
                    :src="form.avatar"
                    size="large"
                  />
                  <el-avatar v-else size="large">
                    <Plus />
                  </el-avatar>
                </el-upload>
              </el-form-item>
              <el-form-item label="会员卡" prop="memberCard">
                <el-input
                  v-model="form.memberCard"
                  placeholder="请输入会员卡号码"
                />
              </el-form-item>
              <el-form-item label="所属门店" prop="storeId">
                <el-select v-model="form.storeId" placeholder="请选择所属门店">
                  <el-option
                    v-for="store in storeStore.stores"
                    :key="store.id"
                    :label="store.name"
                    :value="store.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="所属部门" prop="departmentIds">
                <el-select
                  v-model="form.departmentIds"
                  placeholder="请选择所属部门"
                  multiple
                >
                  <el-option
                    v-for="dept in coreDepartments"
                    :key="dept.id"
                    :label="dept.name"
                    :value="dept.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="服务人" prop="serviceStaffIds">
                <el-select
                  v-model="form.serviceStaffIds"
                  placeholder="请选择服务人"
                  multiple
                >
                  <el-option
                    v-for="employee in employees"
                    :key="employee.id"
                    :label="employee.name"
                    :value="employee.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="管理人" prop="managerIds">
                <el-select
                  v-model="form.managerIds"
                  placeholder="请选择管理人"
                  multiple
                >
                  <el-option label="无" value="0" />
                  <el-option
                    v-for="employee in employees"
                    :key="employee.id"
                    :label="employee.name"
                    :value="employee.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="备注" prop="remark">
                <el-input
                  v-model="form.remark"
                  type="textarea"
                  placeholder="请输入备注信息"
                  :rows="3"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :span="12">
              <el-form-item label="护理喜好">
                <el-input
                  v-model="form.carePreference"
                  type="textarea"
                  placeholder="请输入客户的护理喜好"
                  :rows="2"
                />
              </el-form-item>
              <el-form-item label="服务要求">
                <el-input
                  v-model="form.serviceRequirement"
                  type="textarea"
                  placeholder="请输入客户的服务要求"
                  :rows="2"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="六期管理">
                <el-select
                  v-model="form.sixPeriodManagement"
                  placeholder="请选择六期管理状态"
                >
                  <el-option label="月经期" value="月经期" />
                  <el-option label="排卵期" value="排卵期" />
                  <el-option label="安全期" value="安全期" />
                  <el-option label="妊娠期" value="妊娠期" />
                  <el-option label="哺乳期" value="哺乳期" />
                  <el-option label="更年期" value="更年期" />
                </el-select>
              </el-form-item>
              <el-form-item label="服务禁忌">
                <el-input
                  v-model="form.serviceTaboo"
                  type="textarea"
                  placeholder="请输入客户的服务禁忌"
                  :rows="2"
                />
              </el-form-item>
            </el-col>
          </el-row>
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
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onActivated, watch } from "vue";
import {
  Plus,
  ArrowDown,
  Edit,
  Delete,
  Search,
  Refresh,
  Close,
  Check,
  FullScreen,
  Download
} from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import { http } from "@/utils/http";
import { hasAuth } from "@/router/utils";
import { useStoreStore } from "@/store/modules/store";
import { useDataCacheStoreHook } from "@/store/modules/dataCache";
import { useCompanyChange, useStoreChange } from "@/composables/useCompanyChange";

// 加载状态
const loading = ref(false);

// 门店状态管理
const storeStore = useStoreStore();
// 数据缓存store
const dataCacheStore = useDataCacheStoreHook();

// 部门分段控制器
const activeDepartment = ref(0); // 0: 全部, 其他为部门ID
const departmentOptions = ref([
  { label: "全部", value: 0 }
]);
const coreDepartments = ref<any[]>([]);
const employees = ref<any[]>([]);

// 表格状态管理
const tableDensity = ref("default"); // 表格密度：'default', 'compact', 'comfortable'
const isFullScreen = ref(false); // 是否全屏状态

// 字段自定义显示
const allFields = ref([
  { label: "分店", value: "storeName", default: true },
  { label: "会员卡号", value: "memberCard", default: true },
  { label: "姓名", value: "name", default: true },
  { label: "性别", value: "gender", default: true },
  { label: "电话", value: "phone", default: true },
  { label: "生日", value: "birthday", default: false },
  { label: "生日类别", value: "birthdayType", default: false },
  { label: "积分", value: "points", default: true },
  { label: "注册时间", value: "registerTime", default: false },
  { label: "来源", value: "source", default: false },
  { label: "头像", value: "avatar", default: false },
  { label: "档案编号", value: "archiveNumber", default: false },
  { label: "级别", value: "level", default: true },
  { label: "护理喜好", value: "carePreference", default: false },
  { label: "服务要求", value: "serviceRequirement", default: false },
  { label: "六期管理", value: "sixPeriodManagement", default: false },
  { label: "服务禁忌", value: "serviceTaboo", default: false },
  { label: "服务人", value: "serviceStaffName", default: false },
  { label: "管理人", value: "managerName", default: false },
  { label: "最近消费时间", value: "lastConsumeTime", default: false },
  { label: "最近消耗时间", value: "lastDepleteTime", default: false },
  { label: "备注", value: "remark", default: false }
]);

const visibleFields = ref<any>({});
const tempVisibleFields = ref<any>({});

// 初始化字段显示状态
const initVisibleFields = () => {
  const savedFields = localStorage.getItem("customerVisibleFields");
  if (savedFields) {
    visibleFields.value = JSON.parse(savedFields);
  } else {
    // 使用默认值
    const defaultFields: any = {};
    allFields.value.forEach(field => {
      defaultFields[field.value] = field.default;
    });
    visibleFields.value = defaultFields;
  }
  // 初始化临时字段显示状态
  tempVisibleFields.value = { ...visibleFields.value };
};

// 监听门店变化，重新获取客户列表
watch(
  () => storeStore.currentStore,
  newStore => {
    if (newStore) {
      getCustomerList();
      // 只有当表单中没有选择门店时，才更新员工列表
      if (!form.storeId) {
        form.storeId = newStore.id;
        getEmployees();
      }
    }
  }
);

// 搜索表单
const searchForm = reactive({
  customerName: "",
  phone: "",
  level: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 客户列表
const customerList = ref<any[]>([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 表单数据
const form = reactive({
  id: 0,
  name: "",
  phone: "",
  gender: "1",
  birthday: "",
  birthdayType: "1",
  points: 0,
  registerTime: new Date().toISOString().slice(0, 19).replace("T", " "),
  source: "",
  avatar: "",
  memberCard: "",
  storeId: 0,
  departmentIds: [],
  serviceStaffIds: [], // 服务人ID数组
  managerIds: [],
  remark: "",
  carePreference: "",
  serviceRequirement: "",
  sixPeriodManagement: "",
  serviceTaboo: ""
});

// 监听所属部门变化，重新获取员工列表
watch(
  () => form.departmentIds,
  (newDepartmentIds, oldDepartmentIds) => {
    // 只有当部门ID实际变化且有门店ID时才获取员工列表
    if (
      JSON.stringify(newDepartmentIds) !== JSON.stringify(oldDepartmentIds) &&
      form.storeId
    ) {
      getEmployees();
    }
  },
  { deep: true }
);

// 监听门店变化，重新获取员工列表
watch(
  () => form.storeId,
  (newStoreId, oldStoreId) => {
    // 只有当门店ID实际变化且不为0时才获取员工列表
    if (newStoreId !== oldStoreId && newStoreId) {
      getEmployees();
    }
  }
);

// 监听服务人变化，自动添加管理人为服务人的上级
watch(
  () => form.serviceStaffIds,
  async (newServiceStaffIds, oldServiceStaffIds) => {
    // 只有当服务人ID实际变化时才获取上级信息
    if (
      JSON.stringify(newServiceStaffIds) !== JSON.stringify(oldServiceStaffIds)
    ) {
      try {
        // 遍历所有新选择的服务人
        for (const employeeId of newServiceStaffIds) {
          // 调用后端API获取服务人的上级信息
          const response = await http.request(
            "get",
            "/api/customer/get-superior",
            {
              params: {
                employeeId: employeeId
              }
            }
          );
          if (response.code === 200 && response.data.superior) {
            // 获取上级信息
            const superior = response.data.superior;
            // 检查上级是否已经在employees数组中
            const isSuperiorInEmployees = employees.value.some(
              emp => emp.id === superior.id
            );
            // 如果上级不在employees数组中，就添加进去
            if (!isSuperiorInEmployees) {
              employees.value.push(superior);
            }
            // 检查上级是否已经在managerIds数组中
            const isSuperiorInManagers = form.managerIds.includes(superior.id);
            // 如果上级不在managerIds数组中，就添加进去
            if (!isSuperiorInManagers) {
              // 强制更新管理人的显示文本
              const tempManagerIds = [...form.managerIds, superior.id];
              form.managerIds = [];
              setTimeout(() => {
                form.managerIds = tempManagerIds;
              }, 0);
            }
          }
        }
      } catch (error) {
        console.error("获取服务人上级信息失败:", error);
      }
    }
  },
  { deep: true }
);

// 表单验证规则
const rules = reactive<FormRules>({
  name: [{ required: true, message: "请输入客户姓名", trigger: "blur" }],
  phone: [
    { required: true, message: "请输入手机号码", trigger: "blur" },
    {
      pattern: /^1[3-9]\d{9}$/,
      message: "请输入正确的手机号码",
      trigger: "blur"
    }
  ],
  gender: [{ required: true, message: "请选择性别", trigger: "blur" }],
  storeId: [{ required: true, message: "请选择所属门店", trigger: "blur" }],
  departmentIds: [
    { required: true, message: "请选择所属部门", trigger: "blur" }
  ],
  memberCard: [{ required: true, message: "请输入会员卡号", trigger: "blur" }],
  managerIds: [{ type: "array", required: false }],
  serviceStaffIds: [{ type: "array", required: false }]
});

// 初始化数据
onMounted(async () => {
  if (hasAuth("customer:info:view")) {
    initVisibleFields();
    // 初始化门店数据
    await storeStore.initStores();
    // 强制获取核心部门，不依赖缓存
    await getCoreDepartments(true);
    // 只有当有默认门店时，才初始化员工列表
    if (storeStore.currentStore) {
      form.storeId = storeStore.currentStore.id;
      await getEmployees();
    }
    // 获取客户列表
    await getCustomerList();
  }
});

// 当组件重新激活时（从其他页面返回），重新获取部门数据
const onActivated = async () => {
  if (hasAuth("customer:info:view")) {
    // 强制获取核心部门，不依赖缓存
    await getCoreDepartments(true);
    // 重新获取客户列表
    await getCustomerList();
  }
};

// 监听公司变化，重新加载数据
useCompanyChange(async () => {
  if (hasAuth("customer:info:view")) {
    dataCacheStore.clearCache();
    await getCoreDepartments(true);
    await getCustomerList();
  }
});

// 监听门店变化，重新加载客户列表
useStoreChange(() => {
  if (hasAuth("customer:info:view")) {
    getCustomerList();
  }
});

// 获取核心业务部门
const getCoreDepartments = async (forceRefresh = false) => {
  try {
    if (
      !forceRefresh &&
      !dataCacheStore.isDepartmentsExpired &&
      dataCacheStore.cachedDepartments.length > 0
    ) {
      coreDepartments.value = dataCacheStore.cachedDepartments;
    } else {
      const response = await http.request(
        "get",
        "/api/customer/core-departments"
      );
      if (response.code === 200) {
        dataCacheStore.updateDepartments(response.data);
        coreDepartments.value = response.data;
      }
    }
    if (coreDepartments.value.length > 0) {
      departmentOptions.value = [{ label: "全部", value: 0 }];
      coreDepartments.value.forEach((dept: any) => {
        departmentOptions.value.push({
          label: dept.name,
          value: dept.id
        });
      });
      activeDepartment.value = 0;
    } else {
      departmentOptions.value = [{ label: "全部", value: 0 }];
      activeDepartment.value = 0;
    }
  } catch (error) {
    console.error("获取核心业务部门失败:", error);
    departmentOptions.value = [{ label: "全部", value: 0 }];
    activeDepartment.value = 0;
  }
};

// 获取员工列表
const getEmployees = async () => {
  try {
    // 确定使用哪个storeId
    const currentStoreId = form.storeId || storeStore.currentStore?.id;

    // 只有当storeId有效且不为0时才尝试获取员工列表
    if (!currentStoreId || currentStoreId === 0) {
      employees.value = [];
      return;
    }

    // 调用后端API获取员工列表
    const response = await http.request("get", "/api/customer/employees", {
      params: {
        storeId: currentStoreId,
        departmentIds: form.departmentIds
      }
    });
    if (response.code === 200) {
      employees.value = response.data;
    }
  } catch (error) {
    console.error("获取员工列表失败:", error);
    employees.value = [];
  }
};

// 获取客户列表
const getCustomerList = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.current,
      pageSize: pagination.pageSize,
      name: searchForm.customerName || undefined,
      phone: searchForm.phone || undefined,
      level: searchForm.level || undefined,
      storeId: storeStore.currentStore?.id || undefined,
      departmentId: activeDepartment.value > 0 ? activeDepartment.value : undefined
    };

    const response = await http.request("get", "/api/customer/list", {
      params
    });
    if (response.code === 200) {
      // 为每个客户添加新字段的默认值
      customerList.value = response.data.list.map((customer: any) => {
        return {
          ...customer,
          carePreference: customer.carePreference || "无",
          serviceRequirement: customer.serviceRequirement || "无",
          sixPeriodManagement: customer.sixPeriodManagement || "无",
          serviceTaboo: customer.serviceTaboo || "无"
        };
      });
      pagination.total = response.data.total;
    }
  } catch (error) {
    console.error("获取客户列表失败:", error);
    ElMessage.error("获取客户列表失败");
    customerList.value = [];
    pagination.total = 0;
  } finally {
    loading.value = false;
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getCustomerList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.customerName = "";
  searchForm.phone = "";
  searchForm.level = "";
  pagination.current = 1;
  getCustomerList();
};

// 处理部门分段控制变化
const handleDepartmentChange = () => {
  pagination.current = 1;
  getCustomerList();
};

// 确认字段显示变更
const confirmFieldChange = () => {
  // 更新实际的字段显示状态
  visibleFields.value = { ...tempVisibleFields.value };
  // 保存到本地存储
  localStorage.setItem(
    "customerVisibleFields",
    JSON.stringify(visibleFields.value)
  );
  // 触发视图更新
  customerList.value = [...customerList.value];
};

// 重置字段显示设置
const resetFieldSetting = () => {
  // 使用默认值重置临时字段显示状态
  const defaultFields: any = {};
  allFields.value.forEach(field => {
    defaultFields[field.value] = field.default;
  });
  tempVisibleFields.value = defaultFields;
};

// 刷新客户列表
const handleRefresh = () => {
  pagination.current = 1;
  getCustomerList();
};

// 切换表格密度
const handleDensityChange = (density: string) => {
  tableDensity.value = density;
};

// 改变表格密度的方法
const changeDensity = (density: string) => {
  tableDensity.value = density;
  // 强制刷新表格
  customerList.value = [...customerList.value];
};

// 切换全屏状态
const handleFullScreen = () => {
  isFullScreen.value = !isFullScreen.value;
  // 触发视图更新
  customerList.value = [...customerList.value];
};

// 导出Excel
const handleExportExcel = () => {
  import("xlsx")
    .then(XLSX => {
      // 准备导出数据
      const exportData = customerList.value.map(customer => {
        return {
          ID: customer.id,
          分店: customer.storeName,
          姓名: customer.name,
          部门: customer.departmentName || "未分配",
          性别: customer.gender === 1 ? "男" : "女",
          电话: customer.phone,
          会员卡号: customer.memberCard,
          客户等级: customer.level,
          积分: customer.points,
          服务人: customer.serviceStaffName,
          管理人: customer.managerName
        };
      });

      // 创建工作簿
      const wb = XLSX.utils.book_new();
      // 创建工作表
      const ws = XLSX.utils.json_to_sheet(exportData);
      // 将工作表添加到工作簿
      XLSX.utils.book_append_sheet(wb, ws, "客户信息");
      // 导出文件
      XLSX.writeFile(
        wb,
        `客户信息列表_${new Date().toISOString().slice(0, 10)}.xlsx`
      );

      ElMessage.success("导出成功");
    })
    .catch(error => {
      console.error("导出失败:", error);
      ElMessage.error("导出失败");
    });
};

// 处理头像上传成功
const handleAvatarSuccess = (response: any, file: any) => {
  if (response.code === 200) {
    form.avatar = response.data.url;
    ElMessage.success("头像上传成功");
  } else {
    ElMessage.error("头像上传失败");
  }
};

// 处理头像上传前验证
const beforeAvatarUpload = (file: any) => {
  const isJpgOrPng = file.type === "image/jpeg" || file.type === "image/png";
  const isLt2M = file.size / 1024 / 1024 < 2;

  if (!isJpgOrPng) {
    ElMessage.error("只能上传JPG或PNG格式的图片");
  }
  if (!isLt2M) {
    ElMessage.error("图片大小不能超过2MB");
  }

  return isJpgOrPng && isLt2M;
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getCustomerList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getCustomerList();
};

// 新增客户
const handleAdd = async () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  form.name = "";
  form.phone = "";
  form.gender = "1";
  form.birthday = "";
  form.birthdayType = "1";
  form.points = 0;
  form.registerTime = new Date().toISOString().slice(0, 19).replace("T", " ");
  form.source = "";
  form.avatar = "";
  form.memberCard = "";
  form.storeId = storeStore.currentStore?.id || 0;
  form.departmentIds = [];
  form.serviceStaffIds = [];
  form.managerIds = [];
  form.remark = "";
  form.carePreference = "";
  form.serviceRequirement = "";
  form.sixPeriodManagement = "";
  form.serviceTaboo = "";
  // 初始化员工列表
  if (form.storeId) {
    await getEmployees();
  } else {
    employees.value = [];
  }
  dialogVisible.value = true;
};

// 编辑客户
const handleEdit = async (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.id;
  form.name = row.name;
  form.phone = row.phone;
  form.gender = row.gender.toString();
  form.birthday = row.birthday || "";
  form.birthdayType = row.birthdayType?.toString() || "1";
  form.points = row.points || 0;
  form.registerTime =
    row.registerTime || new Date().toISOString().slice(0, 19).replace("T", " ");
  form.source = row.source || "";
  form.avatar = row.avatar || "";
  form.memberCard = row.memberCard;

  // 保存原始值，用于比较是否需要重新获取员工列表
  const originalStoreId = form.storeId;
  const originalDepartmentIds = [...form.departmentIds];

  form.storeId = row.storeId;
  form.departmentIds = Array.isArray(row.departmentIds)
    ? row.departmentIds
    : row.departmentId
      ? [row.departmentId]
      : [];
  form.serviceStaffIds = Array.isArray(row.serviceStaffIds)
    ? row.serviceStaffIds
    : row.serviceStaffId
      ? [row.serviceStaffId]
      : [];
  form.managerIds = Array.isArray(row.managerIds)
    ? row.managerIds
    : row.managerId
      ? [row.managerId]
      : [];
  form.remark = row.remark || "";
  form.carePreference = row.carePreference || "";
  form.serviceRequirement = row.serviceRequirement || "";
  form.sixPeriodManagement = row.sixPeriodManagement || "";
  form.serviceTaboo = row.serviceTaboo || "";

  // 只有当门店或部门发生变化时，才重新获取员工列表
  if (
    form.storeId !== originalStoreId ||
    JSON.stringify(form.departmentIds) !== JSON.stringify(originalDepartmentIds)
  ) {
    await getEmployees();
  }

  // 强制更新服务人和管理人的选择器的显示文本
  const tempServiceStaffIds = [...form.serviceStaffIds];
  const tempManagerIds = [...form.managerIds];
  form.serviceStaffIds = [];
  form.managerIds = [];
  setTimeout(() => {
    form.serviceStaffIds = tempServiceStaffIds;
    form.managerIds = tempManagerIds;
  }, 0);

  dialogVisible.value = true;
};

// 删除客户
const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该客户吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        // 实际项目中需要替换为真实的API调用
        // const response = await http.request("delete", `/api/customer/info/${id}`);
        // if (response.code === 200) {
        //   ElMessage.success("删除成功");
        //   getCustomerList();
        // }

        // 模拟删除成功
        ElMessage.success("删除成功");
        getCustomerList();
      } catch (error) {
        console.error("删除客户失败:", error);
        ElMessage.error("删除客户失败");
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
  try {
    // 验证表单
    await formRef.value.validate();

    loading.value = true;

    // 准备提交数据
    const submitData = {
      name: form.name,
      phone: form.phone,
      gender: parseInt(form.gender),
      birthday: form.birthday,
      birthdayType: parseInt(form.birthdayType),
      points: form.points,
      registerTime: form.registerTime,
      source: form.source,
      avatar: form.avatar,
      memberCard: form.memberCard,
      storeId: form.storeId,
      departmentIds: form.departmentIds,
      serviceStaffIds: form.serviceStaffIds,
      managerIds: form.managerIds,
      remark: form.remark
    };

    // 调用真实的API接口
    let response;
    if (dialogType.value === "add") {
      response = await http.request("post", "/api/customer/add", {
        data: submitData
      });
    } else {
      response = await http.request("put", `/api/customer/edit/${form.id}`, {
        data: submitData
      });
    }
    if (response.code === 200) {
      ElMessage.success(dialogType.value === "add" ? "新增成功" : "编辑成功");
      dialogVisible.value = false;
      getCustomerList();
    }
  } catch (error) {
    console.error("提交失败:", error);
    // 表单验证失败时，Element Plus会自动显示错误信息，不需要手动显示
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.customer-info-container {
  min-height: calc(100vh - 120px);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
  padding: 8px 16px;
  border-radius: 8px;
}

.table-toolbar {
  padding: 12px 16px;
  border-radius: 8px;
}

.field-popover {
  max-height: 300px;
  overflow-y: auto;
}

.field-item {
  margin-bottom: 8px;
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

/* 全屏样式 */
.full-screen {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: white;
  z-index: 9999;
  padding: 20px;
  overflow: auto;
}

.full-screen .el-table {
  max-height: calc(100vh - 120px);
}

/* 工具栏按钮间距 */
.toolbar-buttons {
  display: flex;
  align-items: center;
  gap: 4px; /* 固定间距为4px */
}

.toolbar-buttons > * {
  margin: 0 !important;
}

/* 表格密度样式 */
.table-density-comfortable {
  --el-table-row-height: 60px;
}

.table-density-default {
  --el-table-row-height: 48px;
}

.table-density-compact {
  --el-table-row-height: 36px;
}

/* 表格无竖线样式 */
.table-no-vertical-lines {
  border: none !important;
}

.table-no-vertical-lines :deep(.el-table__header-wrapper) {
  border: none !important;
}

.table-no-vertical-lines :deep(.el-table__body-wrapper) {
  border: none !important;
}

.table-no-vertical-lines :deep(.el-table__row) {
  border-left: none !important;
  border-right: none !important;
}

.table-no-vertical-lines :deep(.el-table__cell) {
  border-left: none !important;
  border-right: none !important;
}

/* Tooltip wrapper styles */
.tooltip-wrapper {
  display: inline-block;
  cursor: pointer;
}

/* 部门分段控制器样式 */
.segmented-container {
  width: auto;
}

/* 搜索栏元素间距 */
.search-bar .el-form-item {
  margin-right: 8px;
}
</style>
