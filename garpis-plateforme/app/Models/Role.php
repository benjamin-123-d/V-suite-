<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = ['code', 'libelle'];

    public const DIRECTRICE = 'DIRECTRICE';
    public const DIR_ETUDES = 'DIR_ETUDES';
    public const SECRETAIRE = 'SECRETAIRE';
    public const FORMATEUR  = 'FORMATEUR';
    public const COMPTABLE  = 'COMPTABLE';

    public static function tous(): array
    {
        return [
            self::DIRECTRICE => 'Directrice',
            self::DIR_ETUDES => 'Directeur des études',
            self::SECRETAIRE => 'Secrétaire',
            self::FORMATEUR  => 'Formateur',
            self::COMPTABLE  => 'Comptable',
        ];
    }
}
