<?php

use App\Http\Controllers\AccueilController;
use App\Http\Controllers\ApprenantController;
use App\Http\Controllers\AttestationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| Public — hors authentification
|---------------------------------------------------------------------------
| C'est ici qu'atterrit l'employeur qui scanne le QR d'une attestation.
| Verification gratuite, aucune donnee personnelle sensible exposee.
*/
Route::get('/verifier', [VerificationController::class, 'formulaire'])->name('verification.formulaire');
Route::get('/a/{code}', [VerificationController::class, 'afficher'])->name('verification.afficher');

/*
|---------------------------------------------------------------------------
| Authentification
|---------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'formulaire'])->name('connexion');
    Route::post('/connexion', [ConnexionController::class, 'connecter']);
});

Route::post('/deconnexion', [ConnexionController::class, 'deconnecter'])
    ->middleware('auth')->name('deconnexion');

/*
|---------------------------------------------------------------------------
| Application
|---------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/', [AccueilController::class, 'index'])->name('accueil');
    Route::post('/assistant', [AccueilController::class, 'demanderIa'])->name('assistant');

    // --- Apprenants ---
    Route::get('/apprenants', [ApprenantController::class, 'index'])->name('apprenants.index');
    Route::get('/apprenants/nouveau', [ApprenantController::class, 'formulaire'])->name('apprenants.formulaire');
    Route::post('/apprenants', [ApprenantController::class, 'enregistrer'])->name('apprenants.enregistrer');
    Route::get('/apprenants/{apprenant}', [ApprenantController::class, 'fiche'])->name('apprenants.fiche');

    // --- Sessions ---
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [SessionController::class, 'fiche'])->name('sessions.fiche');
    Route::get('/sessions/{session}/inscrire', [InscriptionController::class, 'formulaire'])->name('sessions.inscrire');
    Route::post('/sessions/{session}/inscrire', [InscriptionController::class, 'enregistrer']);

    Route::middleware('role:DIRECTRICE,DIR_ETUDES')->group(function () {
        Route::get('/nouvelle-session', [SessionController::class, 'formulaire'])->name('sessions.formulaire');
        Route::post('/sessions', [SessionController::class, 'enregistrer'])->name('sessions.enregistrer');
        Route::post('/sessions/{session}/dupliquer', [SessionController::class, 'dupliquer'])->name('sessions.dupliquer');
    });

    // --- Encaissement ---
    Route::middleware('role:DIRECTRICE,DIR_ETUDES,SECRETAIRE')->group(function () {
        Route::get('/inscriptions/{inscription}/encaisser', [PaiementController::class, 'formulaire'])->name('paiements.formulaire');
        Route::post('/inscriptions/{inscription}/encaisser', [PaiementController::class, 'enregistrer'])->name('paiements.enregistrer');
        Route::post('/inscriptions/{inscription}/materiel', [InscriptionController::class, 'ajouterMateriel'])->name('inscriptions.materiel');
    });

    Route::get('/recus/{recu}', [PaiementController::class, 'pdf'])->name('recus.pdf');
    Route::get('/recus/{recu}/telecharger', [PaiementController::class, 'telecharger'])->name('recus.telecharger');
    Route::get('/impayes', [PaiementController::class, 'impayes'])->name('impayes');

    // Annulation d'un paiement et validation d'une remise : Directrice uniquement.
    Route::middleware('role:DIRECTRICE')->group(function () {
        Route::post('/paiements/{paiement}/annuler', [PaiementController::class, 'annuler'])->name('paiements.annuler');
        Route::post('/inscriptions/{inscription}/valider-remise', [InscriptionController::class, 'validerRemise'])->name('inscriptions.valider-remise');
        Route::post('/attestations/{attestation}/revoquer', [AttestationController::class, 'revoquer'])->name('attestations.revoquer');
    });

    // --- Attestations ---
    Route::middleware('role:DIRECTRICE,DIR_ETUDES,SECRETAIRE')->group(function () {
        Route::get('/sessions/{session}/attestations', [AttestationController::class, 'preparer'])->name('attestations.preparer');
        Route::post('/sessions/{session}/attestations', [AttestationController::class, 'generer'])->name('attestations.generer');
    });

    Route::get('/attestations/{attestation}', [AttestationController::class, 'pdf'])->name('attestations.pdf');
    Route::get('/attestations/{attestation}/telecharger', [AttestationController::class, 'telecharger'])->name('attestations.telecharger');

    // --- Caisse ---
    Route::get('/caisse', [CaisseController::class, 'jour'])->name('caisse.jour');
    Route::post('/caisse/cloturer', [CaisseController::class, 'cloturer'])->name('caisse.cloturer');
    Route::post('/caisse/{cloture}/valider', [CaisseController::class, 'valider'])
        ->middleware('role:DIRECTRICE')->name('caisse.valider');

    // --- Journal d'audit ---
    Route::get('/journal', [AuditController::class, 'index'])
        ->middleware('role:DIRECTRICE,COMPTABLE')->name('audit');
});
