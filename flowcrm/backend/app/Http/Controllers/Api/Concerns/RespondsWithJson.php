<?php

namespace App\Http\Controllers\Api\Concerns;

trait RespondsWithJson
{
    protected function success(mixed $data = null, string $message = 'Operação realizada com sucesso.', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message = 'Erro ao realizar operação.', array $errors = [], int $status = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
