<?php

namespace App\EntryPoints\Http\Auth\ActionsRequests;

use App\Foundation\Abstracts\Request;
use App\Foundation\Interfaces\RequestInterface;

class AuthLoginRequest extends Request implements RequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ];
    }
}
