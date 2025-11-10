<?php

namespace App\Services;

abstract class BaseService
{
    protected function handleException(\Throwable $e): array
    {
        \Illuminate\Support\Facades\Log::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $message = 'An error occurred while processing your request.';

        if (config('app.debug')) {
            $message = $e->getMessage();
        }

        return [
            'success' => false,
            'message' => $message,
        ];
    }

    protected function paginateData($query, int $perPage = 15, int $page = 1): array
    {
        $total = $query->count();
        $results = $query->forPage($page, $perPage)->get();

        return [
            'data' => $results,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    protected function handleErrors(array $errors): array
    {
        return [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ];
    }
}
