<script setup lang="ts">
import { useNav } from "@/layout/hooks/useNav";
import LaySearch from "../lay-search/index.vue";
import LayNotice from "../lay-notice/index.vue";
import LayNavMix from "../lay-sidebar/NavMix.vue";
import LaySidebarFullScreen from "../lay-sidebar/components/SidebarFullScreen.vue";
import LaySidebarBreadCrumb from "../lay-sidebar/components/SidebarBreadCrumb.vue";
import LaySidebarTopCollapse from "../lay-sidebar/components/SidebarTopCollapse.vue";
import { LockScreen } from "@/components/LockScreen";
import CompanySelector from "@/components/CompanySelector/index.vue";
import { ref, onMounted, onUnmounted, watch } from "vue";
import { useStoreStore } from "@/store/modules/store";
import { useUserStoreHook } from "@/store/modules/user";
import emitter from "@/utils/eventBus";

import LogoutCircleRLine from "~icons/ri/logout-circle-r-line";
import Setting from "~icons/ri/settings-3-line";
import Lock from "~icons/ri/lock-line";

const {
  layout,
  device,
  logout,
  onPanel,
  pureApp,
  username,
  userAvatar,
  avatarsStyle,
  toggleSideBar,
  isLockScreen,
  onUnlock,
  showLockScreen
} = useNav();

const storeStore = useStoreStore();
const userStore = useUserStoreHook();

const selectedStoreId = ref<number | null>(null);
const selectedCompanyId = ref<number | null>(userStore.selectedCompanyId || userStore.companyId);

onMounted(() => {
  storeStore.initStores().then(() => {
    if (storeStore.currentStore) {
      selectedStoreId.value = storeStore.currentStore.id;
    }
  });
  
  emitter.on("company-change", handleCompanyChange);
});

onUnmounted(() => {
  emitter.off("company-change", handleCompanyChange);
});

watch(
  () => storeStore.currentStore,
  newStore => {
    if (newStore) {
      selectedStoreId.value = newStore.id;
    }
  }
);

watch(
  () => userStore.selectedCompanyId,
  newCompanyId => {
    if (newCompanyId) {
      selectedCompanyId.value = newCompanyId;
    }
  }
);

const handleStoreChange = (storeId: number) => {
  const selectedStore = storeStore.stores.find(store => store.id === storeId);
  if (selectedStore) {
    selectedStoreId.value = storeId;
    storeStore.setCurrentStore(selectedStore);
    emitter.emit("store-change", storeId);
  }
};

const handleCompanyChange = (companyId: number) => {
  selectedCompanyId.value = companyId;
  
  storeStore.initStores(companyId).then(() => {
    if (storeStore.stores.length > 0) {
      selectedStoreId.value = storeStore.stores[0].id;
      storeStore.setCurrentStore(storeStore.stores[0]);
    } else {
      selectedStoreId.value = null;
      storeStore.currentStore = null;
    }
  });
};
</script>

<template>
  <div class="navbar bg-[#fff] shadow-xs shadow-[rgba(0,21,41,0.08)]">
    <LaySidebarTopCollapse
      v-if="device === 'mobile'"
      class="hamburger-container"
      :is-active="pureApp.sidebar.opened"
      @toggleClick="toggleSideBar"
    />

    <LaySidebarBreadCrumb
      v-if="layout !== 'mix' && device !== 'mobile'"
      class="breadcrumb-container"
    />

    <LayNavMix v-if="layout === 'mix'" />

    <div v-if="layout === 'vertical'" class="vertical-header-right">
      <!-- 公司选择（仅超级管理员可见） -->
      <div v-if="userStore.isSuper" class="company-select-container mr-2">
        <CompanySelector
          v-model="selectedCompanyId"
          @change="handleCompanyChange"
        />
      </div>
      <!-- 门店选择 -->
      <div class="store-select-container mr-2">
        <el-select
          v-model="selectedStoreId"
          placeholder="选择门店"
          style="width: 120px"
          @change="handleStoreChange"
        >
          <el-option
            v-for="store in storeStore.stores"
            :key="store.id"
            :label="store.name"
            :value="store.id"
          />
        </el-select>
      </div>
      <!-- 菜单搜索 -->
      <LaySearch id="header-search" />
      <!-- 全屏 -->
      <LaySidebarFullScreen id="full-screen" />
      <!-- 消息通知 -->
      <LayNotice id="header-notice" />
      <!-- 退出登录 -->
      <el-dropdown trigger="click">
        <span class="el-dropdown-link navbar-bg-hover select-none">
          <img :src="userAvatar" :style="avatarsStyle" />
          <p v-if="username" class="dark:text-white">{{ username }}</p>
        </span>
        <template #dropdown>
          <el-dropdown-menu class="logout">
            <el-dropdown-item @click="showLockScreen">
              <IconifyIconOffline :icon="Lock" style="margin: 5px" />
              锁定屏幕
            </el-dropdown-item>
            <el-dropdown-item @click="logout">
              <IconifyIconOffline
                :icon="LogoutCircleRLine"
                style="margin: 5px"
              />
              退出系统
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
      <span
        class="set-icon navbar-bg-hover"
        title="打开系统配置"
        @click="onPanel"
      >
        <IconifyIconOffline :icon="Setting" />
      </span>
    </div>

    <!-- 锁定屏幕 -->
    <LockScreen :show="isLockScreen" @unlock="onUnlock" />
  </div>
</template>

<style lang="scss" scoped>
.navbar {
  width: 100%;
  height: 48px;
  overflow: hidden;

  .hamburger-container {
    float: left;
    height: 100%;
    line-height: 48px;
    cursor: pointer;
  }

  .vertical-header-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-width: 480px;
    height: 48px;
    color: #000000d9;

    .store-select-container,
    .company-select-container {
      height: 48px;
      display: flex;
      align-items: center;
    }

    .el-dropdown-link {
      display: flex;
      align-items: center;
      justify-content: space-around;
      height: 48px;
      padding: 10px;
      color: #000000d9;
      cursor: pointer;

      p {
        font-size: 14px;
      }

      img {
        width: 22px;
        height: 22px;
        border-radius: 50%;
      }
    }
  }

  .breadcrumb-container {
    float: left;
    margin-left: 16px;
  }
}

.logout {
  width: 120px;

  ::v-deep(.el-dropdown-menu__item) {
    display: inline-flex;
    flex-wrap: wrap;
    min-width: 100%;
  }
}
</style>
