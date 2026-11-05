<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'name',
        'whatsapp',
        'email',
        'password',
        'role_id',
        'status',
        'trial_ends_at',
        'limite',
        'creditos',
        'plano_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
            'role_id' => 'integer',
            'limite' => 'integer',
            'creditos' => 'integer',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subUsers()
    {
        return $this->hasMany(User::class, 'user_id');
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function planos()
    {
        return $this->hasMany(Plano::class);
    }

    public function servidores()
    {
        return $this->hasMany(Servidor::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

    public function conexoes()
    {
        return $this->hasMany(Conexao::class);
    }

    public function companyDetail()
    {
        return $this->hasOne(CompanyDetail::class);
    }

    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function campanhas()
    {
        return $this->hasMany(Campanha::class);
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === 1;
    }

    public function isMaster(): bool
    {
        return (int) $this->role_id === 2;
    }

    public function isCliente(): bool
    {
        return (int) $this->role_id === 3;
    }

    public function isRevendedor(): bool
    {
        return (int) $this->role_id === 4;
    }
}
