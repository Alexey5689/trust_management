<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_id',
        'model_type',
        'change',
        'old_value',
        'new_value',
        'created_by',
    ];
     /**
     * Связь с пользователем, который внёс изменение.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Связь с пользователем, чьи данные были изменены.
     */
    public function target()
    {
        return $this->belongsTo(User::class, 'model_id');
    }
}
