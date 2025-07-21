<?php

namespace AcceptCode\Actions;

use AcceptCode\Actions\AcceptCodeActions;
use AcceptCode\Constants\AcceptCodeSlugs;
use AcceptCode\DTO\AcceptCodeDTO;
use AcceptCode\DTO\AcceptCodeFormDTO;
use AcceptCode\Models\AcceptCode;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LoginAcceptCodeActions extends AcceptCodeActions {
    /**
     * Ищем и получаем код для входа
     *
     * @param AcceptCodeDTO $dto
     * @param Model $user
     *
     * @return AcceptCode|\Exception
     */
    public function getLoginAcceptCode(AcceptCodeDTO $dto, Model $user): AcceptCode|\Exception {
        $acceptCode = $this->acceptCodeRepository->getAcceptCode(
            $dto, $user, AcceptCodeSlugs::LOGIN_SLUG,
            'Неверный код для авторизации!',
        );

        call_user_func(
            config('accept-code.check_permission_to_auth',  function () {}),
            $dto->isPortal,
            $user->role_id,
            call_user_func(config('accept-code.get_user_role_id', null))
        );

        return $acceptCode;
    }

    /**
     * Отправка кода для логина в системе
     *
     * @param AcceptCodeDTO $dto
     *
     * @return bool|\Exception
     */
    public function sendLoginCode(AcceptCodeDTO $dto): bool|\Exception {
        $user = call_user_func(config('accept-code.get_user_by_phone'), $dto->login, true);

        if (!empty($user)) {
            call_user_func(
                config('accept-code.check_permission_to_auth',  function () {}),
                $dto->isPortal,
                $user->role_id,
                call_user_func(config('accept-code.get_user_role_id', null))
            );
        }

        $this->acceptCodeRepository->canSendLoginAcceptCode($dto, $user);

        return $this->createOrUpdateAcceptCodeDefault(
            AcceptCodeFormDTO::fromCredetinalsPhone($user, AcceptCodeSlugs::LOGIN_SLUG),
            $user,
            'При отправке кода для авторизации произошла ошибка!',
            fn () => $this->successLog(config('accept-code.logger_slugs.login_send_code')),
            fn (string $e) => $this->errorLog(config('accept-code.logger_slugs.login_send_code'), $e)
        );
    }

    /**
     * Подтверждение кода авторизации
     *
     * @param AcceptCodeDTO $dto
     *
     * @return array|\Exception
     */
    public function acceptLoginCodeAndAuth(AcceptCodeDTO $dto): array|\Exception {
        $user = call_user_func(config('accept-code.get_user_by_phone'), $dto->login, true);

        $acceptCode = $this->getLoginAcceptCode($dto, $user);

        $loginAcceptCode = function () use ($user, $acceptCode) {
            if (!$user->is_verified) {
                call_user_func(config('accept-code.verified_user'), $user);
            }

            $acceptCode->delete();

            [ $accessToken, $refreshToken ] = $this->fromUser($user);
            Auth::setUser($user);

            return [ $accessToken, $refreshToken ];
        };

        [ $accessToken, $refreshToken ] = $this->transactionConstructionWithFunc(
            $loginAcceptCode,
            'При авторизации произошла ошибка!',
            fn () => $this->successLog(config('accept-code.logger_slugs.login_accept_code')),
            fn (string $e) => $this->errorLog(config('accept-code.logger_slugs.login_accept_code'), $e),
        );

        $this->writeAsyncLoginHistory($dto->request);

        return [ $accessToken, $refreshToken, $user ];
    }
}
