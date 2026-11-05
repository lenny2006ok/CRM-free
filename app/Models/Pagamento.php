<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'user_id',
        'cliente_id',
        'plano_id',
        'mercado_pago_id',
        'valor',
        'status',
        'payment_date',
        'use_saldo_ganhos',
        'qr_code',
        'qr_code_base64',
        'ticket_url',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'payment_date' => 'date',
            'use_saldo_ganhos' => 'boolean',
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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }
}
