<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class CompanyDetail extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'company_whatsapp',
        'access_token',
        'public_key',
        'pix_manual',
        'evolution_api_url',
        'evolution_api_key',
        'qpanel_api_url',
        'qpanel_api_key',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'public_key' => 'encrypted',
            'evolution_api_key' => 'encrypted',
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
