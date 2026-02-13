<?php

namespace app\model;

use app\model\BaseModel;

class Customer extends BaseModel
{
    protected $table = 'cust_customer';
    
    public $timestamps = true;
    
    // 允许批量赋值的字段
    protected $fillable = [
        'store_id',
        'department_id',
        'member_card',
        'name',
        'gender',
        'phone',
        'birthday',
        'birthday_type',
        'points',
        'register_time',
        'source',
        'avatar',
        'archive_number',
        'level',
        'service_staff_id',
        'manager_id',
        'last_consume_time',
        'last_consume_amount',
        'last_deplete_time',
        'last_deplete_amount',
        'remark',
        'status'
    ];
    
    // 关联关系
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    
    public function serviceStaff()
    {
        return $this->belongsTo(Employee::class, 'service_staff_id', 'id');
    }
    
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id', 'id');
    }
    
    // 计算客户级别
    public static function calculateLevel($consumeAmount)
    {
        if ($consumeAmount >= 10000) {
            return '钻石客户';
        } elseif ($consumeAmount >= 5000) {
            return '金卡客户';
        } elseif ($consumeAmount >= 1000) {
            return '银卡客户';
        } else {
            return '普通客户';
        }
    }
    
    // 生成档案编号
    public static function generateArchiveNumber($storeId, $departmentId)
    {
        $storeCode = str_pad($storeId, 3, '0', STR_PAD_LEFT);
        $deptCode = str_pad($departmentId, 3, '0', STR_PAD_LEFT);
        $dateCode = date('Ymd');
        
        // 获取当天最大的序号
        $lastCustomer = self::where('archive_number', 'like', "ST{$storeCode}-DEP{$deptCode}-{$dateCode}%")
            ->orderBy('archive_number', 'desc')
            ->first();
        
        if ($lastCustomer) {
            $lastNumber = substr($lastCustomer->archive_number, -3);
            $sequence = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }
        
        return "ST{$storeCode}-DEP{$deptCode}-{$dateCode}{$sequence}";
    }
}
?>