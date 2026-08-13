# Invoice Management Module

Мінімальний модуль рахунків-фактур для бухгалтерського робочого процесу, побудований за допомогою Laravel 13 (API) та Nuxt 4 /
Vue 3.5 / Tailwind CSS 4 (frontend).

## Deployment

### Docker (recommended)

```bash
docker-compose up --build
```

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- MySQL: on port `3306`

```bash
docker-compose exec backend php artisan db:seed
```

### without Docker

Backend requires a local MySQL or PostgreSQL instance

```bash
cd backend
composer install
cp .env.example .env

php artisan key:generate
php artisan migrate --seed
php artisan serve     # http://localhost:8000
```

Frontend:

```bash
cd frontend
npm install
npm run dev            # http://localhost:3000/invoices
```
---

## 1. Як ти структурував frontend і backend?

**Backend** - Стандартний Laravel:

- [InvoiceController.php](backend/app/Http/Controllers/Api/InvoiceController.php) чотири ендпоінти (`index`, `show`,
  `store`, `update`). Делегує все сервісу.
- [InvoiceService.php](backend/app/Services/InvoiceService.php) - тута бізнес логіка: listing/filtering/pagination, 
- перевірка на pending за допомогую [InvoiceNotEditableException.php](backend/app/Exceptions/InvoiceNotEditableException.php)
- [StoreInvoiceRequest.php](backend/app/Http/Requests/StoreInvoiceRequest.php) та [UpdateInvoiceRequest.php](backend/app/Http/Requests/UpdateInvoiceRequest.php) - валідація та
  cross-field валідація (`gross = net + vat`, `due_date >= issue_date`)
- [InvoiceResource.php](backend/app/Http/Resources/InvoiceResource.php) - один ресурс для `show` та `index` для простоти.
- [Invoice.php](backend/app/Models/Invoice.php)`app/Models/Invoice.php` + [InvoiceStatus.php](backend/app/Enums/InvoiceStatus.php) - Модель та Enum для статусів.

**Frontend** - Nuxt, з роутінгом по структурі файлів в `pages`:

- [index.vue](frontend/app/pages/index.vue) - Сторінка списку.
- [[id].vue](frontend/app/pages/invoices/%5Bid%5D.vue) - Сторінка конкретного рахунку + форма для редактування. 
   Для простоти зробив в одному місці. В більшій апці це можна розділити
- [InvoiceEditForm.vue](frontend/app/components/InvoiceEditForm.vue) - компонент для edit. Валідація на (vee-validate + zod)
- [StatusBadge.vue](frontend/app/components/StatusBadge.vue) - статус з кольоровим бейджем
- [useInvoicesApi.ts](frontend/app/composables/useInvoicesApi.ts) - Для зв'язку з API
- [invoice.ts](frontend/app/types/invoice.ts) - інтерфейс `Invoice` для "зв'язку" з `InvoiceResource`.
- [format.ts](frontend/app/utils/format.ts) - Допоміжна ютилка для форматування дати

## 2. Які компроміси ти зробив і чому?

- **Первинний ключ - UUID, тому без автоінкремент.**
- **Проста пагінація на Laravel** - без cursor pagination бо треба робити вибір PerPage, 
   тому і базові елементи керування Previous/Next для пагінації на фронті.
-  У ТЗ Присутній пункт 'Створити інвойс POST /api/invoices', але немає пункту створити сторінку на фронті. 
   Прийняв рішення створити компоненти для сторінки, бо здається логічним що вони мають бути. 
   Але наразі помістив їх в stash до уточнення ТЗ.

## 3. Що б ти покращив у production-версії?

- **Auth** - хто може затверджувати, відхиляти або редагувати певні рахунки.
- - **`brick/money` бібліотека**
- **PHP-FPM + nginx в Docker**, config/route кеш та opcache
- **Rate limit** до API.
- **Тести**. PHPUnit та Vitest
- **CI**. `php artisan test` та build/typecheck при кожному git push.

## 4. Які UX edge cases ти врахував?

- **Конфлікт доступу під час редагування**: якщо статус рахунку змінюється в проміжку між завантаженням сторінки та надсиланням форми
  (наприклад, хтось інший встигає його затвердити), система перехоплює відповідь сервера зі статусом 409 і відображає конкретне
  повідомлення: «цей рахунок більше не можна редагувати — його статус змінився», після чого сторінка
  автоматично оновлюється, щоб інтерфейс відображав новий стан (блокування), — замість того, щоб видавати незрозумілу загальну помилку
  або просто ігнорувати спробу надсилання.

## AI Usage

Так як у ТЗ був відсутній конкретний дизайн прийняв рішеня для економії часу делегувати стилізацію front-end до Claude Code
