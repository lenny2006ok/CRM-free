<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Campanha extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'horario',
        'data',
        'contatos',
        'mensagem',
        'arquivo',
        'enviar_diariamente',
        'ultima_execucao',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'contatos' => 'array',
            'enviar_diariamente' => 'boolean',
            'ultima_execucao' => 'datetime',
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
}
