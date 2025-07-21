<?php

namespace AcceptCode\Actions;

use AcceptCode\Constants\AcceptCodeSlugs;
use AcceptCode\Constants\AcceptCodeTypes;
use AcceptCode\DTO\AcceptCodeDTO;
use AcceptCode\DTO\AcceptCodeFormDTO;
use AcceptCode\Repositories\AcceptCodeRepository;
use AcceptCode\Models\AcceptCode;
use AcceptCode\Services\AcceptCodeService;

use Crudler\Traits\DBTransaction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use JWTAuth\JWTAuth;
use LaravelHistory\Traits\LoginHistory;
use Logger\Traits\Logger;

class AcceptCodeActions {
    use DBTransaction, JWTAuth, Logger, LoginHistory;

    public AcceptCodeService $acceptCodeService;

    public AcceptCodeRepository $acceptCodeRepository;

    public function __construct()
    {
        $this->acceptCodeRepository = new AcceptCodeRepository();

        $this->acceptCodeService = new AcceptCodeService();
    }

    /**
     * Получение пользователя по почте или номеру телефона
     *
     * @param AcceptCodeDTO $dto
     *
     * @return Model|Authenticatable|null
     */
    protected function getUser(AcceptCodeDTO $dto): Model|Authenticatable|null
    {
        if ($dto->type === AcceptCodeTypes::EMAIL_TYPE) {
            return call_user_func(config('accept-code.get_user_by_email'), $dto->login, true);
        }

        return call_user_func(config('accept-code.get_user_by_phone'), $dto->login, true);
    }

    /**
     * sendEmailNotification
     *
     * @param Model|Authenticatable $user
     * @param AcceptCode $acceptCode
     *
     * @return void
     */
    public function sendEmailNotification(Model $user, AcceptCode $acceptCode)
    {
        if ($acceptCode->slug === AcceptCodeSlugs::REGISTRATION_SLUG) {
            call_user_func(config('accept-code.email.send_verification_notification'), $user, $acceptCode);
        } else if ($acceptCode->slug === AcceptCodeSlugs::RESET_PASSWORD_SLUG) {
            call_user_func(config('accept-code.email.send_reset_password_notification'), $user, $acceptCode);
        }
    }

    /**
     * sendSmsNotification
     *
     * @param Model|Authenticatable $user
     * @param AcceptCode $acceptCode
     *
     * @return void
     */
    public function sendSmsNotification(Model $user, AcceptCode $acceptCode)
    {
        if ($acceptCode->slug === AcceptCodeSlugs::REGISTRATION_SLUG) {
            call_user_func(config('accept-code.phone.send_registration_code'), $user, $acceptCode);
        } else if ($acceptCode->slug === AcceptCodeSlugs::RESET_PASSWORD_SLUG) {
            call_user_func(config('accept-code.phone.send_reset_password_code'), $user, $acceptCode);
        } else {
            call_user_func(config('accept-code.phone.send_login_code'), $user, $acceptCode);
        }
    }

    /**
     * Дефолтный метод для поиска и обновления или создания записи
     *
     * @param AcceptCodeFormDTO $dto,
     * @param Model|Authenticatable $user,
     * @param string $errorMessage,
     * @param ?callable $successFunc = null,
     * @param ?callable $errorFunc = null
     *
     * @return bool|\Exception
     */
    public function createOrUpdateAcceptCodeDefault(
        AcceptCodeFormDTO $dto,
        Model|Authenticatable $user,
        string $errorMessage,
        ?callable $successFunc = null,
        ?callable $errorFunc = null
    ): bool|\Exception {
        $sendAcceptCode = function () use ($dto, $user) {
            $acceptCode = $this->acceptCodeService->createOrUpdate($dto);

            if ($dto->type === 'email') {
                $this->sendEmailNotification($user, $acceptCode);
            } else {
                $this->sendSmsNotification($user, $acceptCode);
            }
        };

        $this->transactionConstructionWithFunc(
            $sendAcceptCode,
            $errorMessage,
            $successFunc,
            $errorFunc
        );

        return true;
    }
}
