<?php

namespace app\model;

use support\Model;

class Permission extends Model
{
    protected $table = 'sys_permission';
    
    public $timestamps = true;
    
    // 允许批量赋值的字段
    protected $fillable = [
        'name',
        'code',
        'description',
        'menu_id',
        'type',
        'status'
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'sys_role_permission', 'permission_id', 'role_id');
    }
    
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}
