<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * Utilisateurs : admin uniquement — voir docs/schema_bd.md § Rôles. L'admin
 * valide les inscriptions et attribue les roles, il ne cree pas de compte.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin && $model->role !== UserRole::Visiteur;
    }

    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Jamais son propre compte : un admin ne peut pas se retirer ses droits.
     * Corollaire : le dernier admin ne peut pas perdre son role, seul un autre
     * admin pourrait le lui retirer. Le compte visiteur (API front) est hors
     * perimetre.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin
            && ! $user->is($model)
            && $model->role !== UserRole::Visiteur;
    }

    /**
     * Refus d'une demande d'inscription uniquement. Un compte actif ne se
     * supprime pas (histoires rattachees) : on lui retire son role.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->role === UserRole::Admin && $model->role === null;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
