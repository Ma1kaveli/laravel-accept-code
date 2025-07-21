# Laravel Accept Code

**Laravel Accept Code** — это пакет для Laravel, который предоставляет расширенные возможности управления кодами подтверждения (accept codes) для таких действий, как вход, регистрация и сброс пароля. Пакет поддерживает отправку кодов по email и SMS, интегрируется с системой аутентификации и логированием.

## Возможности

- Управление кодами подтверждения для входа, регистрации и сброса пароля.
- Отправка кодов подтверждения через email и SMS.
- Проверка валидности кодов с учетом времени жизни и интервалов повторной отправки.
- Интеграция с системой аутентификации и ролями пользователей.
- Настраиваемые slug'ы и типы кодов (email/phone).
- Консольные команды для миграций.

## Требования

- PHP 8.2 или выше
- Laravel 10.10, 11.0 или 12.0
- Пакет `makaveli/laravel-login-history` (версия ^1.0)

## Установка

1. Установите пакет через Composer:

   ```bash
   composer require makaveli/laravel-accept-code
   ```

2. Выполните миграции для создания таблицы `accept_codes`:

   ```bash
   php artisan migrate:accept-code
   ```

3. (Опционально) Опубликуйте файл конфигурации:

   ```bash
   php artisan vendor:publish --tag=accept-code-config
   ```

   Это создаст файл `config/accept-code.php`, который можно настроить под ваши нужды.

## Конфигурация

Файл `config/accept-code.php` позволяет настроить поведение пакета. Основные параметры:

- **`accept_code_slugs`**: Список slug'ов для кодов подтверждения (`login`, `registration`, `reset-password`).
- **`get_user_by_phone`**: Функция поиска пользователя по номеру телефона.
- **`get_user_by_email`**: Функция поиска пользователя по email.
- **`get_user_role_id`**: Функция получения ID роли пользователя.
- **`check_permission_to_auth`**: Функция проверки прав на авторизацию.
- **`verified_user`**: Функция обновления статуса верификации пользователя.
- **`change_password`**: Функция изменения пароля пользователя.
- **`logger_slugs`**: Slug'ы для логирования действий.
- **`email`**: Функции отправки email-уведомлений.
- **`phone`**: Функции отправки SMS-уведомлений.
- **`accept_code_delay_repeat_ttl`**: Время задержки перед повторной отправкой кода (по умолчанию 90 секунд).
- **`accept_code_ttl`**: Время жизни кода (по умолчанию 43200 секунд, или 12 часов).
- **`phone_validation_rule`**: Правило валидации номера телефона.
- **`password_validation_rule`**: Правило валидации пароля.

### Пример конфигурации

```php
return [
    'accept_code_slugs' => [
        \AcceptCode\Constants\AcceptCodeSlugs::LOGIN_SLUG,
        \AcceptCode\Constants\AcceptCodeSlugs::REGISTRATION_SLUG,
        \AcceptCode\Constants\AcceptCodeSlugs::RESET_PASSWORD_SLUG,
    ],
    'get_user_by_phone' => [\App\Modules\User\Repositories\UserRepository::class, 'getByPhone'],
    'get_user_by_email' => [\App\Modules\User\Repositories\UserRepository::class, 'getByEmail'],
    'email' => [
        'send_verification_notification' => [\App\Modules\Email\Actions\EmailSenderActions::class, 'sendVerificationNotification'],
        'send_reset_password_notification' => [\App\Modules\Email\Actions\EmailSenderActions::class, 'sendResetPasswordNotification'],
    ],
    'phone' => [
        'send_registration_code' => [\App\Modules\Phone\Actions\MTCActions::class, 'sendRegistrationCode'],
        'send_reset_password_code' => [\App\Modules\Phone\Actions\MTCActions::class, 'sendResetPasswordCode'],
        'send_login_code' => [\App\Modules\Phone\Actions\MTCActions::class, 'sendLoginCode'],
    ],
];
```

## Использование

### Вход с кодом подтверждения

#### Отправка кода для входа

```php
use AcceptCode\Actions\LoginAcceptCodeActions;
use AcceptCode\DTO\AcceptCodeDTO;

$actions = new LoginAcceptCodeActions();
$dto = AcceptCodeDTO::fromLoginSendRequest($request);
$actions->sendLoginCode($dto);
```

#### Подтверждение кода и авторизация

```php
[ $accessToken, $refreshToken, $user ] = $actions->acceptLoginCodeAndAuth(
    AcceptCodeDTO::fromLoginAcceptRequest($request)
);
```

### Регистрация с кодом подтверждения

#### Отправка кода для регистрации

```php
use AcceptCode\Actions\RegistrationAcceptCodeActions;

$actions = new RegistrationAcceptCodeActions();
$dto = AcceptCodeDTO::fromRegistrationSendRequest($request);
$actions->sendRegistrationCode($dto);
```

#### Подтверждение кода для регистрации

```php
[ $accessToken, $refreshToken, $user ] = $actions->acceptRegistrationCode(
    AcceptCodeDTO::fromRegistrationAcceptAccountRequest($request)
);
```

### Сброс пароля с кодом подтверждения

#### Отправка кода для сброса пароля

```php
use AcceptCode\Actions\ResetPasswordAcceptCodeActions;

$actions = new ResetPasswordAcceptCodeActions();
$dto = AcceptCodeDTO::fromPasswordResetSendRequest($request);
$actions->sendResetPasswordCode($dto);
```

#### Проверка кода

```php
$actions->verifyResetPasswordCode(
    AcceptCodeDTO::fromPasswordResetVerifyRequest($request)
);
```

#### Установка нового пароля

```php
$user = $actions->acceptResetPasswordCode(
    AcceptCodeDTO::fromPasswordResetSetNewRequest($request)
);
```

## Консольные команды

Для выполнения миграций пакета:

```bash
php artisan migrate:accept-code
```

## Структура базы данных

Таблица `accept_codes` содержит следующие поля:

- `id`: Уникальный идентификатор.
- `user_id`: Внешний ключ на таблицу пользователей.
- `credetinal`: Учетные данные (телефон или email).
- `code`: Код подтверждения.
- `type`: Тип кода (`email` или `phone`).
- `slug`: Slug действия (`login`, `registration`, `reset-password`).
- `created_at`, `updated_at`: Временные метки.

## Расширение

Вы можете настроить поведение пакета, переопределив:

- Модель `AcceptCode` для добавления собственных методов или связей.
- Репозиторий `AcceptCodeRepository` для изменения логики работы с кодами.
- Сервис `AcceptCodeService` для кастомизации создания и обновления кодов.
- Классы действий (`LoginAcceptCodeActions`, `RegistrationAcceptCodeActions`, `ResetPasswordAcceptCodeActions`) для изменения бизнес-логики.