<!--
  套餐卡管理组件
  
  功能说明：
  管理套餐卡信息，包括：
  1. 套餐卡基本信息 - 卡名称、编码、原价、套餐价
  2. 时间与限定设置 - 上下线时间、过期类型、限定分店/部门
  3. 包含项目 - 套餐内包含的服务项目及次数、价格
  4. 包含产品 - 套餐内包含的产品及数量、价格
  
  页面结构：
  - 搜索栏：按卡名称搜索
  - 数据表格：展示套餐卡列表
  - 新增/编辑对话框：多标签页表单（基本信息、时间与限定设置、包含项目、包含产品）
  
  权限控制：
  - card:package:view - 查看套餐卡
  - card:package:add - 新增套餐卡
  - card:package:edit - 编辑套餐卡
  - card:package:delete - 删除套餐卡
  
  数据表：
  - card_package - 套餐卡表
  - card_package_project - 套餐卡项目关联表
  - card_package_product - 套餐卡产品关联表
  
  Props：
  - storeList - 分店列表
  - departmentList - 部门列表
  - projectList - 项目列表
  - productList - 产品列表
-->
<template>
  <div class="package-card">
    <!-- 搜索栏和操作按钮区域 -->
    <div class="mb-4 flex justify-between items-center">
      <!-- 套餐卡搜索表单 -->
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
      <!-- 新增套餐卡按钮 -->
      <el-button v-if="hasAuth('card:package:add')" type="primary" class="ml-4" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        新增套餐卡
      </el-button>
    </div>

    <!-- 套餐卡数据表格 -->
    <div class="flex-1 min-h-0">
      <el-table v-loading="loading" :data="list" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="cardName" label="卡名称" />
        <el-table-column prop="originalPrice" label="原价" width="100" />
        <el-table-column prop="price" label="售价" width="100" />
        <el-table-column prop="projectCount" label="包含项目数" width="100" />
        <el-table-column prop="expireType" label="过期类型" width="120">
          <template #default="scope">
            {{ expireTypeMap[scope.row.expireType] || scope.row.expireType }}
          </template>
        </el-table-column>
        <el-table-column prop="createTime" label="创建时间" width="180" />
        <el-table-column label="操作" width="180">
          <template #default="scope">
            <el-button v-if="hasAuth('card:package:edit')" type="primary" size="small" @click="handleEdit(scope.row)">
              <el-icon><Edit /></el-icon>
              编辑
            </el-button>
            <el-button v-if="hasAuth('card:package:delete')" type="danger" size="small" @click="handleDelete(scope.row.id)">
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

    <!-- 新增/编辑套餐卡对话框 -->
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
        <el-tabs v-model="activeTab" type="border-card">
          <!-- 基本信息标签页 -->
          <el-tab-pane label="基本信息" name="basic">
            <el-form :model="formData" label-width="120px">
              <el-form-item label="套餐卡名称" prop="cardName">
                <el-input v-model="formData.cardName" placeholder="请输入套餐卡名称" :disabled="isModificationDisabled" />
              </el-form-item>
              <el-form-item label="套餐卡编码" prop="cardCode">
                <el-input v-model="formData.cardCode" placeholder="请输入套餐卡编码" :disabled="isModificationDisabled" />
              </el-form-item>
              <div class="flex space-x-4">
                <el-form-item label="原价" prop="originalPrice" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.originalPrice" :min="0" :step="0.01" :precision="2" style="width: 100%" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
                <el-form-item label="套餐价" prop="price" style="width: 48%">
                  <div class="flex items-center">
                    <el-input-number v-model="formData.price" :min="0" :step="0.01" :precision="2" style="width: 100%" :disabled="isModificationDisabled" />
                    <span class="ml-2 text-gray-500">元</span>
                  </div>
                </el-form-item>
              </div>
              
              <el-divider content-position="left">其他设置</el-divider>
              
              <el-form-item label="套餐卡描述" prop="description">
                <el-input
                  v-model="formData.description"
                  type="textarea"
                  placeholder="请输入套餐卡描述"
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
                  <el-checkbox label="2">禁止添加新项</el-checkbox>
                  <el-checkbox label="3">限购1次</el-checkbox>
                  <el-checkbox label="4">过期作废</el-checkbox>
                  <el-checkbox label="5">禁止赠送</el-checkbox>
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

    <!-- 添加包含项目对话框 -->
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
          <el-input-number v-model="giftProjectForm.times" :min="1" :step="1" @change="calculateProjectTotalPrice" />
        </el-form-item>
        <el-form-item label="单价" prop="unitPrice">
          <el-input-number v-model="giftProjectForm.unitPrice" :min="0" :step="0.01" :precision="2" @change="calculateProjectTotalPrice" />
        </el-form-item>
        <el-form-item label="总价" prop="totalPrice">
          <el-input-number v-model="giftProjectForm.totalPrice" :min="0" :step="0.01" :precision="2" @change="calculateProjectUnitPrice" />
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

    <!-- 添加包含产品对话框 -->
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
        <el-form-item label="数量" prop="times">
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

    <!-- 选择项目对话框 -->
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
      <div class="space-y-2 max-h-96 overflow-y-auto">
        <div 
          v-for="project in filteredProjectList" 
          :key="project.id" 
          class="project-card p-2 border rounded bg-white shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-center mb-1">
            <input type="checkbox" :value="project.id" v-model="selectedProjectIds" class="mr-2" />
            <div class="font-medium text-sm ml-2">{{ project.projectName }}</div>
            <el-tag v-if="project.supplierName" size="small" class="ml-2">{{ project.supplierName }}</el-tag>
          </div>
          <div class="flex flex-wrap gap-x-5 gap-y-0.5 text-xs text-gray-500">
            <div class="ml-5">原价: {{ project.originalPrice || 0 }}元</div>
            <div>售卖: {{ project.singleSalePrice || 0 }}元</div>
            <div>服务时长: {{ project.serviceTime || 0 }}分钟</div>
            <div class="w-full mt-0.5 truncate ml-5" :title="project.remark || ''">备注: {{ project.remark || '' }}</div>
          </div>
        </div>
        <div v-if="filteredProjectList.length === 0" class="text-center text-gray-400 py-8">
          暂无匹配的项目
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

    <!-- 选择产品对话框 -->
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
      <div class="space-y-2 max-h-96 overflow-y-auto">
        <div 
          v-for="product in filteredProductList" 
          :key="product.id" 
          class="product-card p-2 border rounded bg-white shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-center mb-1">
            <input type="checkbox" :value="product.id" v-model="selectedProductIds" class="mr-2" />
            <div class="font-medium text-sm">{{ product.productName }}</div>
          </div>
          <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-gray-500">
            <div>原价: {{ product.originalPrice || 0 }}元</div>
            <div>售卖: {{ product.salePrice || 0 }}元</div>
            <div class="w-full mt-0.5 truncate" :title="product.remark || ''">备注: {{ product.remark || '' }}</div>
          </div>
        </div>
        <div v-if="filteredProductList.length === 0" class="text-center text-gray-400 py-8">
          暂无匹配的产品
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
/**
 * 套餐卡管理组件脚本
 * 
 * 主要功能：
 * 1. 套餐卡列表展示 - 支持搜索和分页
 * 2. 套餐卡CRUD操作 - 新增、编辑、删除
 * 3. 包含项目管理 - 添加、删除、批量删除项目
 * 4. 包含产品管理 - 添加、删除、批量删除产品
 */
import { ref, reactive, computed, onMounted } from "vue";
import { Plus, Edit, Delete, Search, Refresh, Close, Check } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import { hasAuth } from "@/router/utils";
import {
  getPackageCards,
  getPackageCardDetail,
  addPackageCard,
  updatePackageCard,
  deletePackageCard
} from '@/api/package';

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

/** 主对话框标题 */
const dialogTitle = ref('新增套餐卡');

/** 当前激活的表单标签页 */
const activeTab = ref('basic');

/** 主表单引用 */
const formRef = ref<any>(null);

/** 包含项目对话框可见性 */
const giftProjectDialogVisible = ref(false);

/** 包含产品对话框可见性 */
const giftProductDialogVisible = ref(false);

/** 包含项目对话框标题 */
const giftProjectDialogTitle = ref('添加包含项目');

/** 包含产品对话框标题 */
const giftProductDialogTitle = ref('添加包含产品');

/** 包含项目表单引用 */
const giftProjectFormRef = ref<any>(null);

/** 包含产品表单引用 */
const giftProductFormRef = ref<any>(null);

/** 选中的项目行 */
const selectedProjectRows = ref<any[]>([]);

/** 选中的产品行 */
const selectedProductRows = ref<any[]>([]);

/** 项目选择对话框可见性 */
const projectTransferVisible = ref(false);

/** 产品选择对话框可见性 */
const productTransferVisible = ref(false);

/** 项目选择搜索关键词 */
const projectTransferSearch = ref('');

/** 产品选择搜索关键词 */
const productTransferSearch = ref('');

/** 选中的项目ID列表 */
const selectedProjectIds = ref<any[]>([]);

/** 选中的产品ID列表 */
const selectedProductIds = ref<any[]>([]);

/** 过滤后的项目列表 - 根据搜索关键词过滤 */
const filteredProjectList = computed(() => {
  if (!projectTransferSearch.value) {
    return props.projectList;
  }
  const keyword = projectTransferSearch.value.toLowerCase();
  return props.projectList.filter((project: any) => 
    project.projectName?.toLowerCase().includes(keyword) ||
    project.supplierName?.toLowerCase().includes(keyword)
  );
});

/** 过滤后的产品列表 - 根据搜索关键词过滤 */
const filteredProductList = computed(() => {
  if (!productTransferSearch.value) {
    return props.productList;
  }
  const keyword = productTransferSearch.value.toLowerCase();
  return props.productList.filter((product: any) => 
    product.productName?.toLowerCase().includes(keyword)
  );
});

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

/** 搜索表单数据 */
const searchForm = reactive({
  cardName: ""
});

/** 分页信息 */
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

/** 套餐卡列表数据 */
const list = ref<any[]>([]);

/** 套餐卡表单数据 */
const formData = reactive({
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

/** 包含项目表单数据 */
const giftProjectForm = reactive({
  id: '',
  packageId: '',
  projectId: '',
  times: 1,
  unitPrice: 0,
  totalPrice: 0,
  consume: 0,
  manualSalary: 0
});

/** 包含产品表单数据 */
const giftProductForm = reactive({
  id: '',
  packageId: '',
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

// ==================== 表单验证规则 ====================

/** 主表单验证规则 */
const rules = reactive({
  cardName: [{ required: true, message: '请输入套餐卡名称', trigger: ['blur', 'change'] }],
  cardCode: [{ required: true, message: '请输入套餐卡编码', trigger: ['blur', 'change'] }],
  originalPrice: [{ required: true, message: '请输入原价', trigger: 'blur' }, { type: 'number', min: 0.01, message: '原价必须大于0', trigger: 'blur' }],
  price: [{ required: true, message: '请输入套餐价', trigger: 'blur' }, { type: 'number', min: 0.01, message: '套餐价必须大于0', trigger: 'blur' }],
  expireType: [{ required: true, message: '请选择过期类型', trigger: 'change' }]
});

/** 包含项目表单验证规则 */
const giftProjectRules = reactive({
  projectId: [{ required: true, message: '请选择项目', trigger: 'change' }],
  times: [{ required: true, message: '请输入次数', trigger: 'blur' }]
});

/** 包含产品表单验证规则 */
const giftProductRules = reactive({
  productId: [{ required: true, message: '请选择产品', trigger: 'change' }],
  times: [{ required: true, message: '请输入数量', trigger: 'blur' }]
});

// ==================== 生命周期 ====================

/** 组件挂载时加载列表数据 */
onMounted(() => {
  getList();
});

// ==================== 数据获取方法 ====================

/**
 * 获取套餐卡列表
 * 支持按卡名称搜索，支持分页
 */
const getList = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.current,
      pageSize: pagination.pageSize,
      cardName: searchForm.cardName
    };
    const response = await getPackageCards(params);
    if (response.code === 200) {
      list.value = response.data;
      pagination.total = response.total || 0;
    } else {
      ElMessage.error(response.message || '获取套餐卡列表失败');
    }
  } catch (error) {
    console.error('获取套餐卡列表失败:', error);
    ElMessage.error('获取套餐卡列表失败');
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
  searchForm.cardName = "";
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

// ==================== 新增/编辑方法 ====================

/** 打开新增套餐卡对话框 */
const handleAdd = () => {
  dialogTitle.value = '新增套餐卡';
  Object.assign(formData, {
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
    saleStoreIds: props.storeList.map(store => store.id),
    consumeStoreIds: props.storeList.map(store => store.id),
    saleDepartmentIds: props.departmentList.map(dept => dept.id),
    consumeDepartmentIds: props.departmentList.map(dept => dept.id),
    giftProjects: [],
    giftProducts: [],
    description: '',
    remark: '',
    featureOptions: ['3']
  });
  activeTab.value = 'basic';
  dialogVisible.value = true;
};

/**
 * 打开编辑套餐卡对话框
 * 加载套餐卡详情数据
 * @param row - 套餐卡数据行
 */
const handleEdit = async (row: any) => {
  dialogTitle.value = '编辑套餐卡';
  
  try {
    const response = await getPackageCardDetail(row.id);
    if (response.code === 200) {
      const cardDetail = response.data;
      
      const featureOptions = [];
      if (cardDetail.isModifiable) featureOptions.push('1');
      if (cardDetail.isAddNewItemForbidden) featureOptions.push('2');
      if (cardDetail.isLimitOnce) featureOptions.push('3');
      if (cardDetail.isExpireInvalid) featureOptions.push('4');
      if (cardDetail.isGiftForbidden) featureOptions.push('5');
      
      Object.assign(formData, {
        id: cardDetail.id,
        cardName: cardDetail.cardName,
        cardCode: cardDetail.cardCode,
        originalPrice: cardDetail.originalPrice,
        price: cardDetail.price,
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
      
      activeTab.value = 'basic';
      dialogVisible.value = true;
    } else {
      ElMessage.error(response.message || '获取套餐卡详情失败');
    }
  } catch (error) {
    console.error('获取套餐卡详情失败:', error);
    ElMessage.error('获取套餐卡详情失败，请稍后重试');
  }
};

/**
 * 删除套餐卡
 * 弹出确认框，确认后调用API删除
 * @param id - 套餐卡ID
 */
const handleDelete = async (id: number) => {
  ElMessageBox.confirm("确定要删除该套餐卡吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        const response = await deletePackageCard(id);
        if (response.code === 200) {
          ElMessage.success("删除成功");
          getList();
        } else {
          ElMessage.error(response.message || "删除失败");
        }
      } catch (error) {
        console.error('删除套餐卡失败:', error);
        ElMessage.error('删除套餐卡失败');
      } finally {
        loading.value = false;
      }
    })
    .catch(() => {});
};

/**
 * 提交套餐卡表单
 * 验证表单后调用新增或更新API
 */
const handleSubmit = async () => {
  if (!formRef.value) return;
  
  if (isModificationDisabled.value) {
    ElMessage.warning('该套餐卡已设置为禁止修改');
    return;
  }
  
  try {
    await formRef.value.validate();
    
    const submitData = {
      ...formData,
      isModifiable: formData.featureOptions.includes('1') ? 1 : 0,
      isAddItemDisabled: formData.featureOptions.includes('2') ? 1 : 0,
      isLimitOnce: formData.featureOptions.includes('3') ? 1 : 0,
      isExpireInvalid: formData.featureOptions.includes('4') ? 1 : 0,
      isProhibitGift: formData.featureOptions.includes('5') ? 1 : 0
    };
    
    if (formData.id) {
      const response = await updatePackageCard(formData.id, submitData);
      if (response.code === 200) {
        ElMessage.success('更新成功');
      } else {
        ElMessage.error(response.message || '更新失败');
        return;
      }
    } else {
      const response = await addPackageCard(submitData);
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

// ==================== 包含项目/产品操作方法 ====================

/** 打开项目选择对话框 */
const handleAddIncludeProject = async () => {
  selectedProjectIds.value = [];
  projectTransferSearch.value = '';
  projectTransferVisible.value = true;
};

/** 打开产品选择对话框 */
const handleAddIncludeProduct = async () => {
  selectedProductIds.value = [];
  productTransferSearch.value = '';
  productTransferVisible.value = true;
};

/**
 * 删除包含项目
 * @param index - 项目索引
 */
const handleDeleteIncludeProject = (index: number) => {
  formData.giftProjects.splice(index, 1);
};

/**
 * 删除包含产品
 * @param index - 产品索引
 */
const handleDeleteIncludeProduct = (index: number) => {
  formData.giftProducts.splice(index, 1);
};

/** 批量删除选中的项目 */
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

/** 批量删除选中的产品 */
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

// ==================== 价格计算方法 ====================

/**
 * 处理项目选择变化
 * 自动填充项目的单价
 * @param projectId - 项目ID
 */
const handleProjectChange = (projectId: string) => {
  const selectedProject = props.projectList.find(p => p.id === projectId);
  if (selectedProject) {
    giftProjectForm.unitPrice = selectedProject.singleSalePrice || 0;
    calculateProjectTotalPrice();
  }
};

/** 计算项目总价 = 次数 × 单价 */
const calculateProjectTotalPrice = () => {
  const { times, unitPrice } = giftProjectForm;
  giftProjectForm.totalPrice = times * unitPrice;
};

/** 计算项目单价 = 总价 ÷ 次数 */
const calculateProjectUnitPrice = () => {
  const { times, totalPrice } = giftProjectForm;
  if (times > 0) {
    giftProjectForm.unitPrice = totalPrice / times;
  }
};

/**
 * 更新表格中项目的总价
 * @param index - 项目索引
 */
const updateProjectTotalPrice = (index: number) => {
  const project = formData.giftProjects[index];
  if (project) {
    project.totalPrice = project.times * project.unitPrice;
  }
};

/**
 * 更新表格中项目的单价
 * @param index - 项目索引
 */
const updateProjectUnitPrice = (index: number) => {
  const project = formData.giftProjects[index];
  if (project && project.times > 0) {
    project.unitPrice = project.totalPrice / project.times;
  }
};

// ==================== 表单提交方法 ====================

/**
 * 提交包含项目表单
 * 将项目添加到套餐卡的包含项目列表
 */
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

/**
 * 提交包含产品表单
 * 将产品添加到套餐卡的包含产品列表
 */
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

// ==================== 选择变化处理方法 ====================

/**
 * 处理项目选择变化
 * @param val - 选中的项目行数组
 */
const handleProjectSelectionChange = (val: any[]) => {
  selectedProjectRows.value = val;
};

/**
 * 处理产品选择变化
 * @param val - 选中的产品行数组
 */
const handleProductSelectionChange = (val: any[]) => {
  selectedProductRows.value = val;
};

// ==================== 项目/产品选择确认方法 ====================

/**
 * 确认选择项目
 * 将选中的项目添加到包含项目列表
 */
const handleProjectTransferConfirm = () => {
  const selectedProjects = props.projectList.filter(project => 
    selectedProjectIds.value.includes(project.id)
  );
  
  selectedProjects.forEach(project => {
    formData.giftProjects.push({
      id: '',
      projectId: project.id,
      projectName: project.projectName,
      times: 1,
      unitPrice: project.singleSalePrice || 0,
      totalPrice: project.singleSalePrice || 0,
      consume: 0,
      manualSalary: 0
    });
    formData.originalPrice += (project.originalPrice || 0);
  });
  
  projectTransferVisible.value = false;
};

/**
 * 确认选择产品
 * 将选中的产品添加到包含产品列表
 */
const handleProductTransferConfirm = () => {
  const selectedProducts = props.productList.filter(product => 
    selectedProductIds.value.includes(product.id)
  );
  
  selectedProducts.forEach(product => {
    formData.giftProducts.push({
      id: '',
      productId: product.id,
      productName: product.productName,
      times: 1,
      unitPrice: product.salePrice || 0,
      totalPrice: product.salePrice || 0,
      manualSalary: 0
    });
    formData.originalPrice += (product.originalPrice || 0);
  });
  
  productTransferVisible.value = false;
};
</script>

<style scoped>
.package-card {
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
