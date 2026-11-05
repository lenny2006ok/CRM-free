<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    protected $fillable = [
        'user_id',
        'id_qpanel',
        'nome',
        'preco',
        'duracao',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'duracao' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
