import { onMounted, onUnmounted } from "vue";
import emitter from "@/utils/eventBus";

export function useCompanyChange(callback: (companyId: number) => void) {
  const handleCompanyChange = (companyId: number) => {
    callback(companyId);
  };

  onMounted(() => {
    emitter.on("company-change", handleCompanyChange);
  });

  onUnmounted(() => {
    emitter.off("company-change", handleCompanyChange);
  });
}

export function useStoreChange(callback: (storeId: number) => void) {
  const handleStoreChange = (storeId: number) => {
    callback(storeId);
  };

  onMounted(() => {
    emitter.on("store-change", handleStoreChange);
  });

  onUnmounted(() => {
    emitter.off("store-change", handleStoreChange);
  });
}
