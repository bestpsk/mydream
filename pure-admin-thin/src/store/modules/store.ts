import { defineStore } from "pinia";
import { http } from "@/utils/http";
import { storageLocal } from "@pureadmin/utils";
import { userKey } from "@/utils/auth";

interface StoreState {
  stores: any[];
  currentStore: any;
  loading: boolean;
}

export const useStoreStore = defineStore("pure-store", {
  state: (): StoreState => ({
    stores: [],
    currentStore: null,
    loading: false
  }),

  getters: {
    getCurrentStore: state => state.currentStore,
    getStores: state => state.stores
  },

  actions: {
    // 初始化门店数据
    async initStores(companyId?: number) {
      this.loading = true;
      try {
        // 从后端获取用户可查看的门店列表
        const params = companyId ? { company_id: companyId } : {};
        const response = await http.request("get", "/api/auth/user-stores", {
          params
        });
        if (response.code === 200) {
          this.stores = response.data;
        }

        // 从本地存储中恢复之前选中的门店
        this.restoreCurrentStore();

        // 如果没有选中的门店，默认选择第一个
        if (!this.currentStore && this.stores.length > 0) {
          this.setCurrentStore(this.stores[0]);
        }
      } catch (error) {
        console.error("获取门店列表失败:", error);
        // 错误处理：如果API调用失败，使用空数组
        this.stores = [];
      } finally {
        this.loading = false;
      }
    },

    // 从本地存储中恢复当前选中的门店
    restoreCurrentStore() {
      const storedStore = storageLocal().getItem<any>("currentStore");
      if (storedStore && this.stores.length > 0) {
        // 确保恢复的门店在 stores 数组中
        const storeInList = this.stores.find(
          store => store.id === storedStore.id
        );
        if (storeInList) {
          this.currentStore = storeInList;
        } else if (this.stores.length > 0) {
          // 如果恢复的门店不在列表中，默认选择第一个
          this.currentStore = this.stores[0];
        }
      }
    },

    // 设置当前选中的门店
    setCurrentStore(store: any) {
      if (store) {
        // 确保设置的门店在 stores 数组中
        const storeInList = this.stores.find(s => s.id === store.id);
        if (storeInList) {
          this.currentStore = storeInList;
        } else {
          this.currentStore = store;
        }
        storageLocal().setItem("currentStore", this.currentStore);
      }
    }
  }
});
