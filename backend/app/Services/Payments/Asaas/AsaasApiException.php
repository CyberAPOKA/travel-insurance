<?php

namespace App\Services\Payments\Asaas;

use Illuminate\Http\Client\Response;
use RuntimeException;

final class AsaasApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>|null  $errors
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?array $errors = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response): self
    {
        $body = $response->json();
        $errors = is_array($body['errors'] ?? null) ? $body['errors'] : null;
        $message = is_array($errors) && isset($errors[0]['description'])
            ? (string) $errors[0]['description']
            : $response->body();

        return new self($message, $response->status(), $errors);
    }
}
