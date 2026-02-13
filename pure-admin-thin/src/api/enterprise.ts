import { http } from "@/utils/http";

/**
 * 公司管理相关API
 */
export const getCompanies = (params?: object) => {
  return http.request("get", "/api/enterprise/company", { params });
};

export const addCompany = (data?: object) => {
  return http.request("post", "/api/enterprise/company", { data });
};

export const updateCompany = (id: number, data?: object) => {
  return http.request("put", `/api/enterprise/company/${id}`, { data });
};

export const deleteCompany = (id: number) => {
  return http.request("delete", `/api/enterprise/company/${id}`);
};

/**
 * 门店管理相关API
 */
export const getStores = (params?: object) => {
  return http.request("get", "/api/enterprise/store", { params });
};

export const addStore = (data?: object) => {
  return http.request("post", "/api/enterprise/store", { data });
};

export const updateStore = (id: number, data?: object) => {
  return http.request("put", `/api/enterprise/store/${id}`, { data });
};

export const deleteStore = (id: number) => {
  return http.request("delete", `/api/enterprise/store/${id}`);
};

/**
 * 部门管理相关API
 */
export const getDepartments = (params?: object) => {
  return http.request("get", "/api/enterprise/department", { params });
};

export const addDepartment = (data?: object) => {
  return http.request("post", "/api/enterprise/department", { data });
};

export const updateDepartment = (id: number, data?: object) => {
  return http.request("put", `/api/enterprise/department/${id}`, { data });
};

export const deleteDepartment = (id: number) => {
  return http.request("delete", `/api/enterprise/department/${id}`);
};

/**
 * 职位管理相关API
 */
export const getPositions = (params?: object) => {
  return http.request("get", "/api/enterprise/position", { params });
};

export const addPosition = (data?: object) => {
  return http.request("post", "/api/enterprise/position", { data });
};

export const updatePosition = (id: number, data?: object) => {
  return http.request("put", `/api/enterprise/position/${id}`, { data });
};

export const deletePosition = (id: number) => {
  return http.request("delete", `/api/enterprise/position/${id}`);
};

/**
 * 员工管理相关API
 */
export const getEmployees = (params?: object) => {
  return http.request("get", "/api/enterprise/employee", { params });
};

export const addEmployee = (data?: object) => {
  return http.request("post", "/api/enterprise/employee", { data });
};

export const updateEmployee = (id: number, data?: object) => {
  return http.request("put", `/api/enterprise/employee/${id}`, { data });
};

export const deleteEmployee = (id: number) => {
  return http.request("delete", `/api/enterprise/employee/${id}`);
};

/**
 * 床位管理相关API
 */
export const getBedrooms = (params?: object) => {
  return http.request("get", "/api/enterprise/bedroom", { params });
};

export const addBedroom = (data?: object) => {
  return http.request("post", "/api/enterprise/bedroom", { data });
};

export const updateBedroom = (id: number, data?: object) => {
  return http.request("put", `/api/enterprise/bedroom/${id}`, { data });
};

export const deleteBedroom = (id: number) => {
  return http.request("delete", `/api/enterprise/bedroom/${id}`);
};

/**
 * 角色管理相关API
 */
export const getRoles = (params?: object) => {
  return http.request("get", "/api/role-menu/role", { params });
};

export const getRolePermissions = (roleId: number) => {
  return http.request("get", `/api/role-menu/role/${roleId}/permissions`);
};

export const updateRolePermissions = (roleId: number, data?: object) => {
  return http.request("put", `/api/role-menu/role/${roleId}/permissions`, {
    data
  });
};
