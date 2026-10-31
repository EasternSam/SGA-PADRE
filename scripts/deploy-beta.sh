#!/usr/bin/env bash
# ==============================================================================
# SGA Academic+ — Script de Despliegue Automatizado (Entorno Beta: tec.90s.agency)
# Uso:
#   bash scripts/deploy-beta.sh [rama_opcional]
# ==============================================================================

set -eo pipefail

BRANCH="${1:-beta}"
DEPLOY_DIR="${DEPLOY_PATH:-/home/grupaqgl/web/tec.90s.agency/public_html}"
TARGET_HOST="tec.90s.agency"
TARGET_URL="https://tec.90s.agency"
SERVER_IP="162.35.181.41"

if [ ! -d "$DEPLOY_DIR" ]; then
    echo "ERROR: El directorio de despliegue no existe: $DEPLOY_DIR"
    exit 1
fi

cd "$DEPLOY_DIR"

PREV_COMMIT="$(git rev-parse HEAD 2>/dev/null || echo 'none')"

echo "================================================================="
echo " SGA ACADEMIC+ — DESPLIEGUE AUTOMATIZADO BETA"
echo " Host:          $TARGET_HOST ($SERVER_IP)"
echo " Directorio:    $DEPLOY_DIR"
echo " Rama objetivo: $BRANCH"
echo " Commit previo: $PREV_COMMIT"
echo " Fecha/Hora:    $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================================="

rollback() {
    echo ""
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo " ERROR EN EL DESPLIEGUE — INICIANDO ROLLBACK AUTOMÁTICO"
    echo " Revertiendo al commit anterior: $PREV_COMMIT"
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    if [ "$PREV_COMMIT" != "none" ]; then
        git reset --hard "$PREV_COMMIT"
        ln -sfn public/build build
        ln -sfn public/branding branding
        composer install --no-dev --optimize-autoloader --no-interaction || true
        php artisan optimize:clear || true
        sudo systemctl reload php8.3-fpm 2>/dev/null || true
    fi
    exit 1
}

# 1. Sincronizar repositorio Git
echo ">> [1/6] Sincronizando código desde origin/$BRANCH..."
git fetch origin "$BRANCH" || { echo "Error en git fetch"; rollback; }
git checkout "$BRANCH" || git checkout -b "$BRANCH" "origin/$BRANCH" || { echo "Error en git checkout"; rollback; }
git reset --hard "origin/$BRANCH" || { echo "Error en git reset"; rollback; }

NEW_COMMIT="$(git rev-parse HEAD)"
SHORT_COMMIT="${NEW_COMMIT:0:7}"
echo "✓ Código actualizado a commit: $SHORT_COMMIT ($NEW_COMMIT)"

# 2. Asegurar directorios y enlaces simbólicos para Nginx/HestiaCP
echo ">> [2/6] Verificando estructura de directorios y symlinks..."
mkdir -p storage/framework/{views,cache/data,sessions} storage/logs bootstrap/cache storage/app/public public/branding
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
php artisan storage:link 2>/dev/null || true
ln -sfn public/build build
ln -sfn public/branding branding

# 3. Instalar dependencias PHP
echo ">> [3/6] Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader --no-interaction || { echo "Error en composer install"; rollback; }

# 4. Compilar assets Frontend (Vite + React)
echo ">> [4/6] Compilando assets Frontend (Vite)..."
if [ "${SKIP_NPM_BUILD:-0}" != "1" ]; then
    npm install --no-audit --no-fund || { echo "Error en npm install"; rollback; }
    npm run build || { echo "Error en npm run build"; rollback; }
fi

# 5. Ejecutar migraciones y optimizar cachés
echo ">> [5/6] Ejecutando migraciones y optimizando cachés..."
php artisan migrate --force || { echo "Error en php artisan migrate"; rollback; }
php artisan optimize:clear
php artisan view:cache

if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl reload php8.3-fpm 2>/dev/null || true
fi

# 6. Healthcheck post-despliegue
echo ">> [6/6] Verificando estado de salud en ${TARGET_URL}/login..."
HEALTH_PASSED=false
for i in {1..5}; do
    HTTP_CODE=$(curl --resolve "${TARGET_HOST}:443:${SERVER_IP}" -k -s -L -o /dev/null -w "%{http_code}" "${TARGET_URL}/login" 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ]; then
        HEALTH_PASSED=true
        break
    fi
    echo "  Intento $i: HTTP $HTTP_CODE. Reintentando en 2s..."
    sleep 2
done

if [ "$HEALTH_PASSED" = true ]; then
    echo "================================================================="
    echo " ¡DESPLIEGUE BETA COMPLETADO EXITOSAMENTE!"
    echo " URL:          ${TARGET_URL}"
    echo " Commit:       $SHORT_COMMIT ($NEW_COMMIT)"
    echo " Healthcheck:  HTTP 200 OK"
    echo "================================================================="
    exit 0
else
    echo "ERROR: Healthcheck falló (HTTP $HTTP_CODE)."
    rollback
fi
