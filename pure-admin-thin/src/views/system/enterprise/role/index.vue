<template>
  <div class="role-container">
    <el-card class="main-card">
      <template #header>
        <div class="card-header">
          <span>角色管理</span>
        </div>
      </template>

      <!-- 搜索栏 -->
      <el-card v-if="hasAuth('role:view')" class="mb-4" shadow="never">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <span class="text-sm font-bold">角色名称</span>
            <el-input
              v-model="searchForm.roleName"
              placeholder="请输入角色名称"
              clearable
              style="width: 200px"
              @clear="handleSearch"
              @keyup.enter="handleSearch"
            />
            <el-button type="primary" @click="handleSearch">
              <el-icon><Search /></el-icon>
              搜索
            </el-button>
            <el-button @click="resetSearch">
              <el-icon><Refresh /></el-icon>
              重置
            </el-button>
          </div>
          <el-button
            v-if="hasAuth('role:add')"
            type="primary"
            @click="handleAdd"
          >
            <el-icon><Plus /></el-icon>
            新增角色
          </el-button>
        </div>
      </el-card>

      <!-- 角色列表 -->
      <div v-if="hasAuth('role:view')" class="role-list">
        <el-table v-loading="loading" :data="roleList" style="width: 100%">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="roleName" label="角色名称" />
          <el-table-column prop="description" label="描述" />
          <el-table-column prop="createTime" label="创建时间" />
          <el-table-column label="操作" width="300">
            <template #default="scope">
              <el-button
                v-if="hasAuth('role:edit')"
                type="primary"
                size="small"
                @click="handleEdit(scope.row)"
              >
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button
                v-if="hasAuth('role:permission')"
                type="warning"
                size="small"
                @click="handlePermission(scope.row)"
              >
                <el-icon><Key /></el-icon>
                权限分配
              </el-button>
              <el-button
                v-if="hasAuth('role:delete')"
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
        <div class="pagination">
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
      <div v-else class="no-permission">
        <el-empty description="无权限查看数据" />
      </div>
    </el-card>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogType === 'add' ? '新增角色' : '编辑角色'"
      width="500px"
    >
      <el-form ref="formRef" :model="form" :rules="rules">
        <el-form-item label="所属公司" prop="companyId">
          <el-select
            v-model="form.companyId"
            placeholder="请选择所属公司"
            :disabled="!isSuperAdmin()"
          >
            <el-option
              v-for="company in companyList"
              :key="company.id"
              :label="company.companyName"
              :value="company.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="角色名称" prop="roleName">
          <el-input v-model="form.roleName" placeholder="请输入角色名称" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            placeholder="请输入角色描述"
          />
        </el-form-item>
        <!-- 调试信息 -->
        <el-form-item v-if="true" label="调试信息">
          <el-input
            v-model="debugInfo"
            type="textarea"
            placeholder="调试信息"
            readonly
          />
        </el-form-item>
        <el-form-item v-if="isSuperAdmin()" label="超级管理员">
          <el-switch v-model="form.isSuper" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmit">确定</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 权限分配对话框 -->
    <el-dialog
      v-model="permissionDialogVisible"
      title="权限分配"
      width="95vw"
      height="90vh"
    >
      <div class="permission-content">
        <h3 class="permission-title">菜单与按钮权限</h3>
        <!-- 自定义菜单树 -->
        <div class="custom-menu-tree">
          <div v-for="menu in menuTree" :key="menu.id" class="menu-item">
            <div class="menu-header">
              <el-checkbox
                v-model="menuChecked[menu.id]"
                @change="handleMenuCheck(menu.id, menuChecked[menu.id])"
              >
                {{ menu.name }}
              </el-checkbox>
            </div>
            <!-- 子菜单 -->
            <div
              v-if="menu.children && menu.children.length > 0"
              class="sub-menu"
            >
              <div
                v-for="subMenu in menu.children"
                :key="subMenu.id"
                class="menu-item"
              >
                <!-- 二级菜单有子菜单时，只渲染菜单头和孙子菜单 -->
                <template
                  v-if="subMenu.children && subMenu.children.length > 0"
                >
                  <div class="menu-header">
                    <el-checkbox
                      v-model="menuChecked[subMenu.id]"
                      @change="
                        handleMenuCheck(subMenu.id, menuChecked[subMenu.id])
                      "
                    >
                      {{ subMenu.name }}
                    </el-checkbox>
                  </div>
                  <!-- 孙子菜单（页面级别） -->
                  <div class="sub-menu">
                    <div
                      v-for="pageMenu in subMenu.children"
                      :key="pageMenu.id"
                      class="menu-item page-menu-item"
                    >
                      <div class="menu-header-with-permissions">
                        <el-checkbox
                          v-model="menuChecked[pageMenu.id]"
                          class="menu-checkbox"
                          @change="
                            handleMenuCheck(
                              pageMenu.id,
                              menuChecked[pageMenu.id]
                            )
                          "
                        >
                          {{ pageMenu.name }}
                        </el-checkbox>
                        <!-- 页面按钮权限 - 与菜单权限同一排 -->
                        <div
                          v-if="menuChecked[pageMenu.id]"
                          class="inline-button-permissions"
                        >
                          <el-checkbox-group
                            v-model="menuButtonPermissions[pageMenu.id]"
                            class="inline-button-permissions-group"
                          >
                            <!-- 动态从后端获取权限 -->
                            <el-checkbox
                              v-for="permission in getMenuPermissions(
                                pageMenu.id
                              )"
                              :key="permission.code"
                              :label="permission.code"
                              class="inline-permission-checkbox"
                              >{{ permission.name }}</el-checkbox
                            >
                          </el-checkbox-group>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
                <!-- 二级菜单直接是页面级别 -->
                <template v-else>
                  <div class="menu-header-with-permissions">
                    <el-checkbox
                      v-model="menuChecked[subMenu.id]"
                      class="menu-checkbox"
                      @change="
                        handleMenuCheck(subMenu.id, menuChecked[subMenu.id])
                      "
                    >
                      {{ subMenu.name }}
                    </el-checkbox>
                    <!-- 页面按钮权限 - 与菜单权限同一排 -->
                    <div
                      v-if="menuChecked[subMenu.id]"
                      class="inline-button-permissions"
                    >
                      <el-checkbox-group
                        v-model="menuButtonPermissions[subMenu.id]"
                        class="inline-button-permissions-group"
                      >
                        <!-- 动态从后端获取权限 -->
                        <el-checkbox
                          v-for="permission in getMenuPermissions(subMenu.id)"
                          :key="permission.code"
                          :label="permission.code"
                          class="inline-permission-checkbox"
                          >{{ permission.name }}</el-checkbox
                        >
                      </el-checkbox-group>
                    </div>
                  </div>
                </template>
              </div>
            </div>
            <!-- 一级菜单直接是页面级别 -->
            <div
              v-else-if="!menu.children"
              class="menu-header-with-permissions"
            >
              <el-checkbox
                v-model="menuChecked[menu.id]"
                class="menu-checkbox"
                @change="handleMenuCheck(menu.id, menuChecked[menu.id])"
              >
                {{ menu.name }}
              </el-checkbox>
              <!-- 页面按钮权限 - 与菜单权限同一排 -->
              <div
                v-if="menuChecked[menu.id]"
                class="inline-button-permissions"
              >
                <el-checkbox-group
                  v-model="menuButtonPermissions[menu.id]"
                  class="inline-button-permissions-group"
                >
                  <!-- 动态从后端获取权限 -->
                  <el-checkbox
                    v-for="permission in getMenuPermissions(menu.id)"
                    :key="permission.code"
                    :label="permission.code"
                    class="inline-permission-checkbox"
                    >{{ permission.name }}</el-checkbox
                  >
                </el-checkbox-group>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="permissionDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleSubmitPermission"
            >确定</el-button
          >
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import { Plus, Edit, Delete, Key, Search, Refresh } from "@element-plus/icons-vue";
import { ElMessage, ElMessageBox } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import {
  getRoles,
  getRolePermissions,
  updateRolePermissions,
  getCompanies
} from "@/api/enterprise";
import { http } from "@/utils/http";
import { hasAuth } from "@/router/utils";
import { isSuperAdmin } from "@/utils/auth";
import { useUserStoreHook } from "@/store/modules/user";

// 加载状态
const loading = ref(false);

// 搜索表单
const searchForm = reactive({
  roleName: ""
});

// 分页信息
const pagination = reactive({
  current: 1,
  pageSize: 10,
  total: 0
});

// 角色列表
const roleList = ref([]);

// 对话框状态
const dialogVisible = ref(false);
const dialogType = ref("add");
const formRef = ref<FormInstance>();

// 获取用户store
const userStore = useUserStoreHook();

// 公司列表
const companyList = ref([]);

// 表单数据
const form = reactive({
  id: 0,
  roleName: "",
  description: "",
  isSuper: false,
  companyId: 0
});

// 表单验证规则
const rules = reactive<FormRules>({
  roleName: [{ required: true, message: "请输入角色名称", trigger: "blur" }],
  companyId: [{ required: true, message: "请选择所属公司", trigger: "change" }]
});

// 权限分配相关
const permissionDialogVisible = ref(false);
const currentRole = ref<any>(null);
const menuChecked = ref<any>({});
const menuButtonPermissions = ref<any>({});

// 菜单权限数据
const menuPermissions = ref<any>({});

// 菜单树数据
const menuTree = ref([]);

// 从后端获取菜单树数据
const fetchMenuTree = async () => {
  try {
    const response = await http.request("get", "/api/permission/menus");
    if (response?.code === 200) {
      menuTree.value = response.data || [];
      // 重新初始化扁平化菜单
      initFlattenedMenus();
    }
  } catch (error) {
    console.error("获取菜单树失败:", error);
    ElMessage.error("获取菜单树失败");
  }
};

// 扁平化菜单
const flattenedMenus = ref<any[]>([]);

// 扁平化菜单树的函数
function flattenMenuTree(menuTree: any[]): any[] {
  const result: any[] = [];

  function traverse(menu: any) {
    // 只处理叶子节点（没有子菜单的菜单）
    if (!menu.children || menu.children.length === 0) {
      result.push(menu);
    } else {
      menu.children.forEach((child: any) => traverse(child));
    }
  }

  menuTree.forEach(menu => traverse(menu));
  return result;
}

// 初始化扁平化菜单
const initFlattenedMenus = () => {
  flattenedMenus.value = flattenMenuTree(menuTree.value);
};

// 树配置
const defaultProps = {
  children: "children",
  label: "label"
};

// 获取公司列表
const getCompanyList = () => {
  getCompanies()
    .then(response => {
      if (response?.code === 200) {
        companyList.value = response.data || [];
      } else {
        ElMessage.error(response?.message || "获取公司列表失败");
      }
    })
    .catch(error => {
      ElMessage.error("获取公司列表失败");
      console.error("getCompanyList error:", error);
    });
};

// 初始化数据
onMounted(() => {
  // 检查是否有查看权限
  if (hasAuth("role:view")) {
    getRoleList();
  }
  // 获取公司列表
  getCompanyList();
  // 获取菜单树数据
  fetchMenuTree();
});

// 获取角色列表
const getRoleList = () => {
  // 检查是否有查看权限
  if (!hasAuth("role:view")) {
    return;
  }
  loading.value = true;
  // 从API获取角色列表
  // 普通用户传递company_id参数，只查看自己所属公司的角色
  // 超级管理员不传递company_id参数，查看所有角色
  const params = isSuperAdmin()
    ? {}
    : {
        company_id: userStore.companyId
      };
  getRoles(params)
    .then(response => {
      loading.value = false;
      if (response?.code === 200) {
        // 转换角色数据，确保字段名正确
        roleList.value = (response.data || []).map((role: any) => {
          return {
            id: role.id,
            roleName: role.name || role.role_name || role.roleName || "",
            description: role.description || "",
            companyId: role.company_id || role.companyId || 0,
            createTime: role.created_at || ""
          };
        });
        pagination.total = roleList.value.length;
      } else {
        ElMessage.error(response?.message || "获取角色列表失败");
      }
    })
    .catch(error => {
      loading.value = false;
      ElMessage.error("获取角色列表失败");
      console.error("getRoleList error:", error);
    });
};

// 搜索
const handleSearch = () => {
  pagination.current = 1;
  getRoleList();
};

// 重置搜索
const resetSearch = () => {
  searchForm.roleName = "";
  pagination.current = 1;
  getRoleList();
};

// 分页大小变化
const handleSizeChange = (size: number) => {
  pagination.pageSize = size;
  getRoleList();
};

// 当前页码变化
const handleCurrentChange = (current: number) => {
  pagination.current = current;
  getRoleList();
};

// 新增角色
const handleAdd = () => {
  dialogType.value = "add";
  // 重置表单
  form.id = 0;
  form.roleName = "";
  form.description = "";
  form.isSuper = false;
  // 普通用户默认使用自己所属的公司ID
  form.companyId = isSuperAdmin() ? 0 : userStore.companyId;
  dialogVisible.value = true;
};

// 编辑角色
const handleEdit = (row: any) => {
  dialogType.value = "edit";
  // 复制数据到表单
  form.id = row.id;
  form.roleName = row.roleName;
  form.description = row.description;
  form.isSuper = row.isSuper || false;
  // 普通用户只能编辑自己所属公司的角色，默认使用自己所属的公司ID
  form.companyId = isSuperAdmin() ? row.companyId || 0 : userStore.companyId;
  dialogVisible.value = true;
};

// 删除角色
const handleDelete = (id: number) => {
  ElMessageBox.confirm("确定要删除该角色吗？", "警告", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning"
  })
    .then(() => {
      // 调用后端API
      http
        .request("delete", `/api/role-menu/role/${id}`)
        .then(response => {
          if (response?.code === 200) {
            ElMessage.success("删除成功");
            getRoleList();
          } else {
            ElMessage.error(response?.message || "删除失败");
          }
        })
        .catch(error => {
          ElMessage.error("删除失败");
          console.error("handleDelete error:", error);
        });
    })
    .catch(() => {
      // 取消删除
    });
};

// 递归初始化菜单和按钮权限
function initMenuPermissions(menuTree: any[]) {
  menuTree.forEach(menu => {
    const menuId = String(menu.id);
    menuChecked.value[menuId] = false;
    // 确保使用响应式方式初始化数组
    menuButtonPermissions.value[menuId] = [];

    if (menu.children && menu.children.length > 0) {
      initMenuPermissions(menu.children);
    }
  });
}

// 从后端获取菜单权限
const fetchMenuPermissions = async () => {
  try {
    // 获取所有菜单的权限
    const response = await http.request(
      "get",
      "/api/permission/menu-permissions"
    );
    if (response?.code === 200) {
      const permissions = response.data || [];
      // 按菜单ID分组权限
      menuPermissions.value = {};
      permissions.forEach((permission: any) => {
        const menuId = String(permission.menu_id);
        if (!menuPermissions.value[menuId]) {
          menuPermissions.value[menuId] = [];
        }
        menuPermissions.value[menuId].push(permission);
      });
      console.log("获取菜单权限成功:", menuPermissions.value);
    }
  } catch (error) {
    console.error("获取菜单权限失败:", error);
  }
};

// 获取指定菜单的权限
const getMenuPermissions = (menuId: string) => {
  return menuPermissions.value[menuId] || [];
};

// 权限分配
const handlePermission = (row: any) => {
  currentRole.value = row;
  // 重置权限数据
  menuChecked.value = {};
  menuButtonPermissions.value = {};
  menuPermissions.value = {};

  // 先获取最新的菜单树数据
  fetchMenuTree().then(() => {
    // 初始化每个菜单的按钮权限
    initMenuPermissions(menuTree.value);

    // 先获取所有菜单的权限
    fetchMenuPermissions().then(() => {
      // 从API获取角色权限数据
      getRolePermissions(row.id)
        .then(response => {
          if (response?.code === 200) {
            const { menu_ids = [], permission_ids = [] } = response.data || {};

            // 设置菜单勾选状态
            menu_ids.forEach((menuId: any) => {
              // 确保menuId是字符串类型，与前端菜单树中的ID类型匹配
              const stringMenuId = String(menuId);
              menuChecked.value[stringMenuId] = true;
            });

            // 设置按钮权限状态
            console.log("后端返回的权限代码:", permission_ids);
            console.log("后端返回的菜单ID:", menu_ids);

            // 确保所有扁平化菜单都有对应的按钮权限数组
            flattenedMenus.value.forEach(menu => {
              const menuId = String(menu.id);
              if (!menuButtonPermissions.value[menuId]) {
                menuButtonPermissions.value[menuId] = [];
                console.log(
                  "初始化菜单按钮权限数组:",
                  menuId,
                  "路径:",
                  menu.path
                );
              }
            });

            // 直接设置权限代码
            permission_ids.forEach((permissionCode: any) => {
              console.log("处理权限代码:", permissionCode);

              // 查找权限对应的菜单ID
              let menuId = "";
              for (const id in menuPermissions.value) {
                const permissions = menuPermissions.value[id];
                if (permissions.some((p: any) => p.code === permissionCode)) {
                  menuId = id;
                  break;
                }
              }

              if (menuId) {
                // 确保菜单被勾选
                menuChecked.value[menuId] = true;

                // 确保menuButtonPermissions[menuId]是一个数组
                if (!menuButtonPermissions.value[menuId]) {
                  // 使用展开运算符创建新对象，确保Vue检测到变化
                  menuButtonPermissions.value = {
                    ...menuButtonPermissions.value,
                    [menuId]: []
                  };
                }

                // 检查权限代码是否已经在数组中
                if (
                  !menuButtonPermissions.value[menuId].includes(permissionCode)
                ) {
                  // 创建新数组，确保Vue检测到变化
                  const updatedPermissions = [
                    ...menuButtonPermissions.value[menuId],
                    permissionCode
                  ];
                  menuButtonPermissions.value = {
                    ...menuButtonPermissions.value,
                    [menuId]: updatedPermissions
                  };
                  console.log(
                    "添加权限代码:",
                    permissionCode,
                    "到菜单:",
                    menuId
                  );
                  console.log("更新后的权限数组:", updatedPermissions);
                } else {
                  console.log(
                    "权限代码已经存在:",
                    permissionCode,
                    "在菜单:",
                    menuId
                  );
                }
              } else {
                console.log("未找到权限对应的菜单:", permissionCode);
              }
            });

            // 检查最终状态
            console.log("最终的菜单勾选状态:", menuChecked.value);
            console.log("最终的按钮权限状态:", menuButtonPermissions.value);
            console.log("菜单权限数据:", menuPermissions.value);

            // 检查每个扁平化菜单的权限状态
            flattenedMenus.value.forEach(menu => {
              const menuId = String(menu.id);
              const permissions = menuButtonPermissions.value[menuId] || [];
              console.log("菜单", menuId, menu.name, "的权限:", permissions);
            });

            // 权限数据加载完成后再显示对话框
            permissionDialogVisible.value = true;
          }
        })
        .catch(error => {
          ElMessage.error("获取角色权限失败");
          console.error("getRolePermissions error:", error);
        });
    });
  });
};

// 处理菜单勾选状态变化
const handleMenuCheck = (menuId: string, checked: boolean) => {
  menuChecked.value[menuId] = checked;

  // 如果取消勾选，清空该菜单的按钮权限
  if (!checked) {
    menuButtonPermissions.value[menuId] = [];
  }
};

// 提交角色表单
const handleSubmit = async () => {
  if (!formRef.value) return;
  await formRef.value.validate((valid: boolean) => {
    if (valid) {
      // 构建请求数据
      const submitData = {
        name: form.roleName,
        description: form.description,
        is_super: form.isSuper ? 1 : 0,
        company_id: form.companyId
      };

      // 调用后端API
      const apiUrl =
        dialogType.value === "add"
          ? "/api/role-menu/role"
          : `/api/role-menu/role/${form.id}`;
      const method = dialogType.value === "add" ? "post" : "put";

      http
        .request(method, apiUrl, { data: submitData })
        .then(response => {
          if (response?.code === 200) {
            ElMessage.success(
              dialogType.value === "add" ? "新增成功" : "编辑成功"
            );
            dialogVisible.value = false;
            getRoleList();
          } else {
            ElMessage.error(
              response?.message ||
                (dialogType.value === "add" ? "新增失败" : "编辑失败")
            );
          }
        })
        .catch(error => {
          ElMessage.error(dialogType.value === "add" ? "新增失败" : "编辑失败");
          console.error("handleSubmit error:", error);
        });
    }
  });
};

// 递归收集勾选的菜单ID
function collectCheckedMenus(menuTree: any[], collected: string[]) {
  menuTree.forEach(menu => {
    if (menuChecked.value[menu.id]) {
      collected.push(menu.id);
    }

    if (menu.children && menu.children.length > 0) {
      collectCheckedMenus(menu.children, collected);
    }
  });
  return collected;
}

// 提交权限分配
const handleSubmitPermission = () => {
  // 收集勾选的菜单ID
  const checkedMenuIds = collectCheckedMenus(menuTree.value, []);

  // 收集所有按钮权限
  const allPermissions = [];
  for (const menuId in menuButtonPermissions.value) {
    if (
      menuButtonPermissions.value[menuId] &&
      menuButtonPermissions.value[menuId].length > 0
    ) {
      // 直接使用前端生成的权限代码格式，不需要转换
      allPermissions.push(...menuButtonPermissions.value[menuId]);
    }
  }

  // 构建提交数据
  const submitData = {
    menu_ids: checkedMenuIds,
    permission_ids: allPermissions
  };

  // 提交到后端
  updateRolePermissions(currentRole.value.id, submitData)
    .then(response => {
      if (response?.code === 200) {
        ElMessage.success("权限分配成功");
        permissionDialogVisible.value = false;
      } else {
        ElMessage.error(response?.message || "权限分配失败");
      }
    })
    .catch(error => {
      ElMessage.error("权限分配失败");
      console.error("handleSubmitPermission error:", error);
    });
};
</script>

<style scoped>
.role-container {
  height: calc(100vh - 120px);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.main-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.main-card :deep(.el-card__body) {
  padding: 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* 角色列表区域 */
.role-list {
  flex: 1;
  min-height: 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* 角色列表表格 */
.role-list .el-table {
  flex: 1;
  min-height: 0;
}

.role-list .el-table__body-wrapper {
  overflow: auto;
}

.pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
}

.permission-content {
  max-height: calc(90vh - 120px);
  overflow-y: auto;
}

.permission-title {
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: bold;
  padding-bottom: 8px;
  border-bottom: 1px solid #e4e7ed;
}

.custom-menu-tree {
  padding: 4px;
}

.menu-item {
  margin-bottom: 4px;
}

.menu-header {
  margin-bottom: 4px;
  display: flex;
  align-items: center;
}

.menu-header-with-permissions {
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.menu-header-with-permissions:hover {
  background-color: #f5f7fa;
}

.menu-checkbox {
  white-space: nowrap;
  flex-shrink: 0;
  min-width: 100px;
}

.sub-menu {
  margin-top: 4px;
  margin-left: 20px;
}

.page-menu-item {
  margin-top: 2px;
}

.button-permissions {
  padding: 6px 10px;
  margin-top: 2px;
  margin-bottom: 4px;
  margin-left: 20px;
  background-color: #f9f9f9;
  border-left: 3px solid #409eff;
  border-radius: 4px;
}

.button-permissions-group {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.permission-checkbox {
  margin-right: 0 !important;
  white-space: nowrap;
}

.permission-checkbox .el-checkbox__label {
  font-size: 13px;
}

/* 新增：同一排显示的按钮权限样式 */
.inline-button-permissions {
  flex-grow: 1;
  min-width: 300px;
}

.inline-button-permissions-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.inline-permission-checkbox {
  margin-right: 0 !important;
  white-space: nowrap;
  font-size: 12px;
}

.inline-permission-checkbox .el-checkbox__label {
  font-size: 12px;
  padding: 2px 4px;
  border-radius: 3px;
  transition: all 0.2s;
}

.inline-permission-checkbox:hover .el-checkbox__label {
  background-color: #ecf5ff;
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

.mt-4 {
  margin-top: 24px;
}
</style>
