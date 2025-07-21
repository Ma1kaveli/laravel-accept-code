<?php

namespace AcceptCode\Services;

use AcceptCode\DTO\AcceptCodeFormDTO;
use AcceptCode\Models\AcceptCode;

class AcceptCodeService {
    public function __construct() {}

    /**
     * Добавление пользователя
     *
     * @param AcceptCodeFormDTO $dto
     *
     * @return AcceptCode
     */
    public function create(AcceptCodeFormDTO $dto): AcceptCode
    {
        return AcceptCode::create([
            'credetinal' => $dto->credetinal,
            'code' => (string) rand(1000, 9999),
            'user_id' => $dto->userId,
            'slug' => $dto->slug,
            'type' => $dto->type,
        ]);
    }

    /**
     * Если есть запись по credetinal и slug, обновляем, иначе создаем
     *
     * @param  AcceptCodeFormDTO $dto
     *
     * @return AcceptCode
     */
    public function createOrUpdate(AcceptCodeFormDTO $dto): AcceptCode
    {
        return AcceptCode::updateOrCreate(
            [
                'credetinal' => $dto->credetinal,
                'slug' => $dto->slug,
                'type' => $dto->type,
            ],
            [
                'credetinal' => $dto->credetinal,
                'code' => (string) rand(1000, 9999),
                'user_id' => $dto->userId,
                'slug' => $dto->slug,
                'type' => $dto->type,
            ]
        );
    }
}
