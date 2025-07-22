<?php

namespace AcceptCode\Actions;

use AcceptCode\Actions\AcceptCodeActions;
use AcceptCode\Constants\AcceptCodeSlugs;
use AcceptCode\DTO\AcceptCodeFormDTO;
use AcceptCode\DTO\AcceptCodeDTO;
use AcceptCode\Models\AcceptCode;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ResetPasswordAcceptCodeActions extends AcceptCodeActions {

    /**
     * Проверка на то, можно ли сбросить пароль
     *
     * @param AcceptCodeDTO $dto
     * @param Model $user
     *
     * @return AcceptCode|\Exception - Возвращает отправленный код
     */
    public function getAcceptCodeResetPassword(AcceptCodeDTO $dto, Model $user): AcceptCode|\Exception {
        $acceptCode = $this->acceptCodeRepository->getAcceptCode(
            $dto, $user, AcceptCodeSlugs::RESET_PASSWORD_SLUG,
            'Неверный код для сброса пароля!',
        );

        return $acceptCode;
    }

    /**
     * Отправка кода для сброса пароля
     *
     * @param AcceptCodeDTO $dto
     *
     * @return bool|\Exception
     */
    public function sendResetPasswordCode(AcceptCodeDTO $dto): bool|\Exception {
        $user = $this->getUser($dto);

        $this->acceptCodeRepository->canSendPasswordResetCode($dto, $user);

        return $this->createOrUpdateAcceptCodeDefault(
            AcceptCodeFormDTO::fromCredetinals(
                $user,
                $dto->login,
                AcceptCodeSlugs::RESET_PASSWORD_SLUG,
                $dto->type
            ),
            $user,
            'При отправке кода для сброса пароля произошла ошибка!',
            fn () => $this->successAsyncLog(config('accept-code.logger_slugs.reset_password_send_code')),
            fn (string $e) => $this->errorAsyncLog(config('accept-code.logger_slugs.reset_password_send_code'), $e),
        );
    }

    /**
     * Проверка на то, что код верен
     *
     * @param  AcceptCodeDTO $dto
     *
     * @return bool|\Exception
     */
    public function verifyResetPasswordCode(AcceptCodeDTO $dto): bool|\Exception {
        $user = $this->getUser($dto);

        $this->getAcceptCodeResetPassword($dto, $user);

        return true;
    }

    /**
     * Смена пароля и удаление кода для сброса пароля
     *
     * @param AcceptCodeDTO $dto,
     *
     * @return Model|\Exception
     */
    public function acceptResetPasswordCode(AcceptCodeDTO $dto): Model|\Exception {
        $user = $this->getUser($dto);
        $acceptCode = $this->getAcceptCodeResetPassword($dto, $user);

        $updatePassword = function () use ($dto, $user, $acceptCode) {
            $user = call_user_func(config('accept-code.verified_user'), $user, $dto->password);
            $acceptCode->delete();

            Auth::setUser($user);

            return $user;
        };

        $user = $this->transactionConstructionWithFunc(
            $updatePassword,
            'При попытке установить новый пароль произошла ошибка!',
            fn () => $this->successAsyncLog(config('accept-code.logger_slugs.reset_password_set_new')),
            fn (string $e) => $this->errorAsyncLog(config('accept-code.logger_slugs.reset_password_set_new'), $e)
        );

        return $user;
    }
}
