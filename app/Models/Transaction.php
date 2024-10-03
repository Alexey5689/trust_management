<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'manager_id',
        'date_transition',
        'status_transition',
        'sum_transition',
    ];

    /**
     * Связь с моделью Contract (многие к одному)
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * Связь с моделью User (менеджер)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
