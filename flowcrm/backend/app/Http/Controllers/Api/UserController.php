<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        return $this->success($request->attributes->get('current_company')->users()->get());
    }
}
