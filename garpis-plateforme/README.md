# GARPIS Formation — plateforme de gestion

Application Laravel 11 pour le centre de formation GARPIS : apprenants, sessions,
caisse commune, reçus numérotés, attestations vérifiables par QR code, assistant IA
en lecture seule.

Interface entièrement en français, pensée pour être utilisée **sur téléphone**,
debout au comptoir, sur connexion 3G.

---

## Ce que fait l'application aujourd'hui

| Domaine | État |
|---|---|
| Connexion, rôles cumulables, permissions vérifiées côté serveur | ✅ |
| Antennes, caisse commune, séquence de reçus unique sous verrou | ✅ |
| Formations, sessions, duplication de session | ✅ |
| Apprenants avec détection de doublons | ✅ |
| Inscriptions, remises plafonnées, dette de matériel | ✅ |
| Encaissement + reçu PDF A5 (chiffres et lettres) | ✅ |
| Impayés + relance WhatsApp pré-remplie | ✅ |
| Clôture de caisse en deux temps, écart justifié | ✅ |
| Attestations : blocage strict, QR bas à gauche, révocation | ✅ |
| Page publique de vérification, gratuite, `noindex` | ✅ |
| Assistant IA avec outils et permissions par rôle | ✅ |
| Journal d'audit | ✅ |
| Appel de présence hors ligne, contrats Word, duplicata payant | ⏳ lots suivants |

---

## Installation en local

```bash
composer install
cp .env.example .env
php artisan key:generate

# Base : PostgreSQL de préférence (SQLite fonctionne pour un essai rapide)
php artisan migrate
php artisan db:seed          # jeu de démonstration

php artisan serve
```

Comptes de démonstration — mot de passe `garpis2026` pour tous :

| Compte | Rôle |
|---|---|
| directrice@garpis.org | Directrice (tout) |
| etudes@garpis.org | Directeur des études **+** formateur (rôles cumulés) |
| secretaire.cotonou@garpis.org | Secrétaire, antenne de Cotonou |
| secretaire.calavi@garpis.org | Secrétaire, antenne d'Abomey-Calavi |
| comptable@garpis.org | Comptable (lecture + journal) |

> Changez ces mots de passe avant la mise en production. Le compte de démonstration
> `directrice@garpis.org` doit être supprimé ou renommé une fois les vrais comptes créés.

---

## Déploiement sur Clever Cloud

**1. Créer les ressources**

- Une application **PHP** (pas Node).
- Un add-on **PostgreSQL**, lié à l'application. Clever Cloud injecte alors
  automatiquement `POSTGRESQL_ADDON_HOST`, `_PORT`, `_DB`, `_USER`, `_PASSWORD`.

**2. Variables d'environnement à saisir dans la console Clever Cloud**

```
APP_NAME=GARPIS Formation
APP_ENV=production
APP_DEBUG=false
APP_KEY=            ← voir ci-dessous
APP_URL=https://votre-app.cleverapps.io
APP_TIMEZONE=Africa/Porto-Novo

DB_CONNECTION=pgsql
DB_HOST=$POSTGRESQL_ADDON_HOST
DB_PORT=$POSTGRESQL_ADDON_PORT
DB_DATABASE=$POSTGRESQL_ADDON_DB
DB_USERNAME=$POSTGRESQL_ADDON_USER
DB_PASSWORD=$POSTGRESQL_ADDON_PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database

CC_WEBROOT=/public
CC_PHP_VERSION=8.3
CC_COMPOSER_VERSION=2

GARPIS_URL_VERIFICATION=https://verif.garpis.org
GARPIS_LIEN_PAIEMENT=        ← votre lien, quand vous l'aurez
ANTHROPIC_API_KEY=           ← facultatif, active l'assistant
```

Pour `APP_KEY`, générez-la en local puis recopiez la valeur :

```bash
php artisan key:generate --show
```

**3. Déployer**

```bash
git remote add clever <l-url-git-donnee-par-clever-cloud>
git push clever main
```

Le fichier `clevercloud/php.json` déclenche automatiquement, après chaque déploiement :
`migrate --force`, puis la mise en cache de la configuration, des routes et des vues.

**4. Créer le premier compte**

```bash
# depuis la console Clever Cloud, ou en local pointé sur la base de production
php artisan db:seed
```

**5. Le domaine de vérification**

Faites pointer `verif.garpis.org` vers la même application et renseignez
`GARPIS_URL_VERIFICATION`. C'est cette URL qui est encodée dans les QR codes :
une fois des attestations imprimées, elle ne peut plus changer.

---

## Le lien de paiement du duplicata

`GARPIS_LIEN_PAIEMENT` est volontairement vide.

- **Laissé vide** : le paiement en ligne est désactivé. Un duplicata se règle au
  comptoir, comme n'importe quel encaissement. C'est le mode de départ recommandé.
- **Renseigné** : collez le lien fourni par votre agrégateur (Kkiapay, FedaPay…).
  Le jeton de la demande est ajouté à la fin de l'URL.

> Le jour où vous branchez un paiement automatique, le déblocage du PDF doit
> dépendre d'un **webhook signé**, jamais d'un retour d'URL du type `?success=true` —
> celui-là, n'importe qui peut le taper dans son navigateur.

---

## Les règles à ne pas casser

Ces quatre points sont le cœur du système. Si une évolution future les contourne,
c'est l'évolution qui a tort.

1. **Blocage strict des attestations.** Aucune attestation si le solde est supérieur
   à zéro — matériel compris, et sans exception, y compris pour la Directrice.
   Le contrôle est dans `AttestationController::generer()`, côté serveur.
   Un cas légitime se traite par une remise motivée ou un règlement tiers.

2. **Une seule série de reçus.** `GARPIS/2026/00042`, continue, sans trou, pour
   toutes les antennes. La séquence passe par `Numerotation::prochainNumeroRecu()`,
   qui verrouille la ligne du compteur. Ne jamais générer un numéro ailleurs.

3. **Montants en entiers.** Le franc CFA n'a pas de centimes. Aucun flottant sur
   l'argent, nulle part.

4. **On désactive, on ne supprime pas.** Utilisateurs, paiements, attestations :
   une annulation est une écriture, pas un `DELETE`. Le journal d'audit doit
   pouvoir répondre « qui, quand, quoi » un an plus tard.

---

## Structure

```
app/
  Http/Controllers/     un contrôleur par domaine métier
  Http/Middleware/      ExigeRole — la vérification serveur des permissions
  Models/               Eloquent, UUID partout
  Services/
    Numerotation.php    séquences verrouillées, code de vérification aléatoire
    Montant.php         formatage et conversion en toutes lettres
    GenerateurDocument.php  PDF attestation et reçu, QR en data-uri
    AssistantIa.php     outils IA + application des permissions par rôle
    Audit.php           journal
database/migrations/    schéma complet + vue v_solde_inscription
resources/views/        Blade, français
public/css/garpis.css   feuille unique, aucune compilation nécessaire
public/assets/          logo et fond officiel de l'attestation
```

Pas de build front : la CSS est servie telle quelle. Deux polices sont chargées
depuis Google Fonts (Fraunces pour les titres, Archivo pour le texte) ; si vous
préférez tout héberger, téléchargez-les dans `public/fonts` et adaptez le `<link>`
de `resources/views/layouts/app.blade.php`.

---

## Sauvegardes

L'add-on PostgreSQL de Clever Cloud effectue des sauvegardes automatiques.
**Testez une restauration au moins une fois par trimestre** : une sauvegarde
jamais restaurée n'est pas une sauvegarde.

---

© ONG GARPIS — Groupe d'Action pour la Réduction de la Pauvreté et des Inégalités Sociales
