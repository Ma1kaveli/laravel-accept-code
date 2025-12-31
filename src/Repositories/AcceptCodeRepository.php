<?php

namespace AcceptCode\Repositories;

use AcceptCode\Constants\AcceptCodeSlugs;
use AcceptCode\DTO\AcceptCodeDTO;
use AcceptCode\Models\AcceptCode;

use Carbon\Carbon;
use Core\Repositories\BaseRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AcceptCodeRepository extends BaseRepository {
    public function __construct() {
        parent::__construct(AcceptCode::class);
    }

    /**
     * Выдача уже отправленного кода пользователю или null
     *
     * @param int $userId
     * @param string $credetinal
     * @param string $slug
     *
     * @return AcceptCode|null
     */
    public function getSendAcceptCodeByUserId(int $userId, string $credetinal, string $slug): AcceptCode|null {
        return $this->query()->where([
            ['credetinal', $credetinal],
            ['user_id', $userId],
            ['slug', $slug]
        ])->first();
    }

    /**
     * Поиск отправленного кода по коду или null
     *
     * @param string $credetinal
     * @param string $slug
     * @param ?string $code = null
     * @return AcceptCode|null
     */
    public function getSendAcceptCode(
        string $credetinal,
        string $slug,
        ?string $code = null
    ): AcceptCode|null {
        return $this->query()->when(
            !empty($code),
            fn ($q) => $q->where('code', $code)
        )->where([
            ['credetinal', $credetinal],
            ['slug', $slug]
        ])->first();
    }

    /**
     * Смотрим закончилось ли время для повторной отправки кода
     *
     * @param AcceptCode $acceptCode
     * @param ?string $error - 'Перед повторной отправкой кода необходимо подождать!'
     *
     * @return bool|\Exception
     */
    public function checkDelayIsOver(
        AcceptCode $acceptCode,
        ?string $error = 'Перед повторной отправкой кода необходимо подождать!'
    ): bool|\Exception {
        $nowDate = Carbon::now();
        $waitDate = Carbon::createFromFormat('Y-m-d H:i:s', $acceptCode->updated_at)
            ->addSeconds(config('accept-code.accept_code_delay_repeat_ttl', 90));

        if ($nowDate < $waitDate) {
            throw new \Exception($error, 400);
        }

        return true;
    }

    /**
     * Валиден ли код подтверждения
     *
     * @param AcceptCode $acceptCode
     * @param ?string $error - 'Срок действия кода истек. Пройдите процесс авторизации заново!'
     *
     * @return bool|\Exception
     */
    public function acceptCodeIsValid(
        AcceptCode $acceptCode,
        ?string $error = 'Срок действия кода истек. Пройдите процесс авторизации заново!'
    ): bool|\Exception {
        $nowDate = Carbon::now();
        $maxDate = Carbon::createFromFormat('Y-m-d H:i:s', $acceptCode->updated_at)
            ->addSeconds(config('accept-code.accept_code_ttl', 43200));

        if ($nowDate > $maxDate) {
            throw new \Exception($error, 400);
        }

        return true;
    }

    /**
     * Получение кода для какого-либо действия
     *
     *  1. Проверяем что пользователь существует
     *  2. Получение кода отправленного для пользователя
     *  3. Проверяем не истек ли срок действия кода
     *
     * @param AcceptCodeDTO $dto
     * @param Model|Authenticatable|null $user
     * @param string $slug
     * @param string $emptyAcceptCodeMessage
     * @param string $notValidAcceptCodeMessage = 'Срок действия кода истек. Пройдите процесс авторизации заново!'
     *
     * @return AcceptCode|\Exception
     */
    public function getAcceptCode(
        AcceptCodeDTO $dto,
        Model|Authenticatable|null $user,
        string $slug,
        string $emptyAcceptCodeMessage,
        string $notValidAcceptCodeMessage = 'Срок действия кода истек. Пройдите процесс авторизации заново!'
    ): AcceptCode|\Exception {
        if (empty($user)) {
            throw new \Exception('Пользователь с таким номером телефона не найден!', 404);
        }

        $acceptCode = $this->getSendAcceptCode(
            $dto->login,
            $slug,
            $dto->code
        );

        if (empty($acceptCode)) {
            throw new \Exception($emptyAcceptCodeMessage, 404);
        }

        $this->acceptCodeIsValid($acceptCode, $notValidAcceptCodeMessage);

        return $acceptCode;
    }

    /**
     * Проверки на то, можно ли отправить код для логина
     *
     *  1. Проверяем что пользователь существует
     *  2. Получение кода отправленного для пользователя по идентификатору пользователя
     *  3. Проверяем можем ли мы еще раз отправить код
     *
     * @param AcceptCodeDTO $dto
     * @param Model|Authenticatable|null $user
     *
     * @return bool|\Exception
     */
    public function canSendLoginAcceptCode(AcceptCodeDTO $dto, Model|Authenticatable|null $user): bool|\Exception {
        if (empty($user)) {
            throw new \Exception('Пользователь с таким номером телефона не найден!', 422);
        }

        $loginSendCode = $this->getSendAcceptCodeByUserId($user->id, $dto->login, AcceptCodeSlugs::LOGIN_SLUG);

        if (!empty($loginSendCode)) {
            $this->checkDelayIsOver($loginSendCode);
        }

        return true;
    }

    /**
     * Проверяем может ли пользователь отправить код для сброса пароля
     *
     *  1. Проверяем что пользователь существует
     *  2. Получение кода отправленного для пользователя
     *  3. Проверяем можем ли мы еще раз отправить код
     *
     * @param AcceptCodeDTO $dto
     * @param Model|Authenticatable|null $user
     *
     * @return ?\Exception
     */
    public function canSendPasswordResetCode(AcceptCodeDTO $dto, Model|Authenticatable|null $user): bool|\Exception {
        if (empty($user)) {
            throw new \Exception('Пользователя с такими учетными данными не существует!', 404);
        }

        $resetPassword = $this->getSendAcceptCode($dto->login, AcceptCodeSlugs::RESET_PASSWORD_SLUG);

        if (!empty($resetPassword)) {
            $this->checkDelayIsOver($resetPassword);
        }

        return true;
    }

    /**
     * Проверяем может ли пользователь отправить код для подтверждения аккаунта
     *
     * @param AcceptCodeDTO $dto
     * @param Model|Authenticatable|null $user
     *
     * @return ?\Exception
     */
    public function canSendRegistrationAcceptCode(AcceptCodeDTO $dto, Model|Authenticatable|null $user): bool|\Exception {
        if ($user->is_verified) {
            throw new \Exception('Вы уже прошли процесс верификации!', 400);
        }

        $registrationAccept = $this->getSendAcceptCode(
            $dto->login,
            AcceptCodeSlugs::REGISTRATION_SLUG
        );

        if (!empty($registrationAccept)) {
            $this->checkDelayIsOver($registrationAccept);
        }

        return true;
    }
}
