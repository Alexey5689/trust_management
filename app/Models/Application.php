<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $fillable = [
        'create_date',
        'user_id',
        'manager_id',
        'contract_id',
        'condition',
        'status',
        'sum',
        'type_of_processing',
        'date_of_payments',
    ];

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

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }
}
