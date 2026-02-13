<?php

namespace app\model;

use app\model\BaseModel;

class Company extends BaseModel
{
    protected $table = 'sys_company';
    
    public $timestamps = true;
    
    protected $fillable = [
        'code',
        'company_name',
        'boss',
        'phone',
        'address',
        'enterprise_type',
        'store_count',
        'service_people',
        'status',
        'isDelete'
    ];
    
    public function stores()
    {
        return $this->hasMany(Store::class, 'company_id', 'id');
    }
    
    public function departments()
    {
        return $this->hasMany(Department::class, 'company_id', 'id');
    }
    
    public function positions()
    {
        return $this->hasMany(Position::class, 'company_id', 'id');
    }
    
    public function employees()
    {
        return $this->hasMany(Employee::class, 'company_id', 'id');
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }
}
