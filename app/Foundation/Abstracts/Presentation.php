<?php

namespace App\Foundation\Abstracts;

use App\Foundation\Interfaces\PresentationInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Presentation implements PresentationInterface
{
    public function beautify(array $data): array
    {
        return $this->cryptArrayWalk($data);
    }

    public function cryptArrayWalk(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $value = $value->toArray();
            }

            if (!is_array($value) || Arr::isList($value)) {
                $array[$key] = $this->checkAndEncrypt($key, $value);
            } else {
                $array[$key] = $this->cryptArrayWalk($value);
            }
        }
        return $array;
    }

    private function checkAndEncrypt($key, $value): mixed
    {
        if ($key === 'id' && is_int($value)) {
            return xencrypt($value);
        } elseif (Str::endsWith($key, '_ids') && Arr::isList($value) && is_int($value[0])) {
            return
                Arr::map($value, function ($item) {
                    return xencrypt($item);
                });
        }

        return $value;
    }
}
