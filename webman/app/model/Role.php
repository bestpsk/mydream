<?php

namespace app\model;

use support\Model;

class Role extends Model
{
    protected $table = 'sys_role';
    
    public $timestamps = true;
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'sys_user_role', 'role_id', 'user_id');
    }
    
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'sys_role_menu', 'role_id', 'menu_id');
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'sys_role_permission', 'role_id', 'permission_id');
    }
    
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
