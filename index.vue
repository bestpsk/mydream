<template>
  <div class="card-container">
    <el-card class="h-full flex flex-col">
      <!-- 标签页 -->
      <el-tabs v-model="activeTab" @tab-click="handleTabClick">
        <!-- 充值卡 -->
        <el-tab-pane label="充值卡" name="recharge">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('card:recharge:view')">
              <div class="mb-4 flex justify-between items-center">
                <span class="text-lg font-medium">充值卡管理</span>
                <el-button type="primary" @click="handleAdd">
                  <el-icon><Plus /></el-icon>
                  新增充值卡
                </el-button>
              </div>
              <div class="flex-1 min-h-0">
                <el-table v-loading="loading" :data="rechargeCardList" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="cardName" label="充值卡名称" min-width="150" />
                  <el-table-column label="充值金额/赠送金额" width="180">
                    <template #default="scope">
                      {{ scope.row.amount }} / {{ scope.row.giftAmount }}
                    </template>
                  </el-table-column>
                  <el-table-column label="项目折扣/产品折扣" width="180">
                    <template #default="scope">
                      {{ scope.row.projectDiscount }}% ({{ (scope.row.projectDiscount / 10).toFixed(1) }}折) / {{ scope.row.productDiscount }}% ({{ (scope.row.productDiscount / 10).toFixed(1) }}折)
                    </template>
                  </el-table-column>
                  <el-table-column label="耗卡率" width="100">
                    <template #default="scope">
                      {{ scope.row.consumeRate }}%
                    </template>
                  </el-table-column>
                  <el-table-column prop="expireType" label="过期类型" width="120">
                    <template #default="scope">
                      {{ expireTypeMap[scope.row.expireType] || scope.row.expireType }}
                    </template>
                  </el-table-column>
                  <el-table-column label="状态" width="120">
                    <template #default="scope">
                      <el-switch
                        v-model="scope.row.status"
                        @change="value => handleStatusChange(scope.row, value)"
                      />
                    </template>
                  </el-table-column>
                  <el-table-column label="操作" width="160" fixed="right">
                    <template #default="scope">
                      <el-button type="primary" size="small" @click="handleEdit(scope.row)">
                        <el-icon><Edit /></el-icon>
                        编辑
                      </el-button>
                      <el-button size="small" type="danger" @click="handleDelete(scope.row.id)">
                        <el-icon><Delete /></el-icon>
                        删除
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
                <div class="flex justify-between mt-4">
                  <el-pagination
                    v-model:current-page="pagination.currentPage"
                    v-model:page-size="pagination.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="pagination.total"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                  />
                </div>
              </div>
            </div>
            <!-- 无权限提示 -->
            <div v-else class="no-permission flex-1 flex items-center justify-center">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>



        <!-- 套餐卡 -->
        <el-tab-pane label="套餐卡" name="package">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('card:package:view')">
              <!-- 新增按钮和搜索栏放在同一行 -->
              <div class="mb-4 flex justify-between items-center">
                <!-- 搜索栏 -->
                <div class="search-bar flex-grow">
                  <el-form :inline="true" :model="packageSearchForm" class="w-full">
                    <el-form-item label="卡名称">
                      <el-input v-model="packageSearchForm.cardName" placeholder="请输入卡名称" />
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary" @click="handlePackageSearch">
                        <el-icon><Search /></el-icon>
                        搜索
                      </el-button>
                      <el-button @click="resetPackageSearch">
                        <el-icon><Refresh /></el-icon>
                        重置
                      </el-button>
                    </el-form-item>
                  </el-form>
                </div>
                <!-- 新增按钮 -->
                <el-button v-if="hasAuth('card:package:add')" type="primary" class="ml-4" @click="handleAddPackage">
                  <el-icon><Plus /></el-icon>
                  新增套餐卡
                </el-button>
              </div>

              <!-- 套餐卡列表 -->
              <div class="flex-1 min-h-0">
                <el-table v-loading="packageLoading" :data="packageList" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="cardName" label="卡名称" />
                  <el-table-column prop="price" label="价格" />
                  <el-table-column prop="projectCount" label="包含项目数" />
                  <el-table-column prop="createTime" label="创建时间" />
                  <el-table-column label="操作" width="180">
                    <template #default="scope">
                      <el-button v-if="hasAuth('card:package:edit')" type="primary" size="small" @click="handleEditPackage(scope.row)">
                        <el-icon><Edit /></el-icon>
                        编辑
                      </el-button>
                      <el-button v-if="hasAuth('card:package:delete')" type="danger" size="small" @click="handleDeletePackage(scope.row.id)">
                        <el-icon><Delete /></el-icon>
                        删除
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>

                <!-- 分页 -->
                <div class="pagination mt-4">
                  <el-pagination
                    v-model:current-page="packagePagination.current"
                    v-model:page-size="packagePagination.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="packagePagination.total"
                    @size-change="handlePackageSizeChange"
                    @current-change="handlePackageCurrentChange"
                  />
                </div>
              </div>
            </div>
            <!-- 无权限提示 -->
            <div v-else class="no-permission flex-1 flex items-center justify-center">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>

        <!-- 时效卡 -->
        <el-tab-pane label="时效卡" name="time">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('card:time:view')">
              <!-- 新增按钮和搜索栏放在同一行 -->
              <div class="mb-4 flex justify-between items-center">
                <!-- 搜索栏 -->
                <div class="search-bar flex-grow">
                  <el-form :inline="true" :model="timeSearchForm" class="w-full">
                    <el-form-item label="卡名称">
                      <el-input v-model="timeSearchForm.cardName" placeholder="请输入卡名称" />
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary" @click="handleTimeSearch">
                        <el-icon><Search /></el-icon>
                        搜索
                      </el-button>
                      <el-button @click="resetTimeSearch">
                        <el-icon><Refresh /></el-icon>
                        重置
                      </el-button>
                    </el-form-item>
                  </el-form>
                </div>
                <!-- 新增按钮 -->
                <el-button v-if="hasAuth('card:time:add')" type="primary" class="ml-4" @click="handleAddTime">
                  <el-icon><Plus /></el-icon>
                  新增时效卡
                </el-button>
              </div>

              <!-- 时效卡列表 -->
              <div class="flex-1 min-h-0">
                <el-table v-loading="timeLoading" :data="timeList" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="cardName" label="卡名称" />
                  <el-table-column prop="validDays" label="有效期(天)" />
                  <el-table-column prop="price" label="价格" />
                  <el-table-column prop="createTime" label="创建时间" />
                  <el-table-column label="操作" width="180">
                    <template #default="scope">
                      <el-button v-if="hasAuth('card:time:edit')" type="primary" size="small" @click="handleEditTime(scope.row)">
                        <el-icon><Edit /></el-icon>
                        编辑
                      </el-button>
                      <el-button v-if="hasAuth('card:time:delete')" type="danger" size="small" @click="handleDeleteTime(scope.row.id)">
                        <el-icon><Delete /></el-icon>
                        删除
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>

                <!-- 分页 -->
                <div class="pagination mt-4">
                  <el-pagination
                    v-model:current-page="timePagination.current"
                    v-model:page-size="timePagination.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="timePagination.total"
                    @size-change="handleTimeSizeChange"
                    @current-change="handleTimeCurrentChange"
                  />
                </div>
              </div>
            </div>
            <!-- 无权限提示 -->
            <div v-else class="no-permission flex-1 flex items-center justify-center">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 充值卡表单对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      :width="dialogWidth"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
      >
        <el-tabs v-model="activeFormTab" type="border-card">
          <!--充值卡--基本信息与其他设置 -->
          <el-tab-pane label="基本信息" name="basic">
            <el-form :model="formData" label-width="120px">
              <el-form-item label="充值卡名称" prop="cardName">
                <el-input v-model="formData.cardName" placeholder="请输入充值卡名称" :disabled="isModificationDisabled" />
              </el-form-item>
              <div class="flex space-x-4">
                <el-form-item label="充值金额" prop="amount" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.amount" :min="0" :step="0.01" :precision="2" class="w-full" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
                <el-form-item label="赠送金额" prop="giftAmount" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.giftAmount" :min="0" :step="0.01" :precision="2" class="w-full" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
              </div>
              <div class="flex space-x-4">
                <el-form-item label="项目折扣" prop="projectDiscount" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.projectDiscount" :min="0" :max="100" :step="1" class="w-full" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">%</span>
                    <span class="ml-2 text-gray-500">({{ (formData.projectDiscount / 10).toFixed(1) }}折)</span>
                  </div>
                </el-form-item>
                <el-form-item label="产品折扣" prop="productDiscount" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.productDiscount" :min="0" :max="100" :step="1" class="w-full" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">%</span>
                    <span class="ml-2 text-gray-500">({{ (formData.productDiscount / 10).toFixed(1) }}折)</span>
                  </div>
                </el-form-item>
              </div>
              <div class="flex space-x-4">
                <el-form-item label="耗卡率" prop="consumeRate" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.consumeRate" :min="0" :max="100" :step="1" class="w-full" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">%</span>
                  </div>
                </el-form-item>
                <el-form-item label="续充最低限额" prop="minRechargeLimit" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.minRechargeLimit" :min="0" :step="0.01" :precision="2" class="w-full" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
              </div>
              
              <el-divider content-position="left">其他设置</el-divider>
              
              <el-form-item label="充值卡描述" prop="description">
                <el-input
                  v-model="formData.description"
                  type="textarea"
                  placeholder="请输入充值卡描述"
                  rows="3"
                  :disabled="isModificationDisabled"
                />
              </el-form-item>
              <el-form-item label="备注" prop="remark">
                <el-input
                  v-model="formData.remark"
                  type="textarea"
                  placeholder="请输入备注"
                  rows="2"
                  :disabled="isModificationDisabled"
                />
              </el-form-item>
              <el-form-item label="功能选项">
                <el-checkbox-group v-model="formData.featureOptions" :disabled="isModificationDisabled">
                  <el-checkbox label="1" :disabled="formData.featureOptions.includes('1')">禁止修改</el-checkbox>
                  <el-checkbox label="2">限购1次</el-checkbox>
                  <el-checkbox label="3">过期卡作废</el-checkbox>
                  <el-checkbox label="4">卡内项目过期</el-checkbox>
                  <el-checkbox label="5">禁止修改折扣</el-checkbox>
                </el-checkbox-group>
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <!-- 充值卡--时间设置与限定设置 -->
          <el-tab-pane label="时间与限定设置" name="time-limits">
            <el-form :model="formData" label-width="120px">
              <el-divider content-position="left">时间设置</el-divider>
              
              <div class="flex space-x-4">
                <el-form-item label="上线时间" prop="onlineTime" style="width: 48%">
                  <el-date-picker
                    v-model="formData.onlineTime"
                    type="datetime"
                    placeholder="选择上线时间"
                    style="width: 100%"
                    :disabled="isModificationDisabled"
                  />
                </el-form-item>
                <el-form-item label="下线时间" prop="offlineTime" style="width: 48%">
                  <el-date-picker
                    v-model="formData.offlineTime"
                    type="datetime"
                    placeholder="选择下线时间"
                    style="width: 100%"
                    :disabled="isModificationDisabled"
                  />
                </el-form-item>
              </div>
              <div class="flex space-x-4">
                <el-form-item label="过期类型" prop="expireType" style="width: 48%">
                  <el-select v-model="formData.expireType" placeholder="请选择过期类型" style="width: 100%" :disabled="isModificationDisabled">
                    <el-option label="固定日期过期" value="3" />
                    <el-option label="从开卡时计算" value="1" />
                    <el-option label="从消耗时计算" value="2" />
                  </el-select>
                </el-form-item>
                <el-form-item label="过期设置" prop="expireDate" style="width: 48%">
                  <template v-if="formData.expireType === '3'">
                    <el-date-picker
                      v-model="formData.expireDate"
                      type="date"
                      placeholder="选择过期日期"
                      style="width: 100%"
                      :disabled="isModificationDisabled"
                    />
                  </template>
                  <template v-else>
                    <div class="flex items-center" style="width: 100%">
                      <el-input-number v-model="formData.expireMonths" :min="1" :step="1" style="width: calc(100% - 70px)" :disabled="isModificationDisabled" />
                      <span class="ml-2 text-gray-500">月后过期</span>
                    </div>
                  </template>
                </el-form-item>
              </div>
              
              <el-divider content-position="left">限定设置</el-divider>
              
              <el-form-item label="限定销售分店" prop="saleStoreIds">
                <el-select
                  v-model="formData.saleStoreIds"
                  multiple
                  placeholder="请选择限定销售分店"
                  class="w-full"
                  :disabled="isModificationDisabled"
                >
                  <el-option
                    v-for="store in storeList"
                    :key="store.id"
                    :label="store.name"
                    :value="store.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="限定消费分店" prop="consumeStoreIds">
                <el-select
                  v-model="formData.consumeStoreIds"
                  multiple
                  placeholder="请选择限定消费分店"
                  class="w-full"
                  :disabled="isModificationDisabled"
                >
                  <el-option
                    v-for="store in storeList"
                    :key="store.id"
                    :label="store.name"
                    :value="store.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="限定销售部门" prop="saleDepartmentIds">
                <el-select
                  v-model="formData.saleDepartmentIds"
                  multiple
                  placeholder="请选择限定销售部门"
                  class="w-full"
                  :disabled="isModificationDisabled"
                >
                  <el-option
                    v-for="dept in departmentList"
                    :key="dept.id"
                    :label="dept.name"
                    :value="dept.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="限定消费部门" prop="consumeDepartmentIds">
                <el-select
                  v-model="formData.consumeDepartmentIds"
                  multiple
                  placeholder="请选择限定消费部门"
                  class="w-full"
                  :disabled="isModificationDisabled"
                >
                  <el-option
                    v-for="dept in departmentList"
                    :key="dept.id"
                    :label="dept.name"
                    :value="dept.id"
                  />
                </el-select>
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <!-- 充值卡--包含项目 -->
          <el-tab-pane label="包含项目" name="projects">
            <div class="mb-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-medium">包含项目</h3>
                <div class="flex space-x-2">
                  <el-button type="primary" size="small" @click="handleAddIncludeProject" :disabled="isModificationDisabled">添加项目</el-button>
                  <el-button type="danger" size="small" @click="handleBatchDeleteIncludeProject" :disabled="selectedProjectRows.length === 0 || isModificationDisabled">
                    <el-icon><Delete /></el-icon>
                    批量删除
                  </el-button>
                </div>
              </div>
              <el-table 
                :data="formData.giftProjects" 
                style="width: 100%" 
                border
                @selection-change="handleProjectSelectionChange"
              >
                <el-table-column type="selection" width="55" :selectable="() => !isModificationDisabled" />
                <el-table-column label="项目" min-width="150">
                  <template #default="scope">
                    <div class="flex items-center">
                      {{ scope.row.projectName }}
                      <el-tag v-if="scope.row.totalPrice === 0" size="small" type="success" class="ml-2">赠</el-tag>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column prop="times" label="总次数*" width="90">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.times" type="number" size="small" @change="() => updateProjectTotalPrice(scope.$index)" style="width: 100%" placeholder="1" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="unitPrice" label="单价*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.unitPrice" type="number" size="small" @change="() => updateProjectTotalPrice(scope.$index)" style="width: 100%" placeholder="0.00" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="totalPrice" label="总价" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.totalPrice" type="number" size="small" @change="() => updateProjectUnitPrice(scope.$index)" style="width: 100%" placeholder="0.00" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="consume" label="耗卡*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.consume" type="number" size="small" style="width: 100%" placeholder="0" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="manualSalary" label="手工*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.manualSalary" type="number" size="small" style="width: 100%" placeholder="0.00" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="60" fixed="right">
                  <template #default="scope">
                    <el-button size="small" type="danger" @click="handleDeleteIncludeProject(scope.$index)" :disabled="isModificationDisabled">
                      <el-icon><Delete /></el-icon>
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </el-tab-pane>
          
          <!-- 充值卡--包含产品 -->
          <el-tab-pane label="包含产品" name="products">
            <div class="mb-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-medium">包含产品</h3>
                <div class="flex space-x-2">
                  <el-button type="primary" size="small" @click="handleAddIncludeProduct" :disabled="isModificationDisabled">添加产品</el-button>
                  <el-button type="danger" size="small" @click="handleBatchDeleteIncludeProduct" :disabled="selectedProductRows.length === 0 || isModificationDisabled">
                    <el-icon><Delete /></el-icon>
                    批量删除
                  </el-button>
                </div>
              </div>
              <el-table 
                :data="formData.giftProducts" 
                style="width: 100%" 
                border
                @selection-change="handleProductSelectionChange"
              >
                <el-table-column type="selection" width="55" :selectable="() => !isModificationDisabled" />
                <el-table-column label="产品" min-width="150">
                  <template #default="scope">
                    <div class="flex items-center">
                      {{ scope.row.productName }}
                      <el-tag v-if="scope.row.totalPrice === 0" size="small" type="success" class="ml-2">赠</el-tag>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column prop="times" label="数量*" width="90">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.times" type="number" size="small" style="width: 100%" placeholder="1" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="unitPrice" label="单价*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.unitPrice" type="number" size="small" style="width: 100%" placeholder="0.00" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="totalPrice" label="总价" width="100">
                  <template #default="scope">
                    {{ (scope.row.times * scope.row.unitPrice).toFixed(2) }}
                  </template>
                </el-table-column>
                <el-table-column prop="manualSalary" label="手工*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.manualSalary" type="number" size="small" style="width: 100%" placeholder="0.00" :disabled="isModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="60" fixed="right">
                  <template #default="scope">
                    <el-button size="small" type="danger" @click="handleDeleteIncludeProduct(scope.$index)" :disabled="isModificationDisabled">
                      <el-icon><Delete /></el-icon>
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmit" :disabled="isModificationDisabled">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 充值卡--配赠管理对话框 -->
    <el-dialog
      v-model="giftDialogVisible"
      title="配赠管理"
      width="900px"
      destroy-on-close
    >
      <div class="gift-management">
        <!-- 配赠项目 -->
        <div class="mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">配赠项目</h3>
            <el-button type="primary" size="small" @click="handleAddGiftProject">新增项目</el-button>
          </div>
          <el-table :data="giftProjects" style="width: 100%">
              <el-table-column label="项目名称">
                <template #default="scope">
                  <div class="flex items-center">
                    {{ scope.row.projectName }}
                    <el-tag v-if="scope.row.totalPrice === 0" size="small" type="success" class="ml-2">赠</el-tag>
                  </div>
                </template>
              </el-table-column>
            <el-table-column prop="times" label="次数" width="100" />
            <el-table-column prop="unitPrice" label="单价" width="100" />
            <el-table-column prop="consume" label="耗卡" width="100" />
            <el-table-column prop="manualSalary" label="手工工资" width="100" />
            <el-table-column label="操作" width="150" fixed="right">
              <template #default="scope">
                <el-button size="small" @click="handleEditGiftProject(scope.row)">编辑</el-button>
                <el-button size="small" type="danger" @click="handleDeleteGiftProject(scope.row.id)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <!-- 充值卡--配赠产品 -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">配赠产品</h3>
            <el-button type="primary" size="small" @click="handleAddGiftProduct">新增产品</el-button>
          </div>
          <el-table :data="giftProducts" style="width: 100%">
            <el-table-column label="产品名称">
              <template #default="scope">
                <div class="flex items-center">
                  {{ scope.row.productName }}
                  <el-tag v-if="scope.row.totalPrice === 0" size="small" type="success" class="ml-2">赠</el-tag>
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="times" label="次数" width="100" />
            <el-table-column prop="unitPrice" label="单价" width="100" />
            <el-table-column prop="consume" label="耗卡" width="100" />
            <el-table-column prop="manualSalary" label="手工工资" width="100" />
            <el-table-column label="操作" width="150" fixed="right">
              <template #default="scope">
                <el-button size="small" @click="handleEditGiftProduct(scope.row)">编辑</el-button>
                <el-button size="small" type="danger" @click="handleDeleteGiftProduct(scope.row.id)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="giftDialogVisible = false">
            <el-icon><Close /></el-icon>
            关闭
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 充值卡--配赠项目表单对话框 -->
    <el-dialog
      v-model="giftProjectDialogVisible"
      :title="giftProjectDialogTitle"
      width="600px"
      destroy-on-close
    >
      <el-form
        ref="giftProjectFormRef"
        :model="giftProjectForm"
        :rules="giftProjectRules"
        label-width="100px"
      >
        <el-form-item label="项目名称" prop="projectId">
          <el-select v-model="giftProjectForm.projectId" placeholder="请选择项目" class="w-full" @change="handleProjectChange">
            <el-option
              v-for="project in projectList"
              :key="project.id"
              :label="project.projectName"
              :value="project.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="次数" prop="times">
          <el-input-number v-model="giftProjectForm.times" :min="1" :step="1" @change="calculateTotalPrice" />
        </el-form-item>
        <el-form-item label="单价" prop="unitPrice">
          <el-input-number v-model="giftProjectForm.unitPrice" :min="0" :step="0.01" :precision="2" @change="calculateTotalPrice" />
        </el-form-item>
        <el-form-item label="总价" prop="totalPrice">
          <el-input-number v-model="giftProjectForm.totalPrice" :min="0" :step="0.01" :precision="2" @change="calculateUnitPrice" />
        </el-form-item>
        <el-form-item label="耗卡" prop="consume">
          <el-input-number v-model="giftProjectForm.consume" :min="0" :step="1" />
        </el-form-item>
        <el-form-item label="手工工资" prop="manualSalary">
          <el-input-number v-model="giftProjectForm.manualSalary" :min="0" :step="0.01" :precision="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="giftProjectDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitGiftProject">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 充值卡--配赠产品表单对话框 -->
    <el-dialog
      v-model="giftProductDialogVisible"
      :title="giftProductDialogTitle"
      width="600px"
      destroy-on-close
    >
      <el-form
        ref="giftProductFormRef"
        :model="giftProductForm"
        :rules="giftProductRules"
        label-width="100px"
      >
        <el-form-item label="产品名称" prop="productId">
          <el-select v-model="giftProductForm.productId" placeholder="请选择产品" class="w-full">
            <el-option
              v-for="product in productList"
              :key="product.id"
              :label="product.productName"
              :value="product.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="次数" prop="times">
          <el-input-number v-model="giftProductForm.times" :min="1" :step="1" />
        </el-form-item>
        <el-form-item label="单价" prop="unitPrice">
          <el-input-number v-model="giftProductForm.unitPrice" :min="0" :step="0.01" :precision="2" />
        </el-form-item>
        <el-form-item label="耗卡" prop="consume">
          <el-input-number v-model="giftProductForm.consume" :min="0" :max="100" :step="1" />
        </el-form-item>
        <el-form-item label="手工工资" prop="manualSalary">
          <el-input-number v-model="giftProductForm.manualSalary" :min="0" :step="0.01" :precision="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="giftProductDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitGiftProduct">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 套餐卡表单对话框 -->
    <el-dialog
      v-model="packageDialogVisible"
      :title="packageDialogTitle"
      :width="dialogWidth"
      destroy-on-close
    >
      <el-form
        ref="packageFormRef"
        :model="packageFormData"
        :rules="packageRules"
      >
        <el-tabs v-model="packageActiveTab" type="border-card">
          <!-- 套餐卡--基本信息与其他设置 -->
          <el-tab-pane label="基本信息" name="basic">
            <el-form :model="packageFormData" label-width="120px">
              <el-form-item label="套餐卡名称" prop="cardName">
                <el-input v-model="packageFormData.cardName" placeholder="请输入套餐卡名称" :disabled="isPackageModificationDisabled" />
              </el-form-item>
              <el-form-item label="套餐卡编码" prop="cardCode">
                <el-input v-model="packageFormData.cardCode" placeholder="请输入套餐卡编码" :disabled="isPackageModificationDisabled" />
              </el-form-item>
              <div class="flex space-x-4">
                <el-form-item label="原价" prop="originalPrice" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="packageFormData.originalPrice" :min="0" :step="0.01" :precision="2" style="width: 100%" :disabled="isPackageModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
                <el-form-item label="套餐价" prop="price" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="packageFormData.price" :min="0" :step="0.01" :precision="2" style="width: 100%" :disabled="isPackageModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
              </div>
              
              <el-divider content-position="left">其他设置</el-divider>
              
              <el-form-item label="套餐卡描述" prop="description">
                <el-input
                  v-model="packageFormData.description"
                  type="textarea"
                  placeholder="请输入套餐卡描述"
                  rows="3"
                  :disabled="isPackageModificationDisabled"
                />
              </el-form-item>
              <el-form-item label="备注" prop="remark">
                <el-input
                  v-model="packageFormData.remark"
                  type="textarea"
                  placeholder="请输入备注"
                  rows="2"
                  :disabled="isPackageModificationDisabled"
                />
              </el-form-item>
              <el-form-item label="功能选项">
                <el-checkbox-group v-model="packageFormData.featureOptions" :disabled="isPackageModificationDisabled">
                  <el-checkbox label="1" :disabled="packageFormData.featureOptions.includes('1')">禁止修改</el-checkbox>
                  <el-checkbox label="2">禁止添加新项</el-checkbox>
                  <el-checkbox label="3">限购1次</el-checkbox>
                  <el-checkbox label="4">过期作废</el-checkbox>
                  <el-checkbox label="5">禁止赠送</el-checkbox>
                </el-checkbox-group>
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <!-- 套餐卡--时间设置与限定设置 -->
          <el-tab-pane label="时间与限定设置" name="time-limits">
            <el-form :model="packageFormData" label-width="120px">
              <el-divider content-position="left">时间设置</el-divider>
              
              <div class="flex space-x-4">
                <el-form-item label="上线时间" prop="onlineTime" style="width: 48%">
                  <el-date-picker
                    v-model="packageFormData.onlineTime"
                    type="datetime"
                    placeholder="选择上线时间"
                    style="width: 100%"
                    :disabled="isPackageModificationDisabled"
                  />
                </el-form-item>
                <el-form-item label="下线时间" prop="offlineTime" style="width: 48%">
                  <el-date-picker
                    v-model="packageFormData.offlineTime"
                    type="datetime"
                    placeholder="选择下线时间"
                    style="width: 100%"
                    :disabled="isPackageModificationDisabled"
                  />
                </el-form-item>
              </div>
              <div class="flex space-x-4">
                <el-form-item label="过期类型" prop="expireType" style="width: 48%">
                  <el-select v-model="packageFormData.expireType" placeholder="请选择过期类型" style="width: 100%" :disabled="isPackageModificationDisabled">
                    <el-option label="固定日期过期" value="3" />
                    <el-option label="从开卡时计算" value="1" />
                    <el-option label="从消耗时计算" value="2" />
                  </el-select>
                </el-form-item>
                <el-form-item label="过期设置" prop="expireDate" style="width: 48%">
                  <template v-if="packageFormData.expireType === '3'">
                    <el-date-picker
                      v-model="packageFormData.expireDate"
                      type="date"
                      placeholder="选择过期日期"
                      style="width: 100%"
                      :disabled="isPackageModificationDisabled"
                    />
                  </template>
                  <template v-else>
                    <div class="flex items-center" style="width: 100%">
                      <el-input-number v-model="packageFormData.expireMonths" :min="1" :step="1" style="width: calc(100% - 70px)" :disabled="isPackageModificationDisabled" />
                      <span class="ml-2 text-gray-500">月后过期</span>
                    </div>
                  </template>
                </el-form-item>
              </div>
              
              <el-divider content-position="left">限定设置</el-divider>
              
              <el-form-item label="限定销售分店" prop="saleStoreIds">
                <el-select
                  v-model="packageFormData.saleStoreIds"
                  multiple
                  placeholder="请选择限定销售分店"
                  class="w-full"
                  :disabled="isPackageModificationDisabled"
                >
                  <el-option
                    v-for="store in storeList"
                    :key="store.id"
                    :label="store.name"
                    :value="store.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="限定消费分店" prop="consumeStoreIds">
                <el-select
                  v-model="packageFormData.consumeStoreIds"
                  multiple
                  placeholder="请选择限定消费分店"
                  class="w-full"
                  :disabled="isPackageModificationDisabled"
                >
                  <el-option
                    v-for="store in storeList"
                    :key="store.id"
                    :label="store.name"
                    :value="store.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="限定销售部门" prop="saleDepartmentIds">
                <el-select
                  v-model="packageFormData.saleDepartmentIds"
                  multiple
                  placeholder="请选择限定销售部门"
                  class="w-full"
                  :disabled="isPackageModificationDisabled"
                >
                  <el-option
                    v-for="dept in departmentList"
                    :key="dept.id"
                    :label="dept.name"
                    :value="dept.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="限定消费部门" prop="consumeDepartmentIds">
                <el-select
                  v-model="packageFormData.consumeDepartmentIds"
                  multiple
                  placeholder="请选择限定消费部门"
                  class="w-full"
                  :disabled="isPackageModificationDisabled"
                >
                  <el-option
                    v-for="dept in departmentList"
                    :key="dept.id"
                    :label="dept.name"
                    :value="dept.id"
                  />
                </el-select>
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <!-- 套餐卡--包含项目 -->
          <el-tab-pane label="包含项目" name="projects">
            <div class="mb-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-medium">包含项目</h3>
                <div class="flex space-x-2">
                  <el-button type="primary" size="small" @click="handleAddPackageIncludeProject" :disabled="isPackageModificationDisabled">添加项目</el-button>
                  <el-button type="danger" size="small" @click="handleBatchDeletePackageIncludeProject" :disabled="selectedPackageProjectRows.length === 0 || isPackageModificationDisabled">
                    <el-icon><Delete /></el-icon>
                    批量删除
                  </el-button>
                </div>
              </div>
              <el-table 
                :data="packageFormData.giftProjects" 
                style="width: 100%" 
                border
                @selection-change="handlePackageProjectSelectionChange"
              >
                <el-table-column type="selection" width="55" :selectable="() => !isPackageModificationDisabled" />
                <el-table-column label="项目" min-width="150">
                  <template #default="scope">
                    <div class="flex items-center">
                      {{ scope.row.projectName }}
                      <el-tag v-if="scope.row.totalPrice === 0" size="small" type="success" class="ml-2">赠</el-tag>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column prop="times" label="总次数*" width="90">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.times" type="number" size="small" @change="() => updatePackageProjectTotalPrice(scope.$index)" style="width: 100%" placeholder="1" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="unitPrice" label="单价*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.unitPrice" type="number" size="small" @change="() => updatePackageProjectTotalPrice(scope.$index)" style="width: 100%" placeholder="0.00" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="totalPrice" label="总价" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.totalPrice" type="number" size="small" @change="() => updatePackageProjectUnitPrice(scope.$index)" style="width: 100%" placeholder="0.00" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="consume" label="耗卡*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.consume" type="number" size="small" style="width: 100%" placeholder="0" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="manualSalary" label="手工*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.manualSalary" type="number" size="small" style="width: 100%" placeholder="0.00" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="60" fixed="right">
                  <template #default="scope">
                    <el-button size="small" type="danger" @click="handleDeletePackageIncludeProject(scope.$index)" :disabled="isPackageModificationDisabled">
                      <el-icon><Delete /></el-icon>
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </el-tab-pane>
          
          <!-- 套餐卡--包含产品 -->
          <el-tab-pane label="包含产品" name="products">
            <div class="mb-4">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-medium">包含产品</h3>
                <div class="flex space-x-2">
                  <el-button type="primary" size="small" @click="handleAddPackageIncludeProduct" :disabled="isPackageModificationDisabled">添加产品</el-button>
                  <el-button type="danger" size="small" @click="handleBatchDeletePackageIncludeProduct" :disabled="selectedPackageProductRows.length === 0 || isPackageModificationDisabled">
                    <el-icon><Delete /></el-icon>
                    批量删除
                  </el-button>
                </div>
              </div>
              <el-table 
                :data="packageFormData.giftProducts" 
                style="width: 100%" 
                border
                @selection-change="handlePackageProductSelectionChange"
              >
                <el-table-column type="selection" width="55" :selectable="() => !isPackageModificationDisabled" />
                <el-table-column label="产品" min-width="150">
                  <template #default="scope">
                    <div class="flex items-center">
                      {{ scope.row.productName }}
                      <el-tag v-if="scope.row.totalPrice === 0" size="small" type="success" class="ml-2">赠</el-tag>
                    </div>
                  </template>
                </el-table-column>
                <el-table-column prop="times" label="数量*" width="90">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.times" type="number" size="small" style="width: 100%" placeholder="1" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="unitPrice" label="单价*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.unitPrice" type="number" size="small" style="width: 100%" placeholder="0.00" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column prop="totalPrice" label="总价" width="100">
                  <template #default="scope">
                    {{ (scope.row.times * scope.row.unitPrice).toFixed(2) }}
                  </template>
                </el-table-column>
                <el-table-column prop="manualSalary" label="手工*" width="100">
                  <template #default="scope">
                    <el-input v-model.number="scope.row.manualSalary" type="number" size="small" style="width: 100%" placeholder="0.00" :disabled="isPackageModificationDisabled" />
                  </template>
                </el-table-column>
                <el-table-column label="操作" width="60" fixed="right">
                  <template #default="scope">
                    <el-button size="small" type="danger" @click="handleDeletePackageIncludeProduct(scope.$index)" :disabled="isPackageModificationDisabled">
                      <el-icon><Delete /></el-icon>
                    </el-button>
                  </template>
                </el-table-column>
              </el-table>
            </div>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="packageDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handlePackageSubmit" :disabled="isPackageModificationDisabled">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 套餐卡配赠项目表单对话框 -->
    <el-dialog
      v-model="packageGiftProjectDialogVisible"
      :title="packageGiftProjectDialogTitle"
      width="600px"
      destroy-on-close
    >
      <el-form
        ref="packageGiftProjectFormRef"
        :model="packageGiftProjectForm"
        :rules="packageGiftProjectRules"
        label-width="100px"
      >
        <el-form-item label="项目名称" prop="projectId">
          <el-select v-model="packageGiftProjectForm.projectId" placeholder="请选择项目" class="w-full" @change="handlePackageProjectChange">
            <el-option
              v-for="project in projectList"
              :key="project.id"
              :label="project.projectName"
              :value="project.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="次数" prop="times">
          <el-input-number v-model="packageGiftProjectForm.times" :min="1" :step="1" @change="calculatePackageProjectTotalPrice" />
        </el-form-item>
        <el-form-item label="单价" prop="unitPrice">
          <el-input-number v-model="packageGiftProjectForm.unitPrice" :min="0" :step="0.01" :precision="2" @change="calculatePackageProjectTotalPrice" />
        </el-form-item>
        <el-form-item label="总价" prop="totalPrice">
          <el-input-number v-model="packageGiftProjectForm.totalPrice" :min="0" :step="0.01" :precision="2" @change="calculatePackageProjectUnitPrice" />
        </el-form-item>
        <el-form-item label="耗卡" prop="consume">
          <el-input-number v-model="packageGiftProjectForm.consume" :min="0" :step="1" />
        </el-form-item>
        <el-form-item label="手工工资" prop="manualSalary">
          <el-input-number v-model="packageGiftProjectForm.manualSalary" :min="0" :step="0.01" :precision="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="packageGiftProjectDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitPackageGiftProject">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 套餐卡--配赠产品表单对话框 -->
    <el-dialog
      v-model="packageGiftProductDialogVisible"
      :title="packageGiftProductDialogTitle"
      width="600px"
      destroy-on-close
    >
      <el-form
        ref="packageGiftProductFormRef"
        :model="packageGiftProductForm"
        :rules="packageGiftProductRules"
        label-width="100px"
      >
        <el-form-item label="产品名称" prop="productId">
          <el-select v-model="packageGiftProductForm.productId" placeholder="请选择产品" class="w-full">
            <el-option
              v-for="product in productList"
              :key="product.id"
              :label="product.productName"
              :value="product.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="数量" prop="times">
          <el-input-number v-model="packageGiftProductForm.times" :min="1" :step="1" />
        </el-form-item>
        <el-form-item label="单价" prop="unitPrice">
          <el-input-number v-model="packageGiftProductForm.unitPrice" :min="0" :step="0.01" :precision="2" />
        </el-form-item>
        <el-form-item label="耗卡" prop="consume">
          <el-input-number v-model="packageGiftProductForm.consume" :min="0" :max="100" :step="1" />
        </el-form-item>
        <el-form-item label="手工工资" prop="manualSalary">
          <el-input-number v-model="packageGiftProductForm.manualSalary" :min="0" :step="0.01" :precision="2" />
        </el-form-item>
      </el-form>
<!-- 套餐卡的合计信息 -->

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="packageGiftProductDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitPackageGiftProduct">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 套餐卡项目选择对话框 -->
    <el-dialog
      v-model="projectTransferVisible"
      title="选择项目"
      width="900px"
      destroy-on-close
    >
      <div class="mb-4">
        <el-input
          v-model="projectTransferSearch"
          placeholder="请输入项目名称搜索"
          prefix-icon="Search"
        />
      </div>
      <div class="space-y-2">
        <div v-for="project in projectList" :key="project.id" class="project-card p-2 border rounded bg-white shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center mb-1">
            <input type="checkbox" :value="project.id" v-model="rightProjectList" class="mr-2" />
            <div class="font-medium text-sm ml-2">{{ project.projectName }}</div>
            <el-tag v-if="project.supplierName" size="small" class="ml-2">{{ project.supplierName }}</el-tag>
          </div>
          <div class="flex flex-wrap gap-x-5 gap-y-0.5 text-xs text-gray-500">
            <div class="ml-5">原价: {{ project.originalPrice || 0 }}元</div>
            <div>售卖: {{ project.singleSalePrice || 0 }}元</div>
            <div>服务时长: {{ project.serviceTime || 0 }}分钟</div>
            <div class="w-full mt-0.5 truncate ml-5" :title="project.description || ''">描述: {{ project.description || '' }}</div>
          </div>
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="projectTransferVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleProjectTransferConfirm">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 套餐卡--产品选择对话框 -->
    <el-dialog
      v-model="productTransferVisible"
      title="选择产品"
      width="900px"
      destroy-on-close
    >
      <div class="mb-4">
        <el-input
          v-model="productTransferSearch"
          placeholder="请输入产品名称搜索"
          prefix-icon="Search"
        />
      </div>
      <div class="space-y-2">
        <div v-for="product in productList" :key="product.id" class="product-card p-2 border rounded bg-white shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center mb-1">
            <input type="checkbox" :value="product.id" v-model="rightProductList" class="mr-2" />
            <div class="font-medium text-sm">{{ product.productName }}</div>
          </div>
          <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-gray-500">
            <div>原价: {{ product.originalPrice || 0 }}元</div>
            <div>售卖: {{ product.salePrice || 0 }}元</div>
            <div class="w-full mt-0.5 truncate" :title="product.description || ''">描述: {{ product.description || '' }}</div>
          </div>
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="productTransferVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleProductTransferConfirm">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from "vue";
import { Plus, Edit, Delete, Search, Refresh, Close, Check } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import { hasAuth } from "@/router/utils";
import { useUserStoreHook } from "@/store/modules/user";
import { useDataCacheStoreHook } from "@/store/modules/dataCache";
import {
  getRechargeCardList,
  getRechargeCardDetail,
  addRechargeCard,
  updateRechargeCard,
  deleteRechargeCard,
  getGiftProjects,
  addGiftProject,
  updateGiftProject,
  deleteGiftProject,
  getGiftProducts,
  addGiftProduct,
  updateGiftProduct,
  deleteGiftProduct
} from '@/api/recharge'
import {
  getPackageCards,
  getPackageCardDetail,
  addPackageCard,
  updatePackageCard,
  deletePackageCard,
  getPackageCardGiftProjects,
  getPackageCardGiftProducts,
  addPackageCardGiftProject,
  updatePackageCardGiftProject,
  deletePackageCardGiftProject,
  addPackageCardGiftProduct,
  updatePackageCardGiftProduct,
  deletePackageCardGiftProduct
} from '@/api/package'
import http from '@/utils/http'

// 当前激活的标签页
const activeTab = ref("recharge");
// 充值卡表单激活的标签页
const activeFormTab = ref("basic");

// 对话框宽度（响应式）
const dialogWidth = computed(() => {
  const screenWidth = window.innerWidth;
  if (screenWidth < 768) {
    return "95%";
  } else if (screenWidth < 1200) {
    return "80%";
  } else {
    return "900px";
  }
});

// 获取用户信息
const userStore = useUserStoreHook();
const dataCacheStore = useDataCacheStoreHook();
const companyId = ref(userStore.companyId || 0);

// 加载状态
const rechargeLoading = ref(false);
const packageLoading = ref(false);
const timeLoading = ref(false);

// 充值卡管理相关状态
const loading = ref(false);
const dialogVisible = ref(false);
const giftDialogVisible = ref(false);
const giftProjectDialogVisible = ref(false);
const giftProductDialogVisible = ref(false);
const dialogTitle = ref('新增充值卡');
const giftProjectDialogTitle = ref('新增配赠项目');
const giftProductDialogTitle = ref('新增配赠产品');

// 套餐卡管理相关状态
const packageDialogVisible = ref(false);
const packageDialogTitle = ref('新增套餐卡');
const packageActiveTab = ref('basic');
const packageFormRef = ref<any>(null);
const packageGiftProjectDialogVisible = ref(false);
const packageGiftProductDialogVisible = ref(false);
const packageGiftProjectDialogTitle = ref('添加包含项目');
const packageGiftProductDialogTitle = ref('添加包含产品');
const selectedPackageProjectRows = ref<any[]>([]);
const selectedPackageProductRows = ref<any[]>([]);

// 穿梭框相关状态
const projectTransferVisible = ref(false);
const productTransferVisible = ref(false);
const projectTransferSearch = ref('');
const productTransferSearch = ref('');
const leftProjectList = ref<any[]>([]);
const rightProjectList = ref<any[]>([]);
const leftProductList = ref<any[]>([]);
const rightProductList = ref<any[]>([]);

// 表单引用
const formRef = ref<any>(null);
const giftProjectFormRef = ref<any>(null);
const giftProductFormRef = ref<any>(null);
const packageGiftProjectFormRef = ref<any>(null);
const packageGiftProductFormRef = ref<any>(null);

// 分页信息
const pagination = reactive({
  currentPage: 1,
  pageSize: 10,
  total: 0
});

// 过期类型映射
const expireTypeMap = {
  '1': '开卡计算过期',
  '2': '消耗计算过期',
  '3': '固定日期过期',
  1: '开卡计算过期',
  2: '消耗计算过期',
  3: '固定日期过期'
};

// 表单数据
const formData = reactive({
  id: '',
  cardName: '',
  amount: 0,
  giftAmount: 0,
  projectDiscount: 100,
  productDiscount: 100,
  consumeRate: 100,
  minRechargeLimit: 0,
  onlineTime: new Date(),
  offlineTime: '',
  expireDate: '',
  expireMonths: 12,
  expireType: '3',
  saleStoreIds: [],
  consumeStoreIds: [],
  saleDepartmentIds: [],
  consumeDepartmentIds: [],
  giftProjects: [],
  giftProducts: [],
  description: '',
  remark: '',
  featureOptions: []
});

// 配赠项目表单
const giftProjectForm = reactive({
  id: '',
  rechargeId: '',
  projectId: '',
  times: 1,
  unitPrice: 0,
  totalPrice: 0,
  consume: 0,
  manualSalary: 0
});

// 配赠产品表单
const giftProductForm = reactive({
  id: '',
  rechargeId: '',
  productId: '',
  times: 1,
  unitPrice: 0,
  consume: 0,
  manualSalary: 0
});

// 套餐卡表单数据
const packageFormData = reactive({
  id: '',
  cardName: '',
  cardCode: '',
  originalPrice: 0,
  price: 0,
  onlineTime: new Date(),
  offlineTime: '',
  expireDate: '',
  expireMonths: 12,
  expireType: '3',
  saleStoreIds: [],
  consumeStoreIds: [],
  saleDepartmentIds: [],
  consumeDepartmentIds: [],
  giftProjects: [],
  giftProducts: [],
  description: '',
  remark: '',
  featureOptions: []
});

// 套餐卡配赠项目表单
const packageGiftProjectForm = reactive({
  id: '',
  packageId: '',
  projectId: '',
  times: 1,
  unitPrice: 0,
  totalPrice: 0,
  consume: 0,
  manualSalary: 0
});

// 套餐卡配赠产品表单
const packageGiftProductForm = reactive({
  id: '',
  packageId: '',
  productId: '',
  times: 1,
  unitPrice: 0,
  consume: 0,
  manualSalary: 0
});

// 数据列表
const rechargeCardList = ref<any[]>([]);
const storeList = ref<any[]>([]);
const departmentList = ref<any[]>([]);
const projectList = ref<any[]>([]);
const productList = ref<any[]>([]);
const giftProjects = ref<any[]>([]);
const giftProducts = ref<any[]>([]);

// 批量选择相关
const selectedProjectRows = ref<any[]>([]);
const selectedProductRows = ref<any[]>([]);

// 计算属性：判断是否禁止修改充值卡
const isModificationDisabled = computed(() => {
  return formData.featureOptions.includes('1');
});

// 计算属性：判断是否禁止修改套餐卡
const isPackageModificationDisabled = computed(() => {
  return packageFormData.featureOptions.includes('1');
});

// 表单验证规则
const rules = reactive({
  cardName: [{ required: true, message: '请输入充值卡名称', trigger: ['blur', 'change'] }],
  amount: [
    { required: true, message: '请输入充值金额', trigger: ['blur', 'change'] },
    { type: 'number', min: 0.01, message: '充值金额必须大于0', trigger: ['blur', 'change'] }
  ],
  projectDiscount: [
    { required: true, message: '请输入项目折扣', trigger: ['blur', 'change'] },
    { type: 'number', min: 0.01, message: '项目折扣必须大于0', trigger: ['blur', 'change'] }
  ],
  productDiscount: [
    { required: true, message: '请输入产品折扣', trigger: ['blur', 'change'] },
    { type: 'number', min: 0.01, message: '产品折扣必须大于0', trigger: ['blur', 'change'] }
  ],
  consumeRate: [
    { required: true, message: '请输入耗卡率', trigger: ['blur', 'change'] },
    { type: 'number', min: 0.01, message: '耗卡率必须大于0', trigger: ['blur', 'change'] }
  ],
  expireType: [{ required: true, message: '请选择过期类型', trigger: 'change' }]
});

// 配赠项目表单验证规则
const giftProjectRules = reactive({
  projectId: [{ required: true, message: '请选择项目', trigger: 'change' }],
  times: [{ required: true, message: '请输入次数', trigger: 'blur' }]
});

// 配赠产品表单验证规则
const giftProductRules = reactive({
  productId: [{ required: true, message: '请选择产品', trigger: 'change' }],
  times: [{ required: true, message: '请输入次数', trigger: 'blur' }]
});

// 套餐卡表单验证规则
const packageRules = reactive({
  cardName: [{ required: true, message: '请输入套餐卡名称', trigger: ['blur', 'change'] }],
  cardCode: [{ required: true, message: '请输入套餐卡编码', trigger: ['blur', 'change'] }],
  originalPrice: [{ required: true, message: '请输入原价', trigger: 'blur' }, { type: 'number', min: 0.01, message: '原价必须大于0', trigger: 'blur' }],
  price: [{ required: true, message: '请输入套餐价', trigger: 'blur' }, { type: 'number', min: 0.01, message: '套餐价必须大于0', trigger: 'blur' }],
  expireType: [{ required: true, message: '请选择过期类型', trigger: 'change' }]
});

// 套餐卡配赠项目表单验证规则
const packageGiftProjectRules = reactive({
  projectId: [{ required: true, message: '请选择项目', trigger: 'change' }],
  times: [{ required: true, message: '请输入次数', trigger: 'blur' }]
});

// 套餐卡配赠产品表单验证规则
const packageGiftProductRules = reactive({
  productId: [{ required: true, message: '请选择产品', trigger: 'change' }],
  times: [{ required: true, message: '请输入数量', trigger: 'blur' }]
});

// 搜索表单
const rechargeSearchForm = reactive({
  cardName: ""
});



const packageSearchForm = reactive({
  cardName: ""
});

const timeSearchForm = reactive({
  cardName: ""
});

// 分页信息
const rechargePagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});



const packagePagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

const timePagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 数据列表
const rechargeList = ref([]);

const packageList = ref([]);
const timeList = ref([]);

// 初始化数据
onMounted(() => {
  // 更新companyId
  companyId.value = userStore.companyId || 0;
  
  // 只在有对应权限时调用API获取数据
  if (hasAuth("card:recharge:view")) {
    getRechargeList();
    // 加载充值卡管理相关数据
    fetchRechargeCardList();
    getDepartments();
    getStores();
    getProjects();
    getProducts();
  }

  if (hasAuth("card:package:view")) {
    getPackageList();
  }
  if (hasAuth("card:time:view")) {
    getTimeList();
  }
});

// 处理标签页切换
const handleTabClick = (tab: any) => {
  // 切换到充值卡标签页时加载数据
  if (tab.props.name === "recharge" && hasAuth("card:recharge:view")) {
    // 更新companyId
    companyId.value = userStore.companyId || 0;
    
    // 只在必要时调用API
    if (rechargeCardList.value.length === 0) {
      getRechargeList();
      fetchRechargeCardList();
    }
    
    // 只在必要时调用API
    if (departmentList.value.length === 0) {
      getDepartments();
    }
    
    // 只在必要时调用API
    if (storeList.value.length === 0) {
      getStores();
    }
  }

  // 切换到套餐卡标签页时加载数据
  if (tab.props.name === "package" && hasAuth("card:package:view")) {
    getPackageList();
  }
  // 切换到时效卡标签页时加载数据
  if (tab.props.name === "time" && hasAuth("card:time:view")) {
    getTimeList();
  }
};

// 获取充值卡列表
const getRechargeList = () => {
  if (!hasAuth("card:recharge:view")) {
    return;
  }

  rechargeLoading.value = true;
  // 从后端API获取数据
  // 暂时使用模拟数据
  setTimeout(() => {
    rechargeLoading.value = false;
    rechargeList.value = [];
    rechargePagination.total = 0;
  }, 500);
};



// 获取套餐卡列表
const getPackageList = async () => {
  if (!hasAuth("card:package:view")) {
    return;
  }

  packageLoading.value = true;
  try {
    // 调用API获取套餐卡列表
    const params = {
      page: packagePagination.current,
      pageSize: packagePagination.pageSize,
      cardName: packageSearchForm.cardName
    };
    const response = await getPackageCards(params);
    if (response.code === 200) {
      packageList.value = response.data;
      packagePagination.total = response.total || 0;
    } else {
      ElMessage.error(response.message || '获取套餐卡列表失败');
    }
  } catch (error) {
    console.error('获取套餐卡列表失败:', error);
    ElMessage.error('获取套餐卡列表失败');
  } finally {
    packageLoading.value = false;
  }
};

// 获取时效卡列表
const getTimeList = () => {
  if (!hasAuth("card:time:view")) {
    return;
  }

  timeLoading.value = true;
  // 从后端API获取数据
  // 暂时使用模拟数据
  setTimeout(() => {
    timeLoading.value = false;
    timeList.value = [];
    timePagination.total = 0;
  }, 500);
};

// 搜索方法
const handleRechargeSearch = () => {
  rechargePagination.current = 1;
  getRechargeList();
};

const resetRechargeSearch = () => {
  rechargeSearchForm.cardName = "";
  rechargePagination.current = 1;
  getRechargeList();
};



const handlePackageSearch = () => {
  packagePagination.current = 1;
  getPackageList();
};

const resetPackageSearch = () => {
  packageSearchForm.cardName = "";
  packagePagination.current = 1;
  getPackageList();
};

const handleTimeSearch = () => {
  timePagination.current = 1;
  getTimeList();
};

const resetTimeSearch = () => {
  timeSearchForm.cardName = "";
  timePagination.current = 1;
  getTimeList();
};

// 分页方法
const handleRechargeSizeChange = (size: number) => {
  rechargePagination.pageSize = size;
  getRechargeList();
};

const handleRechargeCurrentChange = (current: number) => {
  rechargePagination.current = current;
  getRechargeList();
};



const handlePackageSizeChange = (size: number) => {
  packagePagination.pageSize = size;
  getPackageList();
};

const handlePackageCurrentChange = (current: number) => {
  packagePagination.current = current;
  getPackageList();
};

const handleTimeSizeChange = (size: number) => {
  timePagination.pageSize = size;
  getTimeList();
};

const handleTimeCurrentChange = (current: number) => {
  timePagination.current = current;
  getTimeList();
};

// 新增方法
const handleAddRecharge = () => {
  // 打开新增充值卡对话框
  console.log("新增充值卡");
};



const handleAddPackage = () => {
  packageDialogTitle.value = '新增套餐卡';
  // 重置表单
  Object.assign(packageFormData, {
    id: '',
    cardName: '',
    cardCode: '',
    price: 0,
    onlineTime: new Date(),
    offlineTime: '',
    expireDate: '',
    expireMonths: 12,
    expireType: '3',
    saleStoreIds: storeList.value.map(store => store.id),
    consumeStoreIds: storeList.value.map(store => store.id),
    saleDepartmentIds: departmentList.value.map(dept => dept.id),
    consumeDepartmentIds: departmentList.value.map(dept => dept.id),
    giftProjects: [],
    giftProducts: [],
    description: '',
    remark: '',
    featureOptions: ['3'] // 默认选中：过期作废
  });
  packageDialogVisible.value = true;
};

const handleAddTime = () => {
  // 打开新增时效卡对话框
  console.log("新增时效卡");
};

// 编辑方法
const handleEditRecharge = (row: any) => {
  // 打开编辑充值卡对话框
  console.log("编辑充值卡", row);
};



const handleEditPackage = async (row: any) => {
  packageDialogTitle.value = '编辑套餐卡';
  
  try {
    // 调用API获取套餐卡详情
    const response = await getPackageCardDetail(row.id);
    if (response.code === 200) {
      const cardDetail = response.data;
      
      // 处理featureOptions字段
      const featureOptions = [];
      if (cardDetail.isModifiable) featureOptions.push('1');
      if (cardDetail.isAddItemDisabled) featureOptions.push('2');
      if (cardDetail.isLimitOnce) featureOptions.push('3');
      if (cardDetail.isExpireInvalid) featureOptions.push('4');
      if (cardDetail.isProhibitGift) featureOptions.push('5');
      
      // 填充表单数据
      Object.assign(packageFormData, {
        id: cardDetail.id,
        cardName: cardDetail.cardName,
        cardCode: cardDetail.cardCode,
        price: cardDetail.price,
        onlineTime: cardDetail.onlineTime,
        offlineTime: cardDetail.offlineTime,
        expireDate: cardDetail.expireDate,
        expireMonths: 12, // 默认值
        expireType: cardDetail.expireType,
        saleStoreIds: cardDetail.saleStoreIds || [],
        consumeStoreIds: cardDetail.consumeStoreIds || [],
        saleDepartmentIds: cardDetail.saleDepartmentIds || [],
        consumeDepartmentIds: cardDetail.consumeDepartmentIds || [],
        giftProjects: cardDetail.giftProjects || [],
        giftProducts: cardDetail.giftProducts || [],
        description: cardDetail.description,
        remark: cardDetail.remark,
        featureOptions: featureOptions
      });
      
      packageDialogVisible.value = true;
    } else {
      ElMessage.error(response.message || '获取套餐卡详情失败');
    }
  } catch (error) {
    console.error('获取套餐卡详情失败:', error);
    ElMessage.error('获取套餐卡详情失败，请稍后重试');
  }
};

const handleEditTime = (row: any) => {
  // 打开编辑时效卡对话框
  console.log("编辑时效卡", row);
};

// 删除方法
const handleDeleteRecharge = (id: number) => {
  ElMessageBox.confirm("确定要删除该充值卡吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      rechargeLoading.value = true;
      // 调用后端API删除充值卡
      // 暂时使用模拟数据
      setTimeout(() => {
        rechargeLoading.value = false;
        ElMessage.success("删除成功");
        getRechargeList();
      }, 500);
    })
    .catch(() => {
      // 取消删除
    });
};



const handleDeletePackage = async (id: number) => {
  ElMessageBox.confirm("确定要删除该套餐卡吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      packageLoading.value = true;
      try {
        // 调用后端API删除套餐卡
        const response = await deletePackageCard(id);
        if (response.code === 200) {
          ElMessage.success("删除成功");
          getPackageList();
        } else {
          ElMessage.error(response.message || "删除失败");
        }
      } catch (error) {
        console.error('删除套餐卡失败:', error);
        ElMessage.error('删除套餐卡失败');
      } finally {
        packageLoading.value = false;
      }
    })
    .catch(() => {
      // 取消删除
    });
};

const handleDeleteTime = (id: number) => {
  ElMessageBox.confirm("确定要删除该时效卡吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      timeLoading.value = true;
      // 调用后端API删除时效卡
      // 暂时使用模拟数据
      setTimeout(() => {
        timeLoading.value = false;
        ElMessage.success("删除成功");
        getTimeList();
      }, 500);
    })
    .catch(() => {
      // 取消删除
    });
};

// 充值卡管理相关方法
const handleAdd = () => {
  dialogTitle.value = '新增充值卡';
  // 重置表单
  Object.assign(formData, {
    id: '',
    cardName: '',
    amount: 0,
    giftAmount: 0,
    projectDiscount: 100,
    productDiscount: 100,
    consumeRate: 100,
    minRechargeLimit: 0,
    onlineTime: new Date(),
    offlineTime: '',
    expireDate: '',
    expireMonths: 12,
    expireType: '3',
    saleStoreIds: storeList.value.map(store => store.id),
    consumeStoreIds: storeList.value.map(store => store.id),
    saleDepartmentIds: departmentList.value.map(dept => dept.id),
    consumeDepartmentIds: departmentList.value.map(dept => dept.id),
    giftProjects: [],
    giftProducts: [],
    description: '',
    remark: '',
    featureOptions: ['3', '4'] // 默认选中：过期卡作废、卡内项目过期
  });
  dialogVisible.value = true;
};

const handleEdit = async (row: any) => {
  dialogTitle.value = '编辑充值卡';
  
  try {
    // 调用API获取充值卡详情
    const response = await getRechargeCardDetail(row.id);
    if (response.code === 200) {
      const cardDetail = response.data;
      
      // 处理featureOptions字段
      const featureOptions = [];
      if (cardDetail.isModifiable) featureOptions.push('1');
      if (cardDetail.isLimitOnce) featureOptions.push('2');
      if (cardDetail.isExpireInvalid) featureOptions.push('3');
      if (cardDetail.isProjectExpire) featureOptions.push('4');
      if (cardDetail.isProhibitDiscountModify) featureOptions.push('5');
      
      // 填充表单数据
      Object.assign(formData, {
        id: cardDetail.id,
        cardName: cardDetail.cardName,
        amount: cardDetail.amount,
        giftAmount: cardDetail.giftAmount,
        projectDiscount: cardDetail.projectDiscount,
        productDiscount: cardDetail.productDiscount,
        consumeRate: cardDetail.consumeRate,
        minRechargeLimit: cardDetail.minRechargeLimit,
        onlineTime: cardDetail.onlineTime,
        offlineTime: cardDetail.offlineTime,
        expireDate: cardDetail.expireDate,
        expireMonths: 12, // 默认值
        expireType: cardDetail.expireType,
        saleStoreIds: cardDetail.saleStoreIds || [],
        consumeStoreIds: cardDetail.consumeStoreIds || [],
        saleDepartmentIds: cardDetail.saleDepartmentIds || [],
        consumeDepartmentIds: cardDetail.consumeDepartmentIds || [],
        giftProjects: cardDetail.giftProjects || [],
        giftProducts: cardDetail.giftProducts || [],
        description: cardDetail.description,
        remark: cardDetail.remark,
        featureOptions: featureOptions
      });
      
      dialogVisible.value = true;
    } else {
      ElMessage.error(response.message || '获取充值卡详情失败');
    }
  } catch (error) {
    console.error('获取充值卡详情失败:', error);
    ElMessage.error('获取充值卡详情失败，请稍后重试');
  }
};

const handleSubmit = async () => {
  if (!formRef.value) return;
  
  // 如果禁止修改，则不执行提交操作
  if (isModificationDisabled.value) {
    ElMessage.warning('该充值卡已设置为禁止修改');
    return;
  }
  
  try {
    await formRef.value.validate();
    
    // 准备提交数据
    const submitData = {
      ...formData,
      // 将featureOptions转换为对应的布尔字段
      isModifiable: formData.featureOptions.includes('1') ? 1 : 0,
      isLimitOnce: formData.featureOptions.includes('2') ? 1 : 0,
      isExpireInvalid: formData.featureOptions.includes('3') ? 1 : 0,
      isProjectExpire: formData.featureOptions.includes('4') ? 1 : 0,
      isProhibitDiscountModify: formData.featureOptions.includes('5') ? 1 : 0
    };
    
    // 根据是否有ID判断是新增还是更新
    if (formData.id) {
      // 更新充值卡
      const response = await updateRechargeCard(formData.id, submitData);
      if (response.code === 200) {
        ElMessage.success('更新成功');
      } else {
        ElMessage.error(response.message || '更新失败');
        return;
      }
    } else {
      // 新增充值卡
      const response = await addRechargeCard(submitData);
      if (response.code === 200) {
        ElMessage.success('新增成功');
      } else {
        ElMessage.error(response.message || '新增失败');
        return;
      }
    }
    
    dialogVisible.value = false;
    // 重新加载列表
    fetchRechargeCardList();
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

const handleDelete = (id: string) => {
  ElMessageBox.confirm('确定要删除这个充值卡吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const response = await deleteRechargeCard(id);
      if (response.code === 200) {
        ElMessage.success('删除成功');
        // 重新加载列表
        fetchRechargeCardList();
      } else {
        ElMessage.error(response.message || '删除失败');
      }
    } catch (error) {
      console.error('删除失败:', error);
      ElMessage.error('删除失败，请稍后重试');
    }
  }).catch(() => {
    // 取消删除
  });
};

// 处理状态变更
const handleStatusChange = async (row: any, newValue?: boolean) => {
  // 确保只在用户主动操作时才执行
  if (newValue === undefined) return;

  try {
    const response = await updateRechargeCard(row.id, {
      status: newValue ? 1 : 0
    });
    if (response.code !== 200) {
      // 如果更新失败，恢复原来的状态
      row.status = !newValue;
      ElMessage.error('状态更新失败');
    }
  } catch (error) {
    console.error('状态更新失败:', error);
    // 如果请求失败，恢复原来的状态
    row.status = !newValue;
    ElMessage.error('状态更新失败');
  }
};

const handleGiftManage = (row: any) => {
  giftProjectForm.rechargeId = row.id;
  giftProductForm.rechargeId = row.id;
  giftDialogVisible.value = true;
  // 加载配赠项目和产品数据
  fetchGiftProjects(row.id);
  fetchGiftProducts(row.id);
};

const handleAddGiftProject = () => {
  giftProjectDialogTitle.value = '新增配赠项目';
  // 重置表单
  Object.assign(giftProjectForm, {
    id: '',
    projectId: '',
    times: 1,
    unitPrice: 0,
    totalPrice: 0,
    consume: 0,
    manualSalary: 0
  });
  giftProjectDialogVisible.value = true;
};

const handleEditGiftProject = (row: any) => {
  giftProjectDialogTitle.value = '编辑配赠项目';
  // 填充表单数据
  Object.assign(giftProjectForm, {
    id: row.id,
    projectId: row.projectId,
    times: row.times,
    unitPrice: row.unitPrice,
    totalPrice: row.times * row.unitPrice,
    consume: row.consume,
    manualSalary: row.manualSalary
  });
  giftProjectDialogVisible.value = true;
};

const handleSubmitGiftProject = async () => {
  if (!giftProjectFormRef.value) return;
  
  try {
    await giftProjectFormRef.value.validate();
    
    // 准备提交数据
    const submitData = {
      ...giftProjectForm
    };
    
    // 查找选中的项目名称
    const selectedProject = projectList.value.find(p => p.id === giftProjectForm.projectId);
    
    // 添加到表单的包含项目中
    formData.giftProjects.push({
      id: giftProjectForm.id,
      projectId: giftProjectForm.projectId,
      projectName: selectedProject ? selectedProject.projectName : '',
      times: giftProjectForm.times,
      unitPrice: giftProjectForm.unitPrice,
      totalPrice: giftProjectForm.totalPrice,
      consume: giftProjectForm.consume,
      manualSalary: giftProjectForm.manualSalary
    });
    
    giftProjectDialogVisible.value = false;
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

const handleDeleteGiftProject = (id: string) => {
  ElMessageBox.confirm('确定要删除这个配赠项目吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const response = await deleteGiftProject(id);
      if (response.data.code === 200) {
        ElMessage.success('删除成功');
        // 重新加载配赠项目列表
        fetchGiftProjects(giftProjectForm.rechargeId);
      } else {
        ElMessage.error(response.data.message || '删除失败');
      }
    } catch (error) {
      console.error('删除失败:', error);
      ElMessage.error('删除失败，请稍后重试');
    }
  }).catch(() => {
    // 取消删除
  });
};

const handleAddGiftProduct = () => {
  giftProductDialogTitle.value = '新增配赠产品';
  // 重置表单
  Object.assign(giftProductForm, {
    id: '',
    productId: '',
    times: 1,
    unitPrice: 0,
    consume: 0,
    manualSalary: 0
  });
  giftProductDialogVisible.value = true;
};

const handleEditGiftProduct = (row: any) => {
  giftProductDialogTitle.value = '编辑配赠产品';
  // 填充表单数据
  Object.assign(giftProductForm, {
    id: row.id,
    productId: row.productId,
    times: row.times,
    unitPrice: row.unitPrice,
    consume: row.consume,
    manualSalary: row.manualSalary
  });
  giftProductDialogVisible.value = true;
};

// 处理项目选择变化
const handleProjectChange = (projectId: string) => {
  // 查找选中的项目
  const selectedProject = projectList.value.find(p => p.id === projectId);
  if (selectedProject) {
    // 设置单价为项目的单次售价
    giftProjectForm.unitPrice = selectedProject.singleSalePrice || 0;
    // 计算总价
    calculateTotalPrice();
  }
};

// 计算总价
const calculateTotalPrice = () => {
  const { times, unitPrice } = giftProjectForm;
  giftProjectForm.totalPrice = times * unitPrice;
};

// 根据总价反算单价
const calculateUnitPrice = () => {
  const { times, totalPrice } = giftProjectForm;
  if (times > 0) {
    giftProjectForm.unitPrice = totalPrice / times;
  }
};

// 更新包含项目表格中项目的总价
const updateProjectTotalPrice = (index: number) => {
  const project = formData.giftProjects[index];
  if (project) {
    project.totalPrice = project.times * project.unitPrice;
  }
};

// 更新包含项目表格中项目的单价
const updateProjectUnitPrice = (index: number) => {
  const project = formData.giftProjects[index];
  if (project && project.times > 0) {
    project.unitPrice = project.totalPrice / project.times;
  }
};

const handleSubmitGiftProduct = async () => {
  if (!giftProductFormRef.value) return;
  
  try {
    await giftProductFormRef.value.validate();
    
    // 准备提交数据
    const submitData = {
      ...giftProductForm
    };
    
    // 查找选中的产品名称
    const selectedProduct = productList.value.find(p => p.id === giftProductForm.productId);
    
    // 添加到表单的包含产品中
    formData.giftProducts.push({
      id: giftProductForm.id,
      productId: giftProductForm.productId,
      productName: selectedProduct ? selectedProduct.productName : '',
      times: giftProductForm.times,
      unitPrice: giftProductForm.unitPrice,
      totalPrice: giftProductForm.times * giftProductForm.unitPrice,
      manualSalary: giftProductForm.manualSalary
    });
    
    giftProductDialogVisible.value = false;
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

// 处理删除配赠产品
const handleDeleteGiftProduct = (id: string) => {
  ElMessageBox.confirm('确定要删除这个配赠产品吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const response = await deleteGiftProduct(id);
      if (response.data.code === 200) {
        ElMessage.success('删除成功');
        // 重新加载配赠产品列表
        fetchGiftProducts(giftProductForm.rechargeId);
      } else {
        ElMessage.error(response.data.message || '删除失败');
      }
    } catch (error) {
      console.error('删除失败:', error);
      ElMessage.error('删除失败，请稍后重试');
    }
  }).catch(() => {
    // 取消删除
  });
};

// 处理添加包含项目
const handleAddIncludeProject = () => {
  // 打开项目选择对话框
  // 这里可以复用现有的配赠项目对话框，或者创建一个新的对话框
  giftProjectForm.rechargeId = formData.id;
  giftProjectDialogTitle.value = '添加包含项目';
  // 重置表单
  Object.assign(giftProjectForm, {
    id: '',
    projectId: '',
    times: 1,
    unitPrice: 0,
    consume: 0,
    manualSalary: 0
  });
  giftProjectDialogVisible.value = true;
};

// 处理添加包含产品
const handleAddIncludeProduct = () => {
  // 打开产品选择对话框
  // 这里可以复用现有的配赠产品对话框，或者创建一个新的对话框
  giftProductForm.rechargeId = formData.id;
  giftProductDialogTitle.value = '添加包含产品';
  // 重置表单
  Object.assign(giftProductForm, {
    id: '',
    productId: '',
    times: 1,
    unitPrice: 0,
    consume: 0,
    manualSalary: 0
  });
  giftProductDialogVisible.value = true;
};

// 处理删除包含项目
const handleDeleteIncludeProject = (index: number) => {
  formData.giftProjects.splice(index, 1);
};

// 处理删除包含产品
const handleDeleteIncludeProduct = (index: number) => {
  formData.giftProducts.splice(index, 1);
};

// 批量选择相关方法
const handleProjectSelectionChange = (val: any[]) => {
  selectedProjectRows.value = val;
};

const handleProductSelectionChange = (val: any[]) => {
  selectedProductRows.value = val;
};

// 批量删除包含项目
const handleBatchDeleteIncludeProject = () => {
  if (selectedProjectRows.value.length === 0) return;
  
  ElMessageBox.confirm('确定要批量删除选中的项目吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    // 获取选中行的索引
    const indexes = selectedProjectRows.value.map(row => {
      return formData.giftProjects.findIndex(item => item === row);
    }).filter(index => index !== -1).sort((a, b) => b - a);
    
    // 从后往前删除，避免索引变化
    indexes.forEach(index => {
      formData.giftProjects.splice(index, 1);
    });
    
    // 清空选择
    selectedProjectRows.value = [];
    ElMessage.success('批量删除成功');
  }).catch(() => {
    // 取消删除
  });
};

// 批量删除包含产品
const handleBatchDeleteIncludeProduct = () => {
  if (selectedProductRows.value.length === 0) return;
  
  ElMessageBox.confirm('确定要批量删除选中的产品吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    // 获取选中行的索引
    const indexes = selectedProductRows.value.map(row => {
      return formData.giftProducts.findIndex(item => item === row);
    }).filter(index => index !== -1).sort((a, b) => b - a);
    
    // 从后往前删除，避免索引变化
    indexes.forEach(index => {
      formData.giftProducts.splice(index, 1);
    });
    
    // 清空选择
    selectedProductRows.value = [];
    ElMessage.success('批量删除成功');
  }).catch(() => {
    // 取消删除
  });
};

const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  fetchRechargeCardList();
};

const handleCurrentChange = (current: number) => {
  pagination.currentPage = current;
  fetchRechargeCardList();
};

// API调用方法
const fetchRechargeCardList = async () => {
  loading.value = true;
  try {
    // 调用API获取充值卡列表
    const params = {
      page: pagination.currentPage,
      pageSize: pagination.pageSize
    };
    const response = await getRechargeCardList(params);
    if (response.code === 200) {
      // 转换status字段为布尔类型，确保与el-switch组件兼容
      rechargeCardList.value = response.data.map((item: any) => ({
        ...item,
        status: item.status === 1
      }));
      pagination.total = response.total || 0;
    } else {
      ElMessage.error(response.message || '获取充值卡列表失败');
    }
  } catch (error) {
    console.error('获取充值卡列表失败:', error);
    ElMessage.error('获取充值卡列表失败');
  } finally {
    loading.value = false;
  }
};

const fetchGiftProjects = async (rechargeId: string) => {
  try {
    // 调用API获取配赠项目列表
    const response = await getGiftProjects(rechargeId);
    if (response.code === 200) {
      giftProjects.value = response.data;
    } else {
      ElMessage.error(response.message || '获取配赠项目列表失败');
    }
  } catch (error) {
    console.error('获取配赠项目列表失败:', error);
    ElMessage.error('获取配赠项目列表失败');
  }
};

const fetchGiftProducts = async (rechargeId: string) => {
  try {
    // 调用API获取配赠产品列表
    const response = await getGiftProducts(rechargeId);
    if (response.code === 200) {
      giftProducts.value = response.data;
    } else {
      ElMessage.error(response.message || '获取配赠产品列表失败');
    }
  } catch (error) {
    console.error('获取配赠产品列表失败:', error);
    ElMessage.error('获取配赠产品列表失败');
  }
};

const getDepartments = async () => {
  // 检查缓存是否有效
  if (!dataCacheStore.isDepartmentsExpired && dataCacheStore.cachedDepartments.length > 0) {
    // 从缓存中获取部门数据，并只保留核心业务部门（enable_category为true）
    const uniqueDepts = dataCacheStore.cachedDepartments
      .filter(dept => dept.enable_category)
      .map(dept => ({
        id: dept.id,
        name: dept.name
      }));
    departmentList.value = uniqueDepts;
    console.log('使用缓存的部门数据:', departmentList.value);
    return;
  }
  
  try {
    // 调用API获取部门列表，通过companyId筛选
    const response = await http.get('/api/enterprise/department', {
      params: {
        company_id: companyId.value
      }
    });
    console.log('API返回的部门数据:', response.data);
    
    // 检查返回数据结构
    let processedDepartments = [];
    if (Array.isArray(response.data)) {
      // 直接返回了数组
      // 去重处理并只保留核心业务部门（enable_category为true）
      const uniqueDepts = [];
      const deptIds = new Set();
      response.data.forEach((dept: any) => {
        if (!deptIds.has(dept.id)) {
          deptIds.add(dept.id);
          uniqueDepts.push({
            id: dept.id,
            name: dept.deptName,
            enable_category: dept.enable_category
          });
        }
      });
      processedDepartments = uniqueDepts;
    } else if (response.data.code === 200) {
      // 返回了标准格式 {code: 200, data: [...]}
      // 去重处理并只保留核心业务部门（enable_category为true）
      const uniqueDepts = [];
      const deptIds = new Set();
      response.data.data.forEach((dept: any) => {
        if (!deptIds.has(dept.id)) {
          deptIds.add(dept.id);
          uniqueDepts.push({
            id: dept.id,
            name: dept.deptName,
            enable_category: dept.enable_category
          });
        }
      });
      processedDepartments = uniqueDepts;
    } else {
      ElMessage.error('获取部门列表失败');
      return;
    }
    
    // 更新缓存
    dataCacheStore.updateDepartments(processedDepartments);
    
    // 只保留核心业务部门（enable_category为true）
    const coreDepartments = processedDepartments
      .filter(dept => dept.enable_category)
      .map(dept => ({
        id: dept.id,
        name: dept.name
      }));
    departmentList.value = coreDepartments;
    console.log('处理后部门数据:', departmentList.value);
  } catch (error) {
    console.error('获取部门列表失败:', error);
    ElMessage.error('获取部门列表失败');
  }
};

const getStores = async () => {
  // 检查缓存是否有效
  if (!dataCacheStore.isStoresExpired && dataCacheStore.cachedStores.length > 0) {
    storeList.value = dataCacheStore.cachedStores;
    console.log('使用缓存的门店数据:', storeList.value);
    return;
  }
  
  try {
    // 调用API获取分店列表，通过companyId筛选
    const response = await http.get('/api/enterprise/store', {
      params: {
        company_id: companyId.value
      }
    });
    console.log('API返回的门店数据:', response.data);
    
    // 检查返回数据结构
    let processedStores = [];
    if (Array.isArray(response.data)) {
      // 直接返回了数组
      processedStores = response.data.map((store: any) => ({
        id: store.id,
        name: store.storeName
      }));
    } else if (response.data.code === 200) {
      // 返回了标准格式 {code: 200, data: [...]}
      processedStores = response.data.data.map((store: any) => ({
        id: store.id,
        name: store.storeName
      }));
    } else {
      ElMessage.error('获取分店列表失败');
      return;
    }
    
    // 更新缓存
    dataCacheStore.updateStores(processedStores);
    storeList.value = processedStores;
    console.log('处理后门店数据:', storeList.value);
  } catch (error) {
    console.error('获取分店列表失败:', error);
    ElMessage.error('获取分店列表失败');
  }
};

const getProjects = async () => {
  try {
    // 调用API获取项目列表
    const response = await http.get('/api/card-item/get-projects');
    console.log('API返回的项目数据:', response);
    
    // 检查返回数据结构
    if (response.code === 200) {
      projectList.value = response.data;
      console.log('处理后项目数据:', projectList.value);
    } else {
      ElMessage.error(response.message || '获取项目列表失败');
    }
  } catch (error) {
    console.error('获取项目列表失败:', error);
    ElMessage.error('获取项目列表失败');
  }
};

const getProducts = async () => {
  try {
    // 调用API获取产品列表
    const response = await http.get('/api/card-item/get-products');
    console.log('API返回的产品数据:', response);
    
    // 检查返回数据结构
    if (response.code === 200) {
      productList.value = response.data;
      console.log('处理后产品数据:', productList.value);
    } else {
      ElMessage.error(response.message || '获取产品列表失败');
    }
  } catch (error) {
    console.error('获取产品列表失败:', error);
    ElMessage.error('获取产品列表失败');
  }
};

// 套餐卡相关方法
const handlePackageSubmit = async () => {
  if (!packageFormRef.value) return;
  
  // 如果禁止修改，则不执行提交操作
  if (isPackageModificationDisabled.value) {
    ElMessage.warning('该套餐卡已设置为禁止修改');
    return;
  }
  
  try {
    await packageFormRef.value.validate();
    
    // 准备提交数据
    const submitData = {
      ...packageFormData,
      // 将featureOptions转换为对应的布尔字段
      isModifiable: packageFormData.featureOptions.includes('1') ? 1 : 0,
      isAddItemDisabled: packageFormData.featureOptions.includes('2') ? 1 : 0,
      isLimitOnce: packageFormData.featureOptions.includes('3') ? 1 : 0,
      isExpireInvalid: packageFormData.featureOptions.includes('4') ? 1 : 0,
      isProhibitGift: packageFormData.featureOptions.includes('5') ? 1 : 0
    };
    
    // 根据是否有ID判断是新增还是更新
    if (packageFormData.id) {
      // 更新套餐卡
      const response = await updatePackageCard(packageFormData.id, submitData);
      if (response.code === 200) {
        ElMessage.success('更新成功');
      } else {
        ElMessage.error(response.message || '更新失败');
        return;
      }
    } else {
      // 新增套餐卡
      const response = await addPackageCard(submitData);
      if (response.code === 200) {
        ElMessage.success('新增成功');
      } else {
        ElMessage.error(response.message || '新增失败');
        return;
      }
    }
    
    packageDialogVisible.value = false;
    // 重新加载列表
    getPackageList();
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

// 处理添加包含项目
const handleAddPackageIncludeProject = async () => {
  // 检查项目列表是否有数据，如果没有则获取
  if (projectList.value.length === 0) {
    await getProjects();
  }
  // 重置选择
  rightProjectList.value = [];
  projectTransferSearch.value = '';
  projectTransferVisible.value = true;
};

// 处理添加包含产品
const handleAddPackageIncludeProduct = async () => {
  // 检查产品列表是否有数据，如果没有则获取
  if (productList.value.length === 0) {
    await getProducts();
  }
  // 重置选择
  rightProductList.value = [];
  productTransferSearch.value = '';
  productTransferVisible.value = true;
};

// 处理删除包含项目
const handleDeletePackageIncludeProject = (index: number) => {
  packageFormData.giftProjects.splice(index, 1);
};

// 处理删除包含产品
const handleDeletePackageIncludeProduct = (index: number) => {
  packageFormData.giftProducts.splice(index, 1);
};

// 批量删除包含项目
const handleBatchDeletePackageIncludeProject = () => {
  if (selectedPackageProjectRows.value.length === 0) return;
  
  ElMessageBox.confirm('确定要批量删除选中的项目吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    // 获取选中行的索引
    const indexes = selectedPackageProjectRows.value.map(row => {
      return packageFormData.giftProjects.findIndex(item => item === row);
    }).filter(index => index !== -1).sort((a, b) => b - a);
    
    // 从后往前删除，避免索引变化
    indexes.forEach(index => {
      packageFormData.giftProjects.splice(index, 1);
    });
    
    // 清空选择
    selectedPackageProjectRows.value = [];
    ElMessage.success('批量删除成功');
  }).catch(() => {
    // 取消删除
  });
};

// 批量删除包含产品
const handleBatchDeletePackageIncludeProduct = () => {
  if (selectedPackageProductRows.value.length === 0) return;
  
  ElMessageBox.confirm('确定要批量删除选中的产品吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    // 获取选中行的索引
    const indexes = selectedPackageProductRows.value.map(row => {
      return packageFormData.giftProducts.findIndex(item => item === row);
    }).filter(index => index !== -1).sort((a, b) => b - a);
    
    // 从后往前删除，避免索引变化
    indexes.forEach(index => {
      packageFormData.giftProducts.splice(index, 1);
    });
    
    // 清空选择
    selectedPackageProductRows.value = [];
    ElMessage.success('批量删除成功');
  }).catch(() => {
    // 取消删除
  });
};

// 处理项目选择变化
const handlePackageProjectChange = (projectId: string) => {
  // 查找选中的项目
  const selectedProject = projectList.value.find(p => p.id === projectId);
  if (selectedProject) {
    // 设置单价为项目的单次售价
    packageGiftProjectForm.unitPrice = selectedProject.singleSalePrice || 0;
    // 计算总价
    calculatePackageProjectTotalPrice();
  }
};

// 计算套餐卡项目总价
const calculatePackageProjectTotalPrice = () => {
  const { times, unitPrice } = packageGiftProjectForm;
  packageGiftProjectForm.totalPrice = times * unitPrice;
};

// 根据总价反算单价
const calculatePackageProjectUnitPrice = () => {
  const { times, totalPrice } = packageGiftProjectForm;
  if (times > 0) {
    packageGiftProjectForm.unitPrice = totalPrice / times;
  }
};

// 更新包含项目表格中项目的总价
const updatePackageProjectTotalPrice = (index: number) => {
  const project = packageFormData.giftProjects[index];
  if (project) {
    project.totalPrice = project.times * project.unitPrice;
  }
};

// 更新包含项目表格中项目的单价
const updatePackageProjectUnitPrice = (index: number) => {
  const project = packageFormData.giftProjects[index];
  if (project && project.times > 0) {
    project.unitPrice = project.totalPrice / project.times;
  }
};

// 处理提交套餐卡配赠项目
const handleSubmitPackageGiftProject = async () => {
  if (!packageGiftProjectFormRef.value) return;
  
  try {
    await packageGiftProjectFormRef.value.validate();
    
    // 查找选中的项目名称
    const selectedProject = projectList.value.find(p => p.id === packageGiftProjectForm.projectId);
    
    // 添加到表单的包含项目中
    packageFormData.giftProjects.push({
      id: packageGiftProjectForm.id,
      projectId: packageGiftProjectForm.projectId,
      projectName: selectedProject ? selectedProject.projectName : '',
      times: packageGiftProjectForm.times,
      unitPrice: packageGiftProjectForm.unitPrice,
      totalPrice: packageGiftProjectForm.totalPrice,
      consume: packageGiftProjectForm.consume,
      manualSalary: packageGiftProjectForm.manualSalary
    });
    
    packageGiftProjectDialogVisible.value = false;
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

// 处理提交套餐卡配赠产品
const handleSubmitPackageGiftProduct = async () => {
  if (!packageGiftProductFormRef.value) return;
  
  try {
    await packageGiftProductFormRef.value.validate();
    
    // 查找选中的产品名称
    const selectedProduct = productList.value.find(p => p.id === packageGiftProductForm.productId);
    
    // 添加到表单的包含产品中
    packageFormData.giftProducts.push({
      id: packageGiftProductForm.id,
      productId: packageGiftProductForm.productId,
      productName: selectedProduct ? selectedProduct.productName : '',
      times: packageGiftProductForm.times,
      unitPrice: packageGiftProductForm.unitPrice,
      totalPrice: packageGiftProductForm.times * packageGiftProductForm.unitPrice,
      manualSalary: packageGiftProductForm.manualSalary
    });
    
    packageGiftProductDialogVisible.value = false;
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

// 批量选择相关方法
const handlePackageProjectSelectionChange = (val: any[]) => {
  selectedPackageProjectRows.value = val;
};

const handlePackageProductSelectionChange = (val: any[]) => {
  selectedPackageProductRows.value = val;
};

// 处理项目选择确认
const handleProjectTransferConfirm = () => {
  // 获取选中的项目
  const selectedProjects = projectList.value.filter(project => 
    rightProjectList.value.includes(project.id)
  );
  
  // 添加到套餐卡包含项目中
  selectedProjects.forEach(project => {
    packageFormData.giftProjects.push({
      id: '',
      projectId: project.id,
      projectName: project.projectName,
      times: 1,
      unitPrice: project.singleSalePrice || 0,
      totalPrice: project.singleSalePrice || 0,
      consume: 0,
      manualSalary: 0
    });
    // 累加原价
    packageFormData.originalPrice += (project.originalPrice || 0);
  });
  
  projectTransferVisible.value = false;
};

// 处理产品选择确认
const handleProductTransferConfirm = () => {
  // 获取选中的产品
  const selectedProducts = productList.value.filter(product => 
    rightProductList.value.includes(product.id)
  );
  
  // 添加到套餐卡包含产品中
  selectedProducts.forEach(product => {
    packageFormData.giftProducts.push({
      id: '',
      productId: product.id,
      productName: product.productName,
      times: 1,
      unitPrice: product.salePrice || 0,
      totalPrice: product.salePrice || 0,
      manualSalary: 0
    });
    // 累加原价
    packageFormData.originalPrice += (product.originalPrice || 0);
  });
  
  productTransferVisible.value = false;
};
</script>

<style scoped>
.card-container {
  min-height: calc(100vh - 120px);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
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
