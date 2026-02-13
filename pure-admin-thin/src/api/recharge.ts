import { http } from "@/utils/http";

// 充值卡类型定义
export type RechargeCard = {
  id: string;
  departmentId: string;
  departmentName: string;
  cardName: string;
  amount: number;
  giftAmount: number;
  projectDiscount: number;
  productDiscount: number;
  consumeRate: number;
  minRechargeLimit: number;
  onlineTime: string;
  offlineTime: string;
  expireDate: string;
  expireType: string;
  status: number;
  saleStoreIds: string[];
  consumeStoreIds: string[];
  description: string;
  remark: string;
  featureOptions: string[];
  createdAt: string;
  updatedAt: string;
};

// 配赠项目类型定义
export type GiftProject = {
  id: string;
  rechargeId: string;
  projectId: string;
  projectName: string;
  times: number;
  unitPrice: number;
  consume: number;
  manualSalary: number;
  createdAt: string;
  updatedAt: string;
};

// 配赠产品类型定义
export type GiftProduct = {
  id: string;
  rechargeId: string;
  productId: string;
  productName: string;
  times: number;
  unitPrice: number;
  consume: number;
  manualSalary: number;
  createdAt: string;
  updatedAt: string;
};

// 分页响应类型
export type PageResult<T> = {
  code: number;
  message: string;
  data: T[];
  total: number;
};

// 通用响应类型
export type ApiResult<T = any> = {
  code: number;
  message: string;
  data: T;
};

/**
 * 获取充值卡列表
 */
export const getRechargeCardList = (params?: object) => {
  return http.request<ApiResult<RechargeCard[]>>(
    "get",
    "/api/recharge-card/list",
    { params }
  );
};

/**
 * 获取充值卡详情
 */
export const getRechargeCardDetail = (id: string) => {
  return http.request<ApiResult<RechargeCard>>(
    "get",
    `/api/recharge-card/detail/${id}`
  );
};

/**
 * 新增充值卡
 */
export const addRechargeCard = (data?: object) => {
  return http.request<ApiResult>("post", "/api/recharge-card/add", { data });
};

/**
 * 更新充值卡
 */
export const updateRechargeCard = (id: string, data?: object) => {
  return http.request<ApiResult>("put", `/api/recharge-card/update/${id}`, {
    data
  });
};

/**
 * 删除充值卡
 */
export const deleteRechargeCard = (id: string) => {
  return http.request<ApiResult>("delete", `/api/recharge-card/delete/${id}`);
};

/**
 * 获取配赠项目列表
 */
export const getGiftProjects = (rechargeId: string) => {
  return http.request<ApiResult<GiftProject[]>>(
    "get",
    `/api/recharge-gift/projects/${rechargeId}`
  );
};

/**
 * 新增配赠项目
 */
export const addGiftProject = (data?: object) => {
  return http.request<ApiResult>("post", "/api/recharge-gift/add-project", {
    data
  });
};

/**
 * 更新配赠项目
 */
export const updateGiftProject = (id: string, data?: object) => {
  return http.request<ApiResult>(
    "put",
    `/api/recharge-gift/update-project/${id}`,
    { data }
  );
};

/**
 * 删除配赠项目
 */
export const deleteGiftProject = (id: string) => {
  return http.request<ApiResult>(
    "delete",
    `/api/recharge-gift/delete-project/${id}`
  );
};

/**
 * 获取配赠产品列表
 */
export const getGiftProducts = (rechargeId: string) => {
  return http.request<ApiResult<GiftProduct[]>>(
    "get",
    `/api/recharge-gift/products/${rechargeId}`
  );
};

/**
 * 新增配赠产品
 */
export const addGiftProduct = (data?: object) => {
  return http.request<ApiResult>("post", "/api/recharge-gift/add-product", {
    data
  });
};

/**
 * 更新配赠产品
 */
export const updateGiftProduct = (id: string, data?: object) => {
  return http.request<ApiResult>(
    "put",
    `/api/recharge-gift/update-product/${id}`,
    { data }
  );
};

/**
 * 删除配赠产品
 */
export const deleteGiftProduct = (id: string) => {
  return http.request<ApiResult>(
    "delete",
    `/api/recharge-gift/delete-product/${id}`
  );
};
