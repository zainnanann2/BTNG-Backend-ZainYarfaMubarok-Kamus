<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Amplop respons seragam: setiap balasan selalu punya "message" dan "data".
 *
 * Keuntungannya, klien hanya perlu menulis satu jalur pembacaan:
 *   const { message, data } = await res.json();
 * tanpa harus menebak bentuk respons tiap endpoint.
 */
trait ApiResponse
{
    protected function success(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Membungkus koleksi terpaginasi. Resource collection dari Laravel
     * menyimpan paginator di dalam "resource", jadi "meta" dan "links"
     * diambil dari sana agar tetap sejajar dengan "message" dan "data".
     */
    protected function paginated(string $message, AnonymousResourceCollection $collection): JsonResponse
    {
        $paginator = $collection->resource;

        return response()->json([
            'message' => $message,
            'data' => $collection->collection,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'last' => $paginator->url($paginator->lastPage()),
            ],
        ]);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $body = [
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    protected function notFound(string $message = 'Data tidak ditemukan.'): JsonResponse
    {
        return $this->error($message, 404);
    }
}
