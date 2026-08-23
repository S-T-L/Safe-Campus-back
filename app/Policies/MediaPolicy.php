<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Media;
use App\Models\User;

/**
 * Médias : webmaster et rédacteur — voir docs/schema_bd.md § Rôles. Seule
 * ressource Filament partagée entre les deux : illustrations et documents
 * servent aussi bien l'annuaire que les histoires.
 */
class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role !== null;
    }

    public function view(User $user, Media $media): bool
    {
        return $user->role !== null;
    }

    public function create(User $user): bool
    {
        return $user->role !== null;
    }

    public function update(User $user, Media $media): bool
    {
        return $user->role !== null;
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->role !== null;
    }
}
