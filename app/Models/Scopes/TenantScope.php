<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Administradores e Masters têm visão global
            if (in_array((int) $user->role_id, [1, 2])) {
                return;
            }

            $table = $model->getTable();

            // Permite carregar itens com user_id do revendedor ou defaults do sistema (user_id IS NULL)
            if (in_array($table, ['templates', 'planos'])) {
                $builder->where(function (Builder $query) use ($table, $user) {
                    $query->where($table . '.user_id', $user->id)
                          ->orWhereNull($table . '.user_id');
                });
            } else {
                $builder->where($table . '.user_id', $user->id);
            }
        }
    }
}
