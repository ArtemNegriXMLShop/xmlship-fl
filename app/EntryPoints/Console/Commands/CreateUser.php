<?php

namespace App\EntryPoints\Console\Commands;

use App\Data\DataTransferObjects\User;
use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

final class CreateUser extends Command
{
    protected $signature = 'user:create {email : provide new user email}
                                        {name : user name}
                                        {password? : password can be given. Use `\` to escape special characters}';

    protected $description = 'Create new user';

    public function handle(UsersRepositoryInterface $usersRepository, Validator $validator): int
    {
        $email = $this->argument('email');
        $name = $this->argument('name');
        $password = $this->argument('password');

        try {
            $validator->validate(['email' => $email, 'name' => $name, 'password' => $password], [
                'email' => 'required|email|unique:users,email',
                'name' => 'required|string',
                'password' => ['nullable', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);

            $usersRepository->create(new User($email, $name, $password));
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        } catch (\Throwable $t) {
            $this->error('Error: ' . $t->getMessage());

            return self::FAILURE;
        }

        $this->info('User successfully created.');

        return self::SUCCESS;
    }
}
