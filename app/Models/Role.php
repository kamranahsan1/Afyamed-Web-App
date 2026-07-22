<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'label',
    ];

    public function webAdmins(): BelongsToMany
    {
        return $this->belongsToMany(WebAdmin::class, 'role_web_admin');
    }
}
