<script setup lang="ts">
import { ref, onMounted, watch, computed } from "vue";
import { useUserStoreHook } from "@/store/modules/user";
import { http } from "@/utils/http";
import { ElMessage } from "element-plus";
import emitter from "@/utils/eventBus";

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

const currentCompany = computed({
  get: () => userStore.selectedCompanyId || userStore.companyId,
  set: (value: number) => {
    userStore.SET_SELECTED_COMPANY_ID(value);
  }
});

watch(
  () => props.modelValue,
  newValue => {
    if (newValue) {
      userStore.SET_SELECTED_COMPANY_ID(newValue);
    }
  }
);

const getCompanies = async () => {
  if (!userStore.isSuper) return;

  loading.value = true;
  try {
    const response = await http.request("get", "/api/enterprise/company");
    if (response.code === 200) {
      companies.value = response.data;
      
      if (!userStore.selectedCompanyId && userStore.companyId) {
        userStore.SET_SELECTED_COMPANY_ID(userStore.companyId);
      }
    }
  } catch (error) {
    console.error("获取公司列表失败:", error);
  } finally {
    loading.value = false;
  }
};

const handleCompanyChange = (companyId: number) => {
  userStore.SET_SELECTED_COMPANY_ID(companyId);
  emit("update:modelValue", companyId);
  emit("change", companyId);
  
  emitter.emit("company-change", companyId);
  
  ElMessage.success("已切换公司");
};

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
</style>
