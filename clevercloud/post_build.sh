#!/bin/bash
# Hook de build Clever Cloud.
# A declarer dans la console : CC_POST_BUILD_HOOK=./clevercloud/post_build.sh
#
# Ce script s'execute apres `composer install`, avec les variables
# d'environnement de l'application. Il doit rester idempotent : il tourne
# a chaque deploiement et a chaque redemarrage d'instance.

set -euo pipefail

echo "--- GARPIS : preparation du deploiement ---"

# Les caches d'un build precedent n'ont plus rien a faire ici.
php artisan config:clear || true
php artisan route:clear  || true
php artisan view:clear   || true

# Migrations. On echoue volontairement le deploiement si elles echouent :
# mieux vaut garder l'ancienne version en ligne qu'un schema a moitie migre.
php artisan migrate --force --no-interaction

# Lien public/storage -> storage/app/public. --force evite l'echec quand il existe.
php artisan storage:link --force || true

# Caches de production. A faire APRES les migrations.
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- GARPIS : deploiement pret ---"