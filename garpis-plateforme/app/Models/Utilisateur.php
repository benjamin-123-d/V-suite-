<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom', 'prenoms', 'email', 'telephone', 'password',
        'antenne_id', 'doit_changer_mdp', 'actif',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'doit_changer_mdp' => 'boolean',
            'actif' => 'boolean',
            'derniere_connexion' => 'datetime',
        ];
    }

    public function antenne(): BelongsTo
    {
        return $this->belongsTo(Antenne::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_utilisateur', 'utilisateur_id', 'role_id');
    }

    /** Codes des roles, mis en cache pour la duree de la requete. */
    public function codesRoles(): array
    {
        return $this->relationLoaded('roles')
            ? $this->roles->pluck('code')->all()
            : $this->roles()->pluck('code')->all();
    }

    public function a(string ...$codes): bool
    {
        return (bool) array_intersect($codes, $this->codesRoles());
    }

    /** Voit-il toutes les antennes, ou seulement la sienne ? */
    public function voitToutesAntennes(): bool
    {
        return $this->a('DIRECTRICE', 'DIR_ETUDES', 'COMPTABLE');
    }

    public function nomComplet(): string
    {
        return trim($this->prenoms.' '.$this->nom);
    }

    public function initiales(): string
    {
        return mb_strtoupper(mb_substr($this->prenoms, 0, 1).mb_substr($this->nom, 0, 1));
    }
}
