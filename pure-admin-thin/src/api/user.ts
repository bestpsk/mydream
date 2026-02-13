import { http } from "@/utils/http";

export type UserResult = {
  success: boolean;
  data: {
    /** 头像 */
    avatar: string;
    /** 用户名 */
    username: string;
    /** 昵称 */
    nickname: string;
    /** 当前登录用户的角色 */
    roles: Array<string>;
    /** 按钮级别权限 */
    permissions: Array<string>;
    /** `token` */
    accessToken: string;
    /** 用于调用刷新`accessToken`的接口时所需的`token` */
    refreshToken: string;
    /** `accessToken`的过期时间（格式'xxxx/xx/xx xx:xx:xx'） */
    expires: Date;
    /** 是否为超级管理员 */
    isSuper: boolean;
    /** 公司ID */
    companyId?: number;
    /** 公司编码 */
    companyCode?: string;
    /** 公司名称 */
    companyName?: string;
    /** 门店ID */
    storeId?: number;
    /** 员工ID */
    employeeId?: number;
    /** 员工姓名 */
    employeeName?: string;
  };
};

export type RefreshTokenResult = {
  success: boolean;
  data: {
    /** `token` */
    accessToken: string;
    /** 用于调用刷新`accessToken`的接口时所需的`token` */
    refreshToken: string;
    /** `accessToken`的过期时间（格式'xxxx/xx/xx xx:xx:xx'） */
    expires: Date;
  };
};

/** 登录 */
export const getLogin = (data?: object) => {
  return http.request<UserResult>("post", "/api/auth/login", { data });
};

/** 刷新`token` */
export const refreshTokenApi = (data?: object) => {
  return http.request<RefreshTokenResult>("post", "/api/auth/refresh-token", {
    data
  });
};

/** 获取用户信息 */
export const getUserInfo = () => {
  return http.request<UserResult>("get", "/api/auth/user-info");
};
