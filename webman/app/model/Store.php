<?php

namespace app\model;

use app\model\BaseModel;

class Store extends BaseModel
{
    protected $table = 'sys_store';
    
    public $timestamps = true;
    
    protected $fillable = [
        'store_name',
        'phone',
        'address',
        'store_type',
        'company_id',
        'status',
        'isDelete'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'sys_store_department', 'store_id', 'department_id');
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'sys_user_store', 'store_id', 'user_id');
    }
}
