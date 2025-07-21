<?php

namespace AcceptCode\Actions;

use AcceptCode\Constants\AcceptCodeSlugs;
use AcceptCode\DTO\AcceptCodeFormDTO;
use AcceptCode\DTO\AcceptCodeDTO;
use AcceptCode\Models\AcceptCode;
use AcceptCode\Actions\AcceptCodeActions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RegistrationAcceptCodeActions extends AcceptCodeActions {
    /**
     * Берем код который нужно подтвердить для регистрации
     *
     * @param AcceptCodeDTO $dto
     * @param Model $user
     *
     * @return AcceptCode|\Exception
     */
    public function getAcceptRegistrationCode(AcceptCodeDTO $dto, Model $user): AcceptCode|\Exception {
        $acceptCode = $this->acceptCodeRepository->getAcceptCode(
            $dto, $user, AcceptCodeSlugs::REGISTRATION_SLUG,
            'Неверный код для подтверждения аккаунта!',
            'Срок действия кода истек. Пройдите процесс подтверждения аккаунта заново!'
        );

        return $acceptCode;
    }

    /**
     * Отправка кода для регистрации в системе
     *
     * @param mixed $dto
     * @param bool $isFirst = false
     * @param ?Model $user = null
     *
     * @return bool|\Exception
     */
    public function sendRegistrationCode(
        mixed $dto,
        bool $isFirst = false,
        ?Model $user = null
    ): bool|\Exception {
        if (!$isFirst) {
            $user = $this->getUser($dto);
            $this->acceptCodeRepository->canSendRegistrationAcceptCode($dto, $user);
        }

        $updateCreateDTO = AcceptCodeFormDTO::fromCredetinals(
            $user,
            $dto->login,
            AcceptCodeSlugs::REGISTRATION_SLUG,
            $dto->type,
        );

        return $this->createOrUpdateAcceptCodeDefault(
            $updateCreateDTO,
            $user,
            'При отправке кода для подтверждения аккаунта произошла ошибка!',
            fn () => $this->successLog(config('accept-code.logger_slugs.registration_send_code')),
            fn (string $e) => $this->errorLog(config('accept-code.logger_slugs.registration_send_code'), $e),
        );
    }

    /**
     * Подтверждение аккаунта
     *
     * @param AcceptCodeDTO $dto
     *
     * @return array|\Exception
     */
    public function acceptRegistrationCode(AcceptCodeDTO $dto): array|\Exception {
        $user = $this->getUser($dto);
        $acceptCode = $this->getAcceptRegistrationCode($dto, $user);

        $updateUser = function () use ($user, $acceptCode) {
            call_user_func(config('accept-code.verified_user'), $user);
            $acceptCode->delete();

            [ $accessToken, $refreshToken ] = $this->fromUser($user);
            Auth::setUser($user);

            return [ $accessToken, $refreshToken ];
        };

        [ $accessToken, $refreshToken ] = $this->transactionConstructionWithFunc(
            $updateUser,
            'При потверждении аккаунта произошла ошибка!',
            fn () => $this->successLog(config('accept-code.logger_slugs.registration')),
            fn (string $e) => $this->errorLog(config('accept-code.logger_slugs.registration'), $e),
        );

        $this->writeAsyncLoginHistory($dto->request);

        return [ $accessToken, $refreshToken, $user ];
    }
}
