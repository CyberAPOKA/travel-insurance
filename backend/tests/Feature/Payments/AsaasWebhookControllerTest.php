<?php

namespace Tests\Feature\Payments;

use App\Enums\PaymentStatus;
use App\Models\Quote;
use App\Models\QuotePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AsaasWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.asaas.webhook_token' => 'webhook-secret',
        ]);
    }

    #[Test]
    public function it_rejects_webhooks_without_a_valid_token(): void
    {
        $this->postJson('/api/webhooks/asaas', [])
            ->assertUnauthorized();
    }

    #[Test]
    public function it_marks_a_quote_payment_as_paid_from_webhook(): void
    {
        $user = User::factory()->create();
        $quote = Quote::query()->create([
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

        $payment = QuotePayment::query()->create([
            'quote_id' => $quote->id,
            'user_id' => $user->id,
            'asaas_payment_id' => 'pay_123',
            'status' => PaymentStatus::Pending,
            'value' => 852.5,
            'due_date' => '2026-06-07',
        ]);

        $this->postJson('/api/webhooks/asaas', [
            'event' => 'PAYMENT_RECEIVED',
            'payment' => [
                'id' => 'pay_123',
                'status' => 'RECEIVED',
                'value' => 852.5,
                'externalReference' => 'quote:'.$quote->id,
                'paymentDate' => '2026-06-07',
            ],
        ], [
            'asaas-access-token' => 'webhook-secret',
        ])->assertOk()
            ->assertJsonPath('received', true);

        $payment->refresh();

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);
    }
}
