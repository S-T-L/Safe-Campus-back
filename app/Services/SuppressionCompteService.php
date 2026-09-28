<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Histoire;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Suppression d'un compte par l'admin. histoires.user_id est en
 * cascadeOnDelete : sans transfert prealable, supprimer un redacteur
 * effacerait ses histoires, leurs scenes et leurs choix.
 */
class SuppressionCompteService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Redacteurs pouvant reprendre les histoires du compte supprime.
     *
     * @return array<int, string>
     */
    public function repreneursPossibles(User $compte): array
    {
        return User::where('role', UserRole::Redacteur->value)
            ->whereKeyNot($compte->getKey())
            ->orderBy('nom')
            ->get()
            ->mapWithKeys(fn (User $user) => [$user->id => trim("{$user->prenom} {$user->nom}")." ({$user->email})"])
            ->all();
    }

    public function nombreHistoires(User $compte): int
    {
        return Histoire::where('user_id', $compte->getKey())->count();
    }

    /**
     * @throws InvalidArgumentException si le compte a des histoires et que le
     *                                  repreneur est absent ou invalide.
     */
    public function supprimer(User $compte, ?int $repreneurId = null): void
    {
        DB::transaction(function () use ($compte, $repreneurId): void {
            $histoires = Histoire::where('user_id', $compte->getKey())->get();

            if ($histoires->isNotEmpty()) {
                $repreneur = $repreneurId !== null
                    ? User::whereKey($repreneurId)->where('role', UserRole::Redacteur->value)->first()
                    : null;

                if ($repreneur === null || $repreneur->is($compte)) {
                    throw new InvalidArgumentException('Un autre rédacteur doit reprendre les histoires de ce compte.');
                }

                // Une par une (et non update() en masse) : chaque transfert
                // passe par l'observer et apparait dans les logs d'audit.
                foreach ($histoires as $histoire) {
                    $histoire->user_id = $repreneur->id;
                    $histoire->save();
                }

                $cible = $this->audit->decrire($compte);
                $details = ['repreneur' => $this->audit->decrire($repreneur), 'nombre' => $histoires->count()];

                // Comme l'observer : ecrit seulement si la transaction aboutit.
                DB::afterCommit(fn () => $this->audit->ecrire('User.histoires_transferees', null, ['cible' => $cible, ...$details]));
            }

            $compte->delete();
        });
    }
}
