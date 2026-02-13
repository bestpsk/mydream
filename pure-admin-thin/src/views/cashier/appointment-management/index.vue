<template>
  <div class="appointment-management-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>预约管理</span>
          <div class="header-buttons">
            <el-button type="primary" @click="handleAddAppointment">
              <el-icon><Plus /></el-icon>
              新增预约
            </el-button>
            <el-button @click="handleRefresh">
              <el-icon><Refresh /></el-icon>
              刷新
            </el-button>
          </div>
        </div>
      </template>

      <!-- 日期选择和筛选 -->
      <div class="filter-section mb-4">
        <el-form :inline="true" :model="filterForm" class="mb-4">
          <el-form-item>
            <el-date-picker
              v-model="filterForm.date"
              type="date"
              placeholder="选择日期"
              @change="handleDateChange"
            />
          </el-form-item>
          <el-form-item label="门店">
            <el-select
              v-model="filterForm.storeId"
              placeholder="选择门店"
              @change="handleStoreChange"
            >
              <el-option
                v-for="store in storeList"
                :key="store.id"
                :label="store.name"
                :value="store.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="员工">
            <el-select
              v-model="filterForm.employeeId"
              placeholder="选择员工"
              multiple
            >
              <el-option
                v-for="employee in employeeList"
                :key="employee.id"
                :label="employee.name"
                :value="employee.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="filterForm.status" placeholder="选择状态">
              <el-option label="全部" value="" />
              <el-option label="待确认" value="pending" />
              <el-option label="已确认" value="confirmed" />
              <el-option label="已完成" value="completed" />
              <el-option label="已取消" value="cancelled" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleFilter">筛选</el-button>
            <el-button @click="resetFilter">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 员工排班表 -->
      <div class="schedule-section">
        <el-card shadow="hover">
          <template #header>
            <div class="schedule-header">
              <span>员工排班表</span>
              <div class="time-range">
                <el-time-picker
                  v-model="timeRange"
                  type="daterange"
                  range-separator="至"
                  start-placeholder="开始时间"
                  end-placeholder="结束时间"
                  format="HH:mm"
                  value-format="HH:mm"
                  @change="handleTimeRangeChange"
                />
              </div>
            </div>
          </template>

          <!-- 排班表格 -->
          <div class="schedule-table">
            <div class="schedule-header-row">
              <div class="employee-column">员工/时间段</div>
              <div class="time-slots-container time-slots-wrapper">
                <div
                  v-for="(timeSlot, index) in timeSlots"
                  :key="index"
                  class="time-slot time-slot-column"
                  :style="{ width: slotWidth + 'px' }"
                >
                  {{ timeSlot }}
                </div>
              </div>
            </div>

            <div
              v-for="employee in filteredEmployees"
              :key="employee.id"
              class="employee-row"
            >
              <div class="employee-column">
                <div class="employee-name">{{ employee.name }}</div>
              </div>
              <div class="time-slots-container time-slots-wrapper">
                <!-- 渲染所有时间槽 -->
                <div
                  v-for="(timeSlot, index) in timeSlots"
                  :key="index"
                  class="time-slot time-slot-column"
                  :class="[getDragClass(employee.id, index)]"
                  :style="{ width: slotWidth + 'px' }"
                  @click="handleSlotClick(employee.id, index)"
                  @mousedown="handleSlotMouseDown(employee.id, index)"
                  @mouseenter="handleSlotMouseEnter(employee.id, index)"
                  @mouseup="handleSlotMouseUp(employee.id, index)"
                />

                <!-- 合并时间槽覆盖层 -->
                <div class="merged-appointments-overlay">
                  <div
                    v-for="appointment in getEmployeeAppointments(employee.id)"
                    :key="appointment.id"
                    class="merged-time-slot"
                    :class="appointment.status"
                    :style="getMergedSlotStyle(appointment)"
                    @click="viewAppointmentDetail(appointment)"
                  >
                    <div class="appointment-content">
                      <div class="customer">{{ appointment.customerName }}</div>
                      <div class="service">{{ appointment.serviceName }}</div>
                      <div class="time">
                        {{ appointment.startTime }}-{{ appointment.endTime }}
                      </div>
                      <div class="room">{{ appointment.roomName }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </el-card>
      </div>

      <!-- 预约列表 -->
      <div class="appointment-list-section mt-4">
        <el-card shadow="hover">
          <template #header>
            <span>预约列表</span>
          </template>
          <el-table
            v-loading="loading"
            :data="appointmentList"
            style="width: 100%"
          >
            <el-table-column prop="id" label="预约号" />
            <el-table-column prop="customerName" label="客户姓名" />
            <el-table-column prop="customerPhone" label="客户手机号" />
            <el-table-column prop="employeeName" label="服务员工" />
            <el-table-column prop="serviceName" label="服务项目" />
            <el-table-column prop="roomName" label="房间" />
            <el-table-column label="预约时间">
              <template #default="scope">
                {{ scope.row.startTime }}-{{ scope.row.endTime }}
              </template>
            </el-table-column>
            <el-table-column prop="status" label="状态">
              <template #default="scope">
                <el-tag :type="getStatusType(scope.row.status)">{{
                  getStatusText(scope.row.status)
                }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="180">
              <template #default="scope">
                <el-button
                  type="primary"
                  size="small"
                  @click="viewAppointmentDetail(scope.row)"
                >
                  查看详情
                </el-button>
                <el-button
                  v-if="canEditAppointment(scope.row.status)"
                  type="success"
                  size="small"
                  @click="editAppointment(scope.row)"
                >
                  编辑
                </el-button>
                <el-button
                  v-if="canCancelAppointment(scope.row.status)"
                  type="danger"
                  size="small"
                  @click="cancelAppointment(scope.row)"
                >
                  取消
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </div>

      <!-- 配置区域 -->
      <div class="configuration-section mt-4">
        <el-card shadow="hover">
          <template #header>
            <span>配置管理</span>
          </template>
          <el-tabs v-model="activeConfigTab">
            <el-tab-pane label="门店房间配置" name="room">
              <div class="room-config">
                <el-form :inline="true" :model="roomForm" class="mb-4">
                  <el-form-item label="门店">
                    <el-select
                      v-model="roomForm.storeId"
                      placeholder="选择门店"
                    >
                      <el-option
                        v-for="store in storeList"
                        :key="store.id"
                        :label="store.name"
                        :value="store.id"
                      />
                    </el-select>
                  </el-form-item>
                  <el-form-item label="房间名称">
                    <el-input
                      v-model="roomForm.name"
                      placeholder="请输入房间名称"
                    />
                  </el-form-item>
                  <el-form-item>
                    <el-button type="primary" @click="handleAddRoom"
                      >添加房间</el-button
                    >
                  </el-form-item>
                </el-form>
                <el-table :data="roomList" style="width: 100%">
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="storeName" label="门店" />
                  <el-table-column prop="name" label="房间名称" />
                  <el-table-column label="操作" width="120">
                    <template #default="scope">
                      <el-button
                        type="danger"
                        size="small"
                        @click="deleteRoom(scope.row.id)"
                      >
                        删除
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </el-tab-pane>
            <el-tab-pane label="美容师配置" name="beautician">
              <div class="beautician-config">
                <el-form :inline="true" :model="beauticianForm" class="mb-4">
                  <el-form-item label="员工">
                    <el-select
                      v-model="beauticianForm.employeeId"
                      placeholder="选择员工"
                    >
                      <el-option
                        v-for="employee in allEmployees"
                        :key="employee.id"
                        :label="employee.name"
                        :value="employee.id"
                      />
                    </el-select>
                  </el-form-item>
                  <el-form-item label="可服务项目">
                    <el-select
                      v-model="beauticianForm.serviceIds"
                      placeholder="选择服务项目"
                      multiple
                    >
                      <el-option
                        v-for="service in serviceList"
                        :key="service.id"
                        :label="service.name"
                        :value="service.id"
                      />
                    </el-select>
                  </el-form-item>
                  <el-form-item>
                    <el-button
                      type="primary"
                      @click="handleAddBeauticianService"
                      >添加配置</el-button
                    >
                  </el-form-item>
                </el-form>
                <el-table :data="beauticianServiceList" style="width: 100%">
                  <el-table-column prop="employeeName" label="员工" />
                  <el-table-column prop="serviceNames" label="可服务项目" />
                  <el-table-column label="操作" width="120">
                    <template #default="scope">
                      <el-button
                        type="danger"
                        size="small"
                        @click="deleteBeauticianService(scope.row.id)"
                      >
                        删除
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </el-tab-pane>
          </el-tabs>
        </el-card>
      </div>
    </el-card>

    <!-- 新增/编辑预约对话框 -->
    <el-dialog
      v-model="appointmentDialogVisible"
      :title="dialogType === 'add' ? '新增预约' : '编辑预约'"
      width="800px"
    >
      <el-form
        ref="appointmentFormRef"
        :model="appointmentForm"
        :rules="appointmentRules"
      >
        <!-- 基本信息 -->
        <el-collapse v-model="activeCollapseNames">
          <el-collapse-item title="基本信息" name="basic">
            <el-form-item label="部门" prop="departmentId">
              <el-segmented
                v-model="appointmentForm.departmentId"
                :options="coreDepartments"
              />
            </el-form-item>
            <el-form-item label="客户" prop="customerId">
              <el-select
                v-model="appointmentForm.customerId"
                placeholder="请选择客户"
                filterable
                remote
                :remote-method="handleCustomerSearch"
                :loading="customerLoading"
              >
                <el-option
                  v-for="customer in customerList"
                  :key="customer.id"
                  :label="customer.name + ' ' + customer.phone"
                  :value="customer.id"
                />
              </el-select>
            </el-form-item>
            <div class="flex gap-4">
              <el-form-item
                label="预约日期"
                prop="appointmentDate"
                style="flex: 1"
              >
                <el-date-picker
                  v-model="appointmentForm.appointmentDate"
                  type="date"
                  placeholder="选择预约日期"
                  format="YYYY-MM-DD"
                  value-format="YYYY-MM-DD"
                  style="width: 100%"
                />
              </el-form-item>
              <el-form-item label="时间" prop="startTime" style="flex: 2">
                <div class="flex gap-2">
                  <el-time-picker
                    v-model="appointmentForm.startTime"
                    placeholder="开始时间"
                    format="HH:mm"
                    value-format="HH:mm"
                    style="width: 120px"
                  />
                  <span>至</span>
                  <el-time-picker
                    v-model="appointmentForm.endTime"
                    placeholder="结束时间"
                    format="HH:mm"
                    value-format="HH:mm"
                    style="width: 120px"
                  />
                </div>
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item
                label="预约方式"
                prop="appointmentType"
                style="flex: 1"
              >
                <el-radio-group v-model="appointmentForm.appointmentType">
                  <el-radio label="point">点排</el-radio>
                  <el-radio label="round">轮排</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item label="房间" prop="roomId" style="flex: 2">
                <el-select
                  v-model="appointmentForm.roomId"
                  placeholder="请选择服务房间"
                >
                  <el-option
                    v-for="room in roomList"
                    :key="room.id"
                    :label="room.name"
                    :value="room.id"
                  />
                </el-select>
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item label="服务人" prop="employeeId" style="flex: 2">
                <el-select
                  v-model="appointmentForm.employeeId"
                  placeholder="请选择服务人"
                  multiple
                >
                  <el-option
                    v-for="employee in filteredEmployees"
                    :key="employee.id"
                    :label="employee.name"
                    :value="employee.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="管理者" prop="managerId" style="flex: 1">
                <el-select
                  v-model="appointmentForm.managerId"
                  placeholder="选择管理者"
                >
                  <el-option
                    v-for="employee in filteredEmployees"
                    :key="employee.id"
                    :label="employee.name"
                    :value="employee.id"
                  />
                </el-select>
              </el-form-item>
            </div>
            <el-form-item label="进度" prop="status">
              <el-select
                v-model="appointmentForm.status"
                placeholder="选择进度"
              >
                <el-option label="待服务" value="pending" />
                <el-option label="护理中" value="in_service" />
                <el-option label="已完成" value="completed" />
                <el-option label="已取消" value="cancelled" />
              </el-select>
            </el-form-item>
            <el-form-item label="备注" prop="remark">
              <el-input
                v-model="appointmentForm.remark"
                type="textarea"
                placeholder="请输入备注"
              />
            </el-form-item>
          </el-collapse-item>

          <!-- 预约项目 -->
          <el-collapse-item title="预约项目" name="items">
            <div class="mb-4">
              <el-button type="primary" @click="handleAddAppointmentItem">
                <el-icon><Plus /></el-icon>
                添加预约项目
              </el-button>
            </div>

            <!-- 项目选择对话框 -->
            <el-dialog
              v-model="projectDialogVisible"
              title="选择项目"
              width="600px"
            >
              <div class="mb-4">
                <el-tabs v-model="activeProjectTab">
                  <el-tab-pane label="顾客已有项目" name="customer">
                    <el-table :data="customerProjects" style="width: 100%">
                      <el-table-column prop="projectName" label="项目名称" />
                      <el-table-column prop="price" label="价格" />
                      <el-table-column prop="duration" label="时长(分钟)" />
                      <el-table-column prop="remaining" label="剩余次数" />
                      <el-table-column label="操作">
                        <template #default="scope">
                          <el-button
                            type="primary"
                            size="small"
                            @click="selectProject(scope.row)"
                          >
                            选择
                          </el-button>
                        </template>
                      </el-table-column>
                    </el-table>
                  </el-tab-pane>
                  <el-tab-pane label="所有项目" name="all">
                    <el-table :data="allProjects" style="width: 100%">
                      <el-table-column prop="projectName" label="项目名称" />
                      <el-table-column prop="price" label="价格" />
                      <el-table-column prop="duration" label="时长(分钟)" />
                      <el-table-column label="操作">
                        <template #default="scope">
                          <el-button
                            type="primary"
                            size="small"
                            @click="selectProject(scope.row)"
                          >
                            选择
                          </el-button>
                        </template>
                      </el-table-column>
                    </el-table>
                  </el-tab-pane>
                </el-tabs>
              </div>
              <template #footer>
                <span class="dialog-footer">
                  <el-button @click="projectDialogVisible = false"
                    >关闭</el-button
                  >
                </span>
              </template>
            </el-dialog>

            <!-- 已选择的项目列表 -->
            <el-table
              :data="appointmentForm.appointmentItems"
              style="width: 100%"
            >
              <el-table-column prop="projectName" label="项目名称" />
              <el-table-column prop="price" label="价格" />
              <el-table-column prop="quantity" label="数量">
                <template #default="scope">
                  <el-input-number
                    v-model="scope.row.quantity"
                    :min="1"
                    size="small"
                  />
                </template>
              </el-table-column>
              <el-table-column label="操作">
                <template #default="scope">
                  <el-button
                    type="danger"
                    size="small"
                    @click="removeAppointmentItem(scope.$index)"
                  >
                    删除
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </el-collapse-item>
        </el-collapse>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="appointmentDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleAppointmentConfirm"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>

    <!-- 预约详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="预约详情" width="500px">
      <div v-if="currentAppointment" class="appointment-detail">
        <el-card shadow="hover" class="mb-4">
          <template #header>
            <span>预约信息</span>
          </template>
          <div class="grid grid-cols-2 gap-4">
            <div>预约号: {{ currentAppointment.id }}</div>
            <div>客户姓名: {{ currentAppointment.customerName }}</div>
            <div>客户手机号: {{ currentAppointment.customerPhone }}</div>
            <div>服务项目: {{ currentAppointment.serviceName }}</div>
            <div>服务员工: {{ currentAppointment.employeeName }}</div>
            <div>预约日期: {{ currentAppointment.appointmentDate }}</div>
            <div>
              预约时间: {{ currentAppointment.startTime }}-{{
                currentAppointment.endTime
              }}
            </div>
            <div>房间: {{ currentAppointment.roomName }}</div>
            <div>
              状态:
              <el-tag :type="getStatusType(currentAppointment.status)">{{
                getStatusText(currentAppointment.status)
              }}</el-tag>
            </div>
            <div>备注: {{ currentAppointment.remark || "无" }}</div>
          </div>
        </el-card>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">关闭</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from "vue";
import { Plus, Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import http from "@/utils/http";
import { useDataCacheStoreHook } from "@/store/modules/dataCache";

// 日期选择
const filterForm = reactive({
  date: new Date("2026-02-03"),
  storeId: 1,
  employeeId: [],
  status: ""
});

// 时间范围
const timeRange = ref([]);

// 时间槽
const timeSlots = ref([]);
// 时间槽宽度
const slotWidth = ref(55);
// 时间槽容器ref
const timeSlotsWrapper = ref(null);
// 生成半小时为单位的时间槽
const generateTimeSlots = () => {
  const timeSlotsArray = [];
  const startHour = 9;
  const endHour = 21;

  for (let hour = startHour; hour < endHour; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      const timeString = `${hour.toString().padStart(2, "0")}:${minute.toString().padStart(2, "0")}`;
      timeSlotsArray.push(timeString);
    }
  }

  timeSlots.value = timeSlotsArray;
  // 计算时间槽宽度
  calculateSlotWidth();
};

// 计算时间槽宽度
const calculateSlotWidth = () => {
  // 获取容器宽度
  const container = document.querySelector(".time-slots-container");
  if (container) {
    const containerWidth = container.clientWidth;
    const slotCount = timeSlots.value.length;
    if (slotCount > 0) {
      // 计算每个时间槽的宽度
      slotWidth.value = Math.floor(containerWidth / slotCount);
    }
  }
};

// 防抖函数，用于优化窗口 resize 事件
const debounce = (func, delay) => {
  let timer;
  return function () {
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, arguments), delay);
  };
};

// 使用防抖优化窗口大小变化事件
const debouncedCalculateSlotWidth = debounce(calculateSlotWidth, 100);
window.addEventListener("resize", debouncedCalculateSlotWidth);

// 初始化时生成时间槽
generateTimeSlots();

// 门店列表
const storeList = ref([
  { id: 1, name: "旗舰店" },
  { id: 2, name: "中心店" },
  { id: 3, name: "分店" }
]);

// 获取门店列表
const getStores = async () => {
  try {
    // 检查缓存是否有效
    if (
      !dataCacheStore.isStoresExpired &&
      dataCacheStore.cachedStores.length > 0
    ) {
      // 使用缓存数据
      storeList.value = dataCacheStore.cachedStores;
    } else {
      // 调用后端API获取门店列表
      const response = await http.get("/api/appointment/get-stores");
      if (response.code === 200) {
        // 更新缓存
        dataCacheStore.updateStores(response.data);
        storeList.value = response.data;
      }
    }
  } catch (error) {
    console.error("获取门店列表失败", error);
    // 保持现有数据，不设置为空数组
  }
};

// 员工列表（模拟数据）
const allEmployees = ref([
  { id: 1, name: "张美容师", position: "高级美容师", storeId: 1 },
  { id: 2, name: "李美容师", position: "中级美容师", storeId: 1 },
  { id: 3, name: "王美容师", position: "初级美容师", storeId: 2 },
  { id: 4, name: "赵美容师", position: "高级美容师", storeId: 2 },
  { id: 5, name: "钱美容师", position: "中级美容师", storeId: 3 }
]);

// 服务列表（模拟数据）
const serviceList = ref([
  { id: 1, name: "面部护理" },
  { id: 2, name: "身体按摩" },
  { id: 3, name: "美甲" },
  { id: 4, name: "美发" },
  { id: 5, name: "美容咨询" }
]);

// 房间列表（模拟数据）
const roomList = ref([
  { id: 1, storeId: 1, storeName: "旗舰店", name: "VIP房间1" },
  { id: 2, storeId: 1, storeName: "旗舰店", name: "VIP房间2" },
  { id: 3, storeId: 1, storeName: "旗舰店", name: "普通房间1" },
  { id: 4, storeId: 2, storeName: "中心店", name: "VIP房间" },
  { id: 5, storeId: 2, storeName: "中心店", name: "普通房间" },
  { id: 6, storeId: 3, storeName: "分店", name: "唯一房间" }
]);

// 美容师服务配置（模拟数据）
const beauticianServiceList = ref([
  {
    id: 1,
    employeeId: 1,
    employeeName: "张美容师",
    serviceIds: [1, 2],
    serviceNames: "面部护理, 身体按摩"
  },
  {
    id: 2,
    employeeId: 2,
    employeeName: "李美容师",
    serviceIds: [1, 3],
    serviceNames: "面部护理, 美甲"
  },
  {
    id: 3,
    employeeId: 3,
    employeeName: "王美容师",
    serviceIds: [2, 4],
    serviceNames: "身体按摩, 美发"
  },
  {
    id: 4,
    employeeId: 4,
    employeeName: "赵美容师",
    serviceIds: [1, 2, 3, 4, 5],
    serviceNames: "面部护理, 身体按摩, 美甲, 美发, 美容咨询"
  },
  {
    id: 5,
    employeeId: 5,
    employeeName: "钱美容师",
    serviceIds: [3, 4],
    serviceNames: "美甲, 美发"
  }
]);

// 预约列表（模拟数据）
const appointmentList = ref([
  {
    id: "APT202602030001",
    customerName: "张三",
    customerPhone: "13800138001",
    employeeId: 1,
    employeeName: "张美容师",
    serviceId: 1,
    serviceName: "面部护理",
    roomId: 1,
    roomName: "VIP房间1",
    appointmentDate: "2026-02-03",
    appointmentTime: "10:00",
    startTime: "10:00",
    endTime: "11:00",
    status: "confirmed"
  },
  {
    id: "APT202602030002",
    customerName: "李四",
    customerPhone: "13800138002",
    employeeId: 2,
    employeeName: "李美容师",
    serviceId: 3,
    serviceName: "美甲",
    roomId: 3,
    roomName: "普通房间1",
    appointmentDate: "2026-02-03",
    appointmentTime: "14:00",
    startTime: "14:00",
    endTime: "15:30",
    status: "pending"
  },
  {
    id: "APT202602030003",
    customerName: "王五",
    customerPhone: "13800138003",
    employeeId: 1,
    employeeName: "张美容师",
    serviceId: 2,
    serviceName: "身体按摩",
    roomId: 2,
    roomName: "VIP房间2",
    appointmentDate: "2026-02-03",
    appointmentTime: "16:00",
    startTime: "16:00",
    endTime: "17:30",
    status: "completed"
  },
  // 新增模拟数据，测试跨时间槽合并
  {
    id: "APT202602030004",
    customerName: "赵六",
    customerPhone: "13800138004",
    employeeId: 1,
    employeeName: "张美容师",
    serviceId: 4,
    serviceName: "美发",
    roomId: 1,
    roomName: "VIP房间1",
    appointmentDate: "2026-02-03",
    appointmentTime: "09:00",
    startTime: "09:00",
    endTime: "11:30",
    status: "confirmed"
  },
  {
    id: "APT202602030005",
    customerName: "孙七",
    customerPhone: "13800138005",
    employeeId: 2,
    employeeName: "李美容师",
    serviceId: 5,
    serviceName: "美容咨询",
    roomId: 3,
    roomName: "普通房间1",
    appointmentDate: "2026-02-03",
    appointmentTime: "10:30",
    startTime: "10:30",
    endTime: "12:00",
    status: "pending"
  },
  {
    id: "APT202602030006",
    customerName: "周八",
    customerPhone: "13800138006",
    employeeId: 3,
    employeeName: "王美容师",
    serviceId: 2,
    serviceName: "身体按摩",
    roomId: 4,
    roomName: "VIP房间",
    appointmentDate: "2026-02-03",
    appointmentTime: "13:00",
    startTime: "13:00",
    endTime: "16:00",
    status: "completed"
  }
]);

// 加载状态
const loading = ref(false);

// 数据缓存store
const dataCacheStore = useDataCacheStoreHook();

// 配置标签
const activeConfigTab = ref("room");

// 拖动选择相关状态
const isDragging = ref(false);
const dragStartSlot = ref<number | null>(null);
const dragEndSlot = ref<number | null>(null);
const currentEmployeeId = ref<number | null>(null);

// 房间配置表单
const roomForm = reactive({
  storeId: 1,
  name: ""
});

// 美容师配置表单
const beauticianForm = reactive({
  employeeId: 0,
  serviceIds: []
});

// 预约表单
const appointmentDialogVisible = ref(false);
const dialogType = ref("add");
const appointmentFormRef = ref<FormInstance>();
const appointmentForm = reactive({
  id: "",
  departmentId: 0,
  customerId: "",
  employeeId: [],
  managerId: 0,
  appointmentDate: new Date().toISOString().slice(0, 10),
  startTime: "",
  endTime: "",
  appointmentType: "point",
  roomId: "",
  status: "pending",
  remark: "",
  appointmentItems: []
});

const appointmentRules = reactive<FormRules>({
  departmentId: [{ required: true, message: "请选择部门", trigger: "change" }],
  customerId: [{ required: true, message: "请选择客户", trigger: "change" }],
  employeeId: [{ required: true, message: "请选择服务人", trigger: "change" }],
  appointmentDate: [
    { required: true, message: "请选择预约日期", trigger: "change" }
  ],
  startTime: [{ required: true, message: "请选择开始时间", trigger: "change" }],
  endTime: [{ required: true, message: "请选择结束时间", trigger: "change" }],
  appointmentType: [
    { required: true, message: "请选择预约方式", trigger: "change" }
  ],
  roomId: [{ required: true, message: "请选择房间", trigger: "change" }],
  status: [{ required: true, message: "请选择进度", trigger: "change" }]
});

// 部门列表
const departmentList = ref([
  { id: 1, name: "美容部", enableCategory: true },
  { id: 2, name: "美发部", enableCategory: true },
  { id: 3, name: "美甲部", enableCategory: false }
]);

// 原始客户列表
const originalCustomerList = ref([
  { id: 1, name: "张三", phone: "13800138001", storeId: 1, departmentId: 1 },
  { id: 2, name: "李四", phone: "13800138002", storeId: 1, departmentId: 2 },
  { id: 3, name: "王五", phone: "13800138003", storeId: 2, departmentId: 1 }
]);

// 搜索结果列表
const searchCustomerList = ref([]);

// 是否正在搜索
const isSearching = ref(false);

// 筛选后的客户列表
const customerList = computed(() => {
  if (isSearching.value) {
    return searchCustomerList.value;
  }
  return originalCustomerList.value.filter(customer => {
    // 按门店筛选
    if (customer.storeId !== filterForm.storeId) {
      return false;
    }
    // 按部门筛选
    if (customer.departmentId !== appointmentForm.departmentId) {
      return false;
    }
    return true;
  });
});

// 客户搜索加载状态
const customerLoading = ref(false);

// 折叠面板状态
const activeCollapseNames = ref(["basic", "items"]);

// 核心业务部门（用于el-segmented显示）
const coreDepartments = computed(() => {
  return departmentList.value
    .filter(dept => dept.enableCategory)
    .map(dept => ({
      label: dept.name,
      value: dept.id
    }));
});

// 项目选择对话框
const projectDialogVisible = ref(false);
const activeProjectTab = ref("customer");

// 客户已有项目
const customerProjects = ref([
  {
    projectId: 1,
    projectName: "面部护理",
    price: 200,
    duration: 60,
    remaining: 5
  },
  {
    projectId: 2,
    projectName: "身体按摩",
    price: 300,
    duration: 90,
    remaining: 3
  }
]);

// 所有项目
const allProjects = ref([
  { projectId: 1, projectName: "面部护理", price: 200, duration: 60 },
  { projectId: 2, projectName: "身体按摩", price: 300, duration: 90 },
  { projectId: 3, projectName: "美甲", price: 100, duration: 45 },
  { projectId: 4, projectName: "美发", price: 150, duration: 60 }
]);

// 预约详情
const detailDialogVisible = ref(false);
const currentAppointment = ref<any>(null);

// 筛选后的员工
const filteredEmployees = computed(() => {
  // 首先按门店筛选
  const storeFiltered = allEmployees.value.filter(
    employee => employee.storeId === filterForm.storeId
  );

  // 然后按部门筛选（如果选择了部门）
  if (appointmentForm.departmentId) {
    return storeFiltered.filter(employee => {
      // 假设员工数据中包含 departmentId 字段
      return employee.departmentId === appointmentForm.departmentId;
    });
  }

  return storeFiltered;
});

// 员工预约缓存
const employeeAppointmentsCache = ref<Record<number, any[]>>({});

// 缓存键，用于检测是否需要重新计算
const cacheKey = computed(() => {
  const dateStr = filterForm.date.toISOString().slice(0, 10);
  return `${dateStr}-${JSON.stringify(timeSlots.value)}-${JSON.stringify(appointmentList.value.map(a => a.id))}`;
});

// 获取员工的预约列表（包含时间范围信息）
const getEmployeeAppointments = (employeeId: number) => {
  const key = `${employeeId}-${cacheKey.value}`;

  // 检查缓存是否存在
  if (employeeAppointmentsCache.value[key]) {
    return employeeAppointmentsCache.value[key];
  }

  const dateStr = filterForm.date.toISOString().slice(0, 10);
  const employeeAppointments = appointmentList.value.filter(
    appointment =>
      appointment.employeeId === employeeId &&
      appointment.appointmentDate === dateStr
  );

  // 为每个预约计算时间槽索引范围
  const result = employeeAppointments.map(appointment => {
    const startTime = appointment.startTime;
    const endTime = appointment.endTime;

    // 查找开始和结束时间对应的时间槽索引
    const startIndex = timeSlots.value.indexOf(startTime);
    let endIndex = timeSlots.value.indexOf(endTime);

    // 如果结束时间不在时间槽中，找到最接近的下一个时间槽
    if (endIndex === -1) {
      endIndex = timeSlots.value.findIndex(slot => slot > endTime);
      if (endIndex === -1) {
        endIndex = timeSlots.value.length - 1;
      }
    } else {
      // 如果结束时间正好是时间槽的开始时间，使用前一个时间槽
      endIndex = Math.max(0, endIndex - 1);
    }

    return {
      ...appointment,
      startIndex,
      endIndex
    };
  });

  // 缓存结果
  employeeAppointmentsCache.value[key] = result;
  return result;
};

// 初始化数据
onMounted(async () => {
  // 按顺序初始化数据，避免并发请求导致的重复调用
  await getStores(); // 使用缓存获取门店列表
  await getDepartments(); // 使用缓存获取部门列表
  await getCustomers();
  await getEmployees();
  await getBedrooms();
  await getAppointmentList();

  // 延迟计算时间槽宽度，确保DOM完全渲染
  setTimeout(() => {
    calculateSlotWidth();
  }, 100);
});

// 获取预约列表
const getAppointmentList = async () => {
  loading.value = true;
  try {
    // 调用后端API
    const response = await http.get("/api/appointment/get-appointments", {
      params: {
        appointmentDate: filterForm.date.toISOString().slice(0, 10),
        storeId: filterForm.storeId,
        employeeId: filterForm.employeeId,
        status: filterForm.status
      }
    });
    if (response.code === 200) {
      if (response.data && response.data.length > 0) {
        appointmentList.value = response.data;
      } else {
        // 如果没有数据，清空列表但不显示错误
        appointmentList.value = [];
      }
    } else {
      ElMessage.error(response.message || "获取预约列表失败");
    }
  } catch (error) {
    console.error("获取预约列表失败", error);
    ElMessage.error("获取预约列表失败，使用模拟数据");
    // 保持模拟数据，不设置为空数组
  } finally {
    loading.value = false;
  }
};

// 获取部门列表
const getDepartments = async () => {
  try {
    // 检查缓存是否有效
    if (
      !dataCacheStore.isDepartmentsExpired &&
      dataCacheStore.cachedDepartments.length > 0
    ) {
      // 使用缓存数据
      departmentList.value = dataCacheStore.cachedDepartments;
    } else {
      // 调用后端API获取部门列表
      const response = await http.get("/api/appointment/get-departments");
      if (response.code === 200) {
        // 更新缓存
        dataCacheStore.updateDepartments(response.data);
        departmentList.value = response.data;
      }
    }
  } catch (error) {
    console.error("获取部门列表失败", error);
  }
};

// 获取客户列表
const getCustomers = async () => {
  try {
    const response = await http.get("/api/appointment/get-customers");
    if (response.code === 200) {
      originalCustomerList.value = response.data;
    }
  } catch (error) {
    console.error("获取客户列表失败", error);
    // 保持现有数据，不设置为空数组
  }
};

// 获取员工列表
const getEmployees = async () => {
  try {
    // 只有当门店ID有效时才发起请求
    if (!filterForm.storeId || filterForm.storeId === 0) {
      return;
    }

    const response = await http.get("/api/appointment/get-employees", {
      params: {
        storeId: filterForm.storeId
      }
    });
    if (response.code === 200) {
      allEmployees.value = response.data;
    }
  } catch (error) {
    console.error("获取员工列表失败", error);
    // 保持现有数据，不设置为空数组
  }
};

// 获取床位列表
const getBedrooms = async () => {
  try {
    // 只有当门店ID有效时才发起请求
    if (!filterForm.storeId || filterForm.storeId === 0) {
      return;
    }

    const response = await http.get("/api/enterprise/bedroom", {
      params: {
        storeId: filterForm.storeId
      }
    });
    if (response.code === 200) {
      // 将床位数据转换为房间格式
      roomList.value = response.data.map((bedroom: any) => ({
        id: bedroom.id,
        storeId: bedroom.storeId,
        name: bedroom.roomName
      }));
    }
  } catch (error) {
    console.error("获取床位列表失败", error);
    // 保持现有数据，不设置为空数组
  }
};

// 日期变更
const handleDateChange = async () => {
  await getAppointmentList();
};

// 门店变更
const handleStoreChange = async () => {
  // 按顺序调用API，避免并发请求导致的重复调用
  await getEmployees();
  await getBedrooms();
  await getAppointmentList();
};

// 筛选
const handleFilter = async () => {
  await getAppointmentList();
};

// 重置筛选
const resetFilter = async () => {
  // 保存原始值，用于比较是否需要重新获取数据
  const originalStoreId = filterForm.storeId;

  filterForm.date = new Date();
  filterForm.storeId = 1;
  filterForm.employeeId = [];
  filterForm.status = "";

  // 只有当门店ID发生变化时，才重新获取员工和床位列表
  if (filterForm.storeId !== originalStoreId) {
    await getEmployees();
    await getBedrooms();
  }
  // 重新获取预约列表
  await getAppointmentList();
};

// 刷新
const handleRefresh = async () => {
  // 按顺序调用API，避免并发请求导致的重复调用
  await getStores(); // 使用缓存，只有在缓存过期时才会重新请求
  await getDepartments(); // 使用缓存，只有在缓存过期时才会重新请求
  await getCustomers();
  await getEmployees();
  await getBedrooms();
  await getAppointmentList();
};

// 时间范围变更
const handleTimeRangeChange = () => {
  // 重新生成时间槽
  generateTimeSlots();
};

// 获取拖动选择的类名
const getDragClass = (employeeId: number, slotIndex: number) => {
  // 检查是否正在拖动，并且是当前员工的槽位
  if (
    !isDragging.value ||
    employeeId !== currentEmployeeId.value ||
    dragStartSlot.value === null ||
    dragEndSlot.value === null
  ) {
    return "";
  }

  // 检查槽位是否已有预约，如果有则不显示拖动选择
  const employeeAppointments = getEmployeeAppointments(employeeId);
  const hasAppointment = employeeAppointments.some(
    appointment =>
      slotIndex >= appointment.startIndex && slotIndex <= appointment.endIndex
  );
  if (hasAppointment) {
    return "";
  }

  // 计算拖动范围
  const startSlot = Math.min(dragStartSlot.value, dragEndSlot.value);
  const endSlot = Math.max(dragStartSlot.value, dragEndSlot.value);

  // 检查当前槽位是否在拖动范围内
  if (slotIndex >= startSlot && slotIndex <= endSlot) {
    return "drag-selection";
  }

  return "";
};

// 鼠标按下事件 - 开始拖动
const handleSlotMouseDown = (employeeId: number, slotIndex: number) => {
  // 检查槽位是否已有预约，如果有则不允许拖动
  const employeeAppointments = getEmployeeAppointments(employeeId);
  const hasAppointment = employeeAppointments.some(
    appointment =>
      slotIndex >= appointment.startIndex && slotIndex <= appointment.endIndex
  );
  if (hasAppointment) {
    return;
  }

  // 开始拖动
  isDragging.value = true;
  dragStartSlot.value = slotIndex;
  dragEndSlot.value = slotIndex;
  currentEmployeeId.value = employeeId;
};

// 鼠标移动事件 - 更新拖动范围
const handleSlotMouseEnter = (employeeId: number, slotIndex: number) => {
  // 检查是否正在拖动，并且是当前员工的槽位
  if (isDragging.value && employeeId === currentEmployeeId.value) {
    // 检查槽位是否已有预约，如果有则不更新拖动范围
    const employeeAppointments = getEmployeeAppointments(employeeId);
    const hasAppointment = employeeAppointments.some(
      appointment =>
        slotIndex >= appointment.startIndex && slotIndex <= appointment.endIndex
    );
    if (hasAppointment) {
      return;
    }

    // 更新拖动范围
    dragEndSlot.value = slotIndex;
  }
};

// 鼠标释放事件 - 结束拖动
const handleSlotMouseUp = (employeeId: number, slotIndex: number) => {
  if (isDragging.value) {
    isDragging.value = false;

    if (dragStartSlot.value !== null && dragEndSlot.value !== null) {
      const startSlot = Math.min(dragStartSlot.value, dragEndSlot.value);
      const endSlot = Math.max(dragStartSlot.value, dragEndSlot.value);

      if (startSlot !== endSlot) {
        // 计算开始和结束时间
        const startTime = timeSlots.value[startSlot];
        const endSlotIndex = endSlot + 1;
        const endTime =
          endSlotIndex < timeSlots.value.length
            ? timeSlots.value[endSlotIndex]
            : "21:00";

        // 打开预约窗口
        handleAddAppointmentWithTimeRange(employeeId, startTime, endTime);
      }

      // 重置拖动状态
      dragStartSlot.value = null;
      dragEndSlot.value = null;
      currentEmployeeId.value = null;
    }
  }
};

// 点击槽位
const handleSlotClick = (employeeId: number, slotIndex: number) => {
  const slotTime = timeSlots.value[slotIndex];
  const employeeAppointments = getEmployeeAppointments(employeeId);

  // 检查是否有预约占用了这个槽位
  const existingAppointment = employeeAppointments.find(
    appointment =>
      slotIndex >= appointment.startIndex && slotIndex <= appointment.endIndex
  );

  if (existingAppointment) {
    // 查看现有预约
    viewAppointmentDetail(existingAppointment);
  } else {
    // 新增预约
    handleAddAppointmentWithSlot(employeeId, slotTime);
  }
};

// 根据槽位和时间范围新增预约
const handleAddAppointmentWithTimeRange = (
  employeeId: number,
  startTime: string,
  endTime: string
) => {
  dialogType.value = "add";
  resetAppointmentForm();
  appointmentForm.employeeId = employeeId;
  appointmentForm.startTime = startTime;
  appointmentForm.endTime = endTime;
  appointmentDialogVisible.value = true;
};

// 新增预约
const handleAddAppointment = () => {
  dialogType.value = "add";
  resetAppointmentForm();
  appointmentDialogVisible.value = true;
};

// 根据槽位新增预约
const handleAddAppointmentWithSlot = (employeeId: number, slotTime: string) => {
  dialogType.value = "add";
  resetAppointmentForm();
  appointmentForm.employeeId = employeeId;
  appointmentForm.startTime = slotTime;
  // 计算结束时间（默认30分钟）
  const [hour, minute] = slotTime.split(":").map(Number);
  let endHour = hour;
  let endMinute = minute + 30;
  if (endMinute >= 60) {
    endHour += 1;
    endMinute -= 60;
  }
  appointmentForm.endTime = `${endHour.toString().padStart(2, "0")}:${endMinute.toString().padStart(2, "0")}`;
  appointmentDialogVisible.value = true;
};

// 重置预约表单
const resetAppointmentForm = () => {
  appointmentForm.id = "";
  // 默认选中第一个核心业务部门
  const firstCoreDepartment = coreDepartments.value[0];
  appointmentForm.departmentId = firstCoreDepartment
    ? firstCoreDepartment.value
    : 0;
  appointmentForm.customerId = "";
  appointmentForm.employeeId = [];
  appointmentForm.managerId = 0;
  appointmentForm.appointmentDate = new Date().toISOString().slice(0, 10);
  appointmentForm.startTime = "";
  appointmentForm.endTime = "";
  appointmentForm.appointmentType = "point";
  appointmentForm.roomId = "";
  appointmentForm.status = "pending";
  appointmentForm.remark = "";
  appointmentForm.appointmentItems = [];
};

// 防抖函数，用于优化搜索输入
const debounceSearch = (func: Function, delay: number) => {
  let timer: number | null = null;
  return function (this: any, ...args: any[]) {
    if (timer) clearTimeout(timer);
    timer = window.setTimeout(() => {
      func.apply(this, args);
    }, delay);
  };
};

// 处理客户搜索
const handleCustomerSearch = debounceSearch((query: string) => {
  if (query) {
    customerLoading.value = true;
    isSearching.value = true;
    // 先按门店和部门筛选，再按关键词搜索
    searchCustomerList.value = originalCustomerList.value.filter(customer => {
      // 按门店筛选
      if (customer.storeId !== filterForm.storeId) {
        return false;
      }
      // 按部门筛选
      if (customer.departmentId !== appointmentForm.departmentId) {
        return false;
      }
      // 按关键词搜索
      return customer.name.includes(query) || customer.phone.includes(query);
    });
    customerLoading.value = false;
  } else {
    // 清空搜索时，恢复默认筛选
    isSearching.value = false;
    searchCustomerList.value = [];
  }
}, 300);

// 监听服务人变化，自动设置管理者为员工的上级
watch(
  () => appointmentForm.employeeId,
  (newEmployeeIds, oldEmployeeIds) => {
    // 只有当服务人ID实际变化时才执行逻辑
    if (JSON.stringify(newEmployeeIds) !== JSON.stringify(oldEmployeeIds)) {
      if (newEmployeeIds && newEmployeeIds.length > 0) {
        // 假设选择的第一个员工的上级作为管理者
        const firstEmployeeId = Array.isArray(newEmployeeIds)
          ? newEmployeeIds[0]
          : newEmployeeIds;
        const firstEmployee = allEmployees.value.find(
          employee => employee.id === firstEmployeeId
        );
        if (firstEmployee && firstEmployee.managerId) {
          // 如果员工有上级，设置为管理者
          appointmentForm.managerId = firstEmployee.managerId;
        } else {
          // 如果员工没有上级，清空管理者
          appointmentForm.managerId = 0;
        }
      } else {
        // 如果没有选择服务人，清空管理者
        appointmentForm.managerId = 0;
      }
    }
  },
  { immediate: false }
);

// 获取顾客已有项目
const getCustomerProjects = async (customerId: any) => {
  if (!customerId || customerId === "") return;

  try {
    // 调用后端API
    const response = await http.get("/api/appointment/get-customer-projects", {
      params: {
        customerId: customerId
      }
    });
    if (response.code === 200) {
      customerProjects.value = response.data.customerProjects || [];
      allProjects.value = response.data.allProjects || [];
    } else {
      ElMessage.error(response.message || "获取客户项目失败");
    }
  } catch (error) {
    console.error("获取客户项目失败", error);
    ElMessage.error("获取客户项目失败");
    // 保持现有数据，不设置为空数组
  }
};

// 获取所有项目
const getAllProjects = async () => {
  try {
    // 调用后端API
    const response = await http.get("/api/appointment/get-projects");
    if (response.code === 200) {
      allProjects.value = response.data || [];
    } else {
      ElMessage.error(response.message || "获取项目列表失败");
    }
  } catch (error) {
    console.error("获取项目列表失败", error);
    ElMessage.error("获取项目列表失败");
    // 保持现有数据，不设置为空数组
  }
};

// 处理添加预约项目
const handleAddAppointmentItem = async () => {
  // 获取顾客已有项目
  if (appointmentForm.customerId) {
    await getCustomerProjects(appointmentForm.customerId);
  } else {
    // 如果没有选择客户，只获取所有项目
    await getAllProjects();
  }
  projectDialogVisible.value = true;
};

// 选择项目
const selectProject = (project: any) => {
  const existingItem = appointmentForm.appointmentItems.find(
    item => item.projectId === project.projectId
  );
  if (!existingItem) {
    appointmentForm.appointmentItems.push({
      projectId: project.projectId,
      projectName: project.projectName,
      price: project.price,
      quantity: 1
    });
  }
  projectDialogVisible.value = false;
};

// 移除预约项目
const removeAppointmentItem = (index: number) => {
  appointmentForm.appointmentItems.splice(index, 1);
};

// 确认预约
const handleAppointmentConfirm = async () => {
  if (!appointmentFormRef.value) return;
  await appointmentFormRef.value.validate((valid: boolean) => {
    if (valid) {
      // 检查是否添加了预约项目
      if (appointmentForm.appointmentItems.length === 0) {
        ElMessage.warning("请添加预约项目");
        return;
      }

      // 检查是否选择了服务人
      if (
        !appointmentForm.employeeId ||
        appointmentForm.employeeId.length === 0
      ) {
        ElMessage.warning("请选择服务人");
        return;
      }

      // 为每个服务人生成一条预约记录
      const employeeIds = Array.isArray(appointmentForm.employeeId)
        ? appointmentForm.employeeId
        : [appointmentForm.employeeId];
      const promises = employeeIds.map(employeeId => {
        // 准备请求数据
        const requestData = {
          departmentId: appointmentForm.departmentId,
          customerId: appointmentForm.customerId,
          employeeId: employeeId,
          managerId: appointmentForm.managerId,
          appointmentDate: appointmentForm.appointmentDate,
          startTime: appointmentForm.startTime,
          endTime: appointmentForm.endTime,
          appointmentType: appointmentForm.appointmentType,
          roomId: appointmentForm.roomId,
          status: appointmentForm.status,
          remark: appointmentForm.remark,
          appointmentItems: appointmentForm.appointmentItems
        };

        // 调用后端API
        if (dialogType.value === "add") {
          return http.post("/api/appointment/add-appointment", requestData);
        } else {
          // 编辑模式下，只更新当前预约
          return http.put(
            `/api/appointment/update-appointment/${appointmentForm.id}`,
            requestData
          );
        }
      });

      // 并行处理所有预约请求
      Promise.all(promises)
        .then(responses => {
          const allSuccess = responses.every(response => response.code === 200);
          if (allSuccess) {
            appointmentDialogVisible.value = false;
            ElMessage.success(
              dialogType.value === "add" ? "预约添加成功" : "预约更新成功"
            );
            getAppointmentList();
          } else {
            const errorMessages = responses
              .filter(r => r.code !== 200)
              .map(r => r.message);
            ElMessage.error(errorMessages.join("; ") || "处理预约失败");
          }
        })
        .catch(error => {
          ElMessage.error(
            dialogType.value === "add" ? "添加预约失败" : "更新预约失败"
          );
        });
    }
  });
};

// 查看预约详情
const viewAppointmentDetail = (appointment: any) => {
  currentAppointment.value = appointment;
  detailDialogVisible.value = true;
};

// 编辑预约
const editAppointment = (appointment: any) => {
  dialogType.value = "edit";
  appointmentForm.id = appointment.id;
  appointmentForm.departmentId = appointment.departmentId || 0;
  appointmentForm.customerId = appointment.customerId || "";
  appointmentForm.employeeId = Array.isArray(appointment.employeeId)
    ? appointment.employeeId
    : appointment.employeeId
      ? [appointment.employeeId]
      : [];
  appointmentForm.managerId = appointment.managerId || 0;
  appointmentForm.appointmentDate = appointment.appointmentDate;
  appointmentForm.startTime =
    appointment.startTime || appointment.appointmentTime;
  appointmentForm.endTime = appointment.endTime || "";
  appointmentForm.appointmentType = appointment.appointmentType || "point";
  appointmentForm.roomId = appointment.roomId || "";
  appointmentForm.status = appointment.status || "pending";
  appointmentForm.remark = appointment.remark || "";
  // 这里需要从API获取预约项目，暂时使用空数组
  appointmentForm.appointmentItems = [];
  appointmentDialogVisible.value = true;
};

// 取消预约
const cancelAppointment = (appointment: any) => {
  ElMessageBox.confirm("确定要取消该预约吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  }).then(() => {
    // 模拟API请求
    setTimeout(() => {
      const index = appointmentList.value.findIndex(
        item => item.id === appointment.id
      );
      if (index !== -1) {
        appointmentList.value[index].status = "cancelled";
      }
      ElMessage.success("预约已取消");
    }, 500);
  });
};

// 检查是否可以编辑预约
const canEditAppointment = (status: string) => {
  return status === "pending" || status === "confirmed";
};

// 检查是否可以取消预约
const canCancelAppointment = (status: string) => {
  return status === "pending" || status === "confirmed";
};

// 获取状态文本
const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    pending: "待确认",
    confirmed: "已确认",
    completed: "已完成",
    cancelled: "已取消"
  };
  return statusMap[status] || status;
};

// 获取状态类型
const getStatusType = (status: string) => {
  const typeMap: Record<string, string> = {
    pending: "warning",
    confirmed: "info",
    completed: "success",
    cancelled: "danger"
  };
  return typeMap[status] || "";
};

// 获取合并时间槽的样式
const getMergedSlotStyle = (appointment: any) => {
  // 计算开始位置和宽度
  const left = appointment.startIndex * slotWidth.value;
  const width =
    (appointment.endIndex - appointment.startIndex + 1) * slotWidth.value;

  return {
    left: `${left}px`,
    width: `${width}px`,
    position: "absolute",
    top: "0",
    height: "100%"
  };
};

// 添加房间
const handleAddRoom = () => {
  if (!roomForm.name) {
    ElMessage.warning("请输入房间名称");
    return;
  }

  // 查找门店名称
  const store = storeList.value.find(s => s.id === roomForm.storeId);
  const storeName = store ? store.name : "";

  // 添加房间
  roomList.value.push({
    id: roomList.value.length + 1,
    storeId: roomForm.storeId,
    storeName: storeName,
    name: roomForm.name
  });

  roomForm.name = "";
  ElMessage.success("房间添加成功");
};

// 删除房间
const deleteRoom = (id: number) => {
  ElMessageBox.confirm("确定要删除该房间吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  }).then(() => {
    roomList.value = roomList.value.filter(room => room.id !== id);
    ElMessage.success("房间已删除");
  });
};

// 添加美容师服务配置
const handleAddBeauticianService = () => {
  if (!beauticianForm.employeeId || beauticianForm.serviceIds.length === 0) {
    ElMessage.warning("请选择员工和服务项目");
    return;
  }

  // 查找员工名称
  const employee = allEmployees.value.find(
    e => e.id === beauticianForm.employeeId
  );
  const employeeName = employee ? employee.name : "";

  // 查找服务名称
  const serviceNames = serviceList.value
    .filter(s => beauticianForm.serviceIds.includes(s.id))
    .map(s => s.name)
    .join(", ");

  // 添加美容师服务配置
  beauticianServiceList.value.push({
    id: beauticianServiceList.value.length + 1,
    employeeId: beauticianForm.employeeId,
    employeeName: employeeName,
    serviceIds: beauticianForm.serviceIds,
    serviceNames: serviceNames
  });

  beauticianForm.employeeId = 0;
  beauticianForm.serviceIds = [];
  ElMessage.success("美容师服务配置添加成功");
};

// 删除美容师服务配置
const deleteBeauticianService = (id: number) => {
  ElMessageBox.confirm("确定要删除该美容师服务配置吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  }).then(() => {
    beauticianServiceList.value = beauticianServiceList.value.filter(
      item => item.id !== id
    );
    ElMessage.success("美容师服务配置已删除");
  });
};
</script>

<style scoped>
.appointment-management-container {
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

.filter-section {
  margin-bottom: 20px;
}

.schedule-section {
  margin-bottom: 20px;
  overflow-x: auto;
  width: 100%;
  max-width: 100%;
}

.schedule-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.schedule-table {
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  overflow: hidden;
  white-space: nowrap;
  width: 100%;
  min-width: fit-content;
  display: flex;
  flex-direction: column;
}

/* 确保表头行能够弹性变化 */
.schedule-header-row {
  height: 35px;
  display: flex;
  background-color: #f5f7fa;
  border-bottom: 1px solid #e4e7ed;
}

/* 确保员工行能够弹性变化 */
.employee-row {
  height: 80px;
  display: flex;
  align-items: stretch;
  border-bottom: 1px solid #e4e7ed;
}

/* 确保员工列固定宽度 */
.employee-column {
  flex: 0 0 100px;
  padding: 8px;
  border-right: 1px solid #e4e7ed;
  border-bottom: 1px solid #e4e7ed;
  background-color: #fafafa;
  min-height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  font-size: 13px;
  font-weight: bold;
  z-index: 1;
}

/* 表头员工列样式 */
.schedule-header-row .employee-column {
  min-height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 100px;
  font-size: 12px;
}

/* 确保时间槽列有足够的宽度且适应屏幕 */
.time-slot-column {
  padding: 4px;
  border-right: 1px solid #e4e7ed;
  border-bottom: 1px solid #e4e7ed;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
  font-size: 11px;
  min-height: 80px;
  box-sizing: border-box;
  z-index: 1;
}

.time-slots-container {
  display: flex;
  flex: 1;
  overflow: hidden;
  position: relative;
  min-width: 0;
  width: 100%;
}

/* 时间槽包装器，确保时间槽能够弹性分布 */
.time-slots-wrapper {
  display: flex;
  flex: 1;
  overflow: hidden;
  position: relative;
  min-width: 0;
  width: 100%;
}

/* 时间槽样式，确保它们能够弹性分布并填充容器 */
.time-slot {
  flex: 1;
  padding: 4px;
  border-right: 1px solid #e4e7ed;
  border-bottom: 1px solid #e4e7ed;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
  font-size: 11px;
  min-height: 80px;
  box-sizing: border-box;
  z-index: 1;
  min-width: 40px;
}

/* 表头时间槽样式 */
.schedule-header-row .time-slot {
  min-height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 500;
}

/* 时间槽悬停效果 */
.time-slot:hover {
  background-color: #ecf5ff;
}

/* 确保拖动选择样式对.time-slot类也生效 */
.time-slot.drag-selection {
  background-color: #e6f7ff !important;
  border: 1px solid #91d5ff !important;
  cursor: pointer !important;
}

.time-slot-column {
  flex: 1;
  padding: 4px;
  border-right: 1px solid #e4e7ed;
  border-bottom: 1px solid #e4e7ed;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  position: relative;
  overflow: hidden;
  font-size: 11px;
  min-height: 80px;
  box-sizing: border-box;
  z-index: 1;
  min-width: 40px;
}

/* 表头时间槽样式 */
.schedule-header-row .time-slot-column {
  min-height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
}

/* 确保员工行的时间槽与表头时间槽对齐 */
.employee-row {
  display: flex;
  align-items: stretch;
}

/* 合并预约覆盖层 */
.merged-appointments-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  z-index: 2;
}

/* 合并时间槽样式 */
.merged-time-slot {
  position: absolute;
  top: 0;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  box-sizing: border-box;
  z-index: 2;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.3s;
  pointer-events: auto;
}

/* 为不同状态的合并时间槽添加背景色 */
.merged-time-slot.pending {
  background-color: #fdf6ec;
  border: 1px solid #fcd34d;
}

.merged-time-slot.confirmed {
  background-color: #ecf5ff;
  border: 1px solid #60a5fa;
}

.merged-time-slot.completed {
  background-color: #f0f9eb;
  border: 1px solid #34d399;
}

.merged-time-slot.cancelled {
  background-color: #fef0f0;
  border: 1px solid #f87171;
}

.appointment-item {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 4px;
  box-sizing: border-box;
  z-index: 2;
  border-radius: 4px;
  background-color: rgba(255, 255, 255, 0.8);
}

.hidden-item {
  background: transparent;
}

/* 调整预约内容的样式 */
.appointment-content {
  width: 100%;
  max-width: 150px;
  text-align: center;
  font-size: 10px;
}

/* 为不同状态的预约添加背景色 */
.merged-slot-start.pending {
  background-color: #fdf6ec;
}

.merged-slot-middle.pending {
  background-color: #fdf6ec;
}

.merged-slot-end.pending {
  background-color: #fdf6ec;
}

.merged-slot-start.confirmed {
  background-color: #ecf5ff;
}

.merged-slot-middle.confirmed {
  background-color: #ecf5ff;
}

.merged-slot-end.confirmed {
  background-color: #ecf5ff;
}

.merged-slot-start.completed {
  background-color: #f0f9eb;
}

.merged-slot-middle.completed {
  background-color: #f0f9eb;
}

.merged-slot-end.completed {
  background-color: #f0f9eb;
}

.merged-slot-start.cancelled {
  background-color: #fef0f0;
}

.merged-slot-middle.cancelled {
  background-color: #fef0f0;
}

.merged-slot-end.cancelled {
  background-color: #fef0f0;
}

/* 表头时间槽样式 */
.schedule-header-row .time-slot-column {
  min-height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
}

/* 表头员工列样式 */
.schedule-header-row .employee-column {
  min-height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 12px;
}

.employee-row:hover {
  background-color: #f9fafc;
}

.time-slot-column:hover {
  background-color: #ecf5ff;
}

.empty-slot {
  background-color: #ffffff;
}

.appointment-slot {
  background-color: #f0f9eb;
}

.appointment-slot.pending {
  background-color: #fdf6ec;
}

.appointment-slot.confirmed {
  background-color: #ecf5ff;
}

.appointment-slot.completed {
  background-color: #f0f9eb;
}

.appointment-slot.cancelled {
  background-color: #fef0f0;
}

/* 拖动选择样式 */
.drag-selection {
  background-color: #e6f7ff !important;
  border: 1px solid #91d5ff;
  cursor: pointer;
  position: relative;
  z-index: 3;
}

/* 确保拖动选择样式优先于其他样式 */
.time-slot-column.drag-selection {
  background-color: #e6f7ff !important;
  border: 1px solid #91d5ff !important;
  cursor: pointer !important;
}

.appointment-customer {
  font-weight: bold;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 10px;
  margin-bottom: 2px;
}

.appointment-service {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #606266;
  font-size: 9px;
  margin-bottom: 2px;
}

.room {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #606266;
  font-size: 9px;
  margin-bottom: 2px;
}

.appointment-time {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #909399;
  font-size: 8px;
  margin-bottom: 2px;
}

.appointment-room {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #909399;
  font-size: 8px;
}

.configuration-section {
  margin-top: 20px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}
</style>
