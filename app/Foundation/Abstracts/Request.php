<?php

namespace App\Foundation\Abstracts;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class Request extends FormRequest
{
    /**
     * Get validated and modified input
     *
     * @param  bool  $strict
     * @return array
     */
    public function getInput(bool $strict = true): array
    {
        return $this->modify($this->decryptArrayWalk($strict ? $this->validated() : $this->all()));
    }

    /**
     * Modify input data
     *
     * @param  array  $input
     * @return array
     */
    private function modify(array $input): array
    {
        //Add your code for manipulation with request data here

        return $input;
    }

    /**
     * @param  Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'message' => 'Request is not valid',
                    'errors' => $validator->errors(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            )
        );
    }


    public function decryptArrayWalk(array $array): array
    {
        foreach ($array as $key => $value) {
            if (!is_array($value) || Arr::isList($value)) {
                $array[$key] = $this->checkAndDecrypt($key, $value);
            } else {
                $array[$key] = $this->decryptArrayWalk($value);
            }
        }
        return $array;
    }

    private function checkAndDecrypt($key, $value): mixed
    {
        if ($key === 'id' && is_string($value)) {
            return xdecrypt($value);
        } elseif (Str::endsWith($key, '_ids') && Arr::isList($value)) {
            return Arr::map($value, function ($item) {
                return xdecrypt($item);
            });
        }

        return $value;
    }
}
