<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanoRenovacao extends Model
{
    protected $table = 'planos_renovacao';

    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'limite',
        'duracao',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'limite' => 'integer',
            'duracao' => 'integer',
        ];
    }
}
