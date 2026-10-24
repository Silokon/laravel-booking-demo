<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    public const STATUSES = [
        'new' => 'Новая',
        'confirmed' => 'Подтверждена',
        'done' => 'Выполнена',
        'cancelled' => 'Отменена',
    ];

    protected $fillable = [
        'master_id', 'service_id', 'client_name', 'client_phone',
        'starts_at', 'ends_at', 'status', 'comment',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** Записи, которые занимают время мастера (отменённые слот освобождают). */
    public function scopeBlocking($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }
}
