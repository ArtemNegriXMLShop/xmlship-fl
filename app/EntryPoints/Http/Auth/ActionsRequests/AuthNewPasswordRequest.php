<?php

namespace App\EntryPoints\Http\Auth\ActionsRequests;

use App\Foundation\Abstracts\Request;
use App\Foundation\Interfaces\RequestInterface;
use Illuminate\Validation\Rules\Password;

class AuthNewPasswordRequest extends Request implements RequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols(), 'confirmed'],
            'password_confirmation' => 'required|same:password'
        ];
    }
}
