<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'nome',
        'whatsapp',
        'password',
        'vencimento',
        'servidor_id',
        'plano_id',
        'numero_de_telas',
        'iptv_nome',
        'iptv_senha',
        'mac',
        'notificacoes',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'vencimento' => 'date',
            'numero_de_telas' => 'integer',
            'notificacoes' => 'boolean',
            'role_id' => 'integer',
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function servidor()
    {
        return $this->belongsTo(Servidor::class);
    }

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class);
    }

    public function isVencido(): bool
    {
        return $this->vencimento && $this->vencimento->isPast() && !$this->vencimento->isToday();
    }

    public function diasAteVencimento(): int
    {
        if (!$this->vencimento) {
            return 0;
        }
        return (int) now()->startOfDay()->diffInDays($this->vencimento->startOfDay(), false);
    }
}
