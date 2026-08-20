<?php

namespace App\Http\Controllers;

use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConnexionController extends Controller
{
    public function formulaire()
    {
        return view('auth.connexion');
    }

    public function connecter(Request $request)
    {
        $donnees = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [], ['email' => 'adresse e-mail', 'password' => 'mot de passe']);

        if (! Auth::attempt($donnees + ['actif' => true], $request->boolean('memoriser'))) {
            throw ValidationException::withMessages([
                'email' => "Ces identifiants ne correspondent à aucun compte actif.",
            ]);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['derniere_connexion' => now()])->save();
        Audit::tracer('CONNEXION', 'utilisateur', $request->user()->id);

        return redirect()->intended(route('accueil'));
    }

    public function deconnecter(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('connexion');
    }
}
