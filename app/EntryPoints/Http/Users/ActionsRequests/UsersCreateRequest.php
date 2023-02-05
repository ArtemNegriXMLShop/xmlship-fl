<?php

namespace App\EntryPoints\Http\Users\ActionsRequests;

use App\Foundation\Abstracts\Request;
use App\Foundation\Interfaces\RequestInterface;

class UsersCreateRequest extends Request implements RequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ];
    }
}
