<!--
  品项管理页面
  
  功能说明：
  1. 品项管理 - 管理服务项目信息，包括项目名称、分类、价格、供应商、部位类型等
  2. 项目分类 - 管理项目分类，按部门划分，支持树形结构
  
  页面结构：
  - 品项管理标签页：项目列表、搜索、新增/编辑/删除项目
  - 项目分类标签页：分类列表、搜索、新增/编辑/删除分类
  
  权限控制：
  - project:project:view - 查看项目
  - project:project:add - 新增项目
  - project:project:edit - 编辑项目
  - project:project:delete - 删除项目
  - project:category:view - 查看分类
  - project:category:add - 新增分类
  - project:category:edit - 编辑分类
  - project:category:delete - 删除分类
  
  数据表：
  - card_project - 项目表
  - card_project_category - 项目分类表
  - card_project_ingredient - 项目配料表
  - card_project_sub_project - 子项目表
-->
<template>
  <div class="project-container">
    <el-card class="h-full flex flex-col">
      <!-- 标签页切换 -->
      <el-tabs v-model="activeTab" @tab-click="handleTabClick">
        <!-- 品项管理标签页 -->
        <el-tab-pane label="品项管理" name="project">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('project:project:view')">
              <!-- 搜索栏和操作按钮区域 -->
              <el-card class="mb-4" shadow="never">
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-4">
                    <span class="text-sm font-bold">项目名称</span>
                    <el-input
                      v-model="projectSearchForm.projectName"
                      placeholder="请输入项目名称"
                      clearable
                      style="width: 200px"
                      @clear="handleProjectSearch"
                      @keyup.enter="handleProjectSearch"
                    />
                    <span class="text-sm font-bold">所属品类</span>
                    <el-cascader
                      v-model="projectSearchForm.categoryId"
                      :options="cascaderCategoryData"
                      placeholder="请选择所属品类"
                      style="width: 200px"
                      clearable
                      @change="handleCategoryCascaderChange"
                    />
                    <el-button type="primary" @click="handleProjectSearch">
                      <el-icon><Search /></el-icon>
                      搜索
                    </el-button>
                    <el-button @click="resetProjectSearch">
                      <el-icon><Refresh /></el-icon>
                      重置
                    </el-button>
                  </div>
                  <!-- 新增项目按钮 -->
                  <el-button
                    v-if="hasAuth('project:project:add')"
                    type="primary"
                    @click="handleAddProject"
                  >
                    <el-icon><Plus /></el-icon>
                    新增项目
                  </el-button>
                </div>
              </el-card>

              <!-- 项目数据表格 -->
              <div class="flex-1 min-h-0">
                <el-table
                  v-loading="projectLoading"
                  :data="projectList"
                  style="width: 100%"
                  class="h-full"
                  :max-height="`calc(100vh - 320px)`"
                >
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="projectName" label="项目名称" />
                  <el-table-column label="所属分类">
                    <template #default="scope">
                      {{ getCategoryName(scope.row.categoryId) }}
                    </template>
                  </el-table-column>
                  <el-table-column prop="createTime" label="创建时间" />
                  <el-table-column label="操作" width="180">
                    <template #default="scope">
                      <el-button
                        v-if="hasAuth('project:project:edit')"
                        type="primary"
                        size="small"
                        @click="handleEditProject(scope.row)"
                      >
                        <el-icon><Edit /></el-icon>
                        编辑
                      </el-button>
                      <el-button
                        v-if="hasAuth('project:project:delete')"
                        type="danger"
                        size="small"
                        @click="handleDeleteProject(scope.row.id)"
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
                    v-model:current-page="projectPagination.current"
                    v-model:page-size="projectPagination.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="projectPagination.total"
                    @size-change="handleProjectSizeChange"
                    @current-change="handleProjectCurrentChange"
                  />
                </div>
              </div>
            </div>
            <!-- 无权限提示 -->
            <div
              v-else
              class="no-permission flex-1 flex items-center justify-center"
            >
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>

        <!-- 项目分类标签页 -->
        <el-tab-pane label="项目分类" name="category">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('project:category:view')">
              <!-- 搜索栏和操作按钮区域 -->
              <el-card class="mb-4" shadow="never">
                <div class="flex justify-between items-center">
                  <div class="flex items-center space-x-4">
                    <el-segmented
                      v-model="categorySearchForm.departmentId"
                      :options="departmentSegmentedOptions"
                      @change="handleCategorySearch"
                    />
                    <span class="text-sm font-bold">分类名称</span>
                    <el-input
                      v-model="categorySearchForm.categoryName"
                      placeholder="请输入分类名称"
                      clearable
                      style="width: 200px"
                      @clear="handleCategorySearch"
                      @keyup.enter="handleCategorySearch"
                    />
                    <el-button type="primary" @click="handleCategorySearch">
                      <el-icon><Search /></el-icon>
                      搜索
                    </el-button>
                    <el-button @click="resetCategorySearch">
                      <el-icon><Refresh /></el-icon>
                      重置
                    </el-button>
                  </div>
                  <!-- 新增分类按钮 -->
                  <el-button
                    v-if="hasAuth('project:category:add')"
                    type="primary"
                    @click="handleAddCategory"
                  >
                    <el-icon><Plus /></el-icon>
                    新增分类
                  </el-button>
                </div>
              </el-card>

              <!-- 分类数据表格 -->
              <div class="flex-1 min-h-0">
                <el-table
                  v-loading="categoryLoading"
                  :data="categoryList"
                  style="width: 100%"
                  :tree-props="{
                    children: 'children',
                    hasChildren: 'hasChildren'
                  }"
                  class="h-full"
                  :max-height="`calc(100vh - 320px)`"
                >
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="categoryName" label="分类名称" />
                  <el-table-column label="所属部门">
                    <template #default="scope">
                      {{ getDepartmentName(scope.row.departmentId) }}
                    </template>
                  </el-table-column>
                  <el-table-column prop="sort" label="排序" />
                  <el-table-column prop="createTime" label="创建时间" />
                  <el-table-column label="操作" width="180">
                    <template #default="scope">
                      <el-button
                        v-if="hasAuth('project:category:edit')"
                        type="primary"
                        size="small"
                        @click="handleEditCategory(scope.row)"
                      >
                        <el-icon><Edit /></el-icon>
                        编辑
                      </el-button>
                      <el-button
                        v-if="hasAuth('project:category:delete')"
                        type="danger"
                        size="small"
                        @click="handleDeleteCategory(scope.row.id)"
                      >
                        <el-icon><Delete /></el-icon>
                        删除
                      </el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </div>
            <!-- 无权限提示 -->
            <div
              v-else
              class="no-permission flex-1 flex items-center justify-center"
            >
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 新增/编辑分类对话框 -->
    <el-dialog
      v-model="categoryDialogVisible"
      :title="categoryDialogTitle"
      width="500px"
    >
      <!-- 分类表单 -->
      <el-form
        ref="categoryFormRef"
        :model="categoryForm"
        :rules="categoryRules"
        label-width="100px"
      >
        <el-form-item label="分类名称" prop="categoryName">
          <el-input
            v-model="categoryForm.categoryName"
            placeholder="请输入分类名称"
          />
        </el-form-item>
        <el-form-item label="所属部门" prop="departmentId">
          <el-select
            v-model="categoryForm.departmentId"
            placeholder="请选择所属部门"
            style="width: 100%"
          >
            <el-option
              v-for="dept in departmentList"
              :key="dept.id"
              :label="dept.deptName"
              :value="dept.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="排序">
          <el-input
            v-model.number="categoryForm.sort"
            type="number"
            placeholder="请输入排序"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="categoryDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitCategory">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 新增/编辑项目对话框 -->
    <el-dialog
      v-model="projectDialogVisible"
      :title="projectDialogTitle"
      width="800px"
    >
      <el-tabs v-model="activeProjectTab" type="border-card">
        <!-- 基本信息标签页 -->
        <el-tab-pane label="基本信息" name="basic">
          <!-- 项目基本信息表单 -->
          <el-form
            ref="projectFormRef"
            :model="projectForm"
            :rules="projectRules"
            label-width="120px"
          >
            <div class="flex gap-4">
              <el-form-item label="项目名称" prop="projectName" class="flex-1">
                <el-input
                  v-model="projectForm.projectName"
                  placeholder="请输入项目名称"
                  @input="generateProjectCode"
                />
              </el-form-item>
              <el-form-item label="项目编码" class="flex-1">
                <el-input
                  v-model="projectForm.projectCode"
                  placeholder="自动生成"
                  disabled
                />
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item label="对外名称" prop="externalName" class="flex-1">
                <el-input
                  v-model="projectForm.externalName"
                  placeholder="请输入展示给顾客的名字"
                />
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item label="所属品类" prop="categoryId" class="flex-1">
                <el-cascader
                  v-model="projectForm.categoryId"
                  :options="cascaderCategoryData"
                  placeholder="请选择所属品类"
                  style="width: 100%"
                  @change="handleProjectCategoryCascaderChange"
                />
              </el-form-item>
              <el-form-item label="部位类型" prop="projectType" class="flex-1">
                <el-select
                  v-model="projectForm.projectType"
                  multiple
                  filterable
                  placeholder="请选择部位类型"
                  style="width: 100%"
                >
                  <el-option
                    v-for="option in projectTypeOptions"
                    :key="option.value"
                    :label="option.label"
                    :value="option.value"
                  />
                </el-select>
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item label="供应商" prop="supplierId" class="flex-1">
                <el-select
                  v-model="projectForm.supplierId"
                  placeholder="请选择供应商"
                  style="width: 100%"
                >
                  <el-option
                    v-for="supplier in supplierList"
                    :key="supplier.id"
                    :label="supplier.supplierName"
                    :value="supplier.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item label="原价" prop="originalPrice" class="flex-1">
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.originalPrice"
                    type="number"
                    placeholder="请输入原价"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >元</span
                  >
                </div>
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item label="单次售价" prop="singlePrice" class="flex-1">
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.singlePrice"
                    type="number"
                    placeholder="请输入单次售价"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >元</span
                  >
                </div>
              </el-form-item>
              <el-form-item
                label="体验价"
                prop="experiencePrice"
                class="flex-1"
              >
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.experiencePrice"
                    type="number"
                    placeholder="请输入体验价"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >元</span
                  >
                </div>
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item
                label="消费限次/月"
                prop="monthlyLimit"
                class="flex-1"
              >
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.monthlyLimit"
                    type="number"
                    placeholder="请输入每月最多消费几次"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >次</span
                  >
                </div>
              </el-form-item>
              <el-form-item
                label="消费间隔时间"
                prop="consumptionInterval"
                class="flex-1"
              >
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.consumptionInterval"
                    type="number"
                    placeholder="请输入多久消费1次"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >天</span
                  >
                </div>
              </el-form-item>
            </div>
            <div class="flex gap-4">
              <el-form-item label="项目工时" prop="workHours" class="flex-1">
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.workHours"
                    type="number"
                    placeholder="请输入项目工时"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >小时</span
                  >
                </div>
              </el-form-item>
              <el-form-item label="服务时长" prop="serviceTime" class="flex-1">
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.serviceTime"
                    type="number"
                    placeholder="请输入服务时长"
                    style="padding-right: 40px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >分钟</span
                  >
                </div>
              </el-form-item>
            </div>
            <div
              class="el-divider el-divider--horizontal"
              role="separator"
              style="--el-border-style: solid"
            >
              <div class="el-divider__text is-left">限定设置</div>
            </div>
            <el-form-item label="限定销售分店" prop="limitedSaleStores">
              <el-select
                v-model="projectForm.limitedSaleStores"
                multiple
                placeholder="请选择限定销售分店"
                class="w-full"
              >
                <el-option
                  v-for="store in storeList"
                  :key="store.id"
                  :label="store.storeName"
                  :value="store.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="限定服务分店" prop="limitedServiceStores">
              <el-select
                v-model="projectForm.limitedServiceStores"
                multiple
                placeholder="请选择限定服务分店"
                class="w-full"
              >
                <el-option
                  v-for="store in storeList"
                  :key="store.id"
                  :label="store.storeName"
                  :value="store.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="状态" prop="status">
              <el-switch
                v-model="projectForm.status"
                active-text="上线"
                inactive-text="下线"
              />
            </el-form-item>
            <el-form-item label="备注" prop="remark">
              <el-input
                v-model="projectForm.remark"
                type="textarea"
                placeholder="请输入备注"
              />
            </el-form-item>
          </el-form>
        </el-tab-pane>

        <!-- 项目提醒与设置标签页 -->
        <el-tab-pane label="项目提醒与设置" name="reminder-settings">
          <!-- 功能开关设置 -->
          <div class="mb-6">
            <div
              class="el-divider el-divider--horizontal"
              role="separator"
              style="--el-border-style: solid"
            >
              <div class="el-divider__text is-left">功能开关</div>
            </div>
            <el-form :model="projectForm.features" label-width="100px">
              <el-form-item>
                <el-checkbox-group v-model="featuresCheckboxGroup">
                  <el-checkbox label="noDiscount">充值卡不打折</el-checkbox>
                  <el-checkbox label="noProjectCount">不计项目次</el-checkbox>
                  <el-checkbox label="noConsumption">不计消耗</el-checkbox>
                  <el-checkbox label="noConsumptionNotice"
                    >无消费通知</el-checkbox
                  >
                  <el-checkbox label="miniAppBookable"
                    >小程序可预约</el-checkbox
                  >
                  <el-checkbox label="allowGift">允许赠送</el-checkbox>
                </el-checkbox-group>
              </el-form-item>
            </el-form>
          </div>

          <!-- 护理后提醒设置 -->
          <div>
            <div
              class="el-divider el-divider--horizontal"
              role="separator"
              style="--el-border-style: solid"
            >
              <div class="el-divider__text is-left">护理后提醒</div>
            </div>
            <el-form :model="projectForm.reminder" label-width="120px">
              <el-form-item label="提醒类型" prop="reminderType">
                <el-radio-group v-model="projectForm.reminder.reminderType">
                  <el-radio label="single">单次提醒</el-radio>
                  <el-radio label="specific">设定时间</el-radio>
                  <el-radio label="repeat">可重复</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item
                v-if="projectForm.reminder.reminderType === 'single'"
                label="提醒时间"
              >
                <div class="relative">
                  <el-input
                    v-model.number="projectForm.reminder.daysLater"
                    type="number"
                    placeholder="请输入几天后提醒"
                    style="padding-right: 80px"
                  />
                  <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                    >天后提醒</span
                  >
                </div>
              </el-form-item>
              <el-form-item
                v-if="projectForm.reminder.reminderType === 'specific'"
                label="提醒日期"
              >
                <el-date-picker
                  v-model="projectForm.reminder.specificDate"
                  type="datetime"
                  placeholder="请选择提醒日期和时间"
                  style="width: 100%"
                />
              </el-form-item>
              <!-- 可重复提醒设置 -->
              <template v-if="projectForm.reminder.reminderType === 'repeat'">
                <div class="flex gap-4">
                  <el-form-item label="重复类型" class="flex-1">
                    <el-select
                      v-model="projectForm.reminder.repeatType"
                      style="width: 100%"
                    >
                      <el-option label="按日" value="daily" />
                      <el-option label="按周" value="weekly" />
                      <el-option label="按月" value="monthly" />
                    </el-select>
                  </el-form-item>
                  <el-form-item label="重复间隔" class="flex-1">
                    <div class="flex items-center gap-2">
                      <el-input-number
                        v-model="projectForm.reminder.repeatInterval"
                        :min="1"
                        :max="365"
                        style="width: 120px"
                      />
                      <span>{{
                        projectForm.reminder.repeatType === "daily"
                          ? "天"
                          : projectForm.reminder.repeatType === "weekly"
                            ? "周"
                            : "月"
                      }}</span>
                    </div>
                  </el-form-item>
                </div>
                <!-- 按周重复时显示周几选择 -->
                <el-form-item
                  v-if="projectForm.reminder.repeatType === 'weekly'"
                  label="重复周几"
                >
                  <el-checkbox-group
                    v-model="projectForm.reminder.repeatWeekdays"
                  >
                    <el-checkbox label="1">周一</el-checkbox>
                    <el-checkbox label="2">周二</el-checkbox>
                    <el-checkbox label="3">周三</el-checkbox>
                    <el-checkbox label="4">周四</el-checkbox>
                    <el-checkbox label="5">周五</el-checkbox>
                    <el-checkbox label="6">周六</el-checkbox>
                    <el-checkbox label="7">周日</el-checkbox>
                  </el-checkbox-group>
                </el-form-item>
                <!-- 按月重复时显示月几日选择 -->
                <el-form-item
                  v-if="projectForm.reminder.repeatType === 'monthly'"
                  label="重复月几日"
                >
                  <el-select
                    v-model="projectForm.reminder.repeatDayOfMonth"
                    style="width: 100%"
                  >
                    <el-option
                      v-for="day in 31"
                      :key="day"
                      :label="`每月${day}日`"
                      :value="day"
                    />
                  </el-select>
                </el-form-item>
              </template>
              <el-form-item label="提醒任务" prop="task">
                <el-input
                  v-model="projectForm.reminder.task"
                  type="textarea"
                  placeholder="请输入提醒任务内容"
                />
              </el-form-item>
            </el-form>
          </div>
        </el-tab-pane>

        <!-- 项目配料单标签页 -->
        <el-tab-pane label="项目配料单" name="ingredients">
          <el-table :data="projectForm.ingredients" style="width: 100%">
            <el-table-column prop="product" label="产品" width="180">
              <template #default="scope">
                <el-input
                  v-model="scope.row.product"
                  placeholder="请输入产品名称"
                />
              </template>
            </el-table-column>
            <el-table-column prop="type" label="类型" width="120">
              <template #default="scope">
                <el-input v-model="scope.row.type" placeholder="请输入类型" />
              </template>
            </el-table-column>
            <el-table-column prop="quantity" label="配料数量" width="120">
              <template #default="scope">
                <el-input
                  v-model.number="scope.row.quantity"
                  type="number"
                  placeholder="请输入数量"
                />
              </template>
            </el-table-column>
            <el-table-column prop="unit" label="配料单位" width="100">
              <template #default="scope">
                <el-input v-model="scope.row.unit" placeholder="请输入单位" />
              </template>
            </el-table-column>
            <el-table-column prop="remark" label="备注">
              <template #default="scope">
                <el-input v-model="scope.row.remark" placeholder="请输入备注" />
              </template>
            </el-table-column>
            <el-table-column label="操作" width="100">
              <template #default="scope">
                <el-button
                  type="danger"
                  size="small"
                  @click="removeIngredient(scope.$index)"
                  >删除</el-button
                >
              </template>
            </el-table-column>
          </el-table>
          <el-button
            type="primary"
            style="margin-top: 10px"
            @click="addIngredient"
            >添加配料</el-button
          >
        </el-tab-pane>

        <!-- 子项目配置标签页 -->
        <el-tab-pane label="子项目配置" name="subProjects">
          <el-table :data="projectForm.subProjects" style="width: 100%">
            <el-table-column prop="subProjectName" label="项目名称" width="200">
              <template #default="scope">
                <el-input
                  v-model="scope.row.subProjectName"
                  placeholder="请输入子项目名称"
                />
              </template>
            </el-table-column>
            <el-table-column
              prop="consumptionRatio"
              label="耗卡占比"
              width="150"
            >
              <template #default="scope">
                <el-input
                  v-model.number="scope.row.consumptionRatio"
                  type="number"
                  placeholder="请输入占比（%）"
                />
              </template>
            </el-table-column>
            <el-table-column label="操作" width="100">
              <template #default="scope">
                <el-button
                  type="danger"
                  size="small"
                  @click="removeSubProject(scope.$index)"
                  >删除</el-button
                >
              </template>
            </el-table-column>
          </el-table>
          <el-button
            type="primary"
            style="margin-top: 10px"
            @click="addSubProject"
            >添加子项目</el-button
          >
        </el-tab-pane>
      </el-tabs>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="projectDialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitProject">
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
 * 品项管理页面脚本
 * 
 * 主要功能：
 * 1. 品项管理 - CRUD操作、搜索、分页
 * 2. 项目分类管理 - CRUD操作、按部门筛选
 * 3. 项目详情配置 - 基本信息、提醒设置、配料单、子项目
 */
import { ref, reactive, onMounted, computed, watch } from "vue";
import {
  Plus,
  Edit,
  Delete,
  Search,
  Refresh,
  Close,
  Check
} from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox, type FormInstance } from "element-plus";
import { hasAuth } from "@/router/utils";
import { http } from "@/utils/http";
import { pinyin } from "pinyin-pro";

// ==================== 状态定义 ====================

/** 当前激活的主标签页 */
const activeTab = ref("project");

/** 当前激活的项目表单标签页 */
const activeProjectTab = ref("basic");

/** 分店列表 */
const storeList = ref([]);

/** 部位类型选项数组 - 用于项目表单中的部位类型多选 */
const projectTypeOptions = [
  { label: "面部", value: "面部" },
  { label: "眼部", value: "眼部" },
  { label: "口腔", value: "口腔" },
  { label: "头部", value: "头部" },
  { label: "肩部", value: "肩部" },
  { label: "颈部", value: "颈部" },
  { label: "背部", value: "背部" },
  { label: "腰部", value: "腰部" },
  { label: "臀部", value: "臀部" },
  { label: "腿部", value: "腿部" },
  { label: "手臂", value: "手臂" },
  { label: "足部", value: "足部" },
  { label: "淋巴", value: "淋巴" },
  { label: "私密", value: "私密" },
  { label: "胸部", value: "胸部" },
  { label: "腹部", value: "腹部" },
  { label: "头皮", value: "头皮" },
  { label: "全身", value: "全身" },
  { label: "卵巢", value: "卵巢" }
];

/** 加载状态 */
const categoryLoading = ref(false);
const projectLoading = ref(false);

/** 分类搜索表单 */
const categorySearchForm = reactive({
  departmentId: "",
  categoryName: ""
});

/** 项目搜索表单 */
const projectSearchForm = reactive({
  projectName: "",
  categoryId: []
});

/** 项目分页信息 */
const projectPagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

/** 分类分页信息 */
const categoryPagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

/** 供应商列表 - 用于项目表单选择 */
const supplierList = ref([]);

/** 分类列表数据 */
const categoryList = ref([]);

/** 项目列表数据 */
const projectList = ref([]);

/** 部门列表数据 */
const departmentList = ref([]);

/** 分类对话框可见性 */
const categoryDialogVisible = ref(false);

/** 分类对话框标题 */
const categoryDialogTitle = ref("");

/** 分类表单引用 */
const categoryFormRef = ref<FormInstance>();

/** 当前编辑的分类ID */
const currentCategoryId = ref<number | null>(null);

/** 项目对话框可见性 */
const projectDialogVisible = ref(false);

/** 项目对话框标题 */
const projectDialogTitle = ref("");

/** 项目表单引用 */
const projectFormRef = ref<FormInstance>();

/** 当前编辑的项目ID */
const currentProjectId = ref<number | null>(null);

/** 分类表单数据 */
const categoryForm = reactive({
  categoryName: "",
  departmentId: null,
  sort: 0
});

/** 项目表单数据 */
const projectForm = reactive({
  projectName: "",
  projectCode: "",
  categoryId: [],
  originalPrice: 0,
  singlePrice: 0,
  experiencePrice: 0,
  externalName: "",
  supplierId: null,
  projectType: [],
  monthlyLimit: 0,
  consumptionInterval: 0,
  workHours: 0,
  serviceTime: 0,
  status: true,
  remark: "",
  reminder: {
    task: "",
    reminderType: "single",
    daysLater: 0,
    specificDate: null,
    repeatRule: "",
    repeatType: "daily",
    repeatInterval: 1,
    repeatWeekdays: [],
    repeatDayOfMonth: 1
  },
  limitedSaleStores: [],
  limitedServiceStores: [],
  features: {
    noDiscount: false,
    noProjectCount: false,
    noConsumption: false,
    noConsumptionNotice: false,
    miniAppBookable: false,
    allowGift: false
  },
  ingredients: [],
  subProjects: []
});

/** 功能开关复选框组 - 用于绑定功能开关选项 */
const featuresCheckboxGroup = ref([]);

// ==================== 监听器 ====================

/** 监听功能开关变化，同步到复选框组 */
watch(
  () => projectForm.features,
  newFeatures => {
    // 将features对象转换为复选框组数组
    featuresCheckboxGroup.value = Object.entries(newFeatures)
      .filter(([_, value]) => value)
      .map(([key]) => key);
  },
  { deep: true, immediate: true }
);

/** 监听复选框组变化，同步到功能开关对象 */
watch(
  featuresCheckboxGroup,
  newValues => {
    // 将复选框组数组转换为features对象
    const featureKeys = [
      "noDiscount",
      "noProjectCount",
      "noConsumption",
      "noConsumptionNotice",
      "miniAppBookable",
      "allowGift"
    ];
    featureKeys.forEach(key => {
      projectForm.features[key] = newValues.includes(key);
    });
  },
  { deep: true }
);

/** 监听项目名称变化，自动填充对外名称 */
watch(
  () => projectForm.projectName,
  newValue => {
    // 只有当对外名称为空时才自动填充，避免覆盖用户手动修改
    if (!projectForm.externalName) {
      projectForm.externalName = newValue;
    }
  }
);

// ==================== 表单验证规则 ====================

/** 分类表单验证规则 */
const categoryRules = reactive({
  categoryName: [
    { required: true, message: "请输入分类名称", trigger: "blur" }
  ],
  departmentId: [{ required: true, message: "请选择所属部门", trigger: "blur" }]
});

/** 项目表单验证规则 */
const projectRules = reactive({
  projectName: [{ required: true, message: "请输入项目名称", trigger: "blur" }],
  categoryId: [{ required: true, message: "请选择所属分类", trigger: "blur" }],
  originalPrice: [
    { 
      validator: (rule: any, value: any, callback: any) => {
        if (value === '' || value === null || value === undefined) {
          callback(new Error('请输入原价'));
        } else if (isNaN(Number(value))) {
          callback(new Error('原价必须是数字'));
        } else if (Number(value) < 0) {
          callback(new Error('原价必须大于等于0'));
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ],
  singlePrice: [
    { 
      validator: (rule: any, value: any, callback: any) => {
        if (value === '' || value === null || value === undefined) {
          callback(new Error('请输入单次销售价格'));
        } else if (isNaN(Number(value))) {
          callback(new Error('单次销售价格必须是数字'));
        } else if (Number(value) < 0) {
          callback(new Error('单次销售价格必须大于等于0'));
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ],
  experiencePrice: [
    { 
      validator: (rule: any, value: any, callback: any) => {
        if (value !== '' && value !== null && value !== undefined) {
          if (isNaN(Number(value))) {
            callback(new Error('体验价必须是数字'));
          } else if (Number(value) < 0) {
            callback(new Error('体验价必须大于等于0'));
          } else {
            callback();
          }
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ],
  externalName: [
    { required: true, message: "请输入外部显示名", trigger: "blur" }
  ],
  supplierId: [{ required: true, message: "请选择供应商", trigger: "blur" }],
  projectType: [
    { required: true, message: "请选择部位类型", trigger: "change" },
    {
      type: "array",
      min: 1,
      message: "至少选择一个部位类型",
      trigger: "change"
    }
  ],
  monthlyLimit: [
    {
      validator: (rule: any, value: any, callback: any) => {
        if (value !== '' && value !== null && value !== undefined) {
          if (isNaN(Number(value))) {
            callback(new Error('消费限次/月必须是数字'));
          } else if (Number(value) < 0) {
            callback(new Error('消费限次/月必须大于等于0'));
          } else {
            callback();
          }
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ],
  consumptionInterval: [
    {
      validator: (rule: any, value: any, callback: any) => {
        if (value !== '' && value !== null && value !== undefined) {
          if (isNaN(Number(value))) {
            callback(new Error('消费间隔时间必须是数字'));
          } else if (Number(value) < 0) {
            callback(new Error('消费间隔时间必须大于等于0'));
          } else {
            callback();
          }
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ],
  workHours: [
    { 
      validator: (rule: any, value: any, callback: any) => {
        if (value === '' || value === null || value === undefined) {
          callback(new Error('请输入工时'));
        } else if (isNaN(Number(value))) {
          callback(new Error('工时必须是数字'));
        } else if (Number(value) < 0) {
          callback(new Error('工时必须大于等于0'));
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ],
  serviceTime: [
    { 
      validator: (rule: any, value: any, callback: any) => {
        if (value === '' || value === null || value === undefined) {
          callback(new Error('请输入服务时间'));
        } else if (isNaN(Number(value))) {
          callback(new Error('服务时间必须是数字'));
        } else if (Number(value) < 0) {
          callback(new Error('服务时间必须大于等于0'));
        } else {
          callback();
        }
      },
      trigger: "blur"
    }
  ]
});

// ==================== 生命周期 ====================

/**
 * 组件挂载时初始化数据
 * 根据权限加载对应的数据列表
 */
onMounted(() => {
  // 只在有对应权限时调用API获取数据
  if (hasAuth("project:project:view")) {
    getProjectList();
  }
  if (hasAuth("project:supplier:view")) {
    getSupplierList();
  }
  if (hasAuth("project:category:view")) {
    getCategoryList();
    getDepartmentList();
  }
  // 获取分店列表
  getStoreList();
});

// ==================== 工具方法 ====================

/** 根据项目名称自动生成项目编码（取汉字拼音首字母） */
const generateProjectCode = () => {
  if (projectForm.projectName) {
    const name = projectForm.projectName;
    let code = "";
    for (const char of name) {
      const codePoint = char.codePointAt(0);
      if (codePoint >= 65 && codePoint <= 90) {
        code += char;
      } else if (codePoint >= 97 && codePoint <= 122) {
        code += char.toUpperCase();
      } else if (codePoint >= 0x4e00 && codePoint <= 0x9fff) {
        const py = pinyin(char, { pattern: "first", toneType: "none" });
        code += py.toUpperCase();
      }
    }
    projectForm.projectCode = code.substring(0, 10);
  }
};

// ==================== 数据获取方法 ====================

/**
 * 获取分店列表
 * 用于项目表单中的限定销售/服务分店选择
 */
const getStoreList = async () => {
  try {
    // 这里应该调用实际的API获取分店列表
    // 暂时使用模拟数据
    storeList.value = [
      { id: 1, storeName: "顺义店" },
      { id: 2, storeName: "肇庆乐店" }
    ];

    // 如果是新增项目，默认全选所有分店
    if (!currentProjectId.value) {
      projectForm.limitedSaleStores = storeList.value.map(store => store.id);
      projectForm.limitedServiceStores = storeList.value.map(store => store.id);
    }
  } catch (error) {
    console.error("获取分店列表失败", error);
  }
};

/**
 * 处理标签页切换
 * 切换时加载对应标签页的数据
 * @param tab - 标签页实例
 */
const handleTabClick = (tab: any) => {
  // 切换到供应商管理标签页时加载数据
  if (tab.props.name === "supplier" && hasAuth("project:supplier:view")) {
    getSupplierList();
  }
  // 切换到项目分类标签页时加载数据
  if (tab.props.name === "category" && hasAuth("project:category:view")) {
    getCategoryList();
    getDepartmentList();
  }
  // 切换到项目管理标签页时加载数据
  if (tab.props.name === "project" && hasAuth("project:project:view")) {
    getProjectList();
  }
};

/**
 * 获取供应商列表
 * 用于项目表单中的供应商选择
 */
const getSupplierList = async () => {
  try {
    const response = await http.get("/api/card-item/get-suppliers");
    if (response.code === 200) {
      supplierList.value = response.data || [];
    }
  } catch (error) {
    console.error("获取供应商列表失败", error);
  }
};

/**
 * 获取项目分类列表
 * 支持按部门ID和分类名称筛选
 */
const getCategoryList = async () => {
  if (!hasAuth("project:category:view")) {
    return;
  }

  categoryLoading.value = true;
  try {
    // 使用http工具类获取数据，自动处理认证
    const response = await http.get("/api/card-item/get-categories", {
      params: {
        departmentId: categorySearchForm.departmentId,
        categoryName: categorySearchForm.categoryName
      }
    });

    if (response.code === 200) {
      categoryList.value = response.data || [];
    }
  } catch (error) {
    console.error("获取项目分类列表失败", error);
  } finally {
    categoryLoading.value = false;
  }
};

/**
 * 获取核心业务部门列表
 * 用于分类表单中的部门选择
 */
const getDepartmentList = async () => {
  if (!hasAuth("project:category:view")) {
    return;
  }

  try {
    // 使用http工具类获取数据，自动处理认证
    const response = await http.get("/api/card-item/get-core-departments");

    if (response.code === 200) {
      departmentList.value = response.data || [];
    }
  } catch (error) {
    console.error("获取核心业务部门列表失败", error);
  }
};

/**
 * 获取项目列表
 * 支持按项目名称和分类ID筛选，支持分页
 */
const getProjectList = async () => {
  if (!hasAuth("project:project:view")) {
    return;
  }

  projectLoading.value = true;
  try {
    // 处理分类ID，获取级联选择器的最后一个值
    let categoryId = "";
    if (
      Array.isArray(projectSearchForm.categoryId) &&
      projectSearchForm.categoryId.length > 0
    ) {
      categoryId =
        projectSearchForm.categoryId[projectSearchForm.categoryId.length - 1];
    }

    // 使用http工具类获取数据，自动处理认证
    const response = await http.get("/api/card-item/get-projects", {
      params: {
        projectName: projectSearchForm.projectName,
        categoryId: categoryId,
        page: projectPagination.current,
        pageSize: projectPagination.pageSize
      }
    });

    if (response.code === 200) {
      projectList.value = response.data || [];
      projectPagination.total = response.data?.length || 0;
    }
  } catch (error) {
    console.error("获取项目列表失败", error);
  } finally {
    projectLoading.value = false;
  }
};

// ==================== 搜索方法 ====================

/** 执行分类搜索 */
const handleCategorySearch = () => {
  getCategoryList();
};

/** 重置分类搜索条件 */
const resetCategorySearch = () => {
  categorySearchForm.categoryName = "";
  categorySearchForm.departmentId = "";
  getCategoryList();
};

/** 执行项目搜索 */
const handleProjectSearch = () => {
  projectPagination.current = 1;
  getProjectList();
};

/** 重置项目搜索条件 */
const resetProjectSearch = () => {
  projectSearchForm.projectName = "";
  projectSearchForm.categoryId = "";
  projectPagination.current = 1;
  getProjectList();
};

// ==================== 分页方法 ====================

/**
 * 处理项目分页大小变化
 * @param size - 新的分页大小
 */
const handleProjectSizeChange = (size: number) => {
  projectPagination.pageSize = size;
  getProjectList();
};

/**
 * 处理项目当前页变化
 * @param current - 新的当前页码
 */
const handleProjectCurrentChange = (current: number) => {
  projectPagination.current = current;
  getProjectList();
};

// ==================== 新增方法 ====================

/** 打开新增分类对话框 */
const handleAddCategory = () => {
  // 重置表单
  resetCategoryForm();
  categoryDialogTitle.value = "新增分类";
  currentCategoryId.value = null;
  categoryDialogVisible.value = true;
};

/** 打开新增项目对话框 */
const handleAddProject = () => {
  // 重置表单
  resetProjectForm();
  projectDialogTitle.value = "新增项目";
  currentProjectId.value = null;
  projectDialogVisible.value = true;
};

// ==================== 编辑方法 ====================

/**
 * 打开编辑分类对话框
 * @param row - 分类数据行
 */
const handleEditCategory = (row: any) => {
  // 填充表单数据
  Object.assign(categoryForm, {
    categoryName: row.categoryName,
    departmentId: row.departmentId || null,
    sort: row.sort || 0
  });
  categoryDialogTitle.value = "编辑分类";
  currentCategoryId.value = row.id;
  categoryDialogVisible.value = true;
};

/**
 * 打开编辑项目对话框
 * 加载项目详情、配料单和子项目数据
 * @param row - 项目数据行
 */
const handleEditProject = async (row: any) => {
  // 处理分类ID，转换为级联选择器需要的数组格式
  // 由于我们只存储了最底层的分类ID，这里简单地使用单个值的数组
  const categoryIdArray = row.categoryId ? [row.categoryId] : [];

  // 填充表单数据
  Object.assign(projectForm, {
    projectName: row.projectName,
    projectCode: row.projectCode || "",
    categoryId: categoryIdArray,
    originalPrice: row.originalPrice || 0,
    singlePrice: row.singleSalePrice || 0,
    experiencePrice: row.experiencePrice || 0,
    externalName: row.externalDisplayName || "",
    supplierId: row.supplierId || null,
    projectType: Array.isArray(row.projectType)
      ? row.projectType
      : row.projectType
        ? row.projectType.split(",")
        : [],
    monthlyLimit: row.monthlyLimit || 0,
    consumptionInterval: row.consumptionInterval || 0,
    workHours: row.workHours || 0,
    serviceTime: row.serviceTime || 0,
    status: true,
    remark: row.remark || "",
    reminder: {
      task: "",
      reminderType:
        row.reminderType === 1
          ? "single"
          : row.reminderType === 2
            ? "specific"
            : "repeat",
      daysLater: row.reminderDays || 0,
      specificDate: row.reminderDate || null,
      repeatRule: row.repeatRule || "",
      repeatType: "daily",
      repeatInterval: 1,
      repeatWeekdays: [],
      repeatDayOfMonth: 1,
      // 解析重复规则
      ...(row.repeatRule ? JSON.parse(row.repeatRule) : {})
    },
    features: {
      noDiscount: row.noRechargeDiscount === 1,
      noProjectCount: row.noProjectTimes === 1,
      noConsumption: row.noConsumption === 1,
      noConsumptionNotice: row.noConsumptionNotification === 1,
      miniAppBookable: row.miniProgramBookable === 1,
      allowGift: row.allowGift === 1
    },
    ingredients: [],
    subProjects: []
  });

  // 加载配料单数据
  try {
    const ingredientsResponse = await http.get(
      `/api/card-item/get-project-ingredients/${row.id}`
    );
    if (ingredientsResponse.code === 200) {
      projectForm.ingredients = ingredientsResponse.data || [];
    }
  } catch (error) {
    console.error("获取配料单数据失败", error);
  }

  // 加载子项目数据
  try {
    const subProjectsResponse = await http.get(
      `/api/card-item/get-project-sub-projects/${row.id}`
    );
    if (subProjectsResponse.code === 200) {
      projectForm.subProjects = subProjectsResponse.data || [];
    }
  } catch (error) {
    console.error("获取子项目数据失败", error);
  }

  projectDialogTitle.value = "编辑项目";
  currentProjectId.value = row.id;
  projectDialogVisible.value = true;
};

// ==================== 删除方法 ====================

/**
 * 删除分类
 * 弹出确认框，确认后调用API删除
 * @param id - 分类ID
 */
const handleDeleteCategory = async (id: number) => {
  ElMessageBox.confirm("确定要删除该分类吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      categoryLoading.value = true;
      try {
        // 使用http工具类删除数据，自动处理认证
        const response = await http.delete(
          `/api/card-item/delete-category/${id}`
        );

        if (response.code === 200) {
          categoryLoading.value = false;
          ElMessage.success("删除成功");
          getCategoryList();
        } else {
          categoryLoading.value = false;
          ElMessage.error(response.message || "删除失败");
        }
      } catch (error) {
        categoryLoading.value = false;
        console.error("删除分类失败", error);
        ElMessage.error("网络错误，请稍后重试");
      }
    })
    .catch(() => {
      // 取消删除
    });
};

/**
 * 删除项目
 * 弹出确认框，确认后调用API删除
 * @param id - 项目ID
 */
const handleDeleteProject = async (id: number) => {
  ElMessageBox.confirm("确定要删除该项目吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      projectLoading.value = true;
      try {
        // 使用http工具类删除数据，自动处理认证
        const response = await http.delete(
          `/api/card-item/delete-project/${id}`
        );

        if (response.code === 200) {
          projectLoading.value = false;
          ElMessage.success("删除成功");
          getProjectList();
        } else {
          projectLoading.value = false;
          ElMessage.error(response.message || "删除失败");
        }
      } catch (error) {
        projectLoading.value = false;
        console.error("删除项目失败", error);
        ElMessage.error("网络错误，请稍后重试");
      }
    })
    .catch(() => {
      // 取消删除
    });
};

// ==================== 表单操作方法 ====================

/** 重置分类表单 */
const resetCategoryForm = () => {
  categoryFormRef.value?.resetFields();
  Object.assign(categoryForm, {
    categoryName: "",
    departmentId: null,
    sort: 0
  });
  currentCategoryId.value = null;
};

/**
 * 提交分类表单
 * 验证表单后调用新增或更新API
 */
const handleSubmitCategory = async () => {
  if (!categoryFormRef.value) return;

  try {
    await categoryFormRef.value.validate();

    categoryLoading.value = true;

    // 构建提交数据，自动添加companyId字段（默认使用当前用户所属公司ID）
    const submitData = {
      ...categoryForm,
      companyId: 2, // 默认使用当前用户所属公司ID
      parentId: 0 // 固定为一级分类
    };

    let response;
    if (currentCategoryId.value) {
      // 编辑分类
      response = await http.put(
        `/api/card-item/update-category/${currentCategoryId.value}`,
        {
          data: submitData
        }
      );
    } else {
      // 新增分类
      response = await http.post("/api/card-item/add-category", {
        data: submitData
      });
    }

    if (response.code === 200) {
      categoryLoading.value = false;
      categoryDialogVisible.value = false;
      ElMessage.success(currentCategoryId.value ? "编辑成功" : "新增成功");
      getCategoryList();
    } else {
      categoryLoading.value = false;
      ElMessage.error(response.message || "操作失败");
    }
  } catch (error) {
    categoryLoading.value = false;
    console.error("操作失败", error);
    ElMessage.error("网络错误，请稍后重试");
  }
};

/** 重置项目表单 */
const resetProjectForm = () => {
  projectFormRef.value?.resetFields();
  Object.assign(projectForm, {
    projectName: "",
    projectCode: "",
    categoryId: [],
    originalPrice: 0,
    singlePrice: 0,
    experiencePrice: 0,
    externalName: "",
    supplierId: null,
    projectType: [],
    monthlyLimit: 0,
    consumptionInterval: 0,
    workHours: 0,
    serviceTime: 0,
    limitedSaleStores: [],
    limitedServiceStores: [],
    status: true,
    remark: "",
    reminder: {
      task: "",
      reminderType: "single",
      daysLater: 0,
      specificDate: null,
      repeatRule: "",
      repeatType: "daily",
      repeatInterval: 1,
      repeatWeekdays: [],
      repeatDayOfMonth: 1
    },
    features: {
      noDiscount: false,
      noProjectCount: false,
      noConsumption: false,
      noConsumptionNotice: false,
      miniAppBookable: false,
      allowGift: false
    },
    ingredients: [],
    subProjects: []
  });
  currentProjectId.value = null;
};

/** 添加配料项 */
const addIngredient = () => {
  projectForm.ingredients.push({
    product: "",
    type: "",
    quantity: 0,
    unit: "",
    remark: ""
  });
};

/**
 * 删除配料项
 * @param index - 配料项索引
 */
const removeIngredient = (index: number) => {
  projectForm.ingredients.splice(index, 1);
};

/** 添加子项目 */
const addSubProject = () => {
  projectForm.subProjects.push({
    subProjectName: "",
    consumptionRatio: 0
  });
};

/**
 * 删除子项目
 * @param index - 子项目索引
 */
const removeSubProject = (index: number) => {
  projectForm.subProjects.splice(index, 1);
};

/**
 * 提交项目表单
 * 验证表单后调用新增或更新API
 * 包含基本信息、提醒设置、配料单、子项目等数据
 */
const handleSubmitProject = async () => {
  if (!projectFormRef.value) return;

  try {
    await projectFormRef.value.validate();

    projectLoading.value = true;

    // 处理分类ID，获取级联选择器的最后一个值
    let categoryId = null;
    if (
      Array.isArray(projectForm.categoryId) &&
      projectForm.categoryId.length > 0
    ) {
      categoryId = projectForm.categoryId[projectForm.categoryId.length - 1];
    }

    // 构建提交数据，自动添加companyId字段（默认使用当前用户所属公司ID）
    // 构建重复规则
    let repeatRule = "";
    if (projectForm.reminder.reminderType === "repeat") {
      repeatRule = JSON.stringify({
        type: projectForm.reminder.repeatType,
        interval: projectForm.reminder.repeatInterval,
        weekdays: projectForm.reminder.repeatWeekdays,
        dayOfMonth: projectForm.reminder.repeatDayOfMonth
      });
    }

    const submitData = {
      projectName: projectForm.projectName,
      categoryId: categoryId,
      originalPrice: projectForm.originalPrice,
      singleSalePrice: projectForm.singlePrice,
      experiencePrice: projectForm.experiencePrice,
      externalDisplayName: projectForm.externalName,
      supplierId: projectForm.supplierId,
      projectType: Array.isArray(projectForm.projectType)
        ? projectForm.projectType.join(",")
        : projectForm.projectType,
      monthlyLimit: projectForm.monthlyLimit,
      consumptionInterval: projectForm.consumptionInterval,
      workHours: projectForm.workHours,
      serviceTime: projectForm.serviceTime,
      status: projectForm.status,
      remark: projectForm.remark,
      reminderType:
        projectForm.reminder.reminderType === "single"
          ? 1
          : projectForm.reminder.reminderType === "specific"
            ? 2
            : 3,
      reminderDays: projectForm.reminder.daysLater,
      reminderDate: projectForm.reminder.specificDate,
      reminderRepeat: projectForm.reminder.reminderType === "repeat" ? 1 : 0,
      repeatRule: repeatRule,

      noRechargeDiscount: projectForm.features.noDiscount ? 1 : 0,
      noProjectTimes: projectForm.features.noProjectCount ? 1 : 0,
      noConsumption: projectForm.features.noConsumption ? 1 : 0,
      noConsumptionNotification: projectForm.features.noConsumptionNotice
        ? 1
        : 0,
      miniProgramBookable: projectForm.features.miniAppBookable ? 1 : 0,
      limitedSaleStores: projectForm.features.limitedSaleStores ? 1 : 0,
      limitedServiceStores: projectForm.features.limitedServiceStores ? 1 : 0,

      allowGift: projectForm.features.allowGift ? 1 : 0,
      ingredients: projectForm.ingredients,
      subProjects: projectForm.subProjects,
      companyId: 2 // 默认使用当前用户所属公司ID
    };

    let response;
    if (currentProjectId.value) {
      // 编辑项目
      response = await http.put(
        `/api/card-item/update-project/${currentProjectId.value}`,
        {
          data: submitData
        }
      );
    } else {
      // 新增项目
      response = await http.post("/api/card-item/add-project", {
        data: submitData
      });
    }

    if (response.code === 200) {
      projectLoading.value = false;
      projectDialogVisible.value = false;
      ElMessage.success(currentProjectId.value ? "编辑成功" : "新增成功");
      getProjectList();
    } else {
      projectLoading.value = false;
      ElMessage.error(response.message || "操作失败");
    }
  } catch (error) {
    projectLoading.value = false;
    console.error("操作失败", error);
    ElMessage.error("网络错误，请稍后重试");
  }
};

// ==================== 工具方法 ====================

/**
 * 根据分类ID获取分类名称
 * @param categoryId - 分类ID
 * @returns 分类名称
 */
const getCategoryName = (categoryId: string | number) => {
  if (!categoryId) return "";
  const category = categoryList.value.find(item => item.id === categoryId);
  return category ? category.categoryName : categoryId;
};

/**
 * 根据部门ID获取部门名称
 * @param departmentId - 部门ID
 * @returns 部门名称
 */
const getDepartmentName = (departmentId: string | number) => {
  if (!departmentId) return "";
  const department = departmentList.value.find(
    item => item.id === departmentId
  );
  return department ? department.deptName : departmentId;
};

// ==================== 计算属性 ====================

/** 级联选择器分类数据 - 将分类列表转换为级联选择器格式 */
const cascaderCategoryData = computed(() => {
  return categoryList.value.map(item => ({
    value: item.id,
    label: item.categoryName
  }));
});

// ==================== 事件处理方法 ====================

/**
 * 处理搜索栏分类级联选择器变化
 * @param value - 选中的分类ID数组
 */
const handleCategoryCascaderChange = (value: any) => {
  // 级联选择器返回的是数组，我们只需要最后一个值（即选中的最底层分类ID）
  projectSearchForm.categoryId = value;
};

/**
 * 处理项目表单分类级联选择器变化
 * @param value - 选中的分类ID数组
 */
const handleProjectCategoryCascaderChange = (value: any) => {
  // 级联选择器返回的是数组，我们只需要最后一个值（即选中的最底层分类ID）
  projectForm.categoryId = value;
};

/** 部门分段控件选项 - 将部门列表转换为分段控件格式 */
const departmentSegmentedOptions = computed(() => {
  const options = [{ label: "全部", value: "" }];
  departmentList.value.forEach(dept => {
    options.push({ label: dept.deptName, value: dept.id });
  });
  return options;
});
</script>

<style scoped>
.project-container {
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
