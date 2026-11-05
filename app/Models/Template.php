<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'finalidade',
        'conteudo',
        'tipo_mensagem',
        'imagem',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function renderizar(array $variaveis): string
    {
        $conteudo = $this->conteudo;
        foreach ($variaveis as $chave => $valor) {
            $conteudo = str_replace('{' . $chave . '}', (string) $valor, $conteudo);
        }
        return $conteudo;
    }
}
