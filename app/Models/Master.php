<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Master extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'specialty', 'work_from', 'work_to', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
