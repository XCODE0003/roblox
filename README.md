# roblox

Laravel + Inertia (Vue 3) приложение.

## Локально

```bash
composer install
cp .env.example .env && php artisan key:generate
npm install && npm run build
php artisan migrate
composer run dev
```

## Сервер

См. переменные окружения в `.env.example`. На продакшене: `APP_ENV=production`, `APP_DEBUG=false`, настройте БД и `php artisan config:cache`.
