<!--
  充值卡管理组件
  
  功能说明：
  管理充值卡信息，包括：
  1. 充值卡基本信息 - 卡名称、充值金额、赠送金额、折扣率、耗卡率
  2. 时间与限定设置 - 上下线时间、过期类型、限定分店/部门
  3. 包含项目 - 配赠的服务项目及次数、价格
  4. 包含产品 - 配赠的产品及数量、价格
  
  页面结构：
  - 数据表格：展示充值卡列表，支持状态切换
  - 新增/编辑对话框：多标签页表单（基本信息、时间与限定设置、包含项目、包含产品）
  
  权限控制：
  - card:recharge:view - 查看充值卡
  - card:recharge:add - 新增充值卡
  - card:recharge:edit - 编辑充值卡
  - card:recharge:delete - 删除充值卡
  
  数据表：
  - card_recharge - 充值卡表
  - card_recharge_project - 充值卡配赠项目表
  - card_recharge_product - 充值卡配赠产品表
  
  Props：
  - storeList - 分店列表
  - departmentList - 部门列表
  - projectList - 项目列表
  - productList - 产品列表
-->
<template>
  <div class="recharge-card">
    <!-- 标题和操作按钮区域 -->
    <div class="mb-4 flex justify-between items-center">
      <span class="text-lg font-medium">充值卡管理</span>
      <!-- 新增充值卡按钮 -->
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        新增充值卡
      </el-button>
    </div>
    <!-- 充值卡数据表格 -->
    <div class="flex-1 min-h-0">
      <el-table v-loading="loading" :data="list" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
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
      <!-- 分页组件 -->
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

    <!-- 新增/编辑充值卡对话框 -->
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
          <!-- 基本信息标签页 -->
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
          
          <!-- 时间与限定设置标签页 -->
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
          
          <!-- 包含项目标签页 -->
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
          
          <!-- 包含产品标签页 -->
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

    <!-- 配赠管理对话框 -->
    <el-dialog
      v-model="giftDialogVisible"
      title="配赠管理"
      width="900px"
      destroy-on-close
    >
      <div class="gift-management">
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

    <!-- 添加/编辑配赠项目对话框 -->
    <el-dialog
      v-model="giftProjectDialogVisible"
      :title="giftProjectDialogTitle"
      width="900px"
      destroy-on-close
    >
      <div class="mb-4">
        <el-input
          v-model="projectSearchKeyword"
          placeholder="请输入项目名称搜索"
          prefix-icon="Search"
          clearable
        />
      </div>
      <div class="space-y-2 max-h-96 overflow-y-auto">
        <div 
          v-for="project in filteredProjectList" 
          :key="project.id" 
          class="project-card p-3 border rounded bg-white shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          :class="{ 'border-blue-500 bg-blue-50': giftProjectForm.projectId === project.id }"
          @click="selectProject(project)"
        >
          <div class="flex items-center mb-1">
            <div class="font-medium text-sm">{{ project.projectName }}</div>
            <el-tag v-if="project.supplierName" size="small" class="ml-2">{{ project.supplierName }}</el-tag>
          </div>
          <div class="flex flex-wrap gap-x-5 gap-y-0.5 text-xs text-gray-500">
            <div>原价: {{ project.originalPrice || 0 }}元</div>
            <div>售卖: {{ project.singleSalePrice || 0 }}元</div>
            <div>服务时长: {{ project.serviceTime || 0 }}分钟</div>
            <div class="w-full mt-0.5 truncate" :title="project.remark || ''">备注: {{ project.remark || '' }}</div>
          </div>
        </div>
        <div v-if="filteredProjectList.length === 0" class="text-center text-gray-400 py-8">
          暂无匹配的项目
        </div>
      </div>
      
      <el-divider content-position="left" v-if="giftProjectForm.projectId">项目设置</el-divider>
      
      <el-form
        v-if="giftProjectForm.projectId"
        ref="giftProjectFormRef"
        :model="giftProjectForm"
        :rules="giftProjectRules"
        label-width="100px"
        class="mt-4"
      >
        <div class="grid grid-cols-3 gap-4">
          <el-form-item label="次数" prop="times">
            <el-input-number v-model="giftProjectForm.times" :min="1" :step="1" @change="calculateTotalPrice" style="width: 100%" />
          </el-form-item>
          <el-form-item label="单价" prop="unitPrice">
            <el-input-number v-model="giftProjectForm.unitPrice" :min="0" :step="0.01" :precision="2" @change="calculateTotalPrice" style="width: 100%" />
          </el-form-item>
          <el-form-item label="总价" prop="totalPrice">
            <el-input-number v-model="giftProjectForm.totalPrice" :min="0" :step="0.01" :precision="2" @change="calculateUnitPrice" style="width: 100%" />
          </el-form-item>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <el-form-item label="耗卡" prop="consume">
            <el-input-number v-model="giftProjectForm.consume" :min="0" :step="1" style="width: 100%" />
          </el-form-item>
          <el-form-item label="手工工资" prop="manualSalary">
            <el-input-number v-model="giftProjectForm.manualSalary" :min="0" :step="0.01" :precision="2" style="width: 100%" />
          </el-form-item>
        </div>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="giftProjectDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitGiftProject" :disabled="!giftProjectForm.projectId">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 添加/编辑配赠产品对话框 -->
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
  </div>
</template>

<script setup lang="ts">
/**
 * 充值卡管理组件脚本
 * 
 * 主要功能：
 * 1. 充值卡列表展示 - 支持分页、状态切换
 * 2. 充值卡CRUD操作 - 新增、编辑、删除
 * 3. 配赠项目管理 - 添加、删除配赠项目
 * 4. 配赠产品管理 - 添加、删除配赠产品
 */
import { ref, reactive, computed, onMounted } from "vue";
import { Plus, Edit, Delete, Close, Check } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import {
  getRechargeCardList,
  getRechargeCardDetail,
  addRechargeCard,
  updateRechargeCard,
  deleteRechargeCard,
  getGiftProjects,
  getGiftProducts,
  deleteGiftProject,
  deleteGiftProduct
} from '@/api/recharge';

/** 组件Props接口定义 */
interface Props {
  storeList: any[];
  departmentList: any[];
  projectList: any[];
  productList: any[];
}

const props = defineProps<Props>();

// ==================== 状态定义 ====================

/** 加载状态 */
const loading = ref(false);

/** 主对话框可见性 */
const dialogVisible = ref(false);

/** 配赠管理对话框可见性 */
const giftDialogVisible = ref(false);

/** 配赠项目对话框可见性 */
const giftProjectDialogVisible = ref(false);

/** 配赠产品对话框可见性 */
const giftProductDialogVisible = ref(false);

/** 主对话框标题 */
const dialogTitle = ref('新增充值卡');

/** 配赠项目对话框标题 */
const giftProjectDialogTitle = ref('新增配赠项目');

/** 配赠产品对话框标题 */
const giftProductDialogTitle = ref('新增配赠产品');

/** 当前激活的表单标签页 */
const activeFormTab = ref("basic");

/** 主表单引用 */
const formRef = ref<any>(null);

/** 项目搜索关键词 */
const projectSearchKeyword = ref('');

/** 配赠项目表单引用 */
const giftProjectFormRef = ref<any>(null);

/** 配赠产品表单引用 */
const giftProductFormRef = ref<any>(null);

/** 选中的项目行 */
const selectedProjectRows = ref<any[]>([]);

/** 选中的产品行 */
const selectedProductRows = ref<any[]>([]);

/** 配赠项目列表 */
const giftProjects = ref<any[]>([]);

/** 配赠产品列表 */
const giftProducts = ref<any[]>([]);

/** 过期类型映射表 */
const expireTypeMap: Record<string | number, string> = {
  '1': '开卡计算过期',
  '2': '消耗计算过期',
  '3': '固定日期过期',
  1: '开卡计算过期',
  2: '消耗计算过期',
  3: '固定日期过期'
};

/** 对话框宽度 - 根据屏幕宽度自适应 */
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

/** 分页信息 */
const pagination = reactive({
  currentPage: 1,
  pageSize: 10,
  total: 0
});

/** 充值卡列表数据 */
const list = ref<any[]>([]);

/** 充值卡表单数据 */
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
  saleStoreIds: [] as any[],
  consumeStoreIds: [] as any[],
  saleDepartmentIds: [] as any[],
  consumeDepartmentIds: [] as any[],
  giftProjects: [] as any[],
  giftProducts: [] as any[],
  description: '',
  remark: '',
  featureOptions: [] as string[]
});

/** 配赠项目表单数据 */
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

/** 配赠产品表单数据 */
const giftProductForm = reactive({
  id: '',
  rechargeId: '',
  productId: '',
  times: 1,
  unitPrice: 0,
  consume: 0,
  manualSalary: 0
});

/** 是否禁止修改 - 根据功能选项判断 */
const isModificationDisabled = computed(() => {
  return formData.featureOptions.includes('1');
});

/** 过滤后的项目列表 - 根据搜索关键词过滤 */
const filteredProjectList = computed(() => {
  if (!projectSearchKeyword.value) {
    return props.projectList;
  }
  const keyword = projectSearchKeyword.value.toLowerCase();
  return props.projectList.filter((project: any) => 
    project.projectName?.toLowerCase().includes(keyword) ||
    project.supplierName?.toLowerCase().includes(keyword)
  );
});

// ==================== 表单验证规则 ====================

/** 主表单验证规则 */
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

/** 配赠项目表单验证规则 */
const giftProjectRules = reactive({
  projectId: [{ required: true, message: '请选择项目', trigger: 'change' }],
  times: [{ required: true, message: '请输入次数', trigger: 'blur' }]
});

/** 配赠产品表单验证规则 */
const giftProductRules = reactive({
  productId: [{ required: true, message: '请选择产品', trigger: 'change' }],
  times: [{ required: true, message: '请输入次数', trigger: 'blur' }]
});

// ==================== 生命周期 ====================

/** 组件挂载时加载列表数据 */
onMounted(() => {
  getList();
});

// ==================== 数据获取方法 ====================

/**
 * 获取充值卡列表
 * 支持分页
 */
const getList = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.currentPage,
      pageSize: pagination.pageSize
    };
    const response = await getRechargeCardList(params);
    if (response.code === 200) {
      list.value = response.data.map((item: any) => ({
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
  pagination.currentPage = current;
  getList();
};

// ==================== 新增/编辑方法 ====================

/** 打开新增充值卡对话框 */
const handleAdd = () => {
  dialogTitle.value = '新增充值卡';
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
    saleStoreIds: props.storeList.map(store => store.id),
    consumeStoreIds: props.storeList.map(store => store.id),
    saleDepartmentIds: props.departmentList.map(dept => dept.id),
    consumeDepartmentIds: props.departmentList.map(dept => dept.id),
    giftProjects: [],
    giftProducts: [],
    description: '',
    remark: '',
    featureOptions: ['3', '4']
  });
  activeFormTab.value = 'basic';
  dialogVisible.value = true;
};

/**
 * 打开编辑充值卡对话框
 * 加载充值卡详情数据
 * @param row - 充值卡数据行
 */
const handleEdit = async (row: any) => {
  dialogTitle.value = '编辑充值卡';
  
  try {
    const response = await getRechargeCardDetail(row.id);
    if (response.code === 200) {
      const cardDetail = response.data;
      
      const featureOptions = [];
      if (cardDetail.isModifiable) featureOptions.push('1');
      if (cardDetail.isLimitOnce) featureOptions.push('2');
      if (cardDetail.isExpireInvalid) featureOptions.push('3');
      if (cardDetail.isProjectExpire) featureOptions.push('4');
      if (cardDetail.isProhibitDiscountModify) featureOptions.push('5');
      
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
        expireMonths: cardDetail.expireMonths || 12,
        expireType: String(cardDetail.expireType),
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
      
      activeFormTab.value = 'basic';
      dialogVisible.value = true;
    } else {
      ElMessage.error(response.message || '获取充值卡详情失败');
    }
  } catch (error) {
    console.error('获取充值卡详情失败:', error);
    ElMessage.error('获取充值卡详情失败，请稍后重试');
  }
};

/**
 * 删除充值卡
 * 弹出确认框，确认后调用API删除
 * @param id - 充值卡ID
 */
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
        getList();
      } else {
        ElMessage.error(response.message || '删除失败');
      }
    } catch (error) {
      console.error('删除失败:', error);
      ElMessage.error('删除失败，请稍后重试');
    }
  }).catch(() => {});
};

const handleSubmit = async () => {
  if (!formRef.value) return;
  
  if (isModificationDisabled.value) {
    ElMessage.warning('该充值卡已设置为禁止修改');
    return;
  }
  
  try {
    await formRef.value.validate();
    
    const submitData = {
      ...formData,
      isModifiable: formData.featureOptions.includes('1') ? 1 : 0,
      isLimitOnce: formData.featureOptions.includes('2') ? 1 : 0,
      isExpireInvalid: formData.featureOptions.includes('3') ? 1 : 0,
      isProjectExpire: formData.featureOptions.includes('4') ? 1 : 0,
      isProhibitDiscountModify: formData.featureOptions.includes('5') ? 1 : 0
    };
    
    if (formData.id) {
      const response = await updateRechargeCard(formData.id, submitData);
      if (response.code === 200) {
        ElMessage.success('更新成功');
      } else {
        ElMessage.error(response.message || '更新失败');
        return;
      }
    } else {
      const response = await addRechargeCard(submitData);
      if (response.code === 200) {
        ElMessage.success('新增成功');
      } else {
        ElMessage.error(response.message || '新增失败');
        return;
      }
    }
    
    dialogVisible.value = false;
    getList();
  } catch (error) {
    console.error('操作失败:', error);
    ElMessage.error('操作失败，请稍后重试');
  }
};

const handleStatusChange = async (row: any, newValue?: boolean) => {
  if (newValue === undefined) return;

  try {
    const response = await updateRechargeCard(row.id, {
      status: newValue ? 1 : 0
    });
    if (response.code !== 200) {
      row.status = !newValue;
      ElMessage.error('状态更新失败');
    }
  } catch (error) {
    console.error('状态更新失败:', error);
    row.status = !newValue;
    ElMessage.error('状态更新失败');
  }
};

const handleGiftManage = (row: any) => {
  giftProjectForm.rechargeId = row.id;
  giftProductForm.rechargeId = row.id;
  giftDialogVisible.value = true;
  fetchGiftProjects(row.id);
  fetchGiftProducts(row.id);
};

const handleAddGiftProject = () => {
  giftProjectDialogTitle.value = '新增配赠项目';
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
    
    const selectedProject = props.projectList.find(p => p.id === giftProjectForm.projectId);
    
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
        fetchGiftProjects(giftProjectForm.rechargeId);
      } else {
        ElMessage.error(response.data.message || '删除失败');
      }
    } catch (error) {
      console.error('删除失败:', error);
      ElMessage.error('删除失败，请稍后重试');
    }
  }).catch(() => {});
};

const handleAddGiftProduct = () => {
  giftProductDialogTitle.value = '新增配赠产品';
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

const selectProject = (project: any) => {
  giftProjectForm.projectId = project.id;
  giftProjectForm.unitPrice = project.singleSalePrice || 0;
  calculateTotalPrice();
};

const calculateTotalPrice = () => {
  const { times, unitPrice } = giftProjectForm;
  giftProjectForm.totalPrice = times * unitPrice;
};

const calculateUnitPrice = () => {
  const { times, totalPrice } = giftProjectForm;
  if (times > 0) {
    giftProjectForm.unitPrice = totalPrice / times;
  }
};

const updateProjectTotalPrice = (index: number) => {
  const project = formData.giftProjects[index];
  if (project) {
    project.totalPrice = project.times * project.unitPrice;
  }
};

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
    
    const selectedProduct = props.productList.find(p => p.id === giftProductForm.productId);
    
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
        fetchGiftProducts(giftProductForm.rechargeId);
      } else {
        ElMessage.error(response.data.message || '删除失败');
      }
    } catch (error) {
      console.error('删除失败:', error);
      ElMessage.error('删除失败，请稍后重试');
    }
  }).catch(() => {});
};

const handleAddIncludeProject = () => {
  giftProjectForm.rechargeId = formData.id;
  giftProjectDialogTitle.value = '添加包含项目';
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

const handleAddIncludeProduct = () => {
  giftProductForm.rechargeId = formData.id;
  giftProductDialogTitle.value = '添加包含产品';
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

const handleDeleteIncludeProject = (index: number) => {
  formData.giftProjects.splice(index, 1);
};

const handleDeleteIncludeProduct = (index: number) => {
  formData.giftProducts.splice(index, 1);
};

const handleProjectSelectionChange = (val: any[]) => {
  selectedProjectRows.value = val;
};

const handleProductSelectionChange = (val: any[]) => {
  selectedProductRows.value = val;
};

const handleBatchDeleteIncludeProject = () => {
  if (selectedProjectRows.value.length === 0) return;
  
  ElMessageBox.confirm('确定要批量删除选中的项目吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    const indexes = selectedProjectRows.value.map(row => {
      return formData.giftProjects.findIndex(item => item === row);
    }).filter(index => index !== -1).sort((a, b) => b - a);
    
    indexes.forEach(index => {
      formData.giftProjects.splice(index, 1);
    });
    
    selectedProjectRows.value = [];
    ElMessage.success('批量删除成功');
  }).catch(() => {});
};

const handleBatchDeleteIncludeProduct = () => {
  if (selectedProductRows.value.length === 0) return;
  
  ElMessageBox.confirm('确定要批量删除选中的产品吗？', '警告', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    const indexes = selectedProductRows.value.map(row => {
      return formData.giftProducts.findIndex(item => item === row);
    }).filter(index => index !== -1).sort((a, b) => b - a);
    
    indexes.forEach(index => {
      formData.giftProducts.splice(index, 1);
    });
    
    selectedProductRows.value = [];
    ElMessage.success('批量删除成功');
  }).catch(() => {});
};

const fetchGiftProjects = async (rechargeId: string) => {
  try {
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
</script>

<style scoped>
.recharge-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}
</style>
