<template>
  <div class="store-container">
    <el-card>
      <!-- 标签页 -->
      <el-tabs v-model="activeTab" @tab-click="handleTabClick">
        <!-- 门店管理 -->
        <el-tab-pane label="门店管理" name="store">
          <div class="tab-content">
            <div v-if="hasAuth('store:view')">
              <!-- 新增按钮和搜索栏放在同一行 -->
              <el-card class="mb-4" shadow="never">
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold">门店名称</span>
                    <el-input
                      v-model="searchForm.storeName"
                      placeholder="请输入门店名称"
                      clearable
                      style="width: 200px"
                      @clear="handleSearch"
                      @keyup.enter="handleSearch"
                    />
                    <span class="text-sm font-bold">门店类型</span>
                    <el-select
                      v-model="searchForm.storeType"
                      placeholder="请选择门店类型"
                      clearable
                      style="width: 160px"
                    >
                      <el-option label="美容" value="美容" />
                      <el-option label="美发" value="美发" />
                      <el-option label="综合" value="综合" />
                      <el-option label="美容美甲" value="美容美甲" />
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
                  <!-- 新增按钮 -->
                  <el-button
                    v-if="hasAuth('store:add')"
                    type="primary"
                    @click="handleAdd"
                  >
                    <el-icon><Plus /></el-icon>
                    新增门店
                  </el-button>
                </div>
              </el-card>

              <!-- 门店列表 -->
              <el-table
                v-loading="loading"
                :data="storeList"
                style="width: 100%"
              >
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="storeName" label="门店名称" />
                <el-table-column prop="phone" label="电话" />
                <el-table-column prop="address" label="地址" />
                <el-table-column prop="storeType" label="门店类型" />
                <el-table-column label="拥有部门">
                  <template #default="scope">
                    {{
                      scope.row
                        ? scope.row.departments
                          ? scope.row.departments
                              .split(",")
                              .map((deptId: string) => {
                                const dept = departmentList.find(
                                  item => String(item.id) === deptId
                                );
                                return dept ? dept.deptName : deptId;
                              })
                              .join(",")
                          : ""
                        : ""
                    }}
                  </template>
                </el-table-column>
                <el-table-column prop="createTime" label="创建时间" />
                <el-table-column label="操作" width="180">
                  <template #default="scope">
                    <el-button
                      v-if="hasAuth('store:edit')"
                      type="primary"
                      size="small"
                      @click="handleEdit(scope.row)"
                    >
                      <el-icon><Edit /></el-icon>
                      编辑
                    </el-button>
                    <el-button
                      v-if="hasAuth('store:delete')"
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
          </div>
        </el-tab-pane>

        <!-- 部门管理 -->
        <el-tab-pane label="部门管理" name="dept">
          <div class="tab-content">
            <div v-if="hasAuth('store:department:view')">
              <!-- 新增按钮和搜索栏放在同一行 -->
              <el-card class="mb-4" shadow="never">
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold">部门名称</span>
                    <el-input
                      v-model="deptSearchForm.deptName"
                      placeholder="请输入部门名称"
                      clearable
                      style="width: 200px"
                      @clear="handleDeptSearch"
                      @keyup.enter="handleDeptSearch"
                    />
                    <el-button type="primary" @click="handleDeptSearch">
                      <el-icon><Search /></el-icon>
                      搜索
                    </el-button>
                    <el-button @click="resetDeptSearch">
                      <el-icon><Refresh /></el-icon>
                      重置
                    </el-button>
                  </div>
                  <!-- 新增按钮 -->
                  <el-button
                    v-if="hasAuth('store:department:add')"
                    type="primary"
                    @click="handleAddDept"
                  >
                    <el-icon><Plus /></el-icon>
                    新增部门
                  </el-button>
                </div>
              </el-card>

              <!-- 部门列表 -->
              <el-table
                v-loading="deptLoading"
                :data="departmentList"
                style="width: 100%"
              >
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="deptName" label="部门名称" />
                <el-table-column label="上级部门">
                  <template #default="scope">
                    {{
                      scope.row.parentId === 0 || scope.row.parentId === "0"
                        ? "无"
                        : allDepts.find(
                            dept =>
                              String(dept.id) === String(scope.row.parentId)
                          )?.deptName || scope.row.parentId
                    }}
                  </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序" />
                <el-table-column
                  prop="enableCategory"
                  label="核心业务部门"
                  width="120"
                >
                  <template #default="scope">
                    <el-switch
                      v-if="hasAuth('store:department:edit')"
                      v-model="scope.row.enableCategory"
                      @change="handleDeptCategoryChange(scope.row)"
                    />
                    <span v-else>{{
                      scope.row.enableCategory ? "是" : "否"
                    }}</span>
                  </template>
                </el-table-column>
                <el-table-column prop="status" label="状态" width="100">
                  <template #default="scope">
                    <el-switch
                      v-if="hasAuth('store:department:edit')"
                      v-model="scope.row.status"
                      @change="handleDeptStatusChange(scope.row)"
                    />
                    <span v-else>{{ scope.row.status ? "启用" : "禁用" }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="180">
                  <template #default="scope">
                    <el-button
                      v-if="hasAuth('store:department:edit')"
                      type="primary"
                      size="small"
                      @click="handleEditDept(scope.row)"
                    >
                      <el-icon><Edit /></el-icon>
                      编辑
                    </el-button>
                    <el-button
                      v-if="hasAuth('store:department:delete')"
                      type="danger"
                      size="small"
                      @click="handleDeleteDept(scope.row.id)"
                    >
                      <el-icon><Delete /></el-icon>
                      删除
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
            <!-- 无权限提示 -->
            <div v-else class="no-permission">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>

        <!-- 职位管理 -->
        <el-tab-pane label="职位管理" name="position">
          <div class="tab-content">
            <div v-if="hasAuth('store:position:view')">
              <!-- 新增按钮和搜索栏放在同一行 -->
              <el-card class="mb-4" shadow="never">
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold">职位名称</span>
                    <el-input
                      v-model="positionSearchForm.positionName"
                      placeholder="请输入职位名称"
                      clearable
                      style="width: 200px"
                      @clear="handlePositionSearch"
                      @keyup.enter="handlePositionSearch"
                    />
                    <span class="text-sm font-bold">所属部门</span>
                    <el-select
                      v-model="positionSearchForm.deptId"
                      placeholder="请选择所属部门"
                      clearable
                      style="width: 200px"
                    >
                      <el-option label="全部" value="" />
                      <el-option
                        v-for="dept in departmentList"
                        :key="dept.id"
                        :label="dept.deptName"
                        :value="dept.id"
                      />
                    </el-select>
                    <el-button type="primary" @click="handlePositionSearch">
                      <el-icon><Search /></el-icon>
                      搜索
                    </el-button>
                    <el-button @click="resetPositionSearch">
                      <el-icon><Refresh /></el-icon>
                      重置
                    </el-button>
                  </div>
                  <!-- 新增按钮 -->
                  <el-button
                    v-if="hasAuth('store:position:add')"
                    type="primary"
                    @click="handleAddPosition"
                  >
                    <el-icon><Plus /></el-icon>
                    新增职位
                  </el-button>
                </div>
              </el-card>

              <!-- 职位列表 -->
              <el-table
                v-loading="positionLoading"
                :data="positionList"
                style="width: 100%"
              >
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="positionName" label="职位名称" />
                <el-table-column label="所属部门">
                  <template #default="scope">
                    {{
                      scope.row.deptId === 0 || scope.row.deptId === "0"
                        ? "无"
                        : allDepts.find(
                            dept => String(dept.id) === String(scope.row.deptId)
                          )?.deptName || scope.row.deptId
                    }}
                  </template>
                </el-table-column>
                <el-table-column prop="sort" label="排序" />
                <el-table-column prop="status" label="状态" width="100">
                  <template #default="scope">
                    <el-switch
                      v-if="hasAuth('store:position:edit')"
                      v-model="scope.row.status"
                      @change="handlePositionStatusChange(scope.row)"
                    />
                    <span v-else>{{ scope.row.status ? "启用" : "禁用" }}</span>
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="180">
                  <template #default="scope">
                    <el-button
                      v-if="hasAuth('store:position:edit')"
                      type="primary"
                      size="small"
                      @click="handleEditPosition(scope.row)"
                    >
                      <el-icon><Edit /></el-icon>
                      编辑
                    </el-button>
                    <el-button
                      v-if="hasAuth('store:position:delete')"
                      type="danger"
                      size="small"
                      @click="handleDeletePosition(scope.row.id)"
                    >
                      <el-icon><Delete /></el-icon>
                      删除
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
            <!-- 无权限提示 -->
            <div v-else class="no-permission">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>

        <!-- 房间设置 -->
        <el-tab-pane label="房间设置" name="room">
          <div class="tab-content">
            <div v-if="hasAuth('store:room:view')">
              <!-- 新增按钮和搜索栏放在同一行 -->
              <el-card class="mb-4" shadow="never">
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold">门店名称</span>
                    <el-select
                      v-model="roomSearchForm.storeId"
                      placeholder="请选择门店"
                      clearable
                      style="width: 200px"
                    >
                      <el-option label="全部" value="" />
                      <el-option
                        v-for="store in storeList"
                        :key="store.id"
                        :label="store.storeName"
                        :value="store.id"
                      />
                    </el-select>
                    <span class="text-sm font-bold">房间名称</span>
                    <el-input
                      v-model="roomSearchForm.roomName"
                      placeholder="请输入房间名称"
                      clearable
                      style="width: 200px"
                      @clear="handleRoomSearch"
                      @keyup.enter="handleRoomSearch"
                    />
                    <el-button type="primary" @click="handleRoomSearch">
                      <el-icon><Search /></el-icon>
                      搜索
                    </el-button>
                    <el-button @click="resetRoomSearch">
                      <el-icon><Refresh /></el-icon>
                      重置
                    </el-button>
                  </div>
                  <!-- 新增按钮 -->
                  <el-button
                    v-if="hasAuth('store:room:add')"
                    type="primary"
                    @click="handleAddRoom"
                  >
                    <el-icon><Plus /></el-icon>
                    新增房间
                  </el-button>
                </div>
              </el-card>

              <!-- 房间列表 -->
              <el-table
                v-loading="roomLoading"
                :data="roomList"
                style="width: 100%"
              >
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column label="所属门店">
                  <template #default="scope">
                    {{
                      scope.row
                        ? storeList.find(
                            store =>
                              String(store.id) === String(scope.row.storeId)
                          )?.storeName || scope.row.storeId
                        : ""
                    }}
                  </template>
                </el-table-column>
                <el-table-column prop="roomName" label="房间名称" />
                <el-table-column prop="bedCount" label="床位数量" />
                <el-table-column prop="createTime" label="创建时间" />
                <el-table-column label="操作" width="180">
                  <template #default="scope">
                    <template v-if="scope.row">
                      <el-button
                        v-if="hasAuth('store:room:edit')"
                        type="primary"
                        size="small"
                        @click="handleEditRoom(scope.row)"
                      >
                        <el-icon><Edit /></el-icon>
                        编辑
                      </el-button>
                      <el-button
                        v-if="hasAuth('store:room:delete')"
                        type="danger"
                        size="small"
                        @click="handleDeleteRoom(scope.row.id)"
                      >
                        <el-icon><Delete /></el-icon>
                        删除
                      </el-button>
                    </template>
                  </template>
                </el-table-column>
              </el-table>

              <!-- 分页 -->
              <div class="pagination">
                <el-pagination
                  v-model:current-page="roomPagination.current"
                  v-model:page-size="roomPagination.pageSize"
                  :page-sizes="[10, 20, 50, 100]"
                  layout="total, sizes, prev, pager, next, jumper"
                  :total="roomPagination.total"
                  @size-change="handleRoomSizeChange"
                  @current-change="handleRoomCurrentChange"
                />
              </div>
            </div>
            <!-- 无权限提示 -->
            <div v-else class="no-permission">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 新增/编辑门店对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增门店' : '编辑门店'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules">
        <el-form-item label="所属公司" prop="companyId">
          <el-select
            v-model="form.companyId"
            placeholder="请选择所属公司"
            :disabled="!isSuperAdmin"
          >
            <el-option
              v-for="company in companyList"
              :key="company.id"
              :label="company.companyName"
              :value="company.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="门店名称" prop="storeName">
          <el-input v-model="form.storeName" placeholder="请输入门店名称" />
        </el-form-item>
        <el-form-item label="电话" prop="phone">
          <el-input v-model="form.phone" placeholder="请输入联系电话" />
        </el-form-item>
        <el-form-item label="地址" prop="address">
          <el-input v-model="form.address" placeholder="请输入门店地址" />
        </el-form-item>
        <el-form-item label="门店类型" prop="storeType">
          <el-select v-model="form.storeType" placeholder="请选择门店类型">
            <el-option label="美容" value="美容" />
            <el-option label="美发" value="美发" />
            <el-option label="综合" value="综合" />
            <el-option label="美容美甲" value="美容美甲" />
            <el-option label="养生" value="养生" />
          </el-select>
        </el-form-item>
        <el-form-item label="拥有部门" prop="departments">
          <el-select
            v-model="form.departments"
            multiple
            placeholder="请选择拥有部门"
          >
            <el-option
              v-for="dept in departmentList"
              :key="dept.id"
              :label="dept.deptName"
              :value="dept.id"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmit">确定</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 新增/编辑部门对话框 -->
    <el-dialog
      v-model="deptDialogVisible"
      :title="deptDialogType === 'add' ? '新增部门' : '编辑部门'"
      width="500px"
    >
      <el-form ref="deptFormRef" :model="deptForm" :rules="deptRules">
        <el-form-item label="所属公司" prop="companyId">
          <el-select
            v-model="deptForm.companyId"
            placeholder="请选择所属公司"
            :disabled="!isSuperAdmin"
          >
            <el-option label="无" :value="0" />
            <el-option
              v-for="company in companyList"
              :key="company.id"
              :label="company.companyName"
              :value="company.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="部门名称" prop="deptName">
          <el-input v-model="deptForm.deptName" placeholder="请输入部门名称" />
        </el-form-item>
        <el-form-item label="上级部门" prop="parentId">
          <el-select v-model="deptForm.parentId" placeholder="请选择上级部门">
            <el-option label="无" value="0" />
            <el-option
              v-for="dept in allDepts"
              :key="dept.id"
              :label="dept.deptName"
              :value="dept.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number
            v-model="deptForm.sort"
            :min="0"
            placeholder="请输入排序"
          />
        </el-form-item>
        <el-form-item label="核心业务部门" prop="enableCategory">
          <el-switch v-model="deptForm.enableCategory" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="deptDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmitDept">确定</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 新增/编辑职位对话框 -->
    <el-dialog
      v-model="positionDialogVisible"
      :title="positionDialogType === 'add' ? '新增职位' : '编辑职位'"
      width="500px"
    >
      <el-form
        ref="positionFormRef"
        :model="positionForm"
        :rules="positionRules"
      >
        <el-form-item label="所属公司" prop="companyId">
          <el-select
            v-model="positionForm.companyId"
            placeholder="请选择所属公司"
            :disabled="!isSuperAdmin"
          >
            <el-option label="无" :value="0" />
            <el-option
              v-for="company in companyList"
              :key="company.id"
              :label="company.companyName"
              :value="company.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="所属部门" prop="deptId">
          <el-select v-model="positionForm.deptId" placeholder="请选择所属部门">
            <el-option
              v-for="dept in allDepts"
              :key="dept.id"
              :label="dept.deptName"
              :value="String(dept.id)"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="职位名称" prop="positionName">
          <el-input
            v-model="positionForm.positionName"
            placeholder="请输入职位名称"
          />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number
            v-model="positionForm.sort"
            :min="0"
            placeholder="请输入排序"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="positionDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmitPosition"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>

    <!-- 新增/编辑房间对话框 -->
    <el-dialog
      v-model="roomDialogVisible"
      :title="roomDialogType === 'add' ? '新增房间' : '编辑房间'"
      width="500px"
    >
      <el-form ref="roomFormRef" :model="roomForm" :rules="roomRules">
        <el-form-item label="所属门店" prop="storeId">
          <el-select v-model="roomForm.storeId" placeholder="请选择所属门店">
            <el-option
              v-for="store in storeList"
              :key="store.id"
              :label="store.storeName"
              :value="store.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="房间名称" prop="roomName">
          <el-input v-model="roomForm.roomName" placeholder="请输入房间名称" />
        </el-form-item>
        <el-form-item label="床位数量" prop="bedCount">
          <el-input-number
            v-model="roomForm.bedCount"
            :min="1"
            placeholder="请输入床位数量"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="roomDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmitRoom">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from "vue";
import { Plus, Edit, Delete, Search, Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import {
  getStores,
  addStore,
  updateStore,
  deleteStore,
  getCompanies,
  getDepartments,
  addDepartment,
  updateDepartment,
  deleteDepartment,
  getPositions,
  addPosition,
  updatePosition,
  deletePosition,
  getBedrooms,
  addBedroom,
  updateBedroom,
  deleteBedroom
} from "@/api/enterprise";
import { hasAuth } from "@/router/utils";
import { userKey, type DataInfo } from "@/utils/auth";
import { storageLocal } from "@pureadmin/utils";
import { useCompanyChange } from "@/composables/useCompanyChange";

// 当前激活的标签页
const activeTab = ref("store");

// 获取当前用户信息
const currentUser = computed(() => {
  return storageLocal().getItem<DataInfo<number>>(userKey);
});

// 获取当前用户是否为超级管理员
const isSuperAdmin = computed(() => {
  return currentUser.value?.isSuper || false;
});

// 获取当前用户所属公司ID
const currentCompanyId = computed(() => {
  return currentUser.value?.companyId || null;
});

// 加载状态
const loading = ref(false);
const deptLoading = ref(false);
const positionLoading = ref(false);
const roomLoading = ref(false);

// 搜索表单
const searchForm = reactive({
  storeName: "",
  storeType: ""
});

// 部门搜索表单
const deptSearchForm = reactive({
  deptName: ""
});

// 职位搜索表单
const positionSearchForm = reactive({
  positionName: "",
  deptId: ""
});

// 房间搜索表单
const roomSearchForm = reactive({
  storeId: "",
  roomName: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 门店列表
const storeList = ref([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 表单数据
const form = reactive({
  id: 0,
  companyId: null,
  storeName: "",
  phone: "",
  address: "",
  storeType: "",
  departments: []
});

// 公司列表
const companyList = ref([]);
// 部门列表
const departmentList = ref([]);
// 部门列表（用于上级部门选择）
const allDepts = ref([]);
// 职位列表
const positionList = ref([]);

// 房间列表
const roomList = ref([]);

// 房间分页
const roomPagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 房间管理相关
const roomDialogVisible = ref(false);
const roomDialogType = ref("add");
const roomFormRef = ref();
const roomForm = reactive({
  id: 0,
  storeId: "",
  roomName: "",
  bedCount: 1
});

const roomRules = reactive({
  storeId: [{ required: true, message: "请选择所属门店", trigger: "change" }],
  roomName: [{ required: true, message: "请输入房间名称", trigger: "blur" }],
  bedCount: [{ required: true, message: "请输入床位数量", trigger: "blur" }]
});

// 表单验证规则
const rules = reactive<FormRules>({
  companyId: [
    { required: true, message: "请选择所属公司", trigger: "change" },
    {
      validator: (rule, value, callback) => {
        if (value === 0) {
          callback(new Error("请选择所属公司"));
        } else {
          callback();
        }
      },
      trigger: "change"
    }
  ],
  storeName: [{ required: true, message: "请输入门店名称", trigger: "blur" }],
  phone: [{ required: true, message: "请输入联系电话", trigger: "blur" }],
  address: [{ required: true, message: "请输入门店地址", trigger: "blur" }],
  storeType: [{ required: true, message: "请选择门店类型", trigger: "change" }],
  departments: [
    { required: true, message: "请选择拥有部门", trigger: "change" }
  ]
});

// 部门管理相关
const deptDialogVisible = ref(false);
const deptDialogType = ref("add");
const deptFormRef = ref<FormInstance>();
const deptForm = reactive({
  id: 0,
  companyId: null,
  deptName: "",
  parentId: "0",
  sort: 0,
  status: true,
  enableCategory: false
});

const deptRules = reactive<FormRules>({
  companyId: [{ required: true, message: "请选择所属公司", trigger: "change" }],
  deptName: [{ required: true, message: "请输入部门名称", trigger: "blur" }],
  sort: [{ required: true, message: "请输入排序", trigger: "blur" }],
  enableCategory: [{ type: "boolean", trigger: "change" }]
});

// 职位管理相关
const positionDialogVisible = ref(false);
const positionDialogType = ref("add");
const positionFormRef = ref<FormInstance>();
const positionForm = reactive({
  id: 0,
  companyId: null,
  positionName: "",
  deptId: "",
  sort: 0,
  status: true
});

const positionRules = reactive<FormRules>({
  companyId: [{ required: true, message: "请选择所属公司", trigger: "change" }],
  positionName: [
    { required: true, message: "请输入职位名称", trigger: "blur" }
  ],
  deptId: [
    { required: true, message: "请选择所属部门", trigger: "change" },
    {
      validator: (rule, value, callback) => {
        if (value === "0") {
          callback(new Error("请选择有效的所属部门"));
        } else {
          callback();
        }
      },
      trigger: "change"
    }
  ],
  sort: [{ required: true, message: "请输入排序", trigger: "blur" }]
});

// 初始化数据
onMounted(() => {
  if (hasAuth("store:view")) {
    getStoreList();
  }
  getCompanyList();
  if (hasAuth("store:department:view")) {
    getDepartmentList();
    getAllDepartments();
  }
  if (hasAuth("store:position:view")) {
    getPositionList();
  }
  if (hasAuth("store:room:view")) {
    getRoomList();
  }
});

// 监听公司变化，重新加载数据
useCompanyChange(() => {
  getCompanyList();
  if (hasAuth("store:view")) {
    getStoreList();
  }
  if (hasAuth("store:department:view")) {
    getDepartmentList();
    getAllDepartments();
  }
  if (hasAuth("store:position:view")) {
    getPositionList();
  }
  if (hasAuth("store:room:view")) {
    getRoomList();
  }
});

// 获取所有部门列表（用于下拉选择，不受搜索条件影响）
const getAllDepartments = () => {
  if (!hasAuth("store:department:view")) {
    return;
  }
  // 从后端API获取所有部门，不使用搜索条件
  // 超级管理员不传递company_id参数，这样可以看到所有公司的部门
  const params = isSuperAdmin.value
    ? {}
    : {
        company_id: currentCompanyId.value
      };
  getDepartments(params)
    .then(response => {
      if (response?.code === 200) {
        // 直接使用后端返回的数据，已经包含了转换后的字段名
        const depts = (response.data || []).map((item: any) => ({
          ...item,
          status: item.status === 1,
          enableCategory: item.enable_category === 1
        }));
        // 更新部门列表（用于上级部门选择）
        allDepts.value = depts;
      } else {
        ElMessage.error(response?.message || "获取部门列表失败");
      }
    })
    .catch(error => {
      ElMessage.error("获取部门列表失败");
      console.error("getAllDepartments error:", error);
    });
};

// 获取部门列表（用于显示，受搜索条件影响）
const getDepartmentList = () => {
  if (!hasAuth("store:department:view")) {
    return;
  }

  deptLoading.value = true;
  // 从后端API获取数据
  // 超级管理员不传递company_id参数，这样可以看到所有公司的部门
  const params = {
    deptName: deptSearchForm.deptName
  };
  if (!isSuperAdmin.value) {
    params.company_id = currentCompanyId.value;
  }
  getDepartments(params)
    .then(response => {
      deptLoading.value = false;
      if (response?.code === 200) {
        // 直接使用后端返回的数据，已经包含了转换后的字段名
        const depts = (response.data || []).map((item: any) => ({
          ...item,
          status: item.status === 1,
          enableCategory: item.enable_category === 1
        }));
        departmentList.value = depts;
      } else {
        ElMessage.error(response?.message || "获取部门列表失败");
      }
    })
    .catch(error => {
      deptLoading.value = false;
      ElMessage.error("获取部门列表失败");
      console.error("getDepartmentList error:", error);
    });
};

// 获取职位列表
const getPositionList = () => {
  if (!hasAuth("store:position:view")) {
    return;
  }

  positionLoading.value = true;
  // 从后端API获取数据
  // 超级管理员不传递company_id参数，这样可以看到所有公司的职位
  const params = {
    positionName: positionSearchForm.positionName,
    deptId: positionSearchForm.deptId
  };
  if (!isSuperAdmin.value) {
    params.company_id = currentCompanyId.value;
  }
  getPositions(params)
    .then(response => {
      positionLoading.value = false;
      if (response?.code === 200) {
        // 直接使用后端返回的数据，已经包含了转换后的字段名
        positionList.value = (response.data || []).map((item: any) => ({
          ...item,
          status: item.status === 1
        }));
      } else {
        ElMessage.error(response?.message || "获取职位列表失败");
      }
    })
    .catch(error => {
      positionLoading.value = false;
      ElMessage.error("获取职位列表失败");
      console.error("getPositionList error:", error);
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
const getStoreList = () => {
  if (!hasAuth("store:view")) {
    return;
  }

  loading.value = true;
  // 从后端API获取数据
  getStores({
    ...searchForm,
    page: pagination.current,
    page_size: pagination.pageSize
  })
    .then(response => {
      loading.value = false;
      if (response?.code === 200) {
        storeList.value = response.data || [];
        pagination.total = storeList.value.length;
      } else {
        ElMessage.error(response?.message || "获取门店列表失败");
      }
    })
    .catch(error => {
      loading.value = false;
      ElMessage.error("获取门店列表失败");
      console.error("getStoreList error:", error);
    });
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getStoreList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.storeName = "";
  searchForm.storeType = "";
  pagination.current = 1;
  getStoreList();
};

// 部门搜索
const handleDeptSearch = () => {
  getDepartmentList();
};

// 重置部门搜索
const resetDeptSearch = () => {
  deptSearchForm.deptName = "";
  getDepartmentList();
};

// 职位搜索
const handlePositionSearch = () => {
  getPositionList();
};

// 重置职位搜索
const resetPositionSearch = () => {
  positionSearchForm.positionName = "";
  positionSearchForm.deptId = "";
  getPositionList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getStoreList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getStoreList();
};

// 新增门店
const handleAdd = () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  // 普通用户默认设置公司ID为当前用户所属的公司ID
  form.companyId = isSuperAdmin.value ? null : currentCompanyId.value;
  form.storeName = "";
  form.phone = "";
  form.address = "";
  form.storeType = "";
  form.departments = [];
  dialogVisible.value = true;
};

// 编辑门店
const handleEdit = (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.id;
  form.companyId = row.companyId || 0;
  form.storeName = row.storeName;
  form.phone = row.phone;
  form.address = row.address;
  form.storeType = row.storeType;
  form.departments = row.departments
    ? row.departments.split(",").map((id: string) => Number(id))
    : [];
  dialogVisible.value = true;
};

// 删除门店
const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该门店吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      loading.value = true;
      // 调用后端API删除门店
      deleteStore(id)
        .then(response => {
          loading.value = false;
          if (response?.code === 200) {
            ElMessage.success(response?.message || "删除成功");
            getStoreList();
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

// 提交表单
const handleSubmit = () => {
  if (!formRef.value) return;
  formRef.value.validate((valid: boolean) => {
    if (valid) {
      loading.value = true;
      // 直接使用camelCase字段名，与后端保持一致
      const { id, ...restForm } = form;
      const formData = {
        ...restForm,
        companyId: form.companyId || 0,
        departments: Array.isArray(form.departments)
          ? form.departments.join(",")
          : ""
      };
      console.log("formData:", formData);
      console.log("dialogType.value:", dialogType.value);
      const request =
        dialogType.value === "add"
          ? addStore(formData)
          : updateStore(form.id, formData);

      request
        .then(response => {
          loading.value = false;
          if (response?.code === 200) {
            ElMessage.success(
              response?.message ||
                (dialogType.value === "add" ? "新增成功" : "编辑成功")
            );
            dialogVisible.value = false;
            getStoreList();
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

// 新增部门
const handleAddDept = () => {
  deptDialogType.value = "add";
  // 重置表单
  deptForm.id = 0;
  // 普通用户默认设置公司ID为当前用户所属的公司ID
  deptForm.companyId = isSuperAdmin.value ? null : currentCompanyId.value;
  deptForm.deptName = "";
  deptForm.parentId = "0";
  deptForm.sort = 0;
  deptDialogVisible.value = true;
};

// 编辑部门
const handleEditDept = (row: any) => {
  deptDialogType.value = "edit";
  // 复制数据到表单
  deptForm.id = row.id;
  deptForm.companyId = row.companyId || 0;
  deptForm.deptName = row.deptName;
  // 确保 parentId 为 0 时显示"无"选项
  deptForm.parentId = String(
    row.parentId === 0 || row.parentId === "0" ? "0" : row.parentId
  );
  deptForm.sort = row.sort;
  deptForm.status = row.status === 1;
  deptForm.enableCategory = row.enableCategory;
  deptDialogVisible.value = true;
};

// 删除部门
const handleDeleteDept = (id: number) => {
  ElMessageBox.confirm("确定要删除该部门吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      deptLoading.value = true;
      // 调用后端API删除部门
      deleteDepartment(id)
        .then(response => {
          deptLoading.value = false;
          if (response?.code === 200) {
            ElMessage.success(response?.message || "删除成功");
            getDepartmentList();
          } else {
            ElMessage.error(response?.message || "删除失败");
          }
        })
        .catch(error => {
          deptLoading.value = false;
          ElMessage.error("删除失败");
          console.error("handleDeleteDept error:", error);
        });
    })
    .catch(() => {
      // 取消删除
    });
};

// 提交部门表单
const handleSubmitDept = async () => {
  if (!deptFormRef.value) return;
  await deptFormRef.value.validate((valid: boolean) => {
    if (valid) {
      deptLoading.value = true;
      // 直接使用camelCase字段名，与后端保持一致
      const formData = {
        ...deptForm,
        companyId: deptForm.companyId || 0,
        status: deptForm.status ? 1 : 0,
        enable_category: deptForm.enableCategory ? 1 : 0
      };
      const request =
        deptDialogType.value === "add"
          ? addDepartment(formData)
          : updateDepartment(deptForm.id, formData);

      request
        .then(response => {
          deptLoading.value = false;
          if (response?.code === 200) {
            ElMessage.success(
              response?.message ||
                (deptDialogType.value === "add" ? "新增成功" : "编辑成功")
            );
            deptDialogVisible.value = false;
            // 重新获取部门列表，确保显示最新数据
            getDepartmentList();
          } else {
            ElMessage.error(
              response?.message ||
                (deptDialogType.value === "add" ? "新增失败" : "编辑失败")
            );
          }
        })
        .catch(error => {
          deptLoading.value = false;
          ElMessage.error(
            deptDialogType.value === "add" ? "新增失败" : "编辑失败"
          );
          console.error("handleSubmitDept error:", error);
        });
    }
  });
};

// 新增职位
const handleAddPosition = () => {
  positionDialogType.value = "add";
  // 重置表单
  positionForm.id = 0;
  // 普通用户默认设置公司ID为当前用户所属的公司ID
  positionForm.companyId = isSuperAdmin.value ? null : currentCompanyId.value;
  positionForm.positionName = "";
  positionForm.deptId = "";
  positionForm.sort = 0;
  // 确保获取当前用户所属公司的部门列表
  getAllDepartments();
  positionDialogVisible.value = true;
};

// 编辑职位
const handleEditPosition = (row: any) => {
  positionDialogType.value = "edit";
  // 复制数据到表单
  positionForm.id = row.id;
  positionForm.companyId = row.companyId || 0;
  positionForm.positionName = row.positionName;
  // 确保获取所有部门列表，不受搜索条件影响
  getAllDepartments();
  // 等待部门列表加载完成后再设置 deptId
  setTimeout(() => {
    // 查找对应部门，确保类型一致
    const deptId =
      row.deptId === 0 || row.deptId === "0" ? "" : String(row.deptId);
    const dept = allDepts.value.find(d => String(d.id) === String(deptId));
    positionForm.deptId = dept ? String(dept.id) : "";
    positionForm.sort = row.sort;
    positionForm.status = row.status === 1;
    positionDialogVisible.value = true;
  }, 100);
};

// 删除职位
const handleDeletePosition = (id: number) => {
  ElMessageBox.confirm("确定要删除该职位吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      positionLoading.value = true;
      // 调用后端API删除职位
      deletePosition(id)
        .then(response => {
          positionLoading.value = false;
          if (response?.code === 200) {
            ElMessage.success(response?.message || "删除成功");
            getPositionList();
          } else {
            ElMessage.error(response?.message || "删除失败");
          }
        })
        .catch(error => {
          positionLoading.value = false;
          ElMessage.error("删除失败");
          console.error("handleDeletePosition error:", error);
        });
    })
    .catch(() => {
      // 取消删除
    });
};

// 处理部门状态变更
const handleDeptStatusChange = (row: any) => {
  deptLoading.value = true;
  // 调用后端API更新部门状态
  updateDepartment(row.id, {
    ...row,
    status: row.status ? 1 : 0
  })
    .then(response => {
      deptLoading.value = false;
      if (response?.code === 200) {
        ElMessage.success(response?.message || "状态更新成功");
      } else {
        ElMessage.error(response?.message || "状态更新失败");
        // 恢复原始状态
        row.status = !row.status;
      }
    })
    .catch(error => {
      deptLoading.value = false;
      ElMessage.error("状态更新失败");
      // 恢复原始状态
      row.status = !row.status;
      console.error("handleDeptStatusChange error:", error);
    });
};

// 处理核心业务部门变更
const handleDeptCategoryChange = (row: any) => {
  deptLoading.value = true;
  // 调用后端API更新核心业务部门状态
  updateDepartment(row.id, {
    ...row,
    enable_category: row.enableCategory ? 1 : 0
  })
    .then(response => {
      deptLoading.value = false;
      if (response?.code === 200) {
        ElMessage.success(response?.message || "核心业务部门状态更新成功");
      } else {
        ElMessage.error(response?.message || "核心业务部门状态更新失败");
        // 恢复原始状态
        row.enableCategory = !row.enableCategory;
      }
    })
    .catch(error => {
      deptLoading.value = false;
      ElMessage.error("核心业务部门状态更新失败");
      // 恢复原始状态
      row.enableCategory = !row.enableCategory;
      console.error("handleDeptCategoryChange error:", error);
    });
};

// 处理职位状态变更
const handlePositionStatusChange = (row: any) => {
  positionLoading.value = true;
  // 调用后端API更新职位状态
  updatePosition(row.id, {
    ...row,
    status: row.status ? 1 : 0
  })
    .then(response => {
      positionLoading.value = false;
      if (response?.code === 200) {
        ElMessage.success(response?.message || "状态更新成功");
      } else {
        ElMessage.error(response?.message || "状态更新失败");
        // 恢复原始状态
        row.status = !row.status;
      }
    })
    .catch(error => {
      positionLoading.value = false;
      ElMessage.error("状态更新失败");
      // 恢复原始状态
      row.status = !row.status;
      console.error("handlePositionStatusChange error:", error);
    });
};

// 处理标签页切换
const handleTabClick = (tab: any) => {
  // 切换到部门管理标签页时加载数据
  if (tab.props.name === "dept" && hasAuth("store:department:view")) {
    getDepartmentList();
    getAllDepartments();
  }
  // 切换到职位管理标签页时加载数据
  if (tab.props.name === "position" && hasAuth("store:position:view")) {
    getPositionList();
  }
  // 切换到房间设置标签页时加载数据
  if (tab.props.name === "room" && hasAuth("store:room:view")) {
    getRoomList();
  }
};

// 获取房间列表
const getRoomList = () => {
  if (!hasAuth("store:room:view")) {
    return;
  }

  roomLoading.value = true;
  // 从后端API获取数据
  // 超级管理员不传递company_id参数，这样可以看到所有公司的房间
  const params = {
    storeId: roomSearchForm.storeId,
    roomName: roomSearchForm.roomName
  };
  if (!isSuperAdmin.value) {
    params.companyId = currentCompanyId.value;
  }
  getBedrooms(params)
    .then(response => {
      roomLoading.value = false;
      if (response?.code === 200) {
        roomList.value = response.data || [];
        roomPagination.total = roomList.value.length;
      } else {
        ElMessage.error(response?.message || "获取房间列表失败");
      }
    })
    .catch(error => {
      roomLoading.value = false;
      ElMessage.error("获取房间列表失败");
      console.error("getRoomList error:", error);
    });
};

// 房间搜索
const handleRoomSearch = () => {
  roomPagination.current = 1;
  getRoomList();
};

// 重置房间搜索
const resetRoomSearch = () => {
  roomSearchForm.storeId = "";
  roomSearchForm.roomName = "";
  roomPagination.current = 1;
  getRoomList();
};

// 房间分页大小变化
const handleRoomSizeChange = (size: number) => {
  roomPagination.pageSize = size;
  getRoomList();
};

// 房间当前页码变化
const handleRoomCurrentChange = (current: number) => {
  roomPagination.current = current;
  getRoomList();
};

// 新增房间
const handleAddRoom = () => {
  roomDialogType.value = "add";
  // 重置表单
  roomForm.id = 0;
  roomForm.storeId = "";
  roomForm.roomName = "";
  roomForm.bedCount = 1;
  roomDialogVisible.value = true;
};

// 编辑房间
const handleEditRoom = (row: any) => {
  if (!row) {
    ElMessage.error("数据错误，无法编辑房间");
    return;
  }
  roomDialogType.value = "edit";
  // 复制数据到表单
  roomForm.id = row.id || 0;
  roomForm.storeId = row.storeId || "";
  roomForm.roomName = row.roomName || "";
  roomForm.bedCount = row.bedCount || 1;
  roomDialogVisible.value = true;
};

// 删除房间
const handleDeleteRoom = (id: number) => {
  ElMessageBox.confirm("确定要删除该房间吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      roomLoading.value = true;
      // 调用后端API删除房间
      deleteBedroom(id)
        .then(response => {
          roomLoading.value = false;
          if (response?.code === 200) {
            ElMessage.success(response?.message || "删除成功");
            getRoomList();
          } else {
            ElMessage.error(response?.message || "删除失败");
          }
        })
        .catch(error => {
          roomLoading.value = false;
          ElMessage.error("删除失败");
          console.error("handleDeleteRoom error:", error);
        });
    })
    .catch(() => {
      // 取消删除
    });
};

// 提交房间表单
const handleSubmitRoom = async () => {
  if (!roomFormRef.value) return;
  await roomFormRef.value.validate((valid: boolean) => {
    if (valid) {
      roomLoading.value = true;
      // 直接使用camelCase字段名，与后端保持一致
      const formData = {
        ...roomForm,
        storeId: roomForm.storeId || 0
      };
      const request =
        roomDialogType.value === "add"
          ? addBedroom(formData)
          : updateBedroom(roomForm.id, formData);

      request
        .then(response => {
          roomLoading.value = false;
          if (response?.code === 200) {
            ElMessage.success(
              response?.message ||
                (roomDialogType.value === "add" ? "新增成功" : "编辑成功")
            );
            roomDialogVisible.value = false;
            getRoomList();
          } else {
            ElMessage.error(
              response?.message ||
                (roomDialogType.value === "add" ? "新增失败" : "编辑失败")
            );
          }
        })
        .catch(error => {
          roomLoading.value = false;
          ElMessage.error(
            roomDialogType.value === "add" ? "新增失败" : "编辑失败"
          );
          console.error("handleSubmitRoom error:", error);
        });
    }
  });
};

// 提交职位表单
const handleSubmitPosition = async () => {
  if (!positionFormRef.value) return;
  await positionFormRef.value.validate((valid: boolean) => {
    if (valid) {
      positionLoading.value = true;
      // 直接使用camelCase字段名，与后端保持一致
      const formData = {
        ...positionForm,
        companyId: positionForm.companyId || 0,
        deptId: positionForm.deptId || 0,
        status: positionForm.status ? 1 : 0
      };
      const request =
        positionDialogType.value === "add"
          ? addPosition(formData)
          : updatePosition(positionForm.id, formData);

      request
        .then(response => {
          positionLoading.value = false;
          if (response?.code === 200) {
            ElMessage.success(
              response?.message ||
                (positionDialogType.value === "add" ? "新增成功" : "编辑成功")
            );
            positionDialogVisible.value = false;
            getPositionList();
          } else {
            ElMessage.error(
              response?.message ||
                (positionDialogType.value === "add" ? "新增失败" : "编辑失败")
            );
          }
        })
        .catch(error => {
          positionLoading.value = false;
          ElMessage.error(
            positionDialogType.value === "add" ? "新增失败" : "编辑失败"
          );
          console.error("handleSubmitPosition error:", error);
        });
    }
  });
};
</script>

<style scoped>
.store-container {
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.el-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.el-card__body) {
  padding: 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

:deep(.el-tabs) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.el-tabs__content) {
  flex: 1;
  overflow: auto;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
}

.tab-content {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 列表区域 */
.tab-content > div {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 表格样式 */
.tab-content .el-table {
  flex: 1;
  min-height: 0;
}

:deep(.tab-content .el-table__body-wrapper) {
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
