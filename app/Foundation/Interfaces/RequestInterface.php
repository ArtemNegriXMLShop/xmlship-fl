<?php

namespace App\Foundation\Interfaces;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Contracts\Support\Arrayable;
use ArrayAccess;

interface RequestInterface extends ValidatesWhenResolved, Arrayable, ArrayAccess
{
    public function getInput(): array;
}
