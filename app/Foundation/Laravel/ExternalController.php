<?php

namespace App\Foundation\Laravel;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class ExternalController extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
