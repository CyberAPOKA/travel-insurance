<?php

namespace Tests\Feature\Payments;

use App\Enums\PaymentStatus;
use App\Models\Quote;
use App\Models\QuotePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuotePaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.asaas.api_key' => 'test-key',
            'services.asaas.base_url' => 'https://sandbox.asaas.com/api/v3',
            'services.asaas.webhook_token' => 'webhook-secret',
            'services.asaas.default_cpf_cnpj' => '24971563792',
            'quotes.pix.charge_percentage' => 0.1,
        ]);
    }

    #[Test]
    public function it_creates_a_pix_payment_for_a_quote(): void
    {
        Http::fake([
            'https://sandbox.asaas.com/api/v3/pix/addressKeys*' => Http::response([
                'data' => [['id' => 'key_123', 'status' => 'ACTIVE']],
            ]),
            'https://sandbox.asaas.com/api/v3/customers' => Http::response(['id' => 'cus_123']),
            'https://sandbox.asaas.com/api/v3/payments' => Http::response([
                'id' => 'pay_123',
                'status' => 'PENDING',
                'value' => 0.85,
                'dueDate' => '2026-06-07',
            ]),
            'https://sandbox.asaas.com/api/v3/payments/pay_123/pixQrCode' => Http::response([
                'encodedImage' => 'base64-image',
                'payload' => '000201010212',
                'expirationDate' => '2026-06-07 23:59:59',
            ]),
        ]);

        $user = User::factory()->create();
        $quote = $this->createQuote($user);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/quotes/{$quote->id}/payment");

        $response->assertCreated()
            ->assertJsonPath('payment.status', 'pending')
            ->assertJsonPath('payment.value', 0.85)
            ->assertJsonPath('payment.quote_total', 852.5)
            ->assertJsonPath('payment.charge_percentage', 0.1)
            ->assertJsonPath('payment.pix_payload', '000201010212')
            ->assertJsonPath('payment.pix_encoded_image', 'base64-image')
            ->assertJsonPath('payment.environment', 'sandbox');

        $payment = QuotePayment::query()->where('quote_id', $quote->id)->first();

        $this->assertNotNull($payment?->pix_expiration_date);
        $this->assertTrue($payment->pix_expiration_date->between(now()->addMinutes(59), now()->addMinutes(61)));

        $this->assertDatabaseHas('quote_payments', [
            'quote_id' => $quote->id,
            'asaas_payment_id' => 'pay_123',
            'status' => PaymentStatus::Pending->value,
            'value' => 0.85,
        ]);

        Http::assertSent(function ($request) {
            if (! str_ends_with($request->url(), '/payments')) {
                return true;
            }

            return ($request->data()['value'] ?? null) === 0.85;
        });
    }

    #[Test]
    public function it_returns_existing_payment_instead_of_creating_a_duplicate(): void
    {
        Http::fake([
            'https://sandbox.asaas.com/api/v3/payments/pay_123/pixQrCode' => Http::response([
                'encodedImage' => 'refreshed-image',
                'payload' => '000201010212',
                'expirationDate' => '2026-06-07 23:59:59',
            ]),
        ]);

        $user = User::factory()->create();
        $quote = $this->createQuote($user);

        QuotePayment::query()->create([
            'quote_id' => $quote->id,
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_123',
            'status' => PaymentStatus::Pending,
            'value' => 852.5,
            'due_date' => '2026-06-07',
            'pix_encoded_image' => 'old-image',
            'pix_payload' => 'old-payload',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/quotes/{$quote->id}/payment");

        $response->assertOk()
            ->assertJsonPath('payment.pix_encoded_image', 'refreshed-image');

        $this->assertDatabaseCount('quote_payments', 1);
    }

    #[Test]
    public function it_rejects_payment_creation_when_quote_is_already_paid(): void
    {
        $user = User::factory()->create();
        $quote = $this->createQuote($user);

        QuotePayment::query()->create([
            'quote_id' => $quote->id,
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_paid',
            'status' => PaymentStatus::Paid,
            'value' => 852.5,
            'due_date' => '2026-06-07',
            'paid_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/quotes/{$quote->id}/payment")
            ->assertConflict();
    }

    private function createQuote(User $user): Quote
    {
        return Quote::query()->create([
            'user_id' => $user->id,
            'destination' => 'EUROPE',
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-20',
            'charged_days' => 11,
            'group_discount_percentage' => 0,
            'final_total' => 852.5,
            'warnings' => [],
            'calculation_breakdown' => null,
        ]);
    }
}
