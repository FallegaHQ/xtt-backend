<?php

namespace App\DTOs\Response;

use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponse implements JsonSerializable{

    /**
     * @var array{
     * code: int,
     * message: string,
     * timestamp: string,
     * data: \App\DTOs\Response\ResponseData,
     * meta?: array
     * } $response
     */
    private array $response;

    private function __construct(){}

    public static function withData(
        ResponseData $responseData,
        int          $status = Response::HTTP_OK,
        string       $message = 'Success',
        ?array       $meta = null,
    ): ApiResponse{
        $self                        = new ApiResponse();
        $self->response['data']      = $responseData;
        $self->response['code']      = $status;
        $self->response['message']   = $message;
        $self->response['timestamp'] = time();
        if(!!$meta){
            $self->response['meta'] = $meta;
        }

        return $self;
    }

    public function jsonSerialize(): array{
        return $this->response;
    }
}