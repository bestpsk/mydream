<template>
  <div class="time-card">
    <el-card class="mb-4" shadow="never">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <span class="text-sm font-bold">卡名称/编码</span>
          <el-input
            v-model="searchForm.keyword"
            placeholder="请输入卡名称或编码"
            prefix-icon="Search"
            clearable
            style="width: 220px"
            @clear="handleSearch"
            @keyup.enter="handleSearch"
          />
          <span class="text-sm font-bold">状态</span>
          <el-select v-model="searchForm.status" placeholder="全部" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
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
        <div class="flex space-x-2">
          <el-button size="medium" v-if="hasAuth('card:time:add')" type="primary" @click="handleAdd">
            <el-icon><Plus /></el-icon>
            新增时效卡
          </el-button>
          <el-button 
            v-if="hasAuth('card:time:edit')" 
            type="success" 
            :disabled="selectedRows.length === 0"
            @click="handleBatchEnable"
          >
            批量启用
          </el-button>
          <el-button 
            v-if="hasAuth('card:time:edit')" 
            type="warning" 
            :disabled="selectedRows.length === 0"
            @click="handleBatchDisable"
          >
            批量禁用
          </el-button>
        </div>
      </div>
    </el-card>

    <div class="flex-1 min-h-0">
      <el-table 
        v-loading="loading" 
        :data="list" 
        style="width: 100%" 
        class="h-full" 
        :max-height="`calc(100vh - 320px)`"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="70" />
        <el-table-column prop="cardName" label="卡名称" min-width="120" />
        <el-table-column prop="validDays" label="有效期(天)" min-width="100">
          <template #default="scope">
            <el-tag v-if="scope.row.validDays === 7" type="success">周卡</el-tag>
            <el-tag v-else-if="scope.row.validDays === 30" type="primary">月卡</el-tag>
            <el-tag v-else-if="scope.row.validDays === 90" type="warning">季卡</el-tag>
            <el-tag v-else-if="scope.row.validDays === 365" type="danger">年卡</el-tag>
            <span v-else>{{ scope.row.validDays }}天</span>
          </template>
        </el-table-column>
        <el-table-column prop="originalPrice" label="原价" min-width="80">
          <template #default="scope">
            ¥{{ scope.row.originalPrice || 0 }}
          </template>
        </el-table-column>
        <el-table-column prop="price" label="售价" min-width="80">
          <template #default="scope">
            ¥{{ scope.row.price }}
          </template>
        </el-table-column>
        <el-table-column prop="useRuleText" label="使用规则" min-width="100" />
        <el-table-column prop="projectBindText" label="项目绑定" min-width="90" />
        <el-table-column prop="customerCount" label="已购客户" min-width="80">
          <template #default="scope">
            <el-tag type="info">{{ scope.row.customerCount || 0 }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" min-width="90">
          <template #default="scope">
            <el-switch
              v-model="scope.row.status"
              :active-value="1"
              :inactive-value="0"
              active-text="启用"
              inactive-text="禁用"
              inline-prompt
              @change="handleToggleStatus(scope.row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="createTime" label="创建时间" min-width="150" />
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="scope">
            <el-button v-if="hasAuth('card:time:edit')" type="primary" size="small" @click="handleEdit(scope.row)">
              <el-icon><Edit /></el-icon>
              
            </el-button>
            <el-button type="info" size="small" @click="handleCopy(scope.row)">
              <el-icon><CopyDocument /></el-icon>
              
            </el-button>
            <el-button v-if="hasAuth('card:time:delete')" type="danger" size="small" @click="handleDelete(scope.row.id)">
              <el-icon><Delete /></el-icon>
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
      :width="dialogWidth"
      destroy-on-close
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="rules"
      >
        <el-tabs v-model="activeTab" type="border-card">
          <el-tab-pane label="基本信息" name="basic">
            <el-form :model="formData" label-width="120px">
              <div class="flex space-x-4">
                <el-form-item label="卡名称" prop="cardName" style="width: 48%">
                  <el-input v-model="formData.cardName" placeholder="请输入时效卡名称" @input="generateCardCode" />
                </el-form-item>
                <el-form-item label="卡编码" prop="cardCode" style="width: 48%">
                  <el-input v-model="formData.cardCode" placeholder="请输入卡编码（默认自动生成）" />
                </el-form-item>
              </div>
              <div class="flex space-x-4">
                <el-form-item label="原价" prop="originalPrice" style="width: 48%">
                  <el-input-number v-model="formData.originalPrice" :min="0" :step="0.01" :precision="2" style="width: 100%">
                    <template #suffix><span class="text-gray-500">元</span></template>
                  </el-input-number>
                </el-form-item>
                <el-form-item label="售价" prop="price" style="width: 48%">
                  <el-input-number v-model="formData.price" :min="0" :step="0.01" :precision="2" style="width: 100%">
                    <template #suffix><span class="text-gray-500">元</span></template>
                  </el-input-number>
                </el-form-item>
              </div>
              
              <el-divider content-position="left">规则配置</el-divider>
              
              <el-form-item label="有效期类型" prop="validType">
                <el-radio-group v-model="formData.validType">
                  <el-radio :label="1">固定天数</el-radio>
                  <el-radio :label="2">自定义</el-radio>
                </el-radio-group>
              </el-form-item>
              <el-form-item label="有效天数" prop="validDays">
                <template v-if="formData.validType === 1">
                  <div class="flex space-x-2">
                    <el-button 
                      v-for="day in quickDays" 
                      :key="day.value" 
                      :type="formData.validDays === day.value ? 'primary' : 'default'"
                      @click="formData.validDays = day.value"
                    >
                      {{ day.label }}
                    </el-button>
                  </div>
                </template>
                <template v-else>
                  <el-input-number v-model="formData.validDays" :min="1" :step="1" style="width: 200px">
                    <template #suffix><span class="text-gray-500">天</span></template>
                  </el-input-number>
                </template>
              </el-form-item>
              <el-form-item label="使用规则">
                <div class="flex items-center space-x-6">
                  <div class="flex items-center">
                    <el-checkbox v-model="formData.limitTotalCount" />
                    <span class="ml-2">限制总次数，最大</span>
                    <el-input-number 
                      v-model="formData.maxUseCount" 
                      :min="1" 
                      :step="1" 
                      style="width: 150px" 
                      class="mx-2"
                      :disabled="!formData.limitTotalCount"
                    >
                      <template #suffix><span class="text-gray-500">次</span></template>
                    </el-input-number>
                  </div>
                  <div class="flex items-center">
                    <el-checkbox v-model="formData.limitFrequency" />
                    <span class="ml-2">限制频率，每</span>
                    <el-input-number 
                      v-model="formData.intervalDays" 
                      :min="1" 
                      :step="1" 
                      style="width: 150px" 
                      class="mx-2"
                      :disabled="!formData.limitFrequency"
                    >
                      <template #suffix><span class="text-gray-500">天</span></template>
                    </el-input-number>
                    <span>使用1次</span>
                  </div>
                </div>
              </el-form-item>
              
              <el-divider content-position="left">其他信息</el-divider>
              
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
                  placeholder="请输入备注（如：仅限新客办理）"
                  rows="2"
                />
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <el-tab-pane label="包含项目" name="projects">
            <el-form :model="formData">
              <el-form-item prop="projectBindType">
                <el-radio-group v-model="formData.projectBindType" @change="handleProjectBindTypeChange">
                  <el-radio :label="1">单选项目</el-radio>
                  <el-radio :label="2">多选项目</el-radio>
                  <el-radio :label="3">全店通用</el-radio>
                </el-radio-group>
              </el-form-item>
              
              <el-form-item v-if="formData.projectBindType !== 3">
                <div class="w-full">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-500">已选择 {{ formData.projects.length }} 个项目</span>
                    <el-button type="primary" size="small" @click="handleAddProject">添加项目</el-button>
                  </div>
                  <el-table 
                    :data="formData.projects" 
                    style="width: 100%" 
                    border
                    max-height="300"
                  >
                    <el-table-column label="项目名称" min-width="150">
                      <template #default="scope">
                        {{ scope.row.projectName }}
                      </template>
                    </el-table-column>
                    <el-table-column prop="times" label="次数" width="100">
                      <template #default="scope">
                        <el-input 
                          v-model.number="scope.row.times" 
                          type="number" 
                          size="small" 
                          style="width: 100%" 
                          @input="updateProjectTotalPrice(scope.$index)"
                        />
                      </template>
                    </el-table-column>
                    <el-table-column prop="unitPrice" label="单价" width="100">
                      <template #default="scope">
                        <el-input 
                          v-model.number="scope.row.unitPrice" 
                          type="number" 
                          size="small" 
                          style="width: 100%" 
                          @input="updateProjectTotalPrice(scope.$index)"
                        />
                      </template>
                    </el-table-column>
                    <el-table-column prop="totalPrice" label="总价" width="100">
                      <template #default="scope">
                        <el-input 
                          v-model.number="scope.row.totalPrice" 
                          type="number" 
                          size="small" 
                          style="width: 100%" 
                          @input="updateProjectUnitPrice(scope.$index)"
                        />
                      </template>
                    </el-table-column>
                    <el-table-column prop="consume" label="耗卡" width="100">
                      <template #default="scope">
                        <el-input v-model.number="scope.row.consume" type="number" size="small" style="width: 100%" />
                      </template>
                    </el-table-column>
                    <el-table-column prop="manualSalary" label="手工费" width="100">
                      <template #default="scope">
                        <el-input v-model.number="scope.row.manualSalary" type="number" size="small" style="width: 100%" />
                      </template>
                    </el-table-column>
                    <el-table-column label="操作" width="65" fixed="right">
                      <template #default="scope">
                        <el-button size="small" type="danger" @click="handleDeleteProject(scope.$index)">
                          <el-icon><Delete /></el-icon>
                        </el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
              </el-form-item>
              
              <el-form-item v-if="formData.projectBindType === 3">
                <el-alert type="info" :closable="false">
                  全店通用：该时效卡可用于店内所有启用的项目
                </el-alert>
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <el-tab-pane label="包含产品" name="products">
            <el-form :model="formData">
              <el-form-item>
                <div class="w-full">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-500">已选择 {{ formData.products.length }} 个产品</span>
                    <el-button type="primary" size="small" @click="handleAddProduct">添加产品</el-button>
                  </div>
                  <el-table 
                    :data="formData.products" 
                    style="width: 100%" 
                    border
                    max-height="300"
                  >
                    <el-table-column label="产品名称" min-width="150">
                      <template #default="scope">
                        {{ scope.row.productName }}
                      </template>
                    </el-table-column>
                    <el-table-column prop="times" label="数量" width="100">
                      <template #default="scope">
                        <el-input 
                          v-model.number="scope.row.times" 
                          type="number" 
                          size="small" 
                          style="width: 100%" 
                          @input="updateProductTotalPrice(scope.$index)"
                        />
                      </template>
                    </el-table-column>
                    <el-table-column prop="unitPrice" label="单价" width="100">
                      <template #default="scope">
                        <el-input 
                          v-model.number="scope.row.unitPrice" 
                          type="number" 
                          size="small" 
                          style="width: 100%" 
                          @input="updateProductTotalPrice(scope.$index)"
                        />
                      </template>
                    </el-table-column>
                    <el-table-column prop="totalPrice" label="总价" width="100">
                      <template #default="scope">
                        <el-input 
                          v-model.number="scope.row.totalPrice" 
                          type="number" 
                          size="small" 
                          style="width: 100%" 
                          @input="updateProductUnitPrice(scope.$index)"
                        />
                      </template>
                    </el-table-column>
                    <el-table-column prop="manualSalary" label="手工费" width="100">
                      <template #default="scope">
                        <el-input v-model.number="scope.row.manualSalary" type="number" size="small" style="width: 100%" />
                      </template>
                    </el-table-column>
                    <el-table-column label="操作" width="65" fixed="right">
                      <template #default="scope">
                        <el-button size="small" type="danger" @click="handleDeleteProduct(scope.$index)">
                          <el-icon><Delete /></el-icon>
                        </el-button>
                      </template>
                    </el-table-column>
                  </el-table>
                </div>
              </el-form-item>
            </el-form>
          </el-tab-pane>
          
          <el-tab-pane label="限定设置" name="limits">
            <el-form :model="formData" label-width="100px">
              <el-form-item label="卡状态" prop="status">
                <el-radio-group v-model="formData.status">
                  <el-radio :label="1">启用</el-radio>
                  <el-radio :label="0">禁用</el-radio>
                </el-radio-group>
              </el-form-item>
              
              <div class="flex space-x-4">
                <el-form-item label="上线时间" prop="onlineTime" style="width: 48%">
                  <el-date-picker
                    v-model="formData.onlineTime"
                    type="datetime"
                    placeholder="选择上线时间"
                    style="width: 100%"
                  />
                </el-form-item>
                <el-form-item label="下线时间" prop="offlineTime" style="width: 48%">
                  <el-date-picker
                    v-model="formData.offlineTime"
                    type="datetime"
                    placeholder="选择下线时间"
                    style="width: 100%"
                  />
                </el-form-item>
              </div>
              
              <el-divider content-position="left">限定分店/部门</el-divider>
              
              <el-form-item label="限定销售分店" prop="saleStoreIds">
                <el-select
                  v-model="formData.saleStoreIds"
                  multiple
                  placeholder="不限定（可选择多个）"
                  class="w-full"
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
                  placeholder="不限定（可选择多个）"
                  class="w-full"
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
                  placeholder="不限定（可选择多个）"
                  class="w-full"
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
                  placeholder="不限定（可选择多个）"
                  class="w-full"
                >
                  <el-option
                    v-for="dept in departmentList"
                    :key="dept.id"
                    :label="dept.name"
                    :value="dept.id"
                  />
                </el-select>
              </el-form-item>
              
              <el-form-item label="禁止修改" prop="isModifiable">
                <el-switch v-model="formData.isModifiable" :active-value="1" :inactive-value="0" />
                <span class="ml-2 text-gray-500">开启后该卡信息不可修改</span>
              </el-form-item>
            </el-form>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button v-if="hasAuth('card:time:add') || hasAuth('card:time:edit')" type="primary" @click="handleSubmit">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog
      v-model="projectSelectVisible"
      title="选择项目"
      width="800px"
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
          class="project-card p-2 border rounded bg-white shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          :class="{ 'border-primary': selectedProjectIds.includes(project.id) }"
          @click="toggleProjectSelection(project.id)"
        >
          <div class="flex items-center mb-1">
            <input 
              v-if="formData.projectBindType === 1" 
              type="radio" 
              :value="project.id" 
              :checked="selectedProjectIds.includes(project.id)" 
              class="mr-2" 
              @click.stop 
            />
            <input 
              v-else 
              type="checkbox" 
              :value="project.id" 
              v-model="selectedProjectIds" 
              class="mr-2" 
              @click.stop 
            />
            <div class="font-medium text-sm ml-2">{{ project.projectName }}</div>
            <el-tag v-if="project.supplierName" size="small" class="ml-2">{{ project.supplierName }}</el-tag>
          </div>
          <div class="flex flex-wrap gap-x-5 gap-y-0.5 text-xs text-gray-500">
            <div class="ml-5">原价: {{ project.originalPrice || 0 }}元</div>
            <div>售卖: {{ project.singleSalePrice || 0 }}元</div>
            <div>服务时长: {{ project.serviceTime || 0 }}分钟</div>
          </div>
        </div>
        <div v-if="filteredProjectList.length === 0" class="text-center text-gray-400 py-8">
          暂无匹配的项目
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="projectSelectVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleProjectSelectConfirm">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog
      v-model="productSelectVisible"
      title="选择产品"
      width="800px"
      destroy-on-close
    >
      <div class="mb-4">
        <el-input
          v-model="productSearchKeyword"
          placeholder="请输入产品名称搜索"
          prefix-icon="Search"
          clearable
        />
      </div>
      <div class="space-y-2 max-h-96 overflow-y-auto">
        <div 
          v-for="product in filteredProductList" 
          :key="product.id" 
          class="product-card p-2 border rounded bg-white shadow-sm hover:shadow-md transition-shadow cursor-pointer"
          :class="{ 'border-primary': selectedProductIds.includes(product.id) }"
          @click="toggleProductSelection(product.id)"
        >
          <div class="flex items-center mb-1">
            <input type="checkbox" :value="product.id" v-model="selectedProductIds" class="mr-2" @click.stop />
            <div class="font-medium text-sm ml-2">{{ product.productName }}</div>
            <el-tag v-if="product.supplierName" size="small" class="ml-2">{{ product.supplierName }}</el-tag>
          </div>
          <div class="flex flex-wrap gap-x-5 gap-y-0.5 text-xs text-gray-500">
            <div class="ml-5">原价: {{ product.originalPrice || 0 }}元</div>
            <div>售卖: {{ product.salePrice || 0 }}元</div>
            <div class="w-full mt-0.5 truncate ml-5" :title="product.remark || ''">备注: {{ product.remark || '' }}</div>
          </div>
        </div>
        <div v-if="filteredProductList.length === 0" class="text-center text-gray-400 py-8">
          暂无匹配的产品
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="productSelectVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleProductSelectConfirm">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from "vue";
import { Plus, Edit, Delete, Search, Refresh, Close, Check, CopyDocument } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import { hasAuth } from "@/router/utils";
import { pinyin } from "pinyin-pro";
import {
  getTimeCards,
  getTimeCardDetail,
  addTimeCard,
  updateTimeCard,
  deleteTimeCard,
  copyTimeCard,
  toggleTimeCardStatus,
  batchTimeCardStatus
} from '@/api/timeCard';
import { useCompanyChange } from "@/composables/useCompanyChange";

interface Props {
  storeList: any[];
  departmentList: any[];
  projectList: any[];
  productList: any[];
}

const props = defineProps<Props>();

const loading = ref(false);
const dialogVisible = ref(false);
const dialogTitle = ref('新增时效卡');
const activeTab = ref('basic');
const formRef = ref<any>(null);
const selectedRows = ref<any[]>([]);

const projectSelectVisible = ref(false);
const productSelectVisible = ref(false);
const projectSearchKeyword = ref('');
const productSearchKeyword = ref('');
const selectedProjectIds = ref<any[]>([]);
const selectedProductIds = ref<any[]>([]);

const quickDays = [
  { label: '周卡(7天)', value: 7 },
  { label: '月卡(30天)', value: 30 },
  { label: '季卡(90天)', value: 90 },
  { label: '年卡(365天)', value: 365 }
];

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

const filteredProductList = computed(() => {
  if (!productSearchKeyword.value) {
    return props.productList;
  }
  const keyword = productSearchKeyword.value.toLowerCase();
  return props.productList.filter((product: any) => 
    product.productName?.toLowerCase().includes(keyword)
  );
});

const dialogWidth = computed(() => {
  const screenWidth = window.innerWidth;
  if (screenWidth < 768) return "95%";
  if (screenWidth < 1200) return "80%";
  return "900px";
});

const searchForm = reactive({
  keyword: "",
  status: null as number | null
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
  cardCode: '',
  originalPrice: 0,
  price: 0,
  validDays: 30,
  validType: 1,
  limitTotalCount: false,
  maxUseCount: 1,
  limitFrequency: false,
  intervalDays: 1,
  projectBindType: 1,
  customerCount: 0,
  description: '',
  remark: '',
  status: 1,
  onlineTime: null as string | null,
  offlineTime: null as string | null,
  saleStoreIds: [] as any[],
  consumeStoreIds: [] as any[],
  saleDepartmentIds: [] as any[],
  consumeDepartmentIds: [] as any[],
  isModifiable: 0,
  projects: [] as any[],
  products: [] as any[]
});

const rules = reactive({
  cardName: [{ required: true, message: '请输入时效卡名称', trigger: ['blur', 'change'] }],
  validDays: [{ required: true, message: '请输入有效期', trigger: 'blur' }],
  price: [{ required: true, message: '请输入价格', trigger: 'blur' }]
});

const generateCardCode = () => {
  if (formData.cardName) {
    const name = formData.cardName;
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
    formData.cardCode = code.substring(0, 10);
  }
};

onMounted(() => {
  getList();
});

/** 监听公司变化，重新加载数据 */
useCompanyChange(() => {
  getList();
});

const getList = async () => {
  if (!hasAuth('card:time:view')) {
    return;
  }
  
  loading.value = true;
  try {
    const params: any = {
      keyword: searchForm.keyword || undefined,
      status: searchForm.status !== null ? searchForm.status : undefined
    };
    
    const response = await getTimeCards(params);
    if (response.code === 200) {
      list.value = response.data;
      pagination.total = response.data.length || 0;
    } else {
      ElMessage.error(response.message || '获取时效卡列表失败');
    }
  } catch (error) {
    console.error('获取时效卡列表失败:', error);
    ElMessage.error('获取时效卡列表失败');
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => {
  pagination.current = 1;
  getList();
};

const resetSearch = () => {
  searchForm.keyword = "";
  searchForm.status = null;
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

const handleSelectionChange = (val: any[]) => {
  selectedRows.value = val;
};

const resetFormData = () => {
  Object.assign(formData, {
    id: '',
    cardName: '',
    cardCode: '',
    originalPrice: 0,
    price: 0,
    validDays: 30,
    validType: 1,
    limitTotalCount: false,
    maxUseCount: 1,
    limitFrequency: false,
    intervalDays: 1,
    projectBindType: 1,
    customerCount: 0,
    description: '',
    remark: '',
    status: 1,
    onlineTime: null,
    offlineTime: null,
    saleStoreIds: [],
    consumeStoreIds: [],
    saleDepartmentIds: [],
    consumeDepartmentIds: [],
    isModifiable: 0,
    projects: [],
    products: []
  });
};

const handleAdd = () => {
  if (!hasAuth('card:time:add')) {
    ElMessage.warning('无新增权限');
    return;
  }
  dialogTitle.value = '新增时效卡';
  resetFormData();
  activeTab.value = 'basic';
  dialogVisible.value = true;
};

const handleEdit = async (row: any) => {
  if (!hasAuth('card:time:edit')) {
    ElMessage.warning('无编辑权限');
    return;
  }
  dialogTitle.value = '编辑时效卡';
  
  try {
    const response = await getTimeCardDetail(row.id);
    if (response.code === 200) {
      const detail = response.data;
      Object.assign(formData, {
        id: detail.id,
        cardName: detail.cardName,
        cardCode: detail.cardCode,
        originalPrice: detail.originalPrice,
        price: detail.price,
        validDays: detail.validDays,
        validType: detail.validType,
        limitTotalCount: detail.useRuleType === 2 || detail.limitTotalCount === true,
        maxUseCount: detail.maxUseCount || 1,
        limitFrequency: detail.useRuleType === 3 || detail.limitFrequency === true,
        intervalDays: detail.intervalDays || 1,
        projectBindType: detail.projectBindType,
        customerCount: detail.customerCount,
        description: detail.description,
        remark: detail.remark,
        status: detail.status,
        onlineTime: detail.onlineTime,
        offlineTime: detail.offlineTime,
        saleStoreIds: detail.saleStoreIds || [],
        consumeStoreIds: detail.consumeStoreIds || [],
        saleDepartmentIds: detail.saleDepartmentIds || [],
        consumeDepartmentIds: detail.consumeDepartmentIds || [],
        isModifiable: detail.isModifiable,
        projects: detail.projects || [],
        products: detail.products || []
      });
      activeTab.value = 'basic';
      dialogVisible.value = true;
    } else {
      ElMessage.error(response.message || '获取时效卡详情失败');
    }
  } catch (error) {
    console.error('获取时效卡详情失败:', error);
    ElMessage.error('获取时效卡详情失败');
  }
};

const handleDelete = async (id: number) => {
  if (!hasAuth('card:time:delete')) {
    ElMessage.warning('无删除权限');
    return;
  }
  ElMessageBox.confirm("确定要删除该时效卡吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        const response = await deleteTimeCard(id);
        if (response.code === 200) {
          ElMessage.success("删除成功");
          getList();
        } else {
          ElMessage.error(response.message || "删除失败");
        }
      } catch (error) {
        console.error('删除时效卡失败:', error);
        ElMessage.error('删除时效卡失败');
      } finally {
        loading.value = false;
      }
    })
    .catch(() => {});
};

const handleCopy = async (row: any) => {
  ElMessageBox.confirm("确定要复制该时效卡吗？", "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "info"
  })
    .then(async () => {
      loading.value = true;
      try {
        const response = await copyTimeCard(row.id);
        if (response.code === 200) {
          ElMessage.success("复制成功");
          getList();
        } else {
          ElMessage.error(response.message || "复制失败");
        }
      } catch (error) {
        console.error('复制时效卡失败:', error);
        ElMessage.error('复制时效卡失败');
      } finally {
        loading.value = false;
      }
    })
    .catch(() => {});
};

const handleToggleStatus = async (row: any) => {
  const newStatus = row.status;
  const oldStatus = newStatus === 1 ? 0 : 1;
  const action = newStatus === 0 ? '禁用' : '启用';
  
  if (newStatus === 0 && row.customerCount > 0) {
    try {
      await ElMessageBox.confirm(
        `该卡已有 ${row.customerCount} 位顾客办理，确定要禁用吗？`,
        "警告",
        {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning"
        }
      );
    } catch {
      row.status = oldStatus;
      return;
    }
  }
  
  try {
    const response = await toggleTimeCardStatus(row.id);
    if (response.code === 200) {
      ElMessage.success(`${action}成功`);
    } else {
      row.status = oldStatus;
      ElMessage.error(response.message || `${action}失败`);
    }
  } catch (error) {
    console.error(`${action}失败:`, error);
    row.status = oldStatus;
    ElMessage.error(`${action}失败`);
  }
};

const handleBatchEnable = async () => {
  if (selectedRows.value.length === 0) return;
  
  ElMessageBox.confirm(`确定要批量启用 ${selectedRows.value.length} 张时效卡吗？`, "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "info"
  })
    .then(async () => {
      try {
        const response = await batchTimeCardStatus({
          ids: selectedRows.value.map(row => row.id),
          status: 1
        });
        if (response.code === 200) {
          ElMessage.success("批量启用成功");
          getList();
        } else {
          ElMessage.error(response.message || "批量启用失败");
        }
      } catch (error) {
        console.error('批量启用失败:', error);
        ElMessage.error('批量启用失败');
      }
    })
    .catch(() => {});
};

const handleBatchDisable = async () => {
  if (selectedRows.value.length === 0) return;
  
  ElMessageBox.confirm(`确定要批量禁用 ${selectedRows.value.length} 张时效卡吗？`, "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      try {
        const response = await batchTimeCardStatus({
          ids: selectedRows.value.map(row => row.id),
          status: 0
        });
        if (response.code === 200) {
          ElMessage.success("批量禁用成功");
          getList();
        } else {
          ElMessage.error(response.message || "批量禁用失败");
        }
      } catch (error) {
        console.error('批量禁用失败:', error);
        ElMessage.error('批量禁用失败');
      }
    })
    .catch(() => {});
};

const handleSubmit = async () => {
  if (!formRef.value) return;
  
  if (formData.id && !hasAuth('card:time:edit')) {
    ElMessage.warning('无编辑权限');
    return;
  }
  
  if (!formData.id && !hasAuth('card:time:add')) {
    ElMessage.warning('无新增权限');
    return;
  }
  
  try {
    await formRef.value.validate();
    
    const submitData = { ...formData };
    
    if (formData.id) {
      const response = await updateTimeCard(Number(formData.id), submitData);
      if (response.code === 200) {
        ElMessage.success('更新成功');
      } else {
        ElMessage.error(response.message || '更新失败');
        return;
      }
    } else {
      const response = await addTimeCard(submitData);
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

const handleAddProject = () => {
  selectedProjectIds.value = formData.projects.map((p: any) => p.projectId);
  projectSearchKeyword.value = '';
  projectSelectVisible.value = true;
};

const handleAddProduct = () => {
  selectedProductIds.value = formData.products.map((p: any) => p.productId);
  productSearchKeyword.value = '';
  productSelectVisible.value = true;
};

const updateProjectTotalPrice = (index: number) => {
  const project = formData.projects[index];
  if (project) {
    project.totalPrice = Number((project.times * project.unitPrice).toFixed(2));
  }
};

const updateProjectUnitPrice = (index: number) => {
  const project = formData.projects[index];
  if (project && project.times > 0) {
    project.unitPrice = Number((project.totalPrice / project.times).toFixed(2));
  }
};

const updateProductTotalPrice = (index: number) => {
  const product = formData.products[index];
  if (product) {
    product.totalPrice = Number((product.times * product.unitPrice).toFixed(2));
  }
};

const updateProductUnitPrice = (index: number) => {
  const product = formData.products[index];
  if (product && product.times > 0) {
    product.unitPrice = Number((product.totalPrice / product.times).toFixed(2));
  }
};

const handleDeleteProject = (index: number) => {
  formData.projects.splice(index, 1);
};

const handleDeleteProduct = (index: number) => {
  formData.products.splice(index, 1);
};

const handleProjectBindTypeChange = (value: number) => {
  if (value === 1 && formData.projects.length > 1) {
    formData.projects = formData.projects.slice(0, 1);
    selectedProjectIds.value = formData.projects.map((p: any) => p.projectId);
  }
  if (value === 3) {
    formData.projects = [];
    selectedProjectIds.value = [];
  }
};

const toggleProjectSelection = (projectId: number) => {
  if (formData.projectBindType === 1) {
    selectedProjectIds.value = [projectId];
  } else {
    const index = selectedProjectIds.value.indexOf(projectId);
    if (index > -1) {
      selectedProjectIds.value.splice(index, 1);
    } else {
      selectedProjectIds.value.push(projectId);
    }
  }
};

const toggleProductSelection = (productId: number) => {
  const index = selectedProductIds.value.indexOf(productId);
  if (index > -1) {
    selectedProductIds.value.splice(index, 1);
  } else {
    selectedProductIds.value.push(productId);
  }
};

const handleProjectSelectConfirm = () => {
  const existingIds = formData.projects.map((p: any) => p.projectId);
  
  selectedProjectIds.value.forEach(projectId => {
    if (!existingIds.includes(projectId)) {
      const project = props.projectList.find((p: any) => p.id === projectId);
      if (project) {
        formData.projects.push({
          projectId: project.id,
          projectName: project.projectName,
          times: 1,
          unitPrice: project.singleSalePrice || 0,
          totalPrice: project.singleSalePrice || 0,
          consume: 0,
          manualSalary: 0
        });
      }
    }
  });
  
  formData.projects = formData.projects.filter((p: any) => 
    selectedProjectIds.value.includes(p.projectId)
  );
  
  projectSelectVisible.value = false;
};

const handleProductSelectConfirm = () => {
  const existingIds = formData.products.map((p: any) => p.productId);
  
  selectedProductIds.value.forEach(productId => {
    if (!existingIds.includes(productId)) {
      const product = props.productList.find((p: any) => p.id === productId);
      if (product) {
        formData.products.push({
          productId: product.id,
          productName: product.productName,
          times: 1,
          unitPrice: product.salePrice || 0,
          totalPrice: product.salePrice || 0,
          manualSalary: 0
        });
      }
    }
  });
  
  formData.products = formData.products.filter((p: any) => 
    selectedProductIds.value.includes(p.productId)
  );
  
  productSelectVisible.value = false;
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

.project-card,
.product-card {
  transition: all 0.2s ease;
}

.project-card:hover,
.product-card:hover {
  transform: translateY(-1px);
}

.border-primary {
  border-color: var(--el-color-primary);
}
</style>
