<?php

namespace app\model;

use app\model\BaseModel;

class Department extends BaseModel
{
    protected $table = 'sys_department';
    
    public $timestamps = true;
   // 允许批量赋值的字段
    protected $fillable = [
        'dept_name',
        'parent_id',
        'sort',
        'company_id',
        'status',
        'enable_category',
        'isDelete'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'sys_store_department', 'department_id', 'store_id');
    }
    
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id', 'id');
    }
    
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id', 'id');
    }
    
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id', 'id');
    }
}
