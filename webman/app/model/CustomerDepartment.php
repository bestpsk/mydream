<?php

namespace app\model;

use support\Model;

class CustomerDepartment extends Model
{
    protected $table = 'cust_customer_department';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['customer_id', 'department_id'];
}
?>