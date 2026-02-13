<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useUserStoreHook } from "@/store/modules/user";
import Motion from "@/views/login/utils/motion";
import { message } from "@/utils/message";

defineOptions({
  name: "LockScreen"
});

const props = defineProps<{
  show: boolean;
}>();

const emit = defineEmits<{
  (e: "update:show", value: boolean): void;
  (e: "unlock"): void;
}>();

const password = ref("");
const loading = ref(false);
const isPasswordDialogVisible = ref(false);
const isFullScreenLocked = ref(false);
const username = computed(() => useUserStoreHook().username);
const currentTime = ref(new Date());
const timeInterval = ref<number | null>(null);

// 计算属性
const dialogVisible = computed({
  get: () => props.show,
  set: value => emit("update:show", value)
});

const formattedTime = computed(() => {
  const hours = currentTime.value.getHours().toString().padStart(2, "0");
  const minutes = currentTime.value.getMinutes().toString().padStart(2, "0");
  return `${hours}:${minutes}`;
});

const formattedDate = computed(() => {
  const year = currentTime.value.getFullYear();
  const month = (currentTime.value.getMonth() + 1).toString().padStart(2, "0");
  const day = currentTime.value.getDate().toString().padStart(2, "0");
  return `${year}-${month}-${day}`;
});

const formattedWeekday = computed(() => {
  const weekdays = ["周日", "周一", "周二", "周三", "周四", "周五", "周六"];
  return weekdays[currentTime.value.getDay()];
});

// 方法
const updateCurrentTime = () => {
  currentTime.value = new Date();
};

// 点击锁定按钮
const handleLock = () => {
  if (!password.value) {
    message("请输入密码", { type: "warning" });
    return;
  }

  loading.value = true;

  setTimeout(() => {
    loading.value = false;
    isPasswordDialogVisible.value = false;
    password.value = "";
    isFullScreenLocked.value = true;
    message("屏幕已锁定", { type: "success" });
  }, 1000);
};

// 点击解锁按钮
const handleUnlock = () => {
  if (!password.value) {
    message("请输入密码", { type: "warning" });
    return;
  }

  loading.value = true;

  setTimeout(() => {
    loading.value = false;
    isPasswordDialogVisible.value = false;
    password.value = "";
    isFullScreenLocked.value = false;
    dialogVisible.value = false;
    emit("unlock");
    message("解锁成功", { type: "success" });
  }, 1000);
};

// 取消锁定/解锁
const cancelLock = () => {
  if (!isFullScreenLocked.value) {
    // 如果是锁定状态，取消关闭对话框
    isPasswordDialogVisible.value = false;
    password.value = "";
    dialogVisible.value = false;
  } else {
    // 如果是解锁状态，取消返回全屏锁定界面
    isPasswordDialogVisible.value = false;
    password.value = "";
  }
};

// 点击解锁按钮
const showUnlockDialog = () => {
  password.value = "";
  isPasswordDialogVisible.value = true;
};

// 键盘事件
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === "Enter") {
    if (isFullScreenLocked.value) {
      handleUnlock();
    } else {
      handleLock();
    }
  }
};

// 监听显示状态变化
const watchShow = () => {
  if (props.show && !isFullScreenLocked.value) {
    isPasswordDialogVisible.value = true;
  }
};

// 生命周期
onMounted(() => {
  timeInterval.value = window.setInterval(updateCurrentTime, 1000);

  // 监听刷新事件，保持锁屏状态
  window.addEventListener("beforeunload", () => {
    if (isFullScreenLocked.value) {
      localStorage.setItem("isLocked", "true");
    }
  });

  // 初始化时检查是否有锁屏状态
  const savedLockState = localStorage.getItem("isLocked");
  if (savedLockState === "true") {
    isFullScreenLocked.value = true;
  }
});

onUnmounted(() => {
  if (timeInterval.value) {
    clearInterval(timeInterval.value);
  }
  window.removeEventListener("beforeunload", () => {});
});
</script>

<template>
  <!-- 图2：全屏锁定界面 -->
  <div v-if="isFullScreenLocked" class="full-screen-lock">
    <div class="lock-content">
      <div class="time">{{ formattedTime }}</div>
      <div class="date">{{ formattedDate }} {{ formattedWeekday }}</div>
      <div class="unlock-hint" @click="showUnlockDialog">
        <el-icon :size="24"><Lock /></el-icon>
        <span>点击解锁</span>
      </div>
    </div>
  </div>

  <!-- 图1/图4：密码输入对话框 -->
  <el-dialog
    v-model="isPasswordDialogVisible"
    :title="isFullScreenLocked ? '解锁屏幕' : '锁定屏幕'"
    width="450px"
    :close-on-click-modal="false"
    :close-on-press-escape="false"
    :show-close="false"
    center
    class="lock-screen-dialog"
    @closed="password = ''"
  >
    <div class="lock-screen-content">
      <Motion :delay="100">
        <div class="user-info">
          <div class="avatar-container">
            <img src="@/assets/user.jpg" alt="用户头像" class="user-avatar" />
          </div>
          <h3 class="user-name">{{ username || "Admin" }}</h3>
        </div>
      </Motion>

      <Motion :delay="200">
        <div class="password-section">
          <el-input
            v-model="password"
            type="password"
            :placeholder="
              isFullScreenLocked ? '请输入密码解锁' : '请输入密码锁定'
            "
            show-password
            class="password-input"
            :autofocus="true"
            @keydown="handleKeydown"
          />
        </div>
      </Motion>

      <Motion :delay="300">
        <div class="action-section">
          <el-button
            type="primary"
            :loading="loading"
            class="primary-button"
            @click="isFullScreenLocked ? handleUnlock : handleLock"
          >
            {{ isFullScreenLocked ? "解锁" : "锁定" }}
          </el-button>
          <el-button
            :loading="loading"
            class="secondary-button"
            @click="cancelLock"
          >
            {{ isFullScreenLocked ? "取消" : "取消" }}
          </el-button>
        </div>
      </Motion>
    </div>
  </el-dialog>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { Lock } from "@element-plus/icons-vue";

export default defineComponent({
  components: { Lock }
});
</script>

<style lang="scss" scoped>
/* 动画 */
@keyframes pulse {
  0% {
    box-shadow: 0 12px 32px rgb(102 126 234 / 40%);
  }

  50% {
    box-shadow: 0 16px 40px rgb(102 126 234 / 50%);
  }

  100% {
    box-shadow: 0 12px 32px rgb(102 126 234 / 40%);
  }
}

/* 响应式设计 */
@media (width <= 768px) {
  .full-screen-lock {
    .time {
      font-size: 4rem;
    }

    .date {
      font-size: 1.2rem;
    }
  }

  .lock-screen-dialog {
    width: 90% !important;
    max-width: 400px;
  }
}

.full-screen-lock {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100vw;
  height: 100vh;
  color: #fff;
  text-align: center;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);

  .lock-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: center;
  }

  .time {
    font-size: 8rem;
    font-weight: 300;
    letter-spacing: 2px;
    text-shadow: 0 4px 20px rgb(0 0 0 / 30%);
  }

  .date {
    font-size: 1.5rem;
    font-weight: 400;
    letter-spacing: 1px;
    opacity: 0.9;
  }

  .unlock-hint {
    display: flex;
    gap: 8px;
    align-items: center;
    padding: 12px 24px;
    margin-top: 48px;
    font-size: 1rem;
    cursor: pointer;
    background: rgb(255 255 255 / 10%);
    border-radius: 24px;
    opacity: 0.7;
    transition: all 0.3s ease;

    &:hover {
      background: rgb(255 255 255 / 15%);
      opacity: 1;
      transform: translateY(-2px);
    }
  }
}

/* 密码输入模态框样式 */
.lock-screen-dialog {
  :deep(.el-dialog) {
    overflow: hidden;
    background: #fff;
    border: 1px solid rgb(0 0 0 / 10%);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgb(0 0 0 / 20%);
  }

  :deep(.el-dialog__header) {
    padding: 36px 36px 0;
    border-bottom: none;

    h2 {
      font-size: 22px;
      font-weight: 600;
      color: #303133;
      text-align: center;
    }
  }

  :deep(.el-dialog__body) {
    padding: 36px;
  }
}

.lock-screen-content {
  display: flex;
  flex-direction: column;
  gap: 36px;
  align-items: center;
  color: #303133;

  .user-info {
    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: center;

    .avatar-container {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 120px;
      height: 120px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 50%;
      box-shadow: 0 12px 32px rgb(102 126 234 / 40%);
      animation: pulse 2s infinite;
    }

    .user-avatar {
      width: 105px;
      height: 105px;
      border: 4px solid rgb(255 255 255 / 95%);
      border-radius: 50%;
    }

    .user-name {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
      color: #303133;
    }
  }

  .password-section {
    width: 100%;
    max-width: 380px;
  }

  .password-input {
    width: 100%;

    :deep(.el-input__wrapper) {
      height: 56px;
      background: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      transition: all 0.3s ease;

      &:hover {
        background: #fff;
        border-color: #dee2e6;
      }

      &.is-focus {
        background: #fff;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgb(102 126 234 / 15%);
      }
    }

    :deep(.el-input__inner) {
      height: 56px;
      font-size: 16px;
      line-height: 56px;
      color: #303133;

      &::placeholder {
        color: #adb5bd;
      }
    }

    :deep(.el-input__suffix-inner) {
      font-size: 18px;
      color: #adb5bd;
    }
  }

  .action-section {
    display: flex;
    flex-direction: column;
    gap: 18px;
    width: 100%;
    max-width: 380px;
  }

  .primary-button {
    position: relative;
    width: 100%;
    height: 56px;
    overflow: hidden;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;

    &::before {
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      content: "";
      background: linear-gradient(
        90deg,
        transparent,
        rgb(255 255 255 / 20%),
        transparent
      );
      transition: left 0.6s ease;
    }

    &:hover {
      background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
      box-shadow: 0 8px 24px rgb(102 126 234 / 40%);
      transform: translateY(-3px);

      &::before {
        left: 100%;
      }
    }

    &:active {
      background: linear-gradient(135deg, #4d5cc7 0%, #5d3680 100%);
      box-shadow: 0 4px 16px rgb(102 126 234 / 30%);
      transform: translateY(-1px);
    }
  }

  .secondary-button {
    width: 100%;
    height: 48px;
    font-size: 14px;
    font-weight: 500;
    color: #6c757d;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;

    &:hover {
      color: #495057;
      background: #e9ecef;
      border-color: #dee2e6;
      transform: translateY(-2px);
    }

    &:active {
      transform: translateY(0);
    }
  }
}

/* 全屏锁定样式 */
</style>
