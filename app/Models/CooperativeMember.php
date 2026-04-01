<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperativeMember extends Model
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_VALIDATEUR = 'validateur';

    public const ROLE_SAISIE = 'saisie';

    public const ROLE_LECTURE = 'lecture';

    public const STATUT_INVITED = 'invited';

    public const STATUT_ACTIVE = 'active';

    public const STATUT_INACTIVE = 'inactive';

    protected $fillable = [
        'cooperative_id',
        'user_id',
        'invited_phone',
        'role',
        'statut',
        'invited_by_user_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
