<?php

namespace app\model;

use app\model\BaseModel;

class Employee extends BaseModel
{
    protected $table = 'sys_user_employee';
    
    public $timestamps = true;
    
    protected $fillable = [
        'name',
        'company_id',
        'department_id',
        'position_id',
        'user_id',
        'superior_id',
        'store_id',
        'status',
        'isDelete'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    
    public function superior()
    {
        return $this->belongsTo(Employee::class, 'superior_id', 'id');
    }
    
    public function positions()
    {
        return $this->belongsToMany(Position::class, 'sys_employee_position', 'employee_id', 'position_id');
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'sys_employee_role', 'employee_id', 'role_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * 关联到职位
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
    
    /**
     * 关联到门店
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
