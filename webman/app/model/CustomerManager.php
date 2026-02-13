<?php

namespace app\model;

use support\Model;

class CustomerManager extends Model
{
    protected $table = 'cust_customer_manager';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['customer_id', 'manager_id'];
}
?>