<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    protected $fillable = [
        'owner_user_id',
        'nom',
        'double_validation_threshold',
        'validation_rules',
    ];

    protected $casts = [
        'double_validation_threshold' => 'decimal:2',
        'validation_rules' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members()
    {
        return $this->hasMany(CooperativeMember::class);
    }

    public function audits()
    {
        return $this->hasMany(CooperativeAuditLog::class);
    }
}
