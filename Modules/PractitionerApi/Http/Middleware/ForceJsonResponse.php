<?php

namespace Modules\PractitionerApi\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        try {
            $response = $next($request);

            if ($response instanceof JsonResponse && $response->getStatusCode() >= 400) {
                $payload = $response->getData(true);
                if (isset($payload['exception']) || isset($payload['trace'])) {
                    return response()->json([
                        'message' => $payload['message']
                            ?? (Response::$statusTexts[$response->getStatusCode()] ?? 'Request failed.'),
                        'errors' => $payload['errors'] ?? [],
                    ], $response->getStatusCode());
                }
            }

            return $response;
        } catch (HttpExceptionInterface $exception) {
            return response()->json([
                'message' => $exception->getMessage()
                    ?: (Response::$statusTexts[$exception->getStatusCode()] ?? 'Request failed.'),
                'errors' => [],
            ], $exception->getStatusCode(), $exception->getHeaders());
        }
    }
}
