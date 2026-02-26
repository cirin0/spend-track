#!/bin/bash

set -e

echo "Налаштування проєкту ..."
echo ""


echo "📝 Крок 1: Створення .env файлів..."

if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Створено .env для бекенду"
else
    echo "⚠️  .env вже існує, пропускаємо"
fi

if [ ! -f frontend/.env ]; then
    cp frontend/.env.example frontend/.env
    echo "✅ Створено frontend/.env для фронтенду"
else
    echo "⚠️  frontend/.env вже існує, пропускаємо"
fi

echo ""

echo "Крок 2: Запуск Docker контейнерів..."
echo "Це може зайняти кілька хвилин при першому запуску..."
echo "Docker автоматично виконає міграції та сідери..."
echo ""

docker compose up -d --build

echo ""
echo "Очікування запуску сервісів..."
sleep 15

echo "Статус контейнерів:"
docker compose ps
echo ""

echo "Проєкт доступний за адресами:"
echo "   - Фронтенд:        http://localhost:5173"
echo "   - Бекенд API:      http://localhost:8000"
echo "   - API Документація: http://localhost:8000/docs/api"
echo ""
