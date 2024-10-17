<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Contract extends Model
{
    use HasFactory;

    // Разрешённые для массового заполнения поля
    protected $fillable = [
        'title',
        'user_id',
        'manager_id',
        'contract_number',
        'create_date',
        'deadline',
        'sum',
        'procent',
        'contract_status',
    ];

    /**
     * Связь с транзакциями (один ко многим)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'contract_id');
    }

    /**
     * Связь с пользователем (многие к одному)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь с менеджером (многие к одному)
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
