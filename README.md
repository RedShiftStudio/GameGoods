# Game Goods — маркетплейс игровых товаров

Самостоятельный проект на **Laravel 12 + Vue 3 + MySQL + Docker** по мотивам игровых P2P-маркетплейсов.
## Возможности

- Регистрация, вход, выход, профиль продавца и описание; ограничение частоты входа.
- Каталог с поиском, категориями, фильтрами по типу и цене, сортировкой, страницами.
- Публикация, редактирование, приостановка объявлений, изображения и остатки; избранное.
- Заказы с фиксированной ценой на момент оформления, тестовый экран оплаты (успех/отказ), журнал тестовых операций, передача товара, подтверждение, отмена до передачи; защита от одновременной продажи последней единицы.
- Личная переписка покупателей и продавцов, список переписок, отметка прочтения при открытии беседы, отзывы после завершения заказа.
- Споры, решения администратора; модерация объявлений, блокировка аккаунтов, создание категорий.
- Адаптивный интерфейс на Vue 3. Серверные формы защищены CSRF, доступ к операциям проверяется по владельцу и роли.

## Требования

Docker Compose; для запуска без Docker: PHP 8.3 с `pdo_mysql`, `intl`, `zip`, `mbstring`, `bcmath`, Composer 2, Node.js 22 и MySQL 8.

## Запуск в Docker

Команды ниже приведены **как инструкция**, в процессе подготовки проекта они не выполнялись.

```sh
cp .env.example .env
docker compose build
docker compose up -d db app web
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link
docker compose run --rm vite npm install
docker compose run --rm vite npm run build
```

Откройте **http://localhost:8080**. Для разработки интерфейса вместо последней команды используйте `docker compose up -d vite` (порт Vite: 5173); для повторной production-сборки `docker compose run --rm vite npm run build`.

### Первый администратор и категории

Создайте аккаунт через `/register`, затем назначьте ему администратора (замените email):

```sh
docker compose exec app php artisan tinker --execute="App\\Models\\User::where('email', 'admin@example.com')->update(['is_admin' => true]);"
```

Перейдите на `/admin`, добавьте категории с уникальными латинскими slug (например, `steam`, `minecraft`, `accounts`). После этого можно публиковать объявления из личного кабинета. Не оставляйте публичную установку без предварительной настройки администратора.

## Работа без Docker

```sh
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Настройте `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` и `APP_URL` в `.env` под локальную среду. Для разработки запустите `npm run dev` отдельно.

## Сценарий заказа

`pending_payment` → покупатель подтверждает тестовую оплату → `awaiting_delivery` → продавец указывает данные передачи → `delivered` → покупатель подтверждает → `completed` → отзыв. Тестовый отказ оставляет заказ в ожидании оплаты и позволяет повторить попытку. Покупатель может отменить заказ до передачи: остаток восстанавливается, а для оплаченного заказа записывается тестовый возврат. До завершения заказа обе стороны могут открыть спор (`disputed`); администратор выбирает завершение или отмену, при отмене записывается тестовый возврат и восстанавливается остаток. **Тестовая оплата не принимает банковские реквизиты, не списывает, не резервирует и не переводит реальные деньги.**

## Структура

| Путь | Назначение |
| --- | --- |
| `routes/web.php` | Маршруты и ограничения доступа |
| `app/Http/Controllers` | Каталог, вход, объявления, заказы, чат, админка |
| `app/Models` | Eloquent-модели |
| `database/migrations` | Структура MySQL |
| `resources/js/App.vue` | Интерфейс Vue |
| `resources/css/app.css` | Адаптивные стили |
| `docker/`, `docker-compose.yml` | PHP-FPM, nginx, MySQL, Vite |
