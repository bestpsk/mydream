<?php

namespace app\model;

use support\Model;

class User extends Model
{
    protected $table = 'sys_user';
    
    public $timestamps = true;
    
    protected $fillable = [
        'username',
        'password',
        'nickname',
        'avatar',
        'status'
    ];
    
    protected $hidden = ['password'];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'sys_user_role', 'user_id', 'role_id');
    }
    
    /**
     * 关联到员工
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }
    
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'sys_user_store', 'user_id', 'store_id');
    }
    
    /**
     * 关联到用户信息
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }
}
