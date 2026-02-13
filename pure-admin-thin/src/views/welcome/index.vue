<script setup lang="ts">
defineOptions({
  name: "Welcome"
});

import { useUserStoreHook } from "@/store/modules/user";

const userStore = useUserStoreHook();
</script>

<template>
  <div class="welcome-container">
    <h1>链美美业数字化管理系统</h1>
    <p class="welcome-message">
      欢迎您，{{
        userStore.employeeName ||
        userStore.username ||
        userStore.nickname ||
        "用户"
      }}！
    </p>
  </div>
</template>

<style scoped>
.welcome-container {
  padding: 40px;
  text-align: center;
}

.welcome-message {
  margin-top: 20px;
  font-size: 18px;
  color: #666;
}
</style>
