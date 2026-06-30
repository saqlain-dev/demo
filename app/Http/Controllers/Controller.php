<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public function __construct(Request $request)
    {
        define('IMAGES_PATH', public_path('uploads' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR));
        $this->input = $request->except('_method');
    }

    public function authorizeAny(array $permissions, $message = 'This action is unauthorized.')
    {
        if (!Gate::any($permissions)) {
            abort(403, $message);
        }
    }
}
