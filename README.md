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

## Запуск Docker Setup

Цей проект використовує Docker Compose для запуску всіх необхідних сервісів: Vue.js фронтенд, Laravel бекенд та PostgreSQL база даних.

### Швидкий старт

Запустіть всі сервіси однією командою:

```bash
docker-compose up -d
```

### Доступні порти

Після запуску сервіси будуть доступні за наступними адресами:

- **Frontend (Vue.js)**: http://localhost:5173
- **Backend (Laravel API)**: http://localhost:8000
- **Database (PostgreSQL)**: localhost:5433
- **Scrumble Docs**: http://localhost:8000/docs/api

### Перша побудова

При першому запуску Docker побудує всі образи. Це може зайняти кілька хвилин:

```bash
docker-compose up --build
```

### Перебудова образів


```bash
docker-compose up --build
```

## Управління сервісами

### Зупинка всіх сервісів

Зупинити контейнери без їх видалення:

```bash
docker-compose stop
```

### Запуск зупинених сервісів

```bash
docker-compose start
```

### Виконання команд в контейнері

```bash
# Відкрити bash в контейнері бекенду
docker-compose exec app bash

# Виконати artisan команду
docker-compose exec app php artisan migrate

# Відкрити bash в контейнері фронтенду
docker-compose exec frontend sh

# Встановити нову npm залежність
docker-compose exec frontend npm install axios
```
