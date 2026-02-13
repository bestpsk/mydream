import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { store } from "../utils";

interface Store {
  id: number;
  name: string;
}

interface Department {
  id: number;
  name: string;
  enable_category: boolean;
}

interface CacheState {
  stores: Store[];
  departments: Department[];
  lastUpdated: {
    stores: number;
    departments: number;
  };
  cacheExpiry: number;
}

export const useDataCacheStore = defineStore("data-cache", {
  state: (): CacheState => ({
    stores: [],
    departments: [],
    lastUpdated: {
      stores: 0,
      departments: 0
    },
    cacheExpiry: 5 * 60 * 1000 // 5分钟缓存有效期
  }),

  getters: {
    // 获取缓存的门店数据
    cachedStores: state => state.stores,

    // 获取缓存的部门数据
    cachedDepartments: state => state.departments,

    // 检查门店数据是否过期
    isStoresExpired: state => {
      return Date.now() - state.lastUpdated.stores > state.cacheExpiry;
    },

    // 检查部门数据是否过期
    isDepartmentsExpired: state => {
      return Date.now() - state.lastUpdated.departments > state.cacheExpiry;
    }
  },

  actions: {
    // 更新门店缓存
    updateStores(stores: Store[]) {
      this.stores = stores;
      this.lastUpdated.stores = Date.now();
    },

    // 更新部门缓存
    updateDepartments(departments: Department[]) {
      this.departments = departments;
      this.lastUpdated.departments = Date.now();
    },

    // 清除缓存
    clearCache() {
      this.stores = [];
      this.departments = [];
      this.lastUpdated = {
        stores: 0,
        departments: 0
      };
    },

    // 清除门店缓存
    clearStoresCache() {
      this.stores = [];
      this.lastUpdated.stores = 0;
    },

    // 清除部门缓存
    clearDepartmentsCache() {
      this.departments = [];
      this.lastUpdated.departments = 0;
    }
  }
});

export function useDataCacheStoreHook() {
  return useDataCacheStore(store);
}
