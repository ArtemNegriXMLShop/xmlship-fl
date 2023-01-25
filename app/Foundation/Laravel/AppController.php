<?php

namespace App\Foundation\Laravel;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class AppController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
