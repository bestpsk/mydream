<?php

namespace app\model;

use support\Model;

class Bedroom extends Model
{
    protected $table = 'bedroom';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['store_id', 'room_name', 'bed_count', 'created_at', 'updated_at'];
    
    // 关联门店
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
