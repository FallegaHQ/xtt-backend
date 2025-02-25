<?php

namespace App\DTOs\Response;

use JsonSerializable;

abstract class ResponseData implements JsonSerializable{
    private array $data;

    public function jsonSerialize(): array{
        return $this->data;
    }

    protected function setData(array $data): void{
        $this->data = $data;
    }
}