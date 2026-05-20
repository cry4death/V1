<?php

namespace App\Support;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Guzzle middleware — прописывает путь к cacert.pem для запросов Firebase.
 * Нужен на Windows, где PHP не находит системные CA автоматически.
 * Путь: <project_root>/cacert.pem
 */
class FirebaseSslMiddleware
{
    public function __invoke(callable $handler): callable
    {
        $cert = base_path('cacert.pem');

        return function (RequestInterface $request, array $options) use ($handler, $cert): PromiseInterface {
            if (file_exists($cert)) {
                $options['verify'] = $cert;
            }

            return $handler($request, $options);
        };
    }
}
