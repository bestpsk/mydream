<?php

namespace app\middleware;

use support\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    private $key = 'your-secret-key';
    
    public function process(Request $request, callable $next)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return json(['code' => 401, 'message' => '未提供认证令牌']);
        }
        
        try {
            $token = str_replace('Bearer ', '', $token);
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            $userId = $decoded->sub;
            
            // 在请求中存储用户ID，供控制器使用
            $GLOBALS['user_id'] = $userId;
            
            return $next($request);
        } catch (\Exception $e) {
            return json(['code' => 401, 'message' => '认证令牌无效']);
        }
    }
}
