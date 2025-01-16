<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = ['address', 'coordinates'];
    protected $hidden = ['created_at', 'updated_at'];

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
