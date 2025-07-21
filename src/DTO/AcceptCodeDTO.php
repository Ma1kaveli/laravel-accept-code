<?php

namespace AcceptCode\DTO;

use AcceptCode\Requests\LoginAcceptCodeRequest;
use AcceptCode\Requests\LoginSendCodeRequest;
use AcceptCode\Requests\PasswordResetSendRequest;
use AcceptCode\Requests\PasswordResetSetNewRequest;
use AcceptCode\Requests\PasswordResetVerifyRequest;
use AcceptCode\Requests\RegistrationSendCodeRequest;
use AcceptCode\Requests\RegistrationAcceptAccountRequest;

class AcceptCodeDTO {
    public function __construct(
        public readonly string $login,
        public readonly bool $isPortal = true,
        public readonly ?string $code = null,
        public readonly ?string $type = null,
        public readonly ?string $captchaToken = null,
        public readonly ?string $password = null,
        public readonly bool $isPhone = true,
        public readonly mixed $request = null
    ) {}

    /**
     * Формирование DTO с роута отправки кода для верефикации своего аккаунта
     *
     * @param  RegistrationSendCodeRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromRegistrationSendRequest(RegistrationSendCodeRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('login'),
            type: $request->get('type'),
            captchaToken: $request->get('captchaToken'),
            request: $request
        );
    }

    /**
     * Формирование DTO с роута для верефикации своего аккаунта
     *
     * @param  RegistrationAcceptAccountRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromRegistrationAcceptAccountRequest(RegistrationAcceptAccountRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('login'),
            type: $request->get('type'),
            code: $request->get('code'),
            request: $request
        );
    }

    /**
     * Формирование DTO с роута для отправки кода на возможность войтив в аккаунт
     *
     * @param  LoginSendCodeRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromLoginSendRequest(LoginSendCodeRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('phone'),
            isPortal: $request->get('isPortal'),
            captchaToken: $request->get('captchaToken'),
            request: $request
        );
    }

    /**
     * Формирование DTO с роута для проверки кода на возможность войтив аккаунт
     *
     * @param  LoginAcceptCodeRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromLoginAcceptRequest(LoginAcceptCodeRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('phone'),
            isPortal: $request->get('isPortal'),
            code: $request->get('code'),
            request: $request
        );
    }

    /**
     * Формирование DTO с роута для отправки кода для сброса пароля
     *
     * @param  PasswordResetSendRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromPasswordResetSendRequest(PasswordResetSendRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('login'),
            type: $request->get('type'),
            captchaToken: $request->get('captchaToken'),
            request: $request
        );
    }

    /**
     * Формирование DTO с роута для установления нового пароля
     *
     * @param  PasswordResetSetNewRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromPasswordResetSetNewRequest(PasswordResetSetNewRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('login'),
            code: $request->get('code'),
            password: $request->get('password'),
            type: $request->get('type'),
            request: $request
        );
    }

    /**
     * Формирование DTO с роута для проверки кода на валидность
     *
     * @param  PasswordResetVerifyRequest $request
     *
     * @return AcceptCodeDTO
     */
    public static function fromPasswordResetVerifyRequest(PasswordResetVerifyRequest $request): AcceptCodeDTO {
        return new self(
            login: $request->get('login'),
            code: $request->get('code'),
            type: $request->get('type'),
            request: $request
        );
    }
}
