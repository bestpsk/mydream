<script setup lang="ts">
import Motion from "./utils/motion";
import { useRouter } from "vue-router";
import { message } from "@/utils/message";
import { loginRules } from "./utils/rule";
import { ref, reactive, toRaw } from "vue";
import { debounce } from "@pureadmin/utils";
import { useNav } from "@/layout/hooks/useNav";
import { useEventListener } from "@vueuse/core";
import type { FormInstance } from "element-plus";
import { useLayout } from "@/layout/hooks/useLayout";
import { useUserStoreHook } from "@/store/modules/user";
import { useStoreStore } from "@/store/modules/store";
import { initRouter, getTopMenu } from "@/router/utils";
import { bg, avatar, illustration } from "./utils/static";
import { useRenderIcon } from "@/components/ReIcon/src/hooks";
import { useDataThemeChange } from "@/layout/hooks/useDataThemeChange";

const storeStore = useStoreStore();

import dayIcon from "@/assets/svg/day.svg?component";
import darkIcon from "@/assets/svg/dark.svg?component";
import Lock from "~icons/ri/lock-fill";
import User from "~icons/ri/user-3-fill";
import Building from "~icons/ri/building-3-fill";

defineOptions({
  name: "Login"
});

const router = useRouter();
const loading = ref(false);
const disabled = ref(false);
const ruleFormRef = ref<FormInstance>();

const { initStorage } = useLayout();
initStorage();

const { dataTheme, overallStyle, dataThemeChange } = useDataThemeChange();
dataThemeChange(overallStyle.value);
const { title } = useNav();

const ruleForm = reactive({
  companyCode: "admin",
  username: "admin",
  password: "admin123"
});

/**
 * 登录处理函数
 * @param formEl 表单实例
 */
const onLogin = async (formEl: FormInstance | undefined) => {
  if (!formEl) return;
  await formEl.validate(valid => {
    if (valid) {
      // 防止重复登录
      if (loading.value || disabled.value) return;

      loading.value = true;
      useUserStoreHook()
        .loginByUsername({
          companyCode: ruleForm.companyCode,
          username: ruleForm.username,
          password: ruleForm.password
        })
        .then(res => {
          if (res.code === 200) {
            // 登录成功后获取后端路由
            return initRouter().then(() => {
              // 初始化门店数据
              return storeStore.initStores().then(() => {
                disabled.value = true;
                router
                  .push(getTopMenu(true).path)
                  .then(() => {
                    message("登录成功", { type: "success" });
                  })
                  .finally(() => (disabled.value = false));
              });
            });
          } else {
            // 登录失败的错误提示
            message(res.message || "登录失败", { type: "error" });
          }
        })
        .catch(error => {
          // 网络错误或其他异常
          console.error("登录失败:", error);
          message("登录失败，请检查网络连接", { type: "error" });
        })
        .finally(() => (loading.value = false));
    }
  });
};

// 防抖处理，防止重复提交
const immediateDebounce: any = debounce(
  formRef => onLogin(formRef),
  1000,
  true
);

// 监听回车键登录
useEventListener(document, "keydown", ({ code }) => {
  if (
    ["Enter", "NumpadEnter"].includes(code) &&
    !disabled.value &&
    !loading.value
  )
    immediateDebounce(ruleFormRef.value);
});

/**
 * 填充测试用户信息
 * 保留测试账号登录按钮，方便开发测试
 */
const fillTestUser = () => {
  ruleForm.companyCode = "fts";
  ruleForm.username = "test";
  ruleForm.password = "aa123456";
  // 自动登录测试账号
  setTimeout(() => onLogin(ruleFormRef.value), 100);
};
</script>

<template>
  <div class="select-none">
    <img :src="bg" class="wave" />
    <div class="flex-c absolute right-5 top-3">
      <!-- 主题 -->
      <el-switch
        v-model="dataTheme"
        inline-prompt
        :active-icon="dayIcon"
        :inactive-icon="darkIcon"
        @change="dataThemeChange"
      />
    </div>
    <div class="login-container">
      <div class="img">
        <component :is="toRaw(illustration)" />
      </div>
      <div class="login-box">
        <div class="login-form">
          <avatar class="avatar" />
          <Motion>
            <h2 class="outline-hidden">{{ title }}</h2>
          </Motion>

          <el-form
            ref="ruleFormRef"
            :model="ruleForm"
            :rules="loginRules"
            size="large"
          >
            <Motion :delay="50">
              <el-form-item
                :rules="[
                  {
                    required: true,
                    message: '请输入公司编码',
                    trigger: 'blur'
                  }
                ]"
                prop="companyCode"
              >
                <el-input
                  v-model="ruleForm.companyCode"
                  clearable
                  placeholder="公司编码"
                  :prefix-icon="useRenderIcon(Building)"
                />
              </el-form-item>
            </Motion>

            <Motion :delay="100">
              <el-form-item
                :rules="[
                  {
                    required: true,
                    message: '请输入账号',
                    trigger: 'blur'
                  }
                ]"
                prop="username"
              >
                <el-input
                  v-model="ruleForm.username"
                  clearable
                  placeholder="账号"
                  :prefix-icon="useRenderIcon(User)"
                />
              </el-form-item>
            </Motion>

            <Motion :delay="150">
              <el-form-item prop="password">
                <el-input
                  v-model="ruleForm.password"
                  clearable
                  show-password
                  placeholder="密码"
                  :prefix-icon="useRenderIcon(Lock)"
                />
              </el-form-item>
            </Motion>

            <Motion :delay="250">
              <el-button
                class="w-full mt-4!"
                size="default"
                type="primary"
                :loading="loading"
                :disabled="disabled"
                @click="onLogin(ruleFormRef)"
              >
                登录
              </el-button>
            </Motion>

            <Motion :delay="300">
              <el-button
                class="w-full mt-2!"
                size="default"
                type="info"
                :disabled="disabled || loading"
                @click="fillTestUser"
              >
                测试用户登录
              </el-button>
            </Motion>

            <div class="text-xs text-gray-500 mt-4 text-center">
              <p>测试账号：test / aa123456</p>
              <p>管理员账号：admin / admin123</p>
            </div>
          </el-form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("@/style/login.css");
</style>

<style lang="scss" scoped>
:deep(.el-input-group__append, .el-input-group__prepend) {
  padding: 0;
}
</style>
