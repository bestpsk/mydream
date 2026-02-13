import { defineStore } from "pinia";
import {
  type userType,
  store,
  router,
  resetRouter,
  routerArrays,
  storageLocal
} from "../utils";
import {
  type UserResult,
  type RefreshTokenResult,
  getLogin,
  refreshTokenApi,
  getUserInfo
} from "@/api/user";
import { useMultiTagsStoreHook } from "./multiTags";
import { usePermissionStoreHook } from "./permission";
import { useStoreStore } from "./store";
import { type DataInfo, setToken, removeToken, userKey } from "@/utils/auth";

export const useUserStore = defineStore("pure-user", {
  state: (): userType => ({
    // 头像
    avatar: storageLocal().getItem<DataInfo<number>>(userKey)?.avatar ?? "",
    // 用户名
    username: storageLocal().getItem<DataInfo<number>>(userKey)?.username ?? "",
    // 昵称
    nickname: storageLocal().getItem<DataInfo<number>>(userKey)?.nickname ?? "",
    // 员工姓名
    employeeName:
      storageLocal().getItem<DataInfo<number>>(userKey)?.employeeName ?? "",
    // 页面级别权限
    roles: storageLocal().getItem<DataInfo<number>>(userKey)?.roles ?? [],
    // 按钮级别权限
    permissions:
      storageLocal().getItem<DataInfo<number>>(userKey)?.permissions ?? [],
    // 公司信息
    companyId: storageLocal().getItem<DataInfo<number>>(userKey)?.companyId,
    companyCode: storageLocal().getItem<DataInfo<number>>(userKey)?.companyCode,
    companyName: storageLocal().getItem<DataInfo<number>>(userKey)?.companyName,
    // 门店ID
    storeId: storageLocal().getItem<DataInfo<number>>(userKey)?.storeId,
    // 员工ID
    employeeId: storageLocal().getItem<DataInfo<number>>(userKey)?.employeeId,
    // 是否为超级管理员
    isSuper:
      storageLocal().getItem<DataInfo<number>>(userKey)?.isSuper ?? false,
    // 是否勾选了登录页的免登录
    isRemembered: false,
    // 登录页的免登录存储几天，默认7天
    loginDay: 7
  }),
  actions: {
    /** 存储头像 */
    SET_AVATAR(avatar: string) {
      this.avatar = avatar;
    },
    /** 存储用户名 */
    SET_USERNAME(username: string) {
      this.username = username;
    },
    /** 存储昵称 */
    SET_NICKNAME(nickname: string) {
      this.nickname = nickname;
    },
    /** 存储角色 */
    SET_ROLES(roles: Array<string>) {
      this.roles = roles;
    },
    /** 存储按钮级别权限 */
    SET_PERMS(permissions: Array<string>) {
      this.permissions = permissions;
      // 同时更新localStorage中的数据
      const userInfo = storageLocal().getItem<DataInfo<number>>(userKey);
      if (userInfo) {
        storageLocal().setItem(userKey, {
          ...userInfo,
          permissions
        });
      }
    },
    /** 存储公司信息 */
    SET_COMPANY_INFO(companyInfo: {
      companyId?: number;
      companyCode?: string;
      companyName?: string;
    }) {
      this.companyId = companyInfo.companyId;
      this.companyCode = companyInfo.companyCode;
      this.companyName = companyInfo.companyName;
    },
    /** 存储门店ID */
    SET_STORE_ID(storeId?: number) {
      this.storeId = storeId;
    },
    /** 存储员工ID */
    SET_EMPLOYEE_ID(employeeId?: number) {
      this.employeeId = employeeId;
    },
    /** 存储员工姓名 */
    SET_EMPLOYEE_NAME(employeeName?: string) {
      this.employeeName = employeeName;
    },
    /** 存储是否为超级管理员 */
    SET_IS_SUPER(isSuper?: boolean) {
      this.isSuper = isSuper ?? false;
    },
    /** 存储是否勾选了登录页的免登录 */
    SET_ISREMEMBERED(bool: boolean) {
      this.isRemembered = bool;
    },
    /** 设置登录页的免登录存储几天 */
    SET_LOGINDAY(value: number) {
      this.loginDay = Number(value);
    },
    /** 登入 */
    async loginByUsername(data) {
      return new Promise<UserResult>((resolve, reject) => {
        getLogin(data)
          .then(data => {
            if (data?.code === 200) {
              // 从登录响应中获取isSuper标识并设置
              if (data.data.isSuper !== undefined) {
                this.SET_IS_SUPER(data.data.isSuper);
                // 同时更新权限存储中的isSuper标识
                const permissionStore = usePermissionStoreHook();
                permissionStore.setIsSuper(data.data.isSuper);
              }
              setToken(data.data);
              // 登录成功后获取用户信息和权限
              this.getUserInfo();
              this.getUserPermissions();
              // 初始化门店数据
              const storeStore = useStoreStore();
              storeStore.initStores();
            }
            resolve(data);
          })
          .catch(error => {
            reject(error);
          });
      });
    },

    /** 获取用户权限 */
    async getUserPermissions() {
      try {
        const response = await import("@/api/routes").then(m =>
          m.getPermissions()
        );
        if (response?.code === 200) {
          const permissions = response.data || [];
          this.SET_PERMS(permissions);
          // 同时更新权限存储
          const permissionStore = usePermissionStoreHook();
          permissionStore.setPermissions(permissions);
        }
      } catch (error) {
        console.error("获取用户权限失败:", error);
      }
    },
    /** 前端登出（不调用接口） */
    logOut() {
      this.username = "";
      this.roles = [];
      this.permissions = [];
      this.companyId = undefined;
      this.companyCode = undefined;
      this.companyName = undefined;
      this.storeId = undefined;
      this.employeeId = undefined;
      this.isSuper = false;
      removeToken();
      // 清除路由缓存
      storageLocal().removeItem("async-routes");
      useMultiTagsStoreHook().handleTags("equal", [...routerArrays]);
      resetRouter();
      router.push("/login");
    },
    /** 刷新`token` */
    async handRefreshToken(data) {
      return new Promise<RefreshTokenResult>((resolve, reject) => {
        refreshTokenApi(data)
          .then(data => {
            if (data?.code === 200) {
              setToken(data.data);
              resolve(data);
            }
          })
          .catch(error => {
            reject(error);
          });
      });
    },

    /** 获取用户信息 */
    async getUserInfo() {
      return new Promise<UserResult>((resolve, reject) => {
        getUserInfo()
          .then(data => {
            if (data?.code === 200) {
              this.SET_USERNAME(data.data.username);
              this.SET_NICKNAME(data.data.nickname);
              this.SET_AVATAR(data.data.avatar);
              this.SET_ROLES(data.data.roles);
              this.SET_PERMS(data.data.permissions || []);
              this.SET_COMPANY_INFO({
                companyId: data.data.companyId,
                companyCode: data.data.companyCode,
                companyName: data.data.companyName
              });
              this.SET_STORE_ID(data.data.storeId);
              this.SET_EMPLOYEE_ID(data.data.employeeId);
              this.SET_EMPLOYEE_NAME(data.data.employeeName);
              // 只有当后端明确返回了isSuper字段时，才更新isSuper状态
              if (data.data.isSuper !== undefined) {
                this.SET_IS_SUPER(data.data.isSuper);
                // 同时更新权限存储中的isSuper标识
                const permissionStore = usePermissionStoreHook();
                permissionStore.setIsSuper(data.data.isSuper);
              }
            }
            resolve(data);
          })
          .catch(error => {
            reject(error);
          });
      });
    }
  }
});

export function useUserStoreHook() {
  return useUserStore(store);
}
