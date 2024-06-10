<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    public function formatResponse(array $data): JsonResponse
    {
        return response()->json($data, $data['code'], [], JSON_UNESCAPED_UNICODE);
    }
}
