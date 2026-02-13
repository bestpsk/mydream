import { defineStore } from "pinia";
import {
  type cacheType,
  store,
  ascending,
  getKeyList,
  filterTree,
  constantMenus,
  filterNoPermissionTree,
  formatFlatteningRoutes
} from "../utils";
import { useMultiTagsStoreHook } from "./multiTags";

export const usePermissionStore = defineStore("pure-permission", {
  state: () => ({
    // 静态路由生成的菜单
    constantMenus,
    // 整体路由生成的菜单（静态、动态）
    wholeMenus: [],
    // 整体路由（一维数组格式）
    flatteningRoutes: [],
    // 缓存页面keepAlive
    cachePageList: [],
    // 用户拥有的权限列表
    permissions: [],
    // 用户是否为超级管理员
    isSuper: false
  }),
  actions: {
    /** 初始化菜单 */
    initMenus() {
      // 直接使用静态菜单作为整体菜单
      this.wholeMenus = filterNoPermissionTree(
        filterTree(ascending(this.constantMenus))
      );
      this.flatteningRoutes = formatFlatteningRoutes(this.constantMenus as any);
    },
    /** 组装整体路由生成的菜单 */
    handleWholeMenus(routes: any[]) {
      // 确保routes是数组
      const allRoutes = Array.isArray(routes) ? routes : [];
      // 处理菜单
      this.wholeMenus = filterNoPermissionTree(
        filterTree(ascending(allRoutes))
      );
      this.flatteningRoutes = formatFlatteningRoutes(allRoutes as any);
    },
    /** 监听缓存页面是否存在于标签页，不存在则删除 */
    clearCache() {
      let cacheLength = this.cachePageList.length;
      const nameList = getKeyList(useMultiTagsStoreHook().multiTags, "name");
      while (cacheLength > 0) {
        nameList.findIndex(v => v === this.cachePageList[cacheLength - 1]) ===
          -1 &&
          this.cachePageList.splice(
            this.cachePageList.indexOf(this.cachePageList[cacheLength - 1]),
            1
          );
        cacheLength--;
      }
    },
    cacheOperate({ mode, name }: cacheType) {
      const delIndex = this.cachePageList.findIndex(v => v === name);
      switch (mode) {
        case "refresh":
          this.cachePageList = this.cachePageList.filter(v => v !== name);
          this.clearCache();
          break;
        case "add":
          this.cachePageList.push(name);
          break;
        case "delete":
          delIndex !== -1 && this.cachePageList.splice(delIndex, 1);
          this.clearCache();
          break;
      }
    },
    /** 清空缓存页面 */
    clearAllCachePage() {
      this.wholeMenus = [];
      this.cachePageList = [];
    },
    /** 设置用户权限 */
    setPermissions(permissions: string[]) {
      this.permissions = permissions;
    },
    /** 设置用户是否为超级管理员 */
    setIsSuper(isSuper: boolean) {
      this.isSuper = isSuper;
    },
    /** 判断用户是否拥有指定权限 */
    hasPermission(permissionCode: string | string[]) {
      // 超级管理员拥有所有权限
      if (this.isSuper) {
        return true;
      }

      // 如果是字符串，检查是否在权限列表中
      if (typeof permissionCode === "string") {
        return this.permissions.includes(permissionCode);
      }

      // 如果是数组，检查是否拥有任意一个权限
      if (Array.isArray(permissionCode)) {
        return permissionCode.some(code => this.permissions.includes(code));
      }

      return false;
    },
    /** 判断用户是否拥有所有指定权限 */
    hasAllPermissions(permissionCodes: string[]) {
      // 超级管理员拥有所有权限
      if (this.isSuper) {
        return true;
      }

      // 检查是否拥有所有指定权限
      return permissionCodes.every(code => this.permissions.includes(code));
    },
    /** 清空用户权限 */
    clearPermissions() {
      this.permissions = [];
      this.isSuper = false;
    }
  }
});

export function usePermissionStoreHook() {
  return usePermissionStore(store);
}
