<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success($data = [], $message = 'Success', $pagination = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination instanceof LengthAwarePaginator
                ? self::pagination($pagination)
                : $pagination
        ]);
    }

    public static function error($message = 'Error', $code = 400, $data = null)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            'pagination' => null
        ], $code);
    }

    public static function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }
}
