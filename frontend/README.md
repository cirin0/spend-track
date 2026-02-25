# Spend Track Frontend

Vue 3 + TypeScript фронтенд для додатку Spend Track

## Технології

- **Vue 3** - прогресивний JavaScript фреймворк
- **TypeScript** - типізація
- **Pinia** - state management
- **Vue Router** - маршрутизація
- **Axios** - HTTP клієнт
- **Vite** - build tool

## Встановлення

```bash
npm install
```

## Налаштування

Створіть файл `.env` на основі `.env.example`:

```bash
cp .env.example .env
```

Відредагуйте `.env` та вкажіть URL вашого Laravel backend:

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

## Запуск проекту

### Development режим

```bash
npm run dev
```

Додаток буде доступний за адресою: `http://localhost:5173`

### Production build

```bash
npm run build
```

### Preview production build

```bash
npm run preview
```

## Структура проекту

```
src/
├── config/
│   └── api.ts           # Налаштування Axios
├── services/
│   └── authService.ts   # API сервіси для автентифікації
├── stores/
│   └── auth.ts          # Pinia store для автентифікації
├── views/
│   ├── HomeView.vue     # Головна сторінка
│   ├── LoginView.vue    # Сторінка входу
│   ├── RegisterView.vue # Сторінка реєстрації
│   └── ProfileView.vue  # Сторінка профілю
├── router/
│   └── index.ts         # Маршрутизація з guards
├── App.vue              # Головний компонент
└── main.ts              # Точка входу
```

## Особливості

### Автентифікація

- JWT токен зберігається в `localStorage`
- Автоматичне додавання токена до кожного запиту через Axios interceptors
- Автоматичний logout при 401 помилці
- Navigation guards для захисту маршрутів

### Доступні маршрути

- `/` - Головна сторінка (потребує авторизації)
- `/login` - Вхід (тільки для гостей)
- `/register` - Реєстрація (тільки для гостей)
- `/profile` - Профіль користувача (потребує авторизації)

## API Endpoints (Laravel Backend)

### Auth

- `POST /api/auth/login` - Вхід
- `POST /api/auth/register` - Реєстрація
- `GET /api/auth/me` - Інформація про поточного користувача
- `POST /api/auth/logout` - Вихід

## Розробка

### Перевірка типів

```bash
npm run type-check
```

### Linting

```bash
npm run lint
```

### Форматування коду

```bash
npm run format
```

## Recommended IDE Setup

[VS Code](https://code.visualstudio.com/) + [Vue (Official)](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (disable Vetur).

## Інтеграція з Laravel Backend

Переконайтеся, що ваш Laravel backend:

1. Запущений на порту 8000 (або змініть VITE_API_BASE_URL)
2. Має налаштовані CORS заголовки для прийому запитів з frontend
3. Використовує JWT автентифікацію

### Приклад налаштування CORS у Laravel

Додайте в `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:5173'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

## Наступні кроки

Після налаштування автентифікації, можна додати:

1. Управління витратами (Expenses)
2. Управління категоріями (Categories)
3. Статистика та аналітика
4. Фільтрація та пошук

---

**Примітка:** Це базова версія frontend з автентифікацією. Додаткова функціональність буде додана у наступних версіях.
