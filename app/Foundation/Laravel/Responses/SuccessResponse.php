<?php

namespace App\Foundation\Laravel\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

readonly class SuccessResponse implements Responsable
{
    /**
     * @param  mixed  $data
     * @param  array  $metadata
     * @param  int  $code
     * @param  array  $headers
     */
    public function __construct(
        private mixed $data,
        private array $metadata = [],
        private int $code = Response::HTTP_OK,
        private array $headers = []
    ) {}

    /**
     * @param  $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json(
            [
                'data' => $this->data,
                'metadata' => $this->metadata,
            ],
            $this->code,
            $this->headers
        );
    }
}