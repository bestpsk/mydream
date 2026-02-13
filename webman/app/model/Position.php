<?php

namespace app\model;

use app\model\BaseModel;

class Position extends BaseModel
{
    protected $table = 'sys_position';
    
    public $timestamps = true;
    
    protected $fillable = [
        'position_name',
        'dept_id',
        'sort',
        'company_id',
        'status',
        'isDelete'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'sys_employee_position', 'position_id', 'employee_id');
    }
}
