<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CarePlan extends Model
{
    protected $fillable = [
        'ulid',
        'title',
        'slug',
        'summary',
        'body',
        'status',
        'sort_order',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (CarePlan $plan): void {
            if (empty($plan->ulid)) {
                $plan->ulid = (string) Str::ulid();
            }
            if (empty($plan->slug) && ! empty($plan->title)) {
                $plan->slug = Str::slug($plan->title).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(WebAdmin::class, 'created_by');
    }
}
