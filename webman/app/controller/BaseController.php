<?php

namespace app\controller;

use app\service\TenantService;
use support\Request;

class BaseController
{
    protected function getCompanyId(): ?int
    {
        return TenantService::getCurrentCompanyId();
    }

    protected function isSuperAdmin(): bool
    {
        return TenantService::isSuperAdmin();
    }

    protected function applyTenantScope($query, $companyIdField = 'company_id')
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $query->where($companyIdField, $companyId);
        }
        return $query;
    }

    protected function addCompanyIdToData(array $data, $companyIdField = 'company_id'): array
    {
        if (!$this->isSuperAdmin() && !isset($data[$companyIdField])) {
            $companyId = $this->getCompanyId();
            if ($companyId) {
                $data[$companyIdField] = $companyId;
            }
        }
        return $data;
    }

    protected function getTenantFilterParams(Request $request): array
    {
        $params = $request->all();

        if (!$this->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($companyId) {
                $params['company_id'] = $companyId;
            }
        }

        return $params;
    }

    protected function success($data = null, string $message = '操作成功', int $code = 200)
    {
        $response = [
            'code' => $code,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return json($response);
    }

    protected function error(string $message = '操作失败', int $code = 500, $data = null)
    {
        $response = [
            'code' => $code,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return json($response);
    }

    protected function paginate($query, Request $request, $perPage = null)
    {
        $page = (int)$request->get('page', 1);
        $pageSize = (int)($perPage ?? $request->get('pageSize', 10));

        $total = $query->count();
        $items = $query->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize
        ];
    }
}
