<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useUserStoreHook } from "@/store/modules/user";
import { http } from "@/utils/http";
import type { FormInstance } from "element-plus";

const props = defineProps<{
  modelValue?: number;
  disabled?: boolean;
}>();

const emit = defineEmits<{
  "update:modelValue": [value: number];
  change: [value: number];
}>();

const userStore = useUserStoreHook();
const companies = ref<any[]>([]);
const loading = ref(false);
const currentCompany = ref(props.modelValue);

// 监听modelValue变化
watch(
  () => props.modelValue,
  newValue => {
    currentCompany.value = newValue;
  }
);

// 获取公司列表
const getCompanies = async () => {
  if (!userStore.isSuper) return;

  loading.value = true;
  try {
    const response = await http.request("get", "/api/enterprise/company");
    if (response.code === 200) {
      companies.value = response.data;
    }
  } catch (error) {
    console.error("获取公司列表失败:", error);
  } finally {
    loading.value = false;
  }
};

// 处理公司选择
const handleCompanyChange = (companyId: number) => {
  currentCompany.value = companyId;
  emit("update:modelValue", companyId);
  emit("change", companyId);
};

// 组件挂载时获取公司列表
onMounted(() => {
  if (userStore.isSuper) {
    getCompanies();
  }
});
</script>

<template>
  <el-select
    v-model="currentCompany"
    placeholder="选择公司"
    :disabled="disabled || !userStore.isSuper"
    :loading="loading"
    style="width: 120px"
    @change="handleCompanyChange"
  >
    <el-option
      v-for="company in companies"
      :key="company.id"
      :label="company.companyName"
      :value="company.id"
    />
  </el-select>
</template>

<style scoped>
/* 组件样式 */
</style>
