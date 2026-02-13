<?php

namespace app\model;

use support\Model;

class CustomerServiceStaff extends Model
{
    protected $table = 'cust_customer_service_staff';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['customer_id', 'service_staff_id'];
}
?>