<?php

namespace AcceptCode\DTO;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AcceptCodeFormDTO {
    public function __construct(
        public readonly int $userId,
        public readonly string $credetinal,
        public readonly string $slug,
        public readonly string $type,
        public readonly bool $userVerified = false,
    ) {}

    /**
     * Из модели пользователя достаем номер
     *
     * @param Model|Authenticatable $user
     * @param string $slug
     *
     * @return AcceptCodeFormDTO
     */
    public static function fromCredetinalsPhone(Model|Authenticatable $user, string $slug): AcceptCodeFormDTO {
        return new self(
            userId: $user->id,
            credetinal: $user->phone,
            slug: $slug,
            type: 'phone',
            userVerified: $user->is_verified
        );
    }

    /**
     * Из модели пользователя достаем id
     *
     * @param Model|Authenticatable $user
     * @param string $credetinal
     * @param string $slug
     * @param string $type
     *
     * @return AcceptCodeFormDTO
     */
    public static function fromCredetinals(
        Model|Authenticatable $user,
        string $credetinal,
        string $slug,
        string $type,
    ): AcceptCodeFormDTO {
        return new self(
            userId: $user->id,
            credetinal: $credetinal,
            slug: $slug,
            type: $type,
            userVerified: $user->is_verified
        );
    }
}
