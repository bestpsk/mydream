<?php

namespace app\middleware;

use support\Response;

class CorsMiddleware
{
    public function process($request, $handler)
    {
        // 处理预检请求
        if ($request->method() === 'OPTIONS') {
            $response = response('');
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Max-Age', '86400');
            $response->status(204);
            return $response;
        }
        
        // 处理请求
        $response = $handler($request);
        
        // 添加CORS头
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        $response->header('Access-Control-Max-Age', '86400');
        
        return $response;
    }
}
