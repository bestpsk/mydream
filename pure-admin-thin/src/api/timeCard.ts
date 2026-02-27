import http from "@/utils/http";

export const getTimeCards = async (params?: { cardName?: string; status?: number }) => {
  return http.get("/api/card-item/get-time-cards", { params });
};

export const getTimeCardDetail = async (id: number) => {
  return http.get(`/api/card-item/get-time-card-detail/${id}`);
};

export const addTimeCard = async (data: any) => {
  return http.post("/api/card-item/add-time-card", { data });
};

export const updateTimeCard = async (id: number, data: any) => {
  return http.put(`/api/card-item/update-time-card/${id}`, { data });
};

export const deleteTimeCard = async (id: number) => {
  return http.delete(`/api/card-item/delete-time-card/${id}`);
};

export const copyTimeCard = async (id: number) => {
  return http.post(`/api/card-item/copy-time-card/${id}`);
};

export const toggleTimeCardStatus = async (id: number) => {
  return http.put(`/api/card-item/toggle-time-card-status/${id}`);
};

export const batchTimeCardStatus = async (data: { ids: number[]; status: number }) => {
  return http.put("/api/card-item/batch-time-card-status", { data });
};
