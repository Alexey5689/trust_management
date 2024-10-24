<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'middle_name',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'token',
        'refresh_token',
        'avaliable_balance',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'avaliable_balance' => 'decimal:2',
    ];

    /**
     * Связь с моделью Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Связь с менеджерами (пользователи могут иметь менеджеров)
     */
    public function managers()
    {
        return $this->belongsToMany(User::class, 'user_manager', 'user_id', 'manager_id');
    }

    /**
     * Связь с пользователями, которыми управляет данный пользователь (если он менеджер)5
     */
    public function managedUsers()
    {
        return $this->belongsToMany(User::class, 'user_manager', 'manager_id', 'user_id');
    }

    public function userContracts()
    {
        return $this->hasMany(Contract::class, 'user_id',);
    }
    public function managerContracts()
    {
        return $this->hasMany(Contract::class, 'manager_id');
    }

    // public function isAdmin()
    // {
    //     // return $this->role_id === 1 && $this->role->title === 'admin'; // Предполагается, что в модели Role есть поле 'title'
    //     $role = $this->role()->first(); // Явная загрузка связи с ролью
    //     return $this->role_id === 1 && $role && $role->title === 'admin';
    // }

    public function isAdmin()
    {
        return $this->role && $this->role->title === 'admin';
    }

    // public function isManager()
    // {
    //     $role = $this->role()->first(); // Явная загрузка связи с ролью
    //     return $this->role_id === 2 && $role && $role->title === 'manager';
    // }

    public function isManager()
    {
        return $this->role && $this->role->title === 'manager';
    }

    // public function isClient()
    // {
    //     $role = $this->role()->first(); // Явная загрузка связи с ролью
    //     return $this->role_id === 3 && $role && $role->title === 'client';
    // }

    public function isClient()
    {
        return $this->role && $this->role->title === 'client';
    }
}
