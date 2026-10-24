<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'duration_minutes', 'price', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function masters(): BelongsToMany
    {
        return $this->belongsToMany(Master::class);
    }
}
