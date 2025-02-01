<?php

namespace App\Http\Controllers\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function Error($message = "Error Response Message", $data = null, $statusCode = 500): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'value' => $data,
        ], $statusCode);
    }


    public static function Success($message = "isSuccess", $data = [], $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'value' => $data,
        ], $statusCode);
    }

    public static function NotFound($message = "Not Found", $data = [], $statusCode = 404): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'value' => null,
        ], $statusCode);
    }

}
