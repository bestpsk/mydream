import http from "@/utils/http";

/**
 * 获取套餐卡列表
 * @param params 查询参数
 * @returns 套餐卡列表数据
 */
export const getPackageCards = async (params?: { cardName?: string }) => {
  return http.get("/api/card-item/get-package-cards", { params });
};

/**
 * 获取套餐卡详情
 * @param id 套餐卡ID
 * @returns 套餐卡详情数据
 */
export const getPackageCardDetail = async (id: number) => {
  return http.get(`/api/card-item/get-package-card-detail/${id}`);
};

/**
 * 添加套餐卡
 * @param data 套餐卡数据
 * @returns 添加结果
 */
export const addPackageCard = async (data: any) => {
  return http.post("/api/card-item/add-package-card", data);
};

/**
 * 更新套餐卡
 * @param id 套餐卡ID
 * @param data 套餐卡数据
 * @returns 更新结果
 */
export const updatePackageCard = async (id: number, data: any) => {
  return http.post(`/api/card-item/update-package-card/${id}`, data);
};

/**
 * 删除套餐卡
 * @param id 套餐卡ID
 * @returns 删除结果
 */
export const deletePackageCard = async (id: number) => {
  return http.post(`/api/card-item/delete-package-card/${id}`);
};

/**
 * 获取套餐卡包含项目
 * @param packageId 套餐卡ID
 * @returns 套餐卡包含项目数据
 */
export const getPackageCardGiftProjects = async (packageId: number) => {
  return http.get(`/api/card-item/get-package-card-gift-projects/${packageId}`);
};

/**
 * 获取套餐卡包含产品
 * @param packageId 套餐卡ID
 * @returns 套餐卡包含产品数据
 */
export const getPackageCardGiftProducts = async (packageId: number) => {
  return http.get(`/api/card-item/get-package-card-gift-products/${packageId}`);
};

/**
 * 添加套餐卡包含项目
 * @param data 项目数据
 * @returns 添加结果
 */
export const addPackageCardGiftProject = async (data: any) => {
  return http.post("/api/card-item/add-package-card-gift-project", data);
};

/**
 * 更新套餐卡包含项目
 * @param id 项目ID
 * @param data 项目数据
 * @returns 更新结果
 */
export const updatePackageCardGiftProject = async (id: number, data: any) => {
  return http.post(
    `/api/card-item/update-package-card-gift-project/${id}`,
    data
  );
};

/**
 * 删除套餐卡包含项目
 * @param id 项目ID
 * @returns 删除结果
 */
export const deletePackageCardGiftProject = async (id: number) => {
  return http.post(`/api/card-item/delete-package-card-gift-project/${id}`);
};

/**
 * 添加套餐卡包含产品
 * @param data 产品数据
 * @returns 添加结果
 */
export const addPackageCardGiftProduct = async (data: any) => {
  return http.post("/api/card-item/add-package-card-gift-product", data);
};

/**
 * 更新套餐卡包含产品
 * @param id 产品ID
 * @param data 产品数据
 * @returns 更新结果
 */
export const updatePackageCardGiftProduct = async (id: number, data: any) => {
  return http.post(
    `/api/card-item/update-package-card-gift-product/${id}`,
    data
  );
};

/**
 * 删除套餐卡包含产品
 * @param id 产品ID
 * @returns 删除结果
 */
export const deletePackageCardGiftProduct = async (id: number) => {
  return http.post(`/api/card-item/delete-package-card-gift-product/${id}`);
};
