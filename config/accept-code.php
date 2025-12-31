<?php

use AcceptCode\Constants\AcceptCodeSlugs;

return [
    /*
    |--------------------------------------------------------------------------
    | Accept slugs for table enums
    |--------------------------------------------------------------------------
    */
    'accept_code_slugs' => [
        AcceptCodeSlugs::LOGIN_SLUG,
        AcceptCodeSlugs::REGISTRATION_SLUG,
        AcceptCodeSlugs::RESET_PASSWORD_SLUG
    ],

    /*
    |--------------------------------------------------------------------------
    | Function what find user by his phone and return
    |--------------------------------------------------------------------------
    |
    | @param string login
    |
    | @return User | Authenticatable
    */
    'get_user_by_phone' => [\App\Modules\User\Repositories\UserRepository::class, 'getByPhone'],

    /*
    |--------------------------------------------------------------------------
    | Function what find user by his email and return
    |--------------------------------------------------------------------------
    |
    | @param string login
    |
    | @return User|Authenticatable
    */
    'get_user_by_email' => [\App\Modules\User\Repositories\UserRepository::class, 'getByEmail'],

    /*
    |--------------------------------------------------------------------------
    | Function what user role and return id
    |--------------------------------------------------------------------------
    |
    | @return int
    */
    'get_user_role_id' => [\App\Modules\Role\Repositories\RoleRepository::class, 'getUserRoleId'],

    /*
    |--------------------------------------------------------------------------
    | Function what check can user auth
    |--------------------------------------------------------------------------
    |
    | @param bool isPortal
    | @param int authRoleId
    | @param int userRoleId
    |
    | @return bool|Exception
    */
    'check_permission_to_auth' => [\App\Modules\Auth\Repositories\AuthRepository::class, 'checkPermToAuth'],

    /*
    |--------------------------------------------------------------------------
    | Function what update is_verified in User model to true
    |--------------------------------------------------------------------------
    |
    | @param User|Authenticatable user
    |
    | @return User|Authenticatable
    */
    'verified_user' => [\App\Modules\Auth\Services\AuthService::class, 'verifiedUser'],

    /*
    |--------------------------------------------------------------------------
    | Function what update password in User model
    |--------------------------------------------------------------------------
    |
    | @param User|Authenticatable user
    | @param string newPassword
    |
    | @return User|Authenticatable
    */
    'change_password' => [\App\Modules\Auth\Services\AuthService::class, 'changePassword'],

    /*
    |--------------------------------------------------------------------------
    | Slugs for logger
    |--------------------------------------------------------------------------
    */
    'logger_slugs' => [
        'login_accept_code' => 'login-accept',

        'login_send_code' => 'login-send',

        'registration' => 'registration',

        'registration_send_code' => 'registration-send',

        'reset_password_set_new' => 'reset-password-set-new',

        'reset_password_send_code' => 'reset-password-send-code',
    ],

    /*
    |--------------------------------------------------------------------------
    | Functions for send email notifications
    |--------------------------------------------------------------------------
    |
    | @param User|Authenticatable user
    | @param AcceptCode acceptCode
    |
    | @return void
    */
    'email' => [
        'send_verification_notification' => [\App\Modules\Email\Actions\EmailSenderActions::class, 'sendVerificationNotification'],

        'send_reset_password_notification' => [\App\Modules\Email\Actions\EmailSenderActions::class, 'sendResetPasswordNotification'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Functions for send sms notifications
    |--------------------------------------------------------------------------
    |
    | @param User|Authenticatable user
    | @param AcceptCode acceptCode
    |
    | @return void
    */
    'phone' => [
        'send_registration_code' => [\App\Modules\Phone\Actions\MTCActions::class, 'sendRegistrationCode'],

        'send_reset_password_code' => [\App\Modules\Phone\Actions\MTCActions::class, 'sendResetPasswordCode'],

        'send_login_code' => [\App\Modules\Phone\Actions\MTCActions::class, 'sendLoginCode'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Time to delay before repeat send accept code
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in seconds)
    | Defaults to 90 seconds.
    */
    'accept_code_delay_repeat_ttl' => 90,

    /*
    |--------------------------------------------------------------------------
    | Time to live accept code
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in seconds)
    | Defaults to 12 hours.
    */
    'accept_code_ttl' => 43200,

    /*
    |--------------------------------------------------------------------------
    | Phone validation rule for requests
    |--------------------------------------------------------------------------
    */
    'phone_validation_rule' => [\Core\Rules\ValidPhone::class, 'create'],

    /*
    |--------------------------------------------------------------------------
    | Password validation rule for requests
    |--------------------------------------------------------------------------
    */
    'password_validation_rule' => [\Core\Rules\ValidPassword::class, 'create'],
];
