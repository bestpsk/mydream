<!--
  卡项管理页面
  
  功能说明：
  管理三种类型的卡片：
  1. 充值卡 - 预充值消费卡，支持折扣、配赠项目/产品
  2. 套餐卡 - 包含固定项目和产品的套餐
  3. 时效卡 - 按时间限制的消费卡
  
  页面结构：
  - 充值卡标签页：充值卡管理组件
  - 套餐卡标签页：套餐卡管理组件
  - 时效卡标签页：时效卡管理组件
  
  权限控制：
  - card:recharge:view - 查看充值卡
  - card:package:view - 查看套餐卡
  - card:time:view - 查看时效卡
  
  数据表：
  - card_recharge - 充值卡表
  - card_package - 套餐卡表
  - card_time - 时效卡表
  
  子组件：
  - RechargeCard.vue - 充值卡管理
  - PackageCard.vue - 套餐卡管理
  - TimeCard.vue - 时效卡管理
-->
<template>
  <div class="card-container">
    <el-card class="h-full flex flex-col">
      <!-- 卡片类型标签页 -->
      <el-tabs v-model="activeTab" @tab-click="handleTabClick">
        <!-- 充值卡标签页 -->
        <el-tab-pane label="充值卡" name="recharge">
          <RechargeCard
            v-if="hasAuth('card:recharge:view')"
            :store-list="storeList"
            :department-list="departmentList"
            :project-list="projectList"
            :product-list="productList"
          />
          <div v-else class="no-permission flex-1 flex items-center justify-center">
            <el-empty description="无权限查看数据" />
          </div>
        </el-tab-pane>
        
        <!-- 套餐卡标签页 -->
        <el-tab-pane label="套餐卡" name="package">
          <PackageCard
            v-if="hasAuth('card:package:view')"
            :store-list="storeList"
            :department-list="departmentList"
            :project-list="projectList"
            :product-list="productList"
          />
          <div v-else class="no-permission flex-1 flex items-center justify-center">
            <el-empty description="无权限查看数据" />
          </div>
        </el-tab-pane>
        
        <!-- 时效卡标签页 -->
        <el-tab-pane label="时效卡" name="time">
          <TimeCard
            v-if="hasAuth('card:time:view')"
            :store-list="storeList"
            :department-list="departmentList"
            :project-list="projectList"
            :product-list="productList"
          />
          <div v-else class="no-permission flex-1 flex items-center justify-center">
            <el-empty description="无权限查看数据" />
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup lang="ts">
/**
 * 卡项管理页面脚本
 * 
 * 主要功能：
 * 1. 标签页切换 - 在充值卡、套餐卡、时效卡之间切换
 * 2. 数据加载 - 加载分店、部门、项目、产品列表供子组件使用
 * 3. 数据缓存 - 使用 dataCacheStore 缓存常用数据
 */
import { ref, onMounted } from "vue";
import { hasAuth } from "@/router/utils";
import { useUserStoreHook } from "@/store/modules/user";
import { useDataCacheStoreHook } from "@/store/modules/dataCache";
import http from '@/utils/http';
import RechargeCard from './RechargeCard.vue';
import PackageCard from './PackageCard.vue';
import TimeCard from './TimeCard.vue';

// ==================== 状态定义 ====================

/** 当前激活的标签页 */
const activeTab = ref("recharge");

/** 用户存储 */
const userStore = useUserStoreHook();

/** 数据缓存存储 */
const dataCacheStore = useDataCacheStoreHook();

/** 当前公司ID */
const companyId = ref(userStore.companyId || 0);

/** 分店列表 - 传递给子组件 */
const storeList = ref<any[]>([]);

/** 部门列表 - 传递给子组件 */
const departmentList = ref<any[]>([]);

/** 项目列表 - 传递给子组件 */
const projectList = ref<any[]>([]);

/** 产品列表 - 传递给子组件 */
const productList = ref<any[]>([]);

// ==================== 生命周期 ====================

/**
 * 组件挂载时初始化数据
 * 根据权限加载对应的数据列表
 */
onMounted(() => {
  companyId.value = userStore.companyId || 0;
  
  if (hasAuth("card:recharge:view") || hasAuth("card:package:view")) {
    getDepartments();
    getStores();
    getProjects();
    getProducts();
  }
});

// ==================== 事件处理方法 ====================

/**
 * 处理标签页切换
 * 切换时按需加载对应的数据
 * @param tab - 标签页实例
 */
const handleTabClick = (tab: any) => {
  if (tab.props.name === "recharge" && hasAuth("card:recharge:view")) {
    companyId.value = userStore.companyId || 0;
    if (departmentList.value.length === 0) {
      getDepartments();
    }
    if (storeList.value.length === 0) {
      getStores();
    }
  }
};

// ==================== 数据获取方法 ====================

/**
 * 获取部门列表
 * 优先使用缓存数据，缓存过期则从API获取
 * 只返回启用分类的核心业务部门
 */
const getDepartments = async () => {
  if (!dataCacheStore.isDepartmentsExpired && dataCacheStore.cachedDepartments.length > 0) {
    const uniqueDepts = dataCacheStore.cachedDepartments
      .filter((dept: any) => dept.enable_category)
      .map((dept: any) => ({
        id: dept.id,
        name: dept.name
      }));
    departmentList.value = uniqueDepts;
    return;
  }
  
  try {
    const response = await http.get('/api/enterprise/department', {
      params: {
        company_id: companyId.value
      }
    });
    
    let processedDepartments: any[] = [];
    if (Array.isArray(response.data)) {
      const uniqueDepts: any[] = [];
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
      const uniqueDepts: any[] = [];
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
      return;
    }
    
    dataCacheStore.updateDepartments(processedDepartments);
    
    const coreDepartments = processedDepartments
      .filter(dept => dept.enable_category)
      .map(dept => ({
        id: dept.id,
        name: dept.name
      }));
    departmentList.value = coreDepartments;
  } catch (error) {
    console.error('获取部门列表失败:', error);
  }
};

/**
 * 获取分店列表
 * 优先使用缓存数据，缓存过期则从API获取
 */
const getStores = async () => {
  if (!dataCacheStore.isStoresExpired && dataCacheStore.cachedStores.length > 0) {
    storeList.value = dataCacheStore.cachedStores;
    return;
  }
  
  try {
    const response = await http.get('/api/enterprise/store', {
      params: {
        company_id: companyId.value
      }
    });
    
    let processedStores: any[] = [];
    if (Array.isArray(response.data)) {
      processedStores = response.data.map((store: any) => ({
        id: store.id,
        name: store.storeName
      }));
    } else if (response.data.code === 200) {
      processedStores = response.data.data.map((store: any) => ({
        id: store.id,
        name: store.storeName
      }));
    } else {
      return;
    }
    
    dataCacheStore.updateStores(processedStores);
    storeList.value = processedStores;
  } catch (error) {
    console.error('获取分店列表失败:', error);
  }
};

/**
 * 获取项目列表
 * 用于子组件中选择项目
 */
const getProjects = async () => {
  try {
    const response = await http.get('/api/card-item/get-projects');
    
    if (response.code === 200) {
      projectList.value = response.data;
    }
  } catch (error) {
    console.error('获取项目列表失败:', error);
  }
};

/**
 * 获取产品列表
 * 用于子组件中选择产品
 */
const getProducts = async () => {
  try {
    const response = await http.get('/api/card-item/get-products');
    
    if (response.code === 200) {
      productList.value = response.data;
    }
  } catch (error) {
    console.error('获取产品列表失败:', error);
  }
};
</script>

<style scoped>
.card-container {
  min-height: calc(100vh - 120px);
}

.no-permission {
  min-height: 300px;
}
</style>
