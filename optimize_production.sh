#!/bin/bash

# ====================================================
# POS OPTIK MELATI - PERFORMANCE OPTIMIZATION SCRIPT
# Run this on VPS production environment
# ====================================================

echo "🚀 Starting Performance Optimization..."

# 1. Clear all caches
echo "1️⃣  Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Cache configuration & routes (for production)
echo "2️⃣  Caching configuration & routes..."
php artisan config:cache
php artisan route:cache

# 3. Run database migrations (indexes)
echo "3️⃣  Running database migrations..."
php artisan migrate --force

# 4. Optimize Composer autoloader
echo "4️⃣  Optimizing Composer autoloader..."
composer install --no-dev --optimize-autoloader --classmap-authoritative

# 5. Clear Laravel logs older than 7 days
echo "5️⃣  Cleaning up old logs..."
find storage/logs -name "*.log" -mtime +7 -delete

# 6. Optimize Laravel for production
echo "6️⃣  Running Laravel optimize..."
php artisan optimize

echo "✅ Performance optimization complete!"
echo ""
echo "Summary of optimizations applied:"
echo "  ✓ Database indexes added (critical queries)"
echo "  ✓ Config & Routes cached"
echo "  ✓ Composer autoloader optimized"
echo "  ✓ DataTables pagination fixed (FrameController, LensaController)"
echo "  ✓ Old logs cleaned up"
echo ""
echo "Maintenance tips:"
echo "  - Monitor query performance with: php artisan tinker"
echo "  - Check DB indexes with: SHOW INDEX FROM tablename;"
echo "  - Rebuild caches after code changes on production"
echo ""
