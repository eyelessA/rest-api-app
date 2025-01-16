<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = ['name', 'parent_id', 'level'];
    protected $hidden = ['created_at', 'updated_at'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Activity::class, 'parent_id');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'activity_id');
    }

    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($activity) {
            if ($activity->parent_id) {
                $parent = Activity::query()->find($activity->parent_id);

                if ($parent && $parent->level >= 3) {
                    throw new \Exception('Превышен максимальный уровень вложенности');
                }
                $activity->level = $parent ? $parent->level + 1 : 1;
            }
        });
    }
}
