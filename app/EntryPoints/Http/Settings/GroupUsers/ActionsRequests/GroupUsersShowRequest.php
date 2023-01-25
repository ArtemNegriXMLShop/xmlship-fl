<?php

namespace App\EntryPoints\Http\Settings\GroupUsers\ActionsRequests;

use App\Foundation\Abstracts\Request;
use App\Foundation\Interfaces\RequestInterface;

class GroupUsersShowRequest extends Request implements RequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
