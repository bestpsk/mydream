<?php

namespace app\service;

use support\Request;
use app\model\User;
use app\model\Company;

class TenantService
{
    private static $currentCompanyId = null;
    private static $isSuperAdmin = false;
    private static $initialized = false;

    public static function initFromRequest(Request $request)
    {
        if (self::$initialized) {
            return;
        }

        $userId = $GLOBALS['user_id'] ?? null;
        if (!$userId) {
            self::$initialized = true;
            return;
        }

        $user = User::with('roles')->find($userId);
        if (!$user) {
            self::$initialized = true;
            return;
        }

        self::$isSuperAdmin = self::checkIsSuper($user);

        if (self::$isSuperAdmin) {
            $selectedCompanyId = $request->header('X-Company-ID');
            if ($selectedCompanyId) {
                $company = Company::where('id', $selectedCompanyId)
                    ->where('isDelete', 0)
                    ->where('status', 1)
                    ->first();
                if ($company) {
                    self::$currentCompanyId = (int)$selectedCompanyId;
                }
            }

            if (!self::$currentCompanyId) {
                $employee = $user->employee;
                if ($employee && $employee->company_id) {
                    self::$currentCompanyId = $employee->company_id;
                }
            }
        } else {
            $employee = $user->employee;
            if ($employee && $employee->company_id) {
                self::$currentCompanyId = $employee->company_id;
            }
        }

        $GLOBALS['company_id'] = self::$currentCompanyId;
        $GLOBALS['is_super'] = self::$isSuperAdmin;

        self::$initialized = true;
    }

    public static function getCurrentCompanyId(): ?int
    {
        return self::$currentCompanyId;
    }

    public static function isSuperAdmin(): bool
    {
        return self::$isSuperAdmin;
    }

    public static function setCurrentCompanyId(int $companyId): void
    {
        self::$currentCompanyId = $companyId;
        $GLOBALS['company_id'] = $companyId;
    }

    private static function checkIsSuper(User $user): bool
    {
        foreach ($user->roles as $role) {
            if (isset($role->is_super) && $role->is_super == 1) {
                return true;
            }
        }
        return false;
    }

    public static function getCompanyInfo(int $companyId): ?array
    {
        $company = Company::find($companyId);
        if (!$company) {
            return null;
        }

        return [
            'id' => $company->id,
            'companyName' => $company->company_name,
            'code' => $company->code,
            'boss' => $company->boss,
            'phone' => $company->phone,
            'address' => $company->address,
            'status' => $company->status,
            'startDate' => $company->start_date,
            'endDate' => $company->end_date,
            'isTrial' => $company->is_trial,
            'trialDays' => $company->trial_days,
            'maxStores' => $company->max_stores,
            'maxEmployees' => $company->max_employees,
        ];
    }

    public static function isCompanyExpired(int $companyId): bool
    {
        $company = Company::find($companyId);
        if (!$company) {
            return true;
        }

        if ($company->status != 1) {
            return true;
        }

        if ($company->end_date) {
            $endDate = strtotime($company->end_date);
            if ($endDate < time()) {
                return true;
            }
        }

        return false;
    }

    public static function reset(): void
    {
        self::$currentCompanyId = null;
        self::$isSuperAdmin = false;
        self::$initialized = false;
    }
}
