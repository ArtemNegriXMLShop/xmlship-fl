<?php

namespace App\EntryPoints\Http\Auth\ActionsProcessors;

use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;

class AuthLogoutProcessor extends Processor implements ProcessorInterface
{
    public function execute(RequestInterface $request): array
    {
        $request->user()->currentAccessToken()->delete();

        return [];
    }
}
