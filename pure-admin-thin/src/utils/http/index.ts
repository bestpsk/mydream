import Axios, {
  type AxiosInstance,
  type AxiosRequestConfig,
  type CustomParamsSerializer
} from "axios";
import type {
  PureHttpError,
  RequestMethods,
  PureHttpResponse,
  PureHttpRequestConfig
} from "./types.d";
import { stringify } from "qs";
import { getToken, formatToken } from "@/utils/auth";
import { useUserStoreHook } from "@/store/modules/user";

// 相关配置请参考：www.axios-js.com/zh-cn/docs/#axios-request-config-1
const defaultConfig: AxiosRequestConfig = {
  // API基础URL - 为空字符串，使用代理
  baseURL: "",
  // 请求超时时间
  timeout: 10000,
  headers: {
    Accept: "application/json, text/plain, */*",
    "Content-Type": "application/json",
    "X-Requested-With": "XMLHttpRequest"
  },
  // 数组格式参数序列化（https://github.com/axios/axios/issues/5142）
  paramsSerializer: {
    serialize: stringify as unknown as CustomParamsSerializer
  }
};

class PureHttp {
  constructor() {
    this.httpInterceptorsRequest();
    this.httpInterceptorsResponse();
  }

  /** `token`过期后，暂存待执行的请求 */
  private static requests = [];

  /** 防止重复刷新`token` */
  private static isRefreshing = false;

  /** 初始化配置对象 */
  private static initConfig: PureHttpRequestConfig = {};

  /** 保存当前`Axios`实例对象 */
  private static axiosInstance: AxiosInstance = Axios.create(defaultConfig);

  /** 重连原始请求 */
  private static retryOriginalRequest(config: PureHttpRequestConfig) {
    return new Promise(resolve => {
      PureHttp.requests.push((token: string) => {
        config.headers["Authorization"] = formatToken(token);
        resolve(config);
      });
    });
  }

  /** 请求拦截 */
  private httpInterceptorsRequest(): void {
    PureHttp.axiosInstance.interceptors.request.use(
      async (config: PureHttpRequestConfig): Promise<any> => {
        // 优先判断post/get等方法是否传入回调，否则执行初始化设置等回调
        if (typeof config.beforeRequestCallback === "function") {
          config.beforeRequestCallback(config);
          return config;
        }
        if (PureHttp.initConfig.beforeRequestCallback) {
          PureHttp.initConfig.beforeRequestCallback(config);
          return config;
        }
        /** 请求白名单，放置一些不需要`token`的接口（通过设置请求白名单，防止`token`过期后再请求造成的死循环问题） */
        const whiteList = ["/api/auth/refresh-token", "/api/auth/login"];
        return whiteList.some(url => config.url.includes(url))
          ? config
          : new Promise(resolve => {
              const data = getToken();
              if (data) {
                const now = new Date().getTime();
                const expired = parseInt(data.expires) - now <= 0;
                if (expired) {
                  if (!PureHttp.isRefreshing) {
                    PureHttp.isRefreshing = true;
                    // token过期刷新
                    useUserStoreHook()
                      .handRefreshToken({ refreshToken: data.refreshToken })
                      .then(res => {
                        const token = res.data.accessToken;
                        config.headers["Authorization"] = formatToken(token);
                        PureHttp.requests.forEach(cb => cb(token));
                        PureHttp.requests = [];
                      })
                      .finally(() => {
                        PureHttp.isRefreshing = false;
                      });
                  }
                  resolve(PureHttp.retryOriginalRequest(config));
                } else {
                  config.headers["Authorization"] = formatToken(
                    data.accessToken
                  );
                }
              }
              const userStore = useUserStoreHook();
              if (userStore.isSuper && userStore.selectedCompanyId) {
                config.headers["X-Company-ID"] = String(
                  userStore.selectedCompanyId
                );
              }
              resolve(config);
            });
      },
      error => {
        return Promise.reject(error);
      }
    );
  }

  /** 响应拦截 */
  private httpInterceptorsResponse(): void {
    const instance = PureHttp.axiosInstance;
    instance.interceptors.response.use(
      (response: PureHttpResponse) => {
        const $config = response.config;
        // 优先判断post/get等方法是否传入回调，否则执行初始化设置等回调
        if (typeof $config.beforeResponseCallback === "function") {
          $config.beforeResponseCallback(response);
          return response.data;
        }
        if (PureHttp.initConfig.beforeResponseCallback) {
          PureHttp.initConfig.beforeResponseCallback(response);
          return response.data;
        }
        return response.data;
      },
      (error: PureHttpError) => {
        const $error = error;
        $error.isCancelRequest = Axios.isCancel($error);

        // 所有的响应异常 区分来源为取消请求/非取消请求
        if ($error.isCancelRequest) {
          console.log("请求已取消");
        } else if ($error.response) {
          // 服务器返回错误状态码
          const status = $error.response.status;
          switch (status) {
            case 401:
              // 未授权，清除token并跳转到登录页
              console.error("未授权，请重新登录");
              break;
            case 403:
              // 禁止访问
              console.error("没有权限访问该资源");
              break;
            case 404:
              // 资源不存在
              console.error("请求的资源不存在");
              break;
            case 500:
              // 服务器内部错误
              console.error("服务器内部错误");
              break;
            default:
              // 其他错误
              console.error(`请求失败，状态码: ${status}`);
          }
        } else if ($error.request) {
          // 请求已发送但没有收到响应
          console.error("网络错误，服务器无响应");
          console.error("错误详情:", $error.message);
          console.error("请求URL:", $error.config?.url);
        } else {
          // 请求配置错误
          console.error(`请求配置错误: ${$error.message}`);
        }

        return Promise.reject($error);
      }
    );
  }

  /** 通用请求工具函数 */
  public request<T>(
    method: RequestMethods,
    url: string,
    param?: AxiosRequestConfig,
    axiosConfig?: PureHttpRequestConfig
  ): Promise<T> {
    const config = {
      method,
      url,
      ...param,
      ...axiosConfig
    } as PureHttpRequestConfig;

    console.log(`发起请求: ${method} ${url}`);

    // 设置默认重试次数
    const retryCount = config.retryCount || 3;
    // 设置默认重试延迟
    const retryDelay = config.retryDelay || 1000;

    // 单独处理自定义请求/响应回调
    return new Promise((resolve, reject) => {
      let currentRetry = 0;

      const attemptRequest = () => {
        console.log(
          `尝试请求 (${currentRetry + 1}/${retryCount}): ${method} ${url}`
        );
        PureHttp.axiosInstance
          .request(config)
          .then((response: T) => {
            console.log(`请求成功: ${url}`, response);
            resolve(response);
          })
          .catch(error => {
            console.error(`请求失败: ${url}`, error);
            // 判断是否需要重试
            if (
              currentRetry < retryCount &&
              (error.code === "ECONNABORTED" ||
                error.code === "ETIMEDOUT" ||
                (error.response && error.response.status >= 500))
            ) {
              currentRetry++;
              console.log(
                `请求失败，正在重试（${currentRetry}/${retryCount}）...`
              );
              // 延迟重试
              setTimeout(attemptRequest, retryDelay * currentRetry);
            } else {
              reject(error);
            }
          });
      };

      attemptRequest();
    });
  }

  /** 单独抽离的`post`工具函数 */
  public post<T, P>(
    url: string,
    params?: AxiosRequestConfig<P>,
    config?: PureHttpRequestConfig
  ): Promise<T> {
    return this.request<T>("post", url, params, config);
  }

  /** 单独抽离的`get`工具函数 */
  public get<T, P>(
    url: string,
    params?: AxiosRequestConfig<P>,
    config?: PureHttpRequestConfig
  ): Promise<T> {
    return this.request<T>("get", url, params, config);
  }

  /** 单独抽离的`put`工具函数 */
  public put<T, P>(
    url: string,
    params?: AxiosRequestConfig<P>,
    config?: PureHttpRequestConfig
  ): Promise<T> {
    return this.request<T>("put", url, params, config);
  }

  /** 单独抽离的`delete`工具函数 */
  public delete<T, P>(
    url: string,
    params?: AxiosRequestConfig<P>,
    config?: PureHttpRequestConfig
  ): Promise<T> {
    return this.request<T>("delete", url, params, config);
  }
}

export const http = new PureHttp();
export default http;
