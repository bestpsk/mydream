import http from "@/utils/http";

export const getPackageCards = async (params?: { cardName?: string }) => {
  return http.get("/api/card-item/get-package-cards", { params });
};

export const getPackageCardDetail = async (id: number) => {
  return http.get(`/api/card-item/get-package-card-detail/${id}`);
};

export const addPackageCard = async (data: any) => {
  return http.post("/api/card-item/add-package-card", { data });
};

export const updatePackageCard = async (id: number, data: any) => {
  return http.put(`/api/card-item/update-package-card/${id}`, { data });
};

export const deletePackageCard = async (id: number) => {
  return http.post(`/api/card-item/delete-package-card/${id}`);
};

export const getPackageCardGiftProjects = async (packageId: number) => {
  return http.get(`/api/card-item/get-package-card-gift-projects/${packageId}`);
};

export const getPackageCardGiftProducts = async (packageId: number) => {
  return http.get(`/api/card-item/get-package-card-gift-products/${packageId}`);
};

export const addPackageCardGiftProject = async (data: any) => {
  return http.post("/api/card-item/add-package-card-gift-project", { data });
};

export const updatePackageCardGiftProject = async (id: number, data: any) => {
  return http.post(
    `/api/card-item/update-package-card-gift-project/${id}`,
    { data }
  );
};

export const deletePackageCardGiftProject = async (id: number) => {
  return http.post(`/api/card-item/delete-package-card-gift-project/${id}`);
};

export const addPackageCardGiftProduct = async (data: any) => {
  return http.post("/api/card-item/add-package-card-gift-product", { data });
};

export const updatePackageCardGiftProduct = async (id: number, data: any) => {
  return http.post(
    `/api/card-item/update-package-card-gift-product/${id}`,
    { data }
  );
};

export const deletePackageCardGiftProduct = async (id: number) => {
  return http.post(`/api/card-item/delete-package-card-gift-product/${id}`);
};
