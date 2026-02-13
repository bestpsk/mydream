import {
  type RouterHistory,
  type RouteRecordRaw,
  type RouteComponent,
  createWebHistory,
  createWebHashHistory
} from "vue-router";
import { router } from "./index";
import { isProxy, toRaw } from "vue";
import { useTimeoutFn } from "@vueuse/core";
import {
  isString,
  cloneDeep,
  isAllEmpty,
  intersection,
  storageLocal,
  isIncludeAllChildren
} from "@pureadmin/utils";
import { getConfig } from "@/config";
import { buildHierarchyTree } from "@/utils/tree";
import { userKey, type DataInfo } from "@/utils/auth";
import { type menuType, routerArrays } from "@/layout/types";
import { useMultiTagsStoreHook } from "@/store/modules/multiTags";
import { usePermissionStoreHook } from "@/store/modules/permission";
const IFrame = () => import("@/layout/frame.vue");
// https://cn.vitejs.dev/guide/features.html#glob-import
const modulesRoutes = import.meta.glob("/src/views/**/*.{vue,tsx}");

// 动态路由
import { getAsyncRoutes } from "@/api/routes";

function handRank(routeInfo: any) {
  const { name, path, parentId, meta } = routeInfo;
  return isAllEmpty(parentId)
    ? isAllEmpty(meta?.rank) ||
      (meta?.rank === 0 && name !== "Home" && path !== "/")
      ? true
      : false
    : false;
}

/** 按照路由中meta下的rank等级升序来排序路由 */
function ascending(arr: any[]) {
  arr.forEach((v, index) => {
    // 当rank不存在时，根据顺序自动创建，首页路由永远在第一位
    if (handRank(v)) v.meta.rank = index + 2;
  });
  return arr.sort(
    (a: { meta: { rank: number } }, b: { meta: { rank: number } }) => {
      return a?.meta.rank - b?.meta.rank;
    }
  );
}

/** 过滤meta中showLink为false的菜单 */
function filterTree(data: RouteComponent[]) {
  const newTree = cloneDeep(data).filter(
    (v: { meta: { showLink: boolean } }) => v.meta?.showLink !== false
  );
  newTree.forEach(
    (v: { children }) => v.children && (v.children = filterTree(v.children))
  );
  return newTree;
}

/** 过滤children长度为0的的目录，当目录下没有菜单时，会过滤此目录，目录没有赋予roles权限，当目录下只要有一个菜单有显示权限，那么此目录就会显示 */
function filterChildrenTree(data: RouteComponent[]) {
  const newTree = cloneDeep(data).filter((v: any) => v?.children?.length !== 0);
  newTree.forEach(
    (v: { children }) => v.children && (v.children = filterTree(v.children))
  );
  return newTree;
}

/** 判断两个数组彼此是否存在相同值 */
function isOneOfArray(a: Array<string>, b: Array<string>) {
  return Array.isArray(a) && Array.isArray(b)
    ? intersection(a, b).length > 0
      ? true
      : false
    : true;
}

/** 从localStorage里取出当前登录用户的角色roles，过滤无权限的菜单 */
function filterNoPermissionTree(data: RouteComponent[]) {
  const currentRoles =
    storageLocal().getItem<DataInfo<number>>(userKey)?.roles ?? [];
  const newTree = cloneDeep(data).filter((v: any) =>
    isOneOfArray(v.meta?.roles, currentRoles)
  );
  newTree.forEach(
    (v: any) => v.children && (v.children = filterNoPermissionTree(v.children))
  );
  return filterChildrenTree(newTree);
}

/** 通过指定 `key` 获取父级路径集合，默认 `key` 为 `path` */
function getParentPaths(value: string, routes: RouteRecordRaw[], key = "path") {
  // 深度遍历查找
  function dfs(routes: RouteRecordRaw[], value: string, parents: string[]) {
    for (let i = 0; i < routes.length; i++) {
      const item = routes[i];
      // 返回父级path
      if (item[key] === value) return parents;
      // children不存在或为空则不递归
      if (!item.children || !item.children.length) continue;
      // 往下查找时将当前path入栈
      parents.push(item.path);

      if (dfs(item.children, value, parents).length) return parents;
      // 深度遍历查找未找到时当前path 出栈
      parents.pop();
    }
    // 未找到时返回空数组
    return [];
  }

  return dfs(routes, value, []);
}

/** 查找对应 `path` 的路由信息 */
function findRouteByPath(path: string, routes: RouteRecordRaw[]) {
  let res = routes.find((item: { path: string }) => item.path == path);
  if (res) {
    return isProxy(res) ? toRaw(res) : res;
  } else {
    for (let i = 0; i < routes.length; i++) {
      if (
        routes[i].children instanceof Array &&
        routes[i].children.length > 0
      ) {
        res = findRouteByPath(path, routes[i].children);
        if (res) {
          return isProxy(res) ? toRaw(res) : res;
        }
      }
    }
    return null;
  }
}

/** 动态路由注册完成后，再添加全屏404（页面不存在）页面，避免刷新动态路由页面时误跳转到404页面 */
function addPathMatch() {
  if (!router.hasRoute("pathMatch")) {
    router.addRoute({
      path: "/:pathMatch(.*)*",
      name: "PageNotFound",
      component: () => import("@/views/error/404.vue"),
      meta: {
        title: "404",
        showLink: false
      }
    });
  }
}

/**
 * 处理动态路由（后端返回的路由）
 * @param routeList 后端返回的路由列表
 */
function handleAsyncRoutes(routeList) {
  try {
    // 确保routeList是数组
    const routes = Array.isArray(routeList) ? routeList : [];

    // 检查是否包含静态路由（通过检查是否有backstage: false的路由）
    const containsStaticRoutes = routes.some(
      route => route.meta?.backstage === false
    );

    let dynamicRoutes = routes;
    if (containsStaticRoutes) {
      // 如果包含静态路由，提取出动态路由部分
      dynamicRoutes = routes.filter(route => route.meta?.backstage !== false);
    }

    // 转换后端返回的路由数据，确保字段名称和结构正确
    const transformedRoutes = transformBackendRoutes(dynamicRoutes);

    if (transformedRoutes.length === 0) {
      // 当没有动态路由时，使用静态路由作为完整路由列表
      const permissionStore = usePermissionStoreHook();
      usePermissionStoreHook().handleWholeMenus(permissionStore.constantMenus);
    } else {
      // 处理动态路由，添加组件引用
      const processedRoutes = addAsyncRoutes(transformedRoutes);

      // 格式化后的新路由列表
      const newRoutes = formatFlatteningRoutes(processedRoutes);
      const newRoutePaths = newRoutes.map(v => v.path);

      // 从现有路由列表中移除已删除的路由
      const existingRoutes = router.options.routes[0]?.children || [];
      const filteredRoutes = existingRoutes.filter(v => {
        // 保留静态路由和新路由中存在的路由
        return v.meta?.backstage !== true || newRoutePaths.includes(v.path);
      });

      // 更新路由列表
      if (router.options.routes[0]) {
        router.options.routes[0].children = filteredRoutes;

        // 重新添加所有新路由
        newRoutes.forEach((v: RouteRecordRaw) => {
          // 检查路由是否已存在
          if (filteredRoutes.findIndex(value => value.path === v.path) === -1) {
            // 切记将路由push到routes后还需要使用addRoute，这样路由才能正常跳转
            filteredRoutes.push(v);
          }
        });

        // 最终路由进行升序
        ascending(filteredRoutes);

        // 重新添加所有路由到路由器
        filteredRoutes.forEach((v: RouteRecordRaw) => {
          if (!router.hasRoute(v?.name)) router.addRoute(v);
        });

        // 重建根路由
        const flattenRouters: any = router
          .getRoutes()
          .find(n => n.path === "/");
        if (flattenRouters) {
          // 移除旧的根路由
          router.removeRoute(flattenRouters.name);
          // 创建新的根路由
          const newRootRoute = {
            ...flattenRouters,
            children: filteredRoutes
          };
          // 添加新的根路由
          router.addRoute(newRootRoute);
        }
      }

      // 获取静态路由
      const permissionStore = usePermissionStoreHook();
      // 合并静态路由和动态路由
      const allRoutes = permissionStore.constantMenus.concat(processedRoutes);
      // 调用handleWholeMenus，传递完整的路由列表
      usePermissionStoreHook().handleWholeMenus(allRoutes);
    }
    if (!useMultiTagsStoreHook().getMultiTagsCache) {
      useMultiTagsStoreHook().handleTags("equal", [
        ...routerArrays,
        ...usePermissionStoreHook().flatteningRoutes.filter(
          v => v?.meta?.fixedTag
        )
      ]);
    }
    addPathMatch();
  } catch (error) {
    console.error("处理动态路由失败:", error);
    // 发生错误时，使用静态路由
    const permissionStore = usePermissionStoreHook();
    usePermissionStoreHook().handleWholeMenus(permissionStore.constantMenus);
    addPathMatch();
  }
}

/** 转换后端返回的路由数据，确保字段名称和结构正确 */
function transformBackendRoutes(routes) {
  return routes.map(route => {
    // 转换字段名称和结构
    const transformedRoute = {
      id: route.id,
      path: route.path,
      name: route.name,
      component: route.component,
      redirect: route.redirect,
      meta: {
        title: route.meta?.title || route.name,
        icon: route.meta?.icon || route.icon,
        roles: route.meta?.roles,
        frameSrc: route.meta?.frameSrc || route.frame_src,
        showLink: route.meta?.showLink !== false,
        fixedTag: route.meta?.fixedTag,
        rank: route.meta?.rank || route.menu_rank,
        showParent: route.meta?.showParent || false
      }
    };

    // 只在后端返回了非空的children数组时才添加children字段
    if (route.children && route.children.length > 0) {
      transformedRoute.children = transformBackendRoutes(route.children);
    }

    return transformedRoute;
  });
}

/**
 * 初始化路由
 * @returns Promise<Router> 路由实例
 */
function initRouter() {
  try {
    // 先初始化静态菜单
    usePermissionStoreHook().initMenus();

    if (getConfig()?.CachingAsyncRoutes) {
      // 开启动态路由缓存本地localStorage
      const key = "async-routes";

      // 尝试从缓存中获取动态路由
      const asyncRoutesCache = storageLocal().getItem(key) as any;
      if (asyncRoutesCache && asyncRoutesCache?.length > 0) {
        return new Promise(resolve => {
          console.log("Using cached async routes:", asyncRoutesCache);
          // 处理缓存的动态路由
          handleAsyncRoutes(cloneDeep(asyncRoutesCache));
          resolve(router);
        });
      } else {
        return new Promise(resolve => {
          console.log("Fetching async routes from server...");
          getAsyncRoutes()
            .then(response => {
              console.log("getAsyncRoutes response:", response);
              if (response?.code === 200) {
                // 从 response.data 中获取路由数据
                const routesData = response.data;
                console.log("Backend returned routes data:", routesData);

                // 检查 routesData 是否是数组，如果不是，将其转换为数组
                const routesArray = Array.isArray(routesData)
                  ? routesData
                  : [routesData];

                // 处理动态路由
                handleAsyncRoutes(cloneDeep(routesArray));

                // 缓存动态路由
                storageLocal().setItem(key, routesArray);
                console.log("Async routes cached to localStorage");
              } else {
                console.error(
                  "getAsyncRoutes failed with code:",
                  response?.code
                );
                // 处理失败情况，使用静态路由
                const permissionStore = usePermissionStoreHook();
                usePermissionStoreHook().handleWholeMenus(
                  permissionStore.constantMenus
                );
              }
              resolve(router);
            })
            .catch(error => {
              console.error("getAsyncRoutes error:", error);
              // 发生错误时，使用静态路由
              const permissionStore = usePermissionStoreHook();
              usePermissionStoreHook().handleWholeMenus(
                permissionStore.constantMenus
              );
              resolve(router);
            });
        });
      }
    } else {
      return new Promise(resolve => {
        console.log("Fetching async routes from server (no cache)...");
        getAsyncRoutes()
          .then(response => {
            console.log("getAsyncRoutes response (no cache):", response);
            if (response?.code === 200) {
              // 从 response.data 中获取路由数据
              const routesData = response.data;
              console.log(
                "Backend returned routes data (no cache):",
                routesData
              );

              // 检查 routesData 是否是数组，如果不是，将其转换为数组
              const routesArray = Array.isArray(routesData)
                ? routesData
                : [routesData];
              handleAsyncRoutes(cloneDeep(routesArray));
            } else {
              console.error("getAsyncRoutes failed with code:", response?.code);
              // 处理失败情况，使用静态路由
              const permissionStore = usePermissionStoreHook();
              usePermissionStoreHook().handleWholeMenus(
                permissionStore.constantMenus
              );
            }
            resolve(router);
          })
          .catch(error => {
            console.error("getAsyncRoutes error (no cache):", error);
            // 发生错误时，使用静态路由
            const permissionStore = usePermissionStoreHook();
            usePermissionStoreHook().handleWholeMenus(
              permissionStore.constantMenus
            );
            resolve(router);
          });
      });
    }
  } catch (error) {
    console.error("initRouter error:", error);
    // 发生错误时，使用静态路由
    const permissionStore = usePermissionStoreHook();
    usePermissionStoreHook().handleWholeMenus(permissionStore.constantMenus);
    return Promise.resolve(router);
  }
}

/**
 * 将多级嵌套路由处理成一维数组
 * @param routesList 传入路由
 * @returns 返回处理后的一维路由
 */
function formatFlatteningRoutes(routesList: RouteRecordRaw[]) {
  if (routesList.length === 0) return routesList;
  let hierarchyList = buildHierarchyTree(routesList);
  for (let i = 0; i < hierarchyList.length; i++) {
    if (hierarchyList[i].children) {
      hierarchyList = hierarchyList
        .slice(0, i + 1)
        .concat(hierarchyList[i].children, hierarchyList.slice(i + 1));
    }
  }
  return hierarchyList;
}

/**
 * 一维数组处理成多级嵌套数组（三级及以上的路由全部拍成二级，keep-alive 只支持到二级缓存）
 * https://github.com/pure-admin/vue-pure-admin/issues/67
 * @param routesList 处理后的一维路由菜单数组
 * @returns 返回将一维数组重新处理成规定路由的格式
 */
function formatTwoStageRoutes(routesList: RouteRecordRaw[]) {
  if (routesList.length === 0) return routesList;
  const newRoutesList: RouteRecordRaw[] = [];
  routesList.forEach((v: RouteRecordRaw) => {
    if (v.path === "/") {
      newRoutesList.push({
        component: v.component,
        name: v.name,
        path: v.path,
        redirect: v.redirect,
        meta: v.meta,
        children: []
      });
    } else {
      newRoutesList[0]?.children.push({ ...v });
    }
  });
  return newRoutesList;
}

/** 处理缓存路由（添加、删除、刷新） */
function handleAliveRoute({ name }: ToRouteType, mode?: string) {
  switch (mode) {
    case "add":
      usePermissionStoreHook().cacheOperate({
        mode: "add",
        name
      });
      break;
    case "delete":
      usePermissionStoreHook().cacheOperate({
        mode: "delete",
        name
      });
      break;
    case "refresh":
      usePermissionStoreHook().cacheOperate({
        mode: "refresh",
        name
      });
      break;
    default:
      usePermissionStoreHook().cacheOperate({
        mode: "delete",
        name
      });
      useTimeoutFn(() => {
        usePermissionStoreHook().cacheOperate({
          mode: "add",
          name
        });
      }, 100);
  }
}

/** 过滤后端传来的动态路由 重新生成规范路由 */
function addAsyncRoutes(arrRoutes: Array<RouteRecordRaw>) {
  if (!arrRoutes || !arrRoutes.length) return [];
  const modulesRoutesKeys = Object.keys(modulesRoutes);
  arrRoutes.forEach((v: RouteRecordRaw) => {
    // 将backstage属性加入meta，标识此路由为后端返回路由
    v.meta.backstage = true;
    // 父级的redirect属性取值：如果子级存在且父级的redirect属性不存在，默认取第一个子级的path；如果子级存在且父级的redirect属性存在，取存在的redirect属性，会覆盖默认值
    if (v?.children && v.children.length && !v.redirect)
      v.redirect = v.children[0].path;
    // 父级的name属性取值：如果子级存在且父级的name属性不存在，默认取第一个子级的name；如果子级存在且父级的name属性存在，取存在的name属性，会覆盖默认值（注意：测试中发现父级的name不能和子级name重复，如果重复会造成重定向无效（跳转404），所以这里给父级的name起名的时候后面会自动加上`Parent`，避免重复）
    if (v?.children && v.children.length && !v.name)
      v.name = (v.children[0].name as string) + "Parent";
    if (v.meta?.frameSrc) {
      v.component = IFrame;
    } else if (v.component === "Layout") {
      // 特殊处理Layout组件
      v.component = () => import("@/layout/index.vue");
    } else {
      // 对后端传component组件路径和不传做兼容（如果后端传component组件路径，那么path可以随便写，如果不传，component组件路径会跟path保持一致）
      let componentPath = v?.component || v.path;

      // 标准化组件路径，确保正确匹配
      if (componentPath) {
        // 移除开头的斜杠
        if (componentPath.startsWith("/")) {
          componentPath = componentPath.substring(1);
        }
        // 确保路径以.vue结尾
        if (
          !componentPath.endsWith(".vue") &&
          !componentPath.endsWith(".tsx")
        ) {
          componentPath += ".vue";
        }
        // 确保路径不包含多余的前缀
        if (!componentPath.startsWith("views/")) {
          componentPath = "views/" + componentPath;
        }
      }

      // 查找匹配的组件
      let matchedIndex = -1;

      // 优先匹配完整的组件路径
      if (componentPath) {
        // 尝试直接匹配
        matchedIndex = modulesRoutesKeys.findIndex(ev =>
          ev.includes(componentPath)
        );

        // 如果没有匹配到，尝试匹配转换后的路径
        if (matchedIndex === -1) {
          // 转换为可能的路径格式
          const possiblePaths = [
            componentPath, // 原始路径
            "/src/" + componentPath, // 带/src/前缀
            componentPath.replace("views/", ""), // 不带views/前缀
            "/src/views/" + componentPath.replace("views/", "") // 带/src/views/前缀
          ];

          for (const path of possiblePaths) {
            matchedIndex = modulesRoutesKeys.findIndex(ev => ev.includes(path));
            if (matchedIndex !== -1) break;
          }
        }
      }

      // 如果没有匹配到，尝试匹配路径
      if (matchedIndex === -1 && v.path) {
        let pathComponent = v.path;
        if (pathComponent.startsWith("/")) {
          pathComponent = pathComponent.substring(1);
        }

        // 尝试多种可能的路径格式
        const possiblePaths = [
          pathComponent, // 原始路径
          "views/" + pathComponent, // 带views/前缀
          "/src/views/" + pathComponent, // 带/src/views/前缀
          pathComponent + ".vue", // 带.vue后缀
          "views/" + pathComponent + ".vue", // 带views/前缀和.vue后缀
          "/src/views/" + pathComponent + ".vue" // 带/src/views/前缀和.vue后缀
        ];

        for (const path of possiblePaths) {
          matchedIndex = modulesRoutesKeys.findIndex(ev => ev.includes(path));
          if (matchedIndex !== -1) break;
        }
      }

      // 特殊处理部门职位管理路径
      if (
        matchedIndex === -1 &&
        (v.path.includes("department") ||
          v.path.includes("position") ||
          v.path.includes("dept-position"))
      ) {
        // 直接匹配部门职位管理组件
        matchedIndex = modulesRoutesKeys.findIndex(ev =>
          ev.includes("dept-position")
        );
      }

      if (matchedIndex !== -1 && modulesRoutesKeys[matchedIndex]) {
        v.component = modulesRoutes[modulesRoutesKeys[matchedIndex]];
      } else {
        // 如果找不到组件，使用默认的404组件
        v.component = () => import("@/views/error/404.vue");
      }
    }
    if (v?.children && v.children.length) {
      addAsyncRoutes(v.children);
    }
  });
  return arrRoutes;
}

/** 获取路由历史模式 https://next.router.vuejs.org/zh/guide/essentials/history-mode.html */
function getHistoryMode(routerHistory): RouterHistory {
  // len为1 代表只有历史模式 为2 代表历史模式中存在base参数 https://next.router.vuejs.org/zh/api/#%E5%8F%82%E6%95%B0-1
  const historyMode = routerHistory.split(",");
  const leftMode = historyMode[0];
  const rightMode = historyMode[1];
  // no param
  if (historyMode.length === 1) {
    if (leftMode === "hash") {
      return createWebHashHistory("");
    } else if (leftMode === "h5") {
      return createWebHistory("");
    }
  } //has param
  else if (historyMode.length === 2) {
    if (leftMode === "hash") {
      return createWebHashHistory(rightMode);
    } else if (leftMode === "h5") {
      return createWebHistory(rightMode);
    }
  }
}

/** 获取用户的按钮级别权限 */
function getUserPermissions(): Array<string> {
  const userInfo = storageLocal().getItem<DataInfo<number>>(userKey);
  return userInfo?.permissions ?? [];
}

/** 是否有按钮级别的权限（根据用户的permissions字段进行判断）*/
function hasAuth(value: string | Array<string>): boolean {
  if (!value) return false;

  // 检查是否为超级管理员
  const userInfo = storageLocal().getItem<DataInfo<number>>(userKey);
  if (userInfo?.isSuper) return true;

  /** 从用户信息中获取按钮级别的所有权限code值 */
  const userPermissions = getUserPermissions();
  if (!userPermissions || userPermissions.length === 0) return false;

  // 检查是否有全部权限
  if (userPermissions.includes("*:*:*")) return true;

  const isAuths = isString(value)
    ? userPermissions.includes(value)
    : isIncludeAllChildren(value, userPermissions);
  return isAuths ? true : false;
}

function handleTopMenu(route) {
  if (route?.children && route.children.length > 1) {
    if (route.redirect) {
      return route.children.filter(cur => cur.path === route.redirect)[0];
    } else {
      return route.children[0];
    }
  } else {
    return route;
  }
}

/** 获取所有菜单中的第一个菜单（顶级菜单）*/
function getTopMenu(tag = false): menuType {
  const topMenu = handleTopMenu(
    usePermissionStoreHook().wholeMenus[0]?.children[0]
  );
  // 如果没有菜单，返回默认的欢迎页菜单
  if (!topMenu) {
    const defaultMenu = {
      path: "/welcome",
      name: "Welcome",
      component: () => import("@/views/welcome/index.vue"),
      meta: {
        title: "欢迎",
        icon: "HomeFilled",
        showLink: true
      }
    };
    tag && useMultiTagsStoreHook().handleTags("push", defaultMenu);
    return defaultMenu;
  }
  tag && useMultiTagsStoreHook().handleTags("push", topMenu);
  return topMenu;
}

export {
  hasAuth,
  ascending,
  filterTree,
  initRouter,
  getTopMenu,
  addPathMatch,
  isOneOfArray,
  getHistoryMode,
  addAsyncRoutes,
  getParentPaths,
  findRouteByPath,
  handleAliveRoute,
  formatTwoStageRoutes,
  formatFlatteningRoutes,
  filterNoPermissionTree
};
