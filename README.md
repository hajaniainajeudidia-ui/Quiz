# Quiz App

Application de quiz en ligne complète.

## Structure

- `backend/` → API Laravel 12 + Sanctum
- `frontend/` → Application Vue 3 + Vite + Tailwind

## Installation

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve