<?php

namespace Tests\Unit\Services\Payments\Asaas;

use App\Services\Payments\Asaas\AsaasPixQrCodeData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsaasPixQrCodeDataTest extends TestCase
{
    #[Test]
    public function it_strips_data_uri_prefix_from_encoded_image(): void
    {
        $sanitized = AsaasPixQrCodeData::sanitizeEncodedImage(
            'data:image/png;base64,iVBORw0KGgo=',
        );

        $this->assertSame('iVBORw0KGgo=', $sanitized);
    }

    #[Test]
    public function it_parses_api_response_payload(): void
    {
        $parsed = AsaasPixQrCodeData::fromApiResponse([
            'encodedImage' => 'abc123',
            'payload' => '000201010212',
        ]);

        $this->assertSame('abc123', $parsed['encodedImage']);
        $this->assertSame('000201010212', $parsed['payload']);
    }
}
