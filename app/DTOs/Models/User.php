<?php

namespace App\DTOs\Models;

use App\Models\User as UserModel;
use JsonSerializable;

readonly class User implements JsonSerializable{
    private int    $id;
    private string $name;

    public function __construct(UserModel $user){
        $this->id   = $user->id;
        $this->name = $user->name;
    }

    public function jsonSerialize(): array{
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}