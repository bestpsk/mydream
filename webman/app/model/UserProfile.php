<?php

namespace app\model;

use support\Model;

class UserProfile extends Model
{
    protected $table = 'sys_user_profile';
    
    protected $fillable = [
        'user_id',
        'phone',
        'email',
        'birthday_solar',
        'birthday_lunar',
        'id_card',
        'address',
        'emergency_contact',
        'emergency_phone',
        'entry_date',
        'leave_date'
    ];
    
    /**
     * 关联到用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
?>