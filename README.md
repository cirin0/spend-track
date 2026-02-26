<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Про Spend Track

Spend Track - це додаток для відстеження витрат, побудований на Laravel (бекенд) та Vue.js (фронтенд).

### Технології

- **Backend**: Laravel 11 (PHP 8.4)
- **Frontend**: Vue.js 3 + TypeScript + Vite
- **Database**: PostgreSQL 16
- **Containerization**: Docker + Docker Compose

### Основні можливості

- Відстеження особистих витрат та доходів
- Категоризація транзакцій
- Групові витрати для спільних проектів
- Аналітика та візуалізація даних
- RESTful API з автентифікацією через JWT


---

## Запуск проєкту

### Швидкий запуск (одна команда)

```bash
./setup.sh
```

Скрипт автоматично:
- Створить `.env` файли (якщо їх немає)
- Запустить Docker контейнери
- Згенерує APP_KEY
- Виконає міграції бази даних

### Після запуску

Проєкт буде доступний за адресами:
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **API Documentation**: http://localhost:8000/docs/api

### Корисні команди

```bash
# Переглянути логи
docker-compose logs -f

# Зупинити проєкт
docker-compose stop

# Запустити знову
docker-compose up -d

# Видалити все (включно з даними БД)
docker-compose down -v
```
