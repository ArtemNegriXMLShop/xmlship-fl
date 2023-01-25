<?php

namespace App\EntryPoints\Http\Settings\GroupUsers\ActionsProcessors;

use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;

class GroupUsersShowProcessor extends Processor implements ProcessorInterface
{
    public function execute(RequestInterface $request, int|string $id): array
    {
        $data = $request->getInput();

        //Add your code for manipulation with request data here
        //Don't drop hear too much business logic, move it to the next handling level - services / transitions.

        return $data;
    }
}
