<?php

namespace app\middleware;

use support\Request;
use support\Redis;

class CacheMiddleware
{
    /**
     * 缓存键前缀
     */
    protected $prefix = 'webman_cache:';
    
    /**
     * 缓存过期时间（默认5分钟）
     */
    protected $expire = 300;
    
    public function process(Request $request, callable $next)
    {
        // 只缓存GET请求
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        
        // 生成缓存键
        $cacheKey = $this->generateCacheKey($request);
        
        try {
            // 尝试从缓存获取
            if (Redis::exists($cacheKey)) {
                $cachedData = Redis::get($cacheKey);
                $response = json_decode($cachedData, true);
                return json($response);
            }
            
            // 缓存不存在，执行请求
            $response = $next($request);
            
            // 缓存响应
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);
                if (isset($responseData['code']) && $responseData['code'] === 200) {
                    Redis::set($cacheKey, json_encode($responseData), $this->expire);
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            // 缓存失败，继续执行请求
            return $next($request);
        }
    }
    
    /**
     * 生成缓存键
     */
    protected function generateCacheKey(Request $request)
    {
        $path = $request->path();
        $params = $request->all();
        ksort($params);
        $paramString = http_build_query($params);
        
        // 对于超级管理员，添加公司ID到缓存键
        $companyId = $GLOBALS['company_id'] ?? 'all';
        
        return $this->prefix . md5($path . '?' . $paramString . '_' . $companyId);
    }
}
