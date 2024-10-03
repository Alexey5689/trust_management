<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transactions;

class Contract extends Model
{
    use HasFactory;

    // Разрешённые для массового заполнения поля
    protected $fillable = [
        'title',
        'contract_id',
        'user_id',
        'manager_id',
        'contract_number',
        'create_date',
        'deadline',
        'sum',
        'procent',
        'contract_status',
        'doc_number',
    ];

    /**
     * Связь с транзакциями (один ко многим)
     */
    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'contract_id');
    }

    /**
     * Связь с другим контрактом (многие к одному)
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
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
