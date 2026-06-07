<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('asaas_payment_id')->unique();
            $table->string('status');
            $table->decimal('value', 10, 2);
            $table->date('due_date');
            $table->longText('pix_encoded_image')->nullable();
            $table->text('pix_payload')->nullable();
            $table->timestamp('pix_expiration_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_payments');
    }
};
