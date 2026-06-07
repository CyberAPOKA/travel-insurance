<?php

namespace App\Services\Payments\Asaas;

final class AsaasPixQrCodeData
{
    /**
     * @param  array<string, mixed>  $response
     * @return array{encodedImage: ?string, payload: ?string}
     */
    public static function fromApiResponse(array $response): array
    {
        return [
            'encodedImage' => self::sanitizeEncodedImage(
                is_string($response['encodedImage'] ?? null) ? $response['encodedImage'] : null,
            ),
            'payload' => is_string($response['payload'] ?? null)
                ? trim($response['payload'])
                : null,
        ];
    }

    public static function sanitizeEncodedImage(?string $encodedImage): ?string
    {
        if ($encodedImage === null || $encodedImage === '') {
            return null;
        }

        $value = trim($encodedImage);

        if (str_contains($value, 'base64,')) {
            $value = substr($value, (int) strrpos($value, 'base64,') + 7);
        }

        return preg_replace('/\s+/', '', $value) ?: null;
    }
}
