<template>
  <div class="menu-container">
    <el-card class="h-full flex flex-col">
      <template #header>
        <div class="card-header">
          <span>菜单管理</span>
          <el-button
            v-if="hasAuth('menu:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增菜单
          </el-button>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div v-if="hasAuth('menu:view')" class="search-bar mb-4">
        <el-form :inline="true" :model="searchForm">
          <el-form-item label="菜单名称">
            <el-input
              v-model="searchForm.menuName"
              placeholder="请输入菜单名称"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">
              <el-icon><Search /></el-icon>
              搜索
            </el-button>
            <el-button @click="resetSearch">
              <el-icon><Refresh /></el-icon>
              重置
            </el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 菜单列表 -->
      <div v-if="hasAuth('menu:view')" class="flex-1 min-h-0">
        <el-table
          v-loading="loading"
          :data="menuList"
          row-key="id"
          style="width: 100%"
          :tree-props="{ children: 'children' }"
          class="h-full"
          :max-height="`calc(100vh - 280px)`"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="name" label="菜单名称" />
          <el-table-column prop="path" label="路径" />
          <el-table-column prop="component" label="组件" />
          <el-table-column label="图标" width="250">
            <template #default="scope">
              <el-icon v-if="scope.row.icon">{{ scope.row.icon }}</el-icon>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="上级菜单">
            <template #default="scope">
              {{ getParentMenuName(scope.row.parentId) }}
            </template>
          </el-table-column>
          <el-table-column label="状态" width="120">
            <template #default="scope">
              <el-switch
                v-if="hasAuth('menu:edit')"
                v-model="scope.row.status"
                @change="value => handleStatusChange(scope.row, value)"
              />
              <span v-else>{{ scope.row.status ? "启用" : "禁用" }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="300">
            <template #default="scope">
              <el-button
                v-if="hasAuth('menu:edit')"
                type="primary"
                size="small"
                @click="handleEdit(scope.row)"
              >
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('menu:permission')"
                type="warning"
                size="small"
                @click="handlePermission(scope.row)"
              >
                <el-icon><Key /></el-icon>
                权限管理
              </el-button>
              <el-button
                v-if="hasAuth('menu:delete')"
                type="danger"
                size="small"
                @click="handleDelete(scope.row.id)"
              >
                <el-icon><Delete /></el-icon>
                删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <!-- 分页 -->
        <div class="pagination mt-4">
          <el-pagination
            v-model:current-page="pagination.current"
            v-model:page-size="pagination.pageSize"
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            :total="pagination.total"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
          />
        </div>
      </div>
      <!-- 无权限提示 -->
      <div v-else class="no-permission flex-1 flex items-center justify-center">
        <el-empty description="无权限查看数据" />
      </div>
    </el-card>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增菜单' : '编辑菜单'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules">
        <el-form-item label="菜单名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入菜单名称" />
        </el-form-item>
        <el-form-item label="路径" prop="path">
          <el-input v-model="form.path" placeholder="请输入菜单路径" />
        </el-form-item>
        <el-form-item label="组件" prop="component">
          <el-input v-model="form.component" placeholder="请输入组件路径" />
        </el-form-item>
        <el-form-item label="图标" prop="icon">
          <el-input v-model="form.icon" placeholder="请输入图标名称" />
        </el-form-item>
        <el-form-item label="上级菜单" prop="parentId">
          <el-select v-model="form.parentId" placeholder="请选择上级菜单">
            <el-option label="无" value="0" />
            <el-option
              v-for="menu in allMenus"
              :key="menu.id"
              :label="menu.name"
              :value="menu.id.toString()"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-switch v-model="form.status" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number
            v-model="form.sort"
            :min="0"
            placeholder="请输入排序"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmit">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 权限管理对话框 -->
    <el-dialog v-model="permissionDialogVisible" title="权限管理" width="950px">
      <div class="permission-content">
        <h3 class="permission-title">{{ currentMenu?.name }} - 权限列表</h3>
        <div class="permission-header">
          <el-button
            v-if="hasAuth('menu:permission:add')"
            type="primary"
            @click="handleAddPermission"
          >
            <el-icon><Plus /></el-icon>
            新增权限
          </el-button>
        </div>

        <!-- 权限列表 -->
        <el-table
          v-loading="permissionLoading"
          :data="permissionList"
          row-key="id"
          style="width: 100%"
        >
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="name" label="权限名称" />
          <el-table-column prop="code" label="权限代码" />
          <el-table-column prop="description" label="描述" />
          <el-table-column label="状态" width="100">
            <template #default="scope">
              <el-switch
                v-if="hasAuth('menu:permission:edit')"
                v-model="scope.row.status"
                @change="
                  value => handlePermissionStatusChange(scope.row, value)
                "
              />
              <span v-else>{{ scope.row.status ? "启用" : "禁用" }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="220">
            <template #default="scope">
              <el-button
                v-if="hasAuth('menu:permission:edit')"
                type="primary"
                size="small"
                @click="handleEditPermission(scope.row)"
              >
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('menu:permission:delete')"
                type="danger"
                size="small"
                @click="handleDeletePermission(scope.row.id)"
              >
                <el-icon><Delete /></el-icon>
                删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="permissionDialogVisible = false">
            <el-icon><Close /></el-icon>
            关闭
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 新增/编辑权限对话框 -->
    <el-dialog
      v-model="permissionFormVisible"
      :title="permissionDialogType === 'add' ? '新增权限' : '编辑权限'"
      width="750px"
    >
      <el-form
        ref="permissionFormRef"
        :model="permissionForm"
        :rules="permissionRules"
      >
        <el-form-item label="权限名称" prop="name">
          <el-input
            v-model="permissionForm.name"
            placeholder="请输入权限名称"
          />
        </el-form-item>
        <el-form-item label="权限代码" prop="code">
          <el-input
            v-model="permissionForm.code"
            placeholder="请输入权限代码"
          />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="permissionForm.description"
            type="textarea"
            placeholder="请输入权限描述"
          />
        </el-form-item>
        <el-form-item label="类型" prop="type">
          <el-select v-model="permissionForm.type" placeholder="请选择权限类型">
            <el-option label="菜单权限" value="menu" />
            <el-option label="按钮权限" value="button" />
            <el-option label="API权限" value="api" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-switch v-model="permissionForm.status" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="permissionFormVisible = false">
            <el-icon><Close /></el-icon>
            取消
          </el-button>
          <el-button type="primary" @click="handleSubmitPermission">
            <el-icon><Check /></el-icon>
            确定
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import {
  Plus,
  Edit,
  Delete,
  Key,
  Search,
  Refresh,
  Close,
  Check
} from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import { http } from "@/utils/http";
import { hasAuth } from "@/router/utils";

// 加载状态
const loading = ref(false);

// 搜索表单
const searchForm = reactive({
  menuName: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 菜单列表
const menuList = ref<any[]>([]);
// 所有菜单列表（用于上级菜单选择）
const allMenus = ref<any[]>([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 表单数据
const form = reactive({
  id: 0,
  name: "",
  path: "",
  component: "",
  icon: "",
  parentId: "0",
  status: 1,
  sort: 0
});

// 表单验证规则
const rules = reactive<FormRules>({
  name: [{ required: true, message: "请输入菜单名称", trigger: "blur" }],
  path: [{ required: true, message: "请输入菜单路径", trigger: "blur" }],
  component: [{ required: true, message: "请输入组件路径", trigger: "blur" }],
  icon: [{ required: true, message: "请输入图标名称", trigger: "blur" }],
  sort: [{ required: true, message: "请输入排序", trigger: "blur" }]
});

// 权限管理相关
const permissionDialogVisible = ref(false);
const permissionFormVisible = ref(false);
const permissionDialogType = ref("add");
const permissionLoading = ref(false);
const currentMenu = ref<any>(null);
const permissionList = ref<any[]>([]);
const permissionFormRef = ref<FormInstance>();

// 权限表单数据
const permissionForm = reactive({
  id: 0,
  name: "",
  code: "",
  description: "",
  type: "button",
  status: true
});

// 权限表单验证规则
const permissionRules = reactive<FormRules>({
  name: [{ required: true, message: "请输入权限名称", trigger: "blur" }],
  code: [{ required: true, message: "请输入权限代码", trigger: "blur" }],
  type: [{ required: true, message: "请选择权限类型", trigger: "blur" }]
});

// 初始化数据
onMounted(() => {
  // 检查是否有查看权限
  if (hasAuth("menu:view")) {
    getMenuList();
  }
  // 无论是否有查看权限，都获取所有菜单用于上级菜单选择
  getAllMenus();
});

// 转换菜单数据，确保字段名称和类型正确
const transformMenuData = menus => {
  return menus.map(menu => {
    // 转换字段名称和类型
    const transformedMenu = {
      ...menu,
      id: menu.id,
      name: menu.name,
      path: menu.path,
      component: menu.component,
      redirect: menu.redirect || "",
      parentId: menu.parent_id || 0,
      icon: menu.icon || "",
      sort: menu.menu_rank || 0,
      isFrame: menu.is_frame || 0,
      frameSrc: menu.frame_src || "",
      showLink: menu.show_link || 1,
      status: menu.status === 1,
      created_at: menu.created_at
    };

    // 递归处理子菜单
    if (menu.children && menu.children.length > 0) {
      transformedMenu.children = transformMenuData(menu.children);
    }

    return transformedMenu;
  });
};

// 获取菜单列表
const getMenuList = async () => {
  // 检查是否有查看权限
  if (!hasAuth("menu:view")) {
    return;
  }
  loading.value = true;
  try {
    // 构建请求参数
    const params = {
      menuName: searchForm.menuName || undefined,
      page: pagination.current,
      pageSize: pagination.pageSize
    };

    const response = await http.request("get", "/api/role-menu/menu", {
      params: Object.fromEntries(
        Object.entries(params).filter(([_, v]) => v !== undefined)
      )
    });

    if (response.code === 200) {
      // 转换菜单数据，确保status字段为布尔类型
      menuList.value = transformMenuData(response.data);
      pagination.total = response.data.length;
    }
  } catch (error) {
    ElMessage.error("获取菜单列表失败");
  } finally {
    loading.value = false;
  }
};

// 获取所有菜单（用于上级菜单选择）
const getAllMenus = async () => {
  try {
    const response = await http.request("get", "/api/role-menu/menu");
    if (response.code === 200) {
      allMenus.value = response.data;
    }
  } catch (error) {
    console.error("获取所有菜单失败", error);
  }
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getMenuList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.menuName = "";
  pagination.current = 1;
  getMenuList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getMenuList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getMenuList();
};

// 新增菜单
const handleAdd = () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  form.name = "";
  form.path = "";
  form.component = "";
  form.icon = "";
  form.parentId = "";
  form.status = 1;
  form.sort = 0;
  dialogVisible.value = true;
};

// 根据parentId获取上级菜单名称
const getParentMenuName = (parentId: string | number): string => {
  if (parentId === 0 || parentId === "0") {
    return "无";
  }
  const menu = allMenus.value.find(
    item => item.id === parentId || item.id === parseInt(parentId.toString())
  );
  return menu ? menu.name : String(parentId);
};

// 编辑菜单
const handleEdit = (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.id;
  form.name = row.name;
  form.path = row.path;
  form.component = row.component;
  form.icon = row.icon;
  form.parentId = row.parentId.toString();
  form.status = row.status;
  form.sort = row.sort;
  dialogVisible.value = true;
};

// 状态变更
const handleStatusChange = async (row: any, newValue?: boolean) => {
  // 确保只在用户主动操作时才执行
  if (newValue === undefined) return;

  loading.value = true;
  try {
    const response = await http.request(
      "put",
      `/api/role-menu/menu/${row.id}`,
      {
        data: {
          status: newValue ? 1 : 0
        }
      }
    );
    if (response.code === 200) {
      ElMessage.success("状态更新成功");
      // 清除路由缓存，确保下次获取最新菜单数据
      import("@pureadmin/utils").then(({ storageLocal }) => {
        storageLocal().removeItem("async-routes");
        // 重新加载路由数据
        setTimeout(() => {
          window.location.reload();
        }, 500);
      });
    } else {
      // 如果更新失败，恢复原来的状态
      row.status = !newValue;
      ElMessage.error("状态更新失败");
    }
  } catch (error) {
    console.error("状态更新失败:", error);
    // 如果请求失败，恢复原来的状态
    row.status = !newValue;
    ElMessage.error("状态更新失败");
  } finally {
    loading.value = false;
  }
};

// 删除菜单
const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该菜单吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      loading.value = true;
      try {
        const response = await http.request(
          "delete",
          `/api/role-menu/menu/${id}`
        );
        if (response.code === 200) {
          ElMessage.success("删除成功");
          getMenuList();
          getAllMenus();
          // 清除路由缓存，确保下次获取最新菜单数据
          import("@pureadmin/utils").then(({ storageLocal }) => {
            storageLocal().removeItem("async-routes");
            // 重新加载路由数据
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          });
        }
      } catch (error) {
        ElMessage.error("删除失败");
      } finally {
        loading.value = false;
      }
    })
    .catch(() => {
      // 取消删除
    });
};

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid: boolean) => {
    if (valid) {
      loading.value = true;
      try {
        // 准备提交数据，确保数据类型正确
        const submitData = {
          name: form.name,
          path: form.path,
          component: form.component,
          parent_id: form.parentId ? parseInt(form.parentId) : 0,
          icon: form.icon || "",
          menu_rank: form.sort || 0,
          status: form.status ? 1 : 0
        };
        console.log("提交数据:", submitData);

        let response;
        if (dialogType.value === "add") {
          // 新增
          response = await http.request("post", "/api/role-menu/menu", {
            data: submitData
          });
        } else {
          // 编辑
          response = await http.request(
            "put",
            `/api/role-menu/menu/${form.id}`,
            { data: submitData }
          );
        }

        if (response.code === 200) {
          ElMessage.success(
            dialogType.value === "add" ? "新增成功" : "编辑成功"
          );
          dialogVisible.value = false;
          getMenuList();
          getAllMenus();
          // 清除路由缓存，确保下次获取最新菜单数据
          import("@pureadmin/utils").then(({ storageLocal }) => {
            storageLocal().removeItem("async-routes");
            // 重新加载路由数据
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          });
        } else {
          console.error("提交失败:", response);
          ElMessage.error(dialogType.value === "add" ? "新增失败" : "编辑失败");
        }
      } catch (error) {
        console.error("提交失败:", error);
        ElMessage.error(dialogType.value === "add" ? "新增失败" : "编辑失败");
      } finally {
        loading.value = false;
      }
    }
  });
};

// 权限管理
const handlePermission = (row: any) => {
  currentMenu.value = row;
  permissionDialogVisible.value = true;
  getMenuPermissions(row.id);
};

// 获取菜单权限列表
const getMenuPermissions = async (menuId: number) => {
  permissionLoading.value = true;
  try {
    const response = await http.request(
      "get",
      `/api/role-menu/menu/${menuId}/permissions`
    );
    if (response.code === 200) {
      permissionList.value = response.data.map((permission: any) => ({
        ...permission,
        status: permission.status === 1,
        // 将数字类型的权限类型转换为字符串类型，以便前端显示
        type:
          permission.type === 1
            ? "menu"
            : permission.type === 2
              ? "button"
              : "api"
      }));
    }
  } catch (error) {
    console.error("获取菜单权限失败:", error);
    ElMessage.error("获取菜单权限失败");
  } finally {
    permissionLoading.value = false;
  }
};

// 新增权限
const handleAddPermission = () => {
  permissionDialogType.value = "add";
  // 重置表单
  permissionForm.id = 0;
  permissionForm.name = "";
  permissionForm.code = "";
  permissionForm.description = "";
  permissionForm.type = "button";
  permissionForm.status = true;
  permissionFormVisible.value = true;
};

// 编辑权限
const handleEditPermission = (row: any) => {
  permissionDialogType.value = "edit";
  // 复制数据到表单
  permissionForm.id = row.id;
  permissionForm.name = row.name;
  permissionForm.code = row.code;
  permissionForm.description = row.description;
  permissionForm.type = row.type || "button";
  permissionForm.status = row.status;
  permissionFormVisible.value = true;
};

// 删除权限
const handleDeletePermission = (id: number) => {
  ElMessageBox.confirm("确定要删除该权限吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(async () => {
      permissionLoading.value = true;
      try {
        const response = await http.request(
          "delete",
          `/api/role-menu/menu/${currentMenu.value.id}/permissions/${id}`
        );
        if (response.code === 200) {
          ElMessage.success("删除成功");
          getMenuPermissions(currentMenu.value.id);
        }
      } catch (error) {
        console.error("删除权限失败:", error);
        ElMessage.error("删除权限失败");
      } finally {
        permissionLoading.value = false;
      }
    })
    .catch(() => {
      // 取消删除
    });
};

// 提交权限表单
const handleSubmitPermission = async () => {
  if (!permissionFormRef.value) return;
  await permissionFormRef.value.validate(async (valid: boolean) => {
    if (valid) {
      permissionLoading.value = true;
      try {
        // 准备提交数据，确保数据类型正确
        // 将权限类型从字符串转换为数字
        const typeMap = {
          menu: 1,
          button: 2,
          api: 3
        };

        const submitData = {
          name: permissionForm.name,
          code: permissionForm.code,
          description: permissionForm.description,
          type: typeMap[permissionForm.type] || 2, // 默认按钮权限
          status: permissionForm.status ? 1 : 0
        };
        console.log("提交权限数据:", submitData);

        let response;
        if (permissionDialogType.value === "add") {
          // 新增
          response = await http.request(
            "post",
            `/api/role-menu/menu/${currentMenu.value.id}/permissions`,
            {
              data: submitData
            }
          );
        } else {
          // 编辑
          response = await http.request(
            "put",
            `/api/role-menu/menu/${currentMenu.value.id}/permissions/${permissionForm.id}`,
            { data: submitData }
          );
        }

        if (response.code === 200) {
          ElMessage.success(
            permissionDialogType.value === "add"
              ? "新增权限成功"
              : "编辑权限成功"
          );
          permissionFormVisible.value = false;
          getMenuPermissions(currentMenu.value.id);
        } else {
          console.error("提交权限失败:", response);
          ElMessage.error(
            permissionDialogType.value === "add"
              ? "新增权限失败"
              : "编辑权限失败"
          );
        }
      } catch (error) {
        console.error("提交权限失败:", error);
        ElMessage.error(
          permissionDialogType.value === "add" ? "新增权限失败" : "编辑权限失败"
        );
      } finally {
        permissionLoading.value = false;
      }
    }
  });
};

// 权限状态变更
const handlePermissionStatusChange = async (row: any, newValue?: boolean) => {
  // 确保只在用户主动操作时才执行
  if (newValue === undefined) return;

  permissionLoading.value = true;
  try {
    const response = await http.request(
      "put",
      `/api/role-menu/menu/${currentMenu.value.id}/permissions/${row.id}`,
      {
        data: {
          status: newValue ? 1 : 0
        }
      }
    );
    if (response.code !== 200) {
      // 如果更新失败，恢复原来的状态
      row.status = !newValue;
      ElMessage.error("状态更新失败");
    }
  } catch (error) {
    console.error("状态更新失败:", error);
    // 如果请求失败，恢复原来的状态
    row.status = !newValue;
    ElMessage.error("状态更新失败");
  } finally {
    permissionLoading.value = false;
  }
};
</script>

<style scoped>
.menu-container {
  min-height: calc(100vh - 120px);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.search-bar {
}

.pagination {
  display: flex;
  justify-content: flex-end;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}

/* 权限管理相关样式 */
.permission-content {
  max-height: calc(80vh - 120px);
  overflow-y: auto;
}

.permission-title {
  padding-bottom: 8px;
  margin-bottom: 16px;
  font-size: 16px;
  font-weight: bold;
  border-bottom: 1px solid #e4e7ed;
}

.permission-header {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 16px;
}

/* 优化滚动条样式 */
.permission-content::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.permission-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.permission-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.permission-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
