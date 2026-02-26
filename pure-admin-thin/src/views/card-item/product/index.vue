<!--
  产品管理页面
  
  功能说明：
  1. 产品管理 - 管理产品信息，包括产品名称、编码、价格、规格、库存等
  2. 产品分类 - 管理产品分类，按部门划分
  
  页面结构：
  - 产品管理标签页：产品列表、搜索、新增/编辑/删除产品
  - 产品分类标签页：分类列表、搜索、新增/编辑/删除分类
  
  权限控制：
  - product:product:view - 查看产品
  - product:product:add - 新增产品
  - product:product:edit - 编辑产品
  - product:product:delete - 删除产品
  - product:category:view - 查看产品分类
  - product:category:add - 新增产品分类
  - product:category:edit - 编辑产品分类
  - product:category:delete - 删除产品分类
  
  数据表：
  - card_product - 产品表
  - card_product_category - 产品分类表
-->
<template>
  <div class="product-container">
    <el-card class="h-full flex flex-col">
      <!-- 主标签页：产品管理 / 产品分类 -->
      <el-tabs v-model="activeTab" @tab-click="handleTabClick">
        <!-- 产品管理标签页 -->
        <el-tab-pane label="产品管理" name="product">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('product:product:view')">
              <!-- 搜索栏和新增按钮 -->
              <div class="mb-4 flex justify-between items-center">
                <div class="search-bar flex-grow">
                  <el-form :inline="true" :model="productSearchForm" class="w-full">
                    <el-form-item label="产品名称">
                      <el-input v-model="productSearchForm.productName" placeholder="请输入产品名称" />
                    </el-form-item>
                    <el-form-item label="所属分类">
                      <el-select v-model="productSearchForm.categoryId" placeholder="请选择分类" clearable style="width: 200px">
                        <el-option v-for="category in categoryList" :key="category.id" :label="category.categoryName" :value="category.id" />
                      </el-select>
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary" @click="handleProductSearch">
                        <el-icon><Search /></el-icon>
                        搜索
                      </el-button>
                      <el-button @click="resetProductSearch">
                        <el-icon><Refresh /></el-icon>
                        重置
                      </el-button>
                    </el-form-item>
                  </el-form>
                </div>
                <el-button v-if="hasAuth('product:product:add')" type="primary" class="ml-4" @click="handleAddProduct">
                  <el-icon><Plus /></el-icon>
                  新增产品
                </el-button>
              </div>

              <!-- 产品列表表格 -->
              <div class="flex-1 min-h-0">
                <el-table v-loading="productLoading" :data="productList" style="width: 100%" class="h-full" :max-height="`calc(100vh - 320px)`">
                  <el-table-column prop="id" label="ID" width="60" />
                  <el-table-column prop="productCode" label="编码" width="80" />
                  <el-table-column prop="productName" label="产品名称" min-width="120" />
                  <el-table-column label="分类" width="100">
                    <template #default="scope">
                      {{ getCategoryName(scope.row.categoryId) }}
                    </template>
                  </el-table-column>
                  <el-table-column prop="unit" label="单位" width="60" />
                  <el-table-column prop="specification" label="规格" width="80" />
                  <el-table-column label="原价" width="80">
                    <template #default="scope">{{ scope.row.originalPrice || 0 }}元</template>
                  </el-table-column>
                  <el-table-column label="售价" width="80">
                    <template #default="scope">{{ scope.row.salePrice || 0 }}元</template>
                  </el-table-column>
                  <el-table-column label="状态" width="80">
                    <template #default="scope">
                      <el-tag :type="scope.row.status === 1 ? 'success' : 'info'">{{ scope.row.status === 1 ? '上线' : '下线' }}</el-tag>
                    </template>
                  </el-table-column>
                  <el-table-column prop="createTime" label="创建时间" width="160" />
                  <el-table-column label="操作" width="150" fixed="right">
                    <template #default="scope">
                      <el-button v-if="hasAuth('product:product:edit')" type="primary" size="small" @click="handleEditProduct(scope.row)">编辑</el-button>
                      <el-button v-if="hasAuth('product:product:delete')" type="danger" size="small" @click="handleDeleteProduct(scope.row.id)">删除</el-button>
                    </template>
                  </el-table-column>
                </el-table>

                <!-- 分页 -->
                <div class="pagination mt-4">
                  <el-pagination
                    v-model:current-page="productPagination.current"
                    v-model:page-size="productPagination.pageSize"
                    :page-sizes="[10, 20, 50, 100]"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="productPagination.total"
                    @size-change="handleProductSizeChange"
                    @current-change="handleProductCurrentChange"
                  />
                </div>
              </div>
            </div>
            <div v-else class="no-permission flex-1 flex items-center justify-center">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>

        <!-- 产品分类标签页 -->
        <el-tab-pane label="产品分类" name="category">
          <div class="tab-content flex flex-col h-full">
            <div v-if="hasAuth('product:category:view')">
              <!-- 搜索栏和新增按钮 -->
              <div class="mb-4 flex justify-between items-center">
                <div class="search-bar flex-grow">
                  <el-form :inline="true" :model="categorySearchForm" class="w-full">
                    <el-form-item>
                      <el-segmented v-model="categorySearchForm.departmentId" :options="departmentSegmentedOptions" @change="handleCategorySearch" />
                    </el-form-item>
                    <el-form-item label="分类名称">
                      <el-input v-model="categorySearchForm.categoryName" placeholder="请输入分类名称" />
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary" @click="handleCategorySearch">搜索</el-button>
                      <el-button @click="resetCategorySearch">重置</el-button>
                    </el-form-item>
                  </el-form>
                </div>
                <el-button v-if="hasAuth('product:category:add')" type="primary" class="ml-4" @click="handleAddCategory">新增分类</el-button>
              </div>

              <!-- 分类列表表格 -->
              <div class="flex-1 min-h-0">
                <el-table v-loading="categoryLoading" :data="categoryList" style="width: 100%" :max-height="`calc(100vh - 320px)`">
                  <el-table-column prop="id" label="ID" width="80" />
                  <el-table-column prop="categoryName" label="分类名称" />
                  <el-table-column label="所属部门">
                    <template #default="scope">{{ getDepartmentName(scope.row.departmentId) }}</template>
                  </el-table-column>
                  <el-table-column prop="sort" label="排序" />
                  <el-table-column prop="createTime" label="创建时间" />
                  <el-table-column label="操作" width="180">
                    <template #default="scope">
                      <el-button v-if="hasAuth('product:category:edit')" type="primary" size="small" @click="handleEditCategory(scope.row)">编辑</el-button>
                      <el-button v-if="hasAuth('product:category:delete')" type="danger" size="small" @click="handleDeleteCategory(scope.row.id)">删除</el-button>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </div>
            <div v-else class="no-permission flex-1 flex items-center justify-center">
              <el-empty description="无权限查看数据" />
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 产品新增/编辑对话框 -->
    <el-dialog v-model="productDialogVisible" :title="productDialogTitle" width="800px" top="5vh">
      <el-form ref="productFormRef" :model="productForm" :rules="productRules" label-width="100px">
        <el-tabs v-model="productFormTab">
          <!-- 基本信息标签页 -->
          <el-tab-pane label="基本信息" name="basic">
            <!-- 产品名称和编码 -->
            <div class="flex gap-4">
              <el-form-item label="产品名称" prop="productName" class="flex-1">
                <el-input v-model="productForm.productName" placeholder="请输入产品名称" @input="generateProductCode" />
              </el-form-item>
              <el-form-item label="产品编码" class="flex-1">
                <el-input v-model="productForm.productCode" placeholder="自动生成" />
              </el-form-item>
            </div>
            <!-- 外部显示名和条码 -->
            <div class="flex gap-4">
              <el-form-item label="外部显示名" class="flex-1">
                <el-input v-model="productForm.externalName" placeholder="展示给顾客的名字" />
              </el-form-item>
              <el-form-item label="条码" class="flex-1">
                <el-input v-model="productForm.barcode" placeholder="请输入条码" />
              </el-form-item>
            </div>
            <!-- 所属品类和供应商 -->
            <div class="flex gap-4">
              <el-form-item label="所属品类" prop="categoryId" class="flex-1">
                <el-select v-model="productForm.categoryId" placeholder="请选择分类" style="width: 100%">
                  <el-option v-for="category in categoryList" :key="category.id" :label="category.categoryName" :value="category.id" />
                </el-select>
              </el-form-item>
              <el-form-item label="供应商" class="flex-1">
                <el-select v-model="productForm.supplierId" placeholder="请选择供应商" style="width: 100%">
                  <el-option v-for="supplier in supplierList" :key="supplier.id" :label="supplier.supplierName" :value="supplier.id" />
                </el-select>
              </el-form-item>
            </div>
            <!-- 批准文号和单位 -->
            <div class="flex gap-4">
              <el-form-item label="批准文号" class="flex-1">
                <el-input v-model="productForm.approvalNumber" placeholder="请输入批准文号" />
              </el-form-item>
              <el-form-item label="单位" class="flex-1">
                <el-select v-model="productForm.unit" placeholder="请选择单位" style="width: 100%" allow-create filterable>
                  <el-option label="支" value="支" />
                  <el-option label="瓶" value="瓶" />
                  <el-option label="盒" value="盒" />
                  <el-option label="套" value="套" />
                  <el-option label="件" value="件" />
                  <el-option label="包" value="包" />
                  <el-option label="袋" value="袋" />
                </el-select>
              </el-form-item>
            </div>
            <!-- 规格与价格分隔线 -->
            <el-divider content-position="left">规格与价格</el-divider>
            <!-- 规格和到期日期 -->
            <div class="flex gap-4">
              <el-form-item label="规格" class="flex-1">
                <div class="flex gap-2 w-full">
                  <el-input-number v-model="productForm.specificationNum" :min="0" :precision="2" placeholder="数量" style="width: 50%" />
                  <el-select v-model="productForm.specificationUnit" placeholder="单位" style="width: 50%" allow-create filterable>
                    <el-option label="毫升(ml)" value="ml" />
                    <el-option label="升(L)" value="L" />
                    <el-option label="克(g)" value="g" />
                    <el-option label="千克(kg)" value="kg" />
                    <el-option label="件" value="件" />
                    <el-option label="个" value="个" />
                    <el-option label="包" value="包" />
                    <el-option label="片" value="片" />
                    <el-option label="盒" value="盒" />
                  </el-select>
                </div>
              </el-form-item>
              <el-form-item label="到期日期" class="flex-1">
                <el-date-picker v-model="productForm.expiryDate" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
              </el-form-item>
            </div>
            <!-- 原价和销售价格 -->
            <div class="flex gap-4">
              <el-form-item label="原价" class="flex-1">
                <el-input-number v-model="productForm.originalPrice" :min="0" :precision="2" style="width: 100%" />
              </el-form-item>
              <el-form-item label="销售价格" class="flex-1">
                <el-input-number v-model="productForm.salePrice" :min="0" :precision="2" style="width: 100%" />
              </el-form-item>
            </div>
            <!-- 体验价格和进货价格 -->
            <div class="flex gap-4">
              <el-form-item label="体验价格" class="flex-1">
                <el-input-number v-model="productForm.experiencePrice" :min="0" :precision="2" style="width: 100%" />
              </el-form-item>
              <el-form-item label="进货价格" class="flex-1">
                <el-input-number v-model="productForm.purchasePrice" :min="0" :precision="2" style="width: 100%" />
              </el-form-item>
            </div>
            <!-- 库存下限和上限 -->
            <div class="flex gap-4">
              <el-form-item label="库存下限" class="flex-1">
                <el-input-number v-model="productForm.stockMin" :min="0" style="width: 100%" />
              </el-form-item>
              <el-form-item label="库存上限" class="flex-1">
                <el-input-number v-model="productForm.stockMax" :min="0" style="width: 100%" />
              </el-form-item>
            </div>
            <!-- 上线日期和下线日期 -->
            <div class="flex gap-4">
              <el-form-item label="上线日期" class="flex-1">
                <el-date-picker v-model="productForm.onlineDate" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
              </el-form-item>
              <el-form-item label="下线日期" class="flex-1">
                <el-date-picker v-model="productForm.offlineDate" type="date" placeholder="选择日期" style="width: 100%" value-format="YYYY-MM-DD" />
              </el-form-item>
            </div>
            <!-- 状态 -->
            <div class="flex gap-4">
              <el-form-item label="状态" class="flex-1">
                <el-switch v-model="productForm.status" :active-value="1" :inactive-value="0" active-text="上线" inactive-text="下线" />
              </el-form-item>
            </div>
            <!-- 备注 -->
            <el-form-item label="备注">
              <el-input v-model="productForm.remark" type="textarea" placeholder="请输入备注" />
            </el-form-item>
          </el-tab-pane>

          <!-- 限定设置标签页 -->
          <el-tab-pane label="限定设置" name="limit">
            <!-- 分店限定分隔线 -->
            <el-divider content-position="left">分店限定</el-divider>
            <!-- 每月限次和消费间隔 -->
            <div class="flex gap-4">
              <el-form-item label="每月限次" class="flex-1">
                <el-input v-model.number="productForm.monthlyLimit" type="number" placeholder="请输入次数" style="width: 100%">
                  <template #suffix>次</template>
                </el-input>
              </el-form-item>
              <el-form-item label="消费间隔" class="flex-1">
                <el-input v-model.number="productForm.consumptionInterval" type="number" placeholder="请输入天数" style="width: 100%">
                  <template #suffix>天</template>
                </el-input>
              </el-form-item>
            </div>
            <!-- 限定销售分店 -->
            <el-form-item label="限定销售分店">
              <el-select v-model="productForm.limitedSaleStores" multiple placeholder="请选择分店" style="width: 100%">
                <el-option v-for="store in storeList" :key="store.id" :label="store.storeName" :value="store.id" />
              </el-select>
            </el-form-item>
            <!-- 限定消耗分店 -->
            <el-form-item label="限定消耗分店">
              <el-select v-model="productForm.limitedConsumeStores" multiple placeholder="请选择分店" style="width: 100%">
                <el-option v-for="store in storeList" :key="store.id" :label="store.storeName" :value="store.id" />
              </el-select>
            </el-form-item>
            <!-- 部门限定分隔线 -->
            <el-divider content-position="left">部门限定</el-divider>
            <!-- 限定销售部门 -->
            <el-form-item label="限定销售部门">
              <el-select v-model="productForm.limitedSaleDepts" multiple placeholder="请选择部门" style="width: 100%">
                <el-option v-for="dept in coreDepartmentList" :key="dept.id" :label="dept.deptName" :value="dept.id" />
              </el-select>
            </el-form-item>
            <!-- 限定消耗部门 -->
            <el-form-item label="限定消耗部门">
              <el-select v-model="productForm.limitedConsumeDepts" multiple placeholder="请选择部门" style="width: 100%">
                <el-option v-for="dept in coreDepartmentList" :key="dept.id" :label="dept.deptName" :value="dept.id" />
              </el-select>
            </el-form-item>
            <!-- 功能开关分隔线 -->
            <el-divider content-position="left">功能开关</el-divider>
            <!-- 功能开关复选框组 -->
            <el-form-item label="">
              <el-checkbox-group v-model="featuresCheckboxGroup">
                <el-checkbox label="noDiscount">充值卡不打折</el-checkbox>
                <el-checkbox label="allowGift">允许赠送</el-checkbox>
                <el-checkbox label="noConsumption">不计消耗</el-checkbox>
                <el-checkbox label="noModify">禁止修改</el-checkbox>
                <el-checkbox label="isCooperative">合作产品</el-checkbox>
                <el-checkbox label="isYm">YM产品</el-checkbox>
                <el-checkbox label="isSpecial">特项产品</el-checkbox>
              </el-checkbox-group>
            </el-form-item>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="productDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmitProduct">确定</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 产品分类新增/编辑对话框 -->
    <el-dialog v-model="categoryDialogVisible" :title="categoryDialogTitle" width="500px">
      <el-form ref="categoryFormRef" :model="categoryForm" :rules="categoryRules" label-width="100px">
        <el-form-item label="分类名称" prop="categoryName">
          <el-input v-model="categoryForm.categoryName" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="所属部门" prop="departmentId">
          <el-select v-model="categoryForm.departmentId" placeholder="请选择所属部门" style="width: 100%">
            <el-option v-for="dept in departmentList" :key="dept.id" :label="dept.deptName" :value="dept.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序">
          <el-input v-model.number="categoryForm.sort" type="number" placeholder="请输入排序" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="categoryDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmitCategory">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
/**
 * 产品管理页面
 * 
 * 主要功能：
 * 1. 产品管理 - 增删改查产品信息
 * 2. 产品分类管理 - 增删改查产品分类
 * 
 * 依赖组件：
 * - el-tabs: 标签页切换
 * - el-table: 数据表格
 * - el-dialog: 弹窗表单
 * - el-form: 表单验证
 */
import { ref, reactive, onMounted, computed, watch } from "vue";
import { Plus, Edit, Delete, Search, Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox, type FormInstance } from "element-plus";
import { hasAuth } from "@/router/utils";
import http from "@/utils/http";
import { pinyin } from "pinyin-pro";

// ==================== 状态定义 ====================

/** 当前激活的标签页 */
const activeTab = ref("product");
/** 产品表单当前激活的标签页 */
const productFormTab = ref("basic");

/** 产品列表加载状态 */
const productLoading = ref(false);
/** 分类列表加载状态 */
const categoryLoading = ref(false);

/** 产品搜索表单 */
const productSearchForm = reactive({ productName: "", categoryId: null });
/** 分类搜索表单 */
const categorySearchForm = reactive({ departmentId: "", categoryName: "" });

/** 产品分页配置 */
const productPagination = reactive({ current: 1, pageSize: 10, total: 0 });

/** 产品列表数据 */
const productList = ref<any[]>([]);
/** 产品分类列表数据 */
const categoryList = ref<any[]>([]);
/** 部门列表数据 */
const departmentList = ref<any[]>([]);
/** 核心业务部门列表数据 */
const coreDepartmentList = ref<any[]>([]);
/** 供应商列表数据 */
const supplierList = ref<any[]>([]);
/** 分店列表数据 */
const storeList = ref<any[]>([]);

/** 产品对话框显示状态 */
const productDialogVisible = ref(false);
/** 产品对话框标题 */
const productDialogTitle = ref("");
/** 产品表单引用 */
const productFormRef = ref<FormInstance>();
/** 当前编辑的产品ID */
const currentProductId = ref<number | null>(null);

/** 分类对话框显示状态 */
const categoryDialogVisible = ref(false);
/** 分类对话框标题 */
const categoryDialogTitle = ref("");
/** 分类表单引用 */
const categoryFormRef = ref<FormInstance>();
/** 当前编辑的分类ID */
const currentCategoryId = ref<number | null>(null);

/** 产品表单数据 */
const productForm = reactive({
  productName: "",
  productCode: "",
  externalName: "",
  barcode: "",
  categoryId: null,
  supplierId: null,
  unit: "",
  monthlyLimit: 0,
  consumptionInterval: 0,
  specificationNum: null as number | null,
  specificationUnit: "",
  specification: "",
  originalPrice: 0,
  salePrice: 0,
  experiencePrice: 0,
  purchasePrice: 0,
  onlineDate: null,
  offlineDate: null,
  stockMin: 0,
  stockMax: 0,
  approvalNumber: "",
  expiryDate: null,
  status: 1,
  remark: "",
  limitedSaleStores: [] as number[],
  limitedConsumeStores: [] as number[],
  limitedSaleDepts: [] as number[],
  limitedConsumeDepts: [] as number[],
  noDiscount: 0,
  allowGift: 0,
  noConsumption: 0,
  noModify: 0,
  isCooperative: 0,
  isYm: 0,
  isSpecial: 0
});

/** 分类表单数据 */
const categoryForm = reactive({ categoryName: "", departmentId: null, sort: 0 });

/** 产品表单验证规则 */
const productRules = reactive({ productName: [{ required: true, message: "请输入产品名称", trigger: "blur" }] });
/** 分类表单验证规则 */
const categoryRules = reactive({
  categoryName: [{ required: true, message: "请输入分类名称", trigger: "blur" }],
  departmentId: [{ required: true, message: "请选择所属部门", trigger: "blur" }]
});

/** 功能开关复选框组 */
const featuresCheckboxGroup = ref<string[]>([]);

// ==================== 监听器 ====================

/** 监听功能开关复选框变化，同步到表单数据 */
watch(featuresCheckboxGroup, (val) => {
  productForm.noDiscount = val.includes("noDiscount") ? 1 : 0;
  productForm.allowGift = val.includes("allowGift") ? 1 : 0;
  productForm.noConsumption = val.includes("noConsumption") ? 1 : 0;
  productForm.noModify = val.includes("noModify") ? 1 : 0;
  productForm.isCooperative = val.includes("isCooperative") ? 1 : 0;
  productForm.isYm = val.includes("isYm") ? 1 : 0;
  productForm.isSpecial = val.includes("isSpecial") ? 1 : 0;
});

// ==================== 计算属性 ====================

/** 部门分段控制器选项 */
const departmentSegmentedOptions = computed(() => {
  const options = [{ label: "全部", value: "" }];
  departmentList.value.forEach((dept: any) => { options.push({ label: dept.deptName, value: String(dept.id) }); });
  return options;
});

// ==================== 生命周期 ====================

/** 页面加载时初始化数据 */
onMounted(() => {
  if (hasAuth("product:product:view")) { getProductList(); getSupplierList(); getStoreList(); getCoreDepartmentList(); }
  if (hasAuth("product:category:view")) { getCategoryList(); getDepartmentList(); }
});

// ==================== 工具方法 ====================

/** 根据产品名称自动生成产品编码（取汉字拼音首字母） */
const generateProductCode = () => {
  if (productForm.productName) {
    const name = productForm.productName;
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
    productForm.productCode = code.substring(0, 10);
  }
};

// ==================== 数据获取方法 ====================

/** 获取产品列表 */
const getProductList = async () => {
  if (!hasAuth("product:product:view")) return;
  productLoading.value = true;
  try {
    const response = await http.get("/api/card-item/get-products", { params: { productName: productSearchForm.productName, categoryId: productSearchForm.categoryId } });
    if (response.code === 200) { productList.value = response.data || []; productPagination.total = response.data?.length || 0; }
  } catch (error) { console.error("获取产品列表失败", error); }
  finally { productLoading.value = false; }
};

/** 获取产品分类列表 */
const getCategoryList = async () => {
  if (!hasAuth("product:category:view")) return;
  categoryLoading.value = true;
  try {
    const response = await http.get("/api/card-item/get-product-categories", { params: { departmentId: categorySearchForm.departmentId, categoryName: categorySearchForm.categoryName } });
    if (response.code === 200) { categoryList.value = response.data || []; }
  } catch (error) { console.error("获取产品分类列表失败", error); }
  finally { categoryLoading.value = false; }
};

/** 获取部门列表 */
const getDepartmentList = async () => {
  try {
    const response = await http.get("/api/card-item/get-core-departments");
    if (response.code === 200) { departmentList.value = response.data || []; }
  } catch (error) { console.error("获取部门列表失败", error); }
};

/** 获取核心业务部门列表 */
const getCoreDepartmentList = async () => {
  try {
    const response = await http.get("/api/card-item/get-core-departments");
    if (response.code === 200) { 
      coreDepartmentList.value = response.data || [];
    }
  } catch (error) { console.error("获取核心业务部门列表失败", error); }
};

/** 获取供应商列表 */
const getSupplierList = async () => {
  try {
    const response = await http.get("/api/card-item/get-suppliers");
    if (response.code === 200) { supplierList.value = response.data || []; }
  } catch (error) { console.error("获取供应商列表失败", error); }
};

/** 获取分店列表 */
const getStoreList = async () => {
  try {
    const response = await http.get("/api/enterprise/store");
    if (response.code === 200) { storeList.value = response.data || []; }
  } catch (error) { console.error("获取分店列表失败", error); }
};

// ==================== 辅助方法 ====================

/** 根据分类ID获取分类名称 */
const getCategoryName = (categoryId: number) => { const category = categoryList.value.find((c: any) => c.id === categoryId); return category ? category.categoryName : ""; };
/** 根据部门ID获取部门名称 */
const getDepartmentName = (departmentId: number) => { const dept = departmentList.value.find((d: any) => d.id === departmentId); return dept ? dept.deptName : ""; };

// ==================== 事件处理方法 ====================

/** 标签页切换处理 */
const handleTabClick = (tab: any) => {
  if (tab.props.name === "category" && hasAuth("product:category:view")) { getCategoryList(); getDepartmentList(); }
  if (tab.props.name === "product" && hasAuth("product:product:view")) { getProductList(); }
};

/** 产品搜索 */
const handleProductSearch = () => { productPagination.current = 1; getProductList(); };
/** 重置产品搜索 */
const resetProductSearch = () => { productSearchForm.productName = ""; productSearchForm.categoryId = null; productPagination.current = 1; getProductList(); };
/** 分类搜索 */
const handleCategorySearch = () => { getCategoryList(); };
/** 重置分类搜索 */
const resetCategorySearch = () => { categorySearchForm.categoryName = ""; categorySearchForm.departmentId = ""; getCategoryList(); };
/** 产品分页大小变化 */
const handleProductSizeChange = (size: number) => { productPagination.pageSize = size; getProductList(); };
/** 产品当前页变化 */
const handleProductCurrentChange = (current: number) => { productPagination.current = current; getProductList(); };

// ==================== 表单操作方法 ====================

/** 重置产品表单 */
const resetProductForm = () => {
  productFormRef.value?.resetFields();
  const allStoreIds = storeList.value.map((s: any) => s.id);
  const allDeptIds = coreDepartmentList.value.map((d: any) => d.id);
  Object.assign(productForm, { productName: "", productCode: "", externalName: "", barcode: "", categoryId: null, supplierId: null, unit: "", monthlyLimit: 0, consumptionInterval: 0, specificationNum: null, specificationUnit: "", specification: "", originalPrice: 0, salePrice: 0, experiencePrice: 0, purchasePrice: 0, onlineDate: null, offlineDate: null, stockMin: 0, stockMax: 0, approvalNumber: "", expiryDate: null, status: 1, remark: "", limitedSaleStores: [...allStoreIds], limitedConsumeStores: [...allStoreIds], limitedSaleDepts: [...allDeptIds], limitedConsumeDepts: [...allDeptIds], noDiscount: 0, allowGift: 0, noConsumption: 0, noModify: 0, isCooperative: 0, isYm: 0, isSpecial: 0 });
  featuresCheckboxGroup.value = [];
  productFormTab.value = "basic";
};

/** 重置分类表单 */
const resetCategoryForm = () => { categoryFormRef.value?.resetFields(); Object.assign(categoryForm, { categoryName: "", departmentId: null, sort: 0 }); };

/** 新增产品 */
const handleAddProduct = () => { resetProductForm(); productDialogTitle.value = "新增产品"; currentProductId.value = null; productDialogVisible.value = true; };

/** 编辑产品 */
const handleEditProduct = (row: any) => {
  let specificationNum = null;
  let specificationUnit = "";
  if (row.specification) {
    const match = row.specification.match(/^(\d+\.?\d*)\s*(.*)$/);
    if (match) {
      specificationNum = parseFloat(match[1]);
      specificationUnit = match[2] || "";
    }
  }
  Object.assign(productForm, {
    productName: row.productName, productCode: row.productCode || "", externalName: row.externalName || "", barcode: row.barcode || "",
    categoryId: row.categoryId, supplierId: row.supplierId,
    unit: row.unit || "",
    monthlyLimit: row.monthlyLimit || 0, consumptionInterval: row.consumptionInterval || 0,
    specificationNum, specificationUnit, specification: row.specification || "",
    originalPrice: row.originalPrice || 0, salePrice: row.salePrice || 0, experiencePrice: row.experiencePrice || 0, purchasePrice: row.purchasePrice || 0,
    onlineDate: row.onlineDate, offlineDate: row.offlineDate, stockMin: row.stockMin || 0, stockMax: row.stockMax || 0, approvalNumber: row.approvalNumber || "",
    expiryDate: row.expiryDate, status: row.status ?? 1, remark: row.remark || "",
    limitedSaleStores: row.limitedSaleStores || [], limitedConsumeStores: row.limitedConsumeStores || [],
    limitedSaleDepts: row.limitedSaleDepts || [], limitedConsumeDepts: row.limitedConsumeDepts || [],
    noDiscount: row.noDiscount || 0, allowGift: row.allowGift || 0, noConsumption: row.noConsumption || 0, noModify: row.noModify || 0,
    isCooperative: row.isCooperative || 0, isYm: row.isYm || 0, isSpecial: row.isSpecial || 0
  });
  featuresCheckboxGroup.value = [];
  if (row.noDiscount) featuresCheckboxGroup.value.push("noDiscount");
  if (row.allowGift) featuresCheckboxGroup.value.push("allowGift");
  if (row.noConsumption) featuresCheckboxGroup.value.push("noConsumption");
  if (row.noModify) featuresCheckboxGroup.value.push("noModify");
  if (row.isCooperative) featuresCheckboxGroup.value.push("isCooperative");
  if (row.isYm) featuresCheckboxGroup.value.push("isYm");
  if (row.isSpecial) featuresCheckboxGroup.value.push("isSpecial");
  productDialogTitle.value = "编辑产品";
  currentProductId.value = row.id;
  productFormTab.value = "basic";
  productDialogVisible.value = true;
};

/** 删除产品 */
const handleDeleteProduct = async (id: number) => {
  ElMessageBox.confirm("确定要删除该产品吗？", "警告", { confirmButtonText: "确定", cancelButtonText: "取消", type: "warning" })
    .then(async () => {
      productLoading.value = true;
      try {
        const response = await http.delete(`/api/card-item/delete-product/${id}`);
        if (response.code === 200) { ElMessage.success("删除成功"); getProductList(); }
        else { ElMessage.error(response.message || "删除失败"); }
      } catch (error) { console.error("删除产品失败", error); ElMessage.error("网络错误，请稍后重试"); }
      finally { productLoading.value = false; }
    }).catch(() => {});
};

/** 提交产品表单 */
const handleSubmitProduct = async () => {
  if (!productFormRef.value) return;
  try {
    await productFormRef.value.validate();
    productLoading.value = true;
    const specification = productForm.specificationNum && productForm.specificationUnit 
      ? `${productForm.specificationNum}${productForm.specificationUnit}` 
      : "";
    const submitData = { 
      ...productForm, 
      specification,
      limitedSaleDepts: productForm.limitedSaleDepts,
      limitedConsumeDepts: productForm.limitedConsumeDepts
    };
    let response;
    if (currentProductId.value) { response = await http.put(`/api/card-item/update-product/${currentProductId.value}`, { data: submitData }); }
    else { response = await http.post("/api/card-item/add-product", { data: submitData }); }
    if (response.code === 200) { productDialogVisible.value = false; ElMessage.success(currentProductId.value ? "编辑成功" : "新增成功"); getProductList(); }
    else { ElMessage.error(response.message || "操作失败"); }
  } catch (error) { console.error("操作失败", error); ElMessage.error("网络错误，请稍后重试"); }
  finally { productLoading.value = false; }
};

/** 新增分类 */
const handleAddCategory = () => { resetCategoryForm(); categoryDialogTitle.value = "新增分类"; currentCategoryId.value = null; categoryDialogVisible.value = true; };
/** 编辑分类 */
const handleEditCategory = (row: any) => { Object.assign(categoryForm, { categoryName: row.categoryName, departmentId: row.departmentId, sort: row.sort || 0 }); categoryDialogTitle.value = "编辑分类"; currentCategoryId.value = row.id; categoryDialogVisible.value = true; };

/** 删除分类 */
const handleDeleteCategory = async (id: number) => {
  ElMessageBox.confirm("确定要删除该分类吗？", "警告", { confirmButtonText: "确定", cancelButtonText: "取消", type: "warning" })
    .then(async () => {
      categoryLoading.value = true;
      try {
        const response = await http.delete(`/api/card-item/delete-product-category/${id}`);
        if (response.code === 200) { ElMessage.success("删除成功"); getCategoryList(); }
        else { ElMessage.error(response.message || "删除失败"); }
      } catch (error) { console.error("删除分类失败", error); ElMessage.error("网络错误，请稍后重试"); }
      finally { categoryLoading.value = false; }
    }).catch(() => {});
};

/** 提交分类表单 */
const handleSubmitCategory = async () => {
  if (!categoryFormRef.value) return;
  try {
    await categoryFormRef.value.validate();
    categoryLoading.value = true;
    const submitData = { ...categoryForm };
    let response;
    if (currentCategoryId.value) { response = await http.put(`/api/card-item/update-product-category/${currentCategoryId.value}`, { data: submitData }); }
    else { response = await http.post("/api/card-item/add-product-category", { data: submitData }); }
    if (response.code === 200) { categoryDialogVisible.value = false; ElMessage.success(currentCategoryId.value ? "编辑成功" : "新增成功"); getCategoryList(); }
    else { ElMessage.error(response.message || "操作失败"); }
  } catch (error) { console.error("操作失败", error); ElMessage.error("网络错误，请稍后重试"); }
  finally { categoryLoading.value = false; }
};
</script>

<style scoped>
.product-container { min-height: calc(100vh - 120px); }
.pagination { display: flex; justify-content: flex-end; }
.dialog-footer { display: flex; justify-content: flex-end; }
</style>
