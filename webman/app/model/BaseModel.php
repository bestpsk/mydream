<?php

namespace app\model;

use support\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BaseModel extends Model
{
    /**
     * 自动应用公司ID过滤
     */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('company', function (Builder $builder) {
            // 检查是否为超级管理员
            if (!isset($GLOBALS['is_super']) || !$GLOBALS['is_super']) {
                // 检查是否有公司ID
                if (isset($GLOBALS['company_id'])) {
                    try {
                        // 检查模型是否有company_id字段
                        if (Schema::hasColumn((new static())->getTable(), 'company_id')) {
                            $builder->where('company_id', $GLOBALS['company_id']);
                        }
                    } catch (\Exception $e) {
                        // 忽略错误，继续执行
                    }
                }
            }
        });
    }
    
    /**
     * 忽略公司ID过滤，用于超级管理员
     */
    public function scopeIgnoreCompany(Builder $builder)
    {
        return $builder->withoutGlobalScope('company');
    }
    
    /**
     * 指定公司ID过滤
     */
    public function scopeForCompany(Builder $builder, $companyId)
    {
        if (Schema::hasColumn($this->getTable(), 'company_id')) {
            return $builder->where('company_id', $companyId);
        }
        return $builder;
    }
}
