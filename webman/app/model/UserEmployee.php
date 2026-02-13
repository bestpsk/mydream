<?php

namespace app\model;

use support\Model;

class UserEmployee extends Model
{
    protected $table = 'sys_user_employee';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['name', 'user_id', 'company_id', 'store_id', 'department_id', 'position_id', 'superior_id', 'status', 'isDelete'];
}
?>