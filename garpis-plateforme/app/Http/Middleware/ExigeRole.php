<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verification des permissions COTE SERVEUR.
 * Masquer un bouton dans une vue n'est pas une permission, c'est une decoration.
 */
class ExigeRole
{
    public function handle(Request $request, Closure $next, string ...$codes): Response
    {
        $utilisateur = $request->user();

        if (! $utilisateur || ! $utilisateur->actif) {
            abort(403, 'Compte inactif.');
        }

        if (! $utilisateur->a(...$codes)) {
            abort(403, "Cette action n'est pas ouverte à votre rôle.");
        }

        return $next($request);
    }
}
