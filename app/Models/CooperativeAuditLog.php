<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperativeAuditLog extends Model
{
    protected $fillable = [
        'cooperative_id',
        'actor_user_id',
        'member_user_id',
        'transaction_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
