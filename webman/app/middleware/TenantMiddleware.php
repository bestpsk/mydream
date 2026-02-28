<?php

namespace app\middleware;

use support\Request;
use app\service\TenantService;

class TenantMiddleware
{
    public function process(Request $request, callable $next)
    {
        try {
            TenantService::reset();

            if (!isset($GLOBALS['user_id'])) {
                return json([
                    'code' => 401,
                    'message' => '未授权，请重新登录'
                ]);
            }

            TenantService::initFromRequest($request);

            $isSuper = TenantService::isSuperAdmin();
            $currentCompanyId = TenantService::getCurrentCompanyId();

            if (!$isSuper && !$currentCompanyId) {
                return json([
                    'code' => 403,
                    'message' => '用户未关联公司，请联系管理员'
                ]);
            }

            if ($currentCompanyId && TenantService::isCompanyExpired($currentCompanyId)) {
                if (!$isSuper) {
                    return json([
                        'code' => 403,
                        'message' => '公司已过期或被禁用，请联系管理员'
                    ]);
                }
            }

            return $next($request);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'message' => '服务器内部错误: ' . $e->getMessage()
            ]);
        }
    }
}
