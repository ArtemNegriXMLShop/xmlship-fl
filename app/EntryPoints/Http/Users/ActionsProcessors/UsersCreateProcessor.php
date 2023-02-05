<?php

namespace App\EntryPoints\Http\Users\ActionsProcessors;

use App\Data\DataTransferObjects\User;
use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;

class UsersCreateProcessor extends Processor implements ProcessorInterface
{

    public function __construct(private readonly UsersRepositoryInterface $usersRepository)
    {
    }

    public function execute(RequestInterface $request): array
    {
        $this->usersRepository->create(new User(
            $request->get('email'),
            $request->get('name'),
        ));

        return $request->getInput();
    }
}
