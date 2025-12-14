<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'travel_orders';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'destination',
        'departure_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'solicitado',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
