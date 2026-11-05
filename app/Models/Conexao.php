<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Conexao extends Model
{
    protected $table = 'conexoes';

    protected $fillable = [
        'user_id',
        'whatsapp',
        'status',
        'tokenid',
        'conn',
        'instance_name',
    ];

    protected function casts(): array
    {
        return [
            'conn' => 'boolean',
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
