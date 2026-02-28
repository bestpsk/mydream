<?php

namespace app\model;

use support\Model;

class Menu extends Model
{
    protected $table = 'sys_menu';
    
    public $timestamps = true;
    
    // 允许批量赋值的字段
    protected $fillable = [
        'name',
        'path',
        'component',
        'redirect',
        'parent_id',
        'icon',
        'menu_rank',
        'is_frame',
        'frame_src',
        'show_link',
        'status',
        'is_tenant_visible',
        'is_delete'
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'sys_role_menu', 'menu_id', 'role_id');
    }
    
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'menu_id', 'id');
    }
    
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }
    
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id', 'id');
    }
}
