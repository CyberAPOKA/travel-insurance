<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('destination');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('charged_days');
            $table->unsignedTinyInteger('group_discount_percentage');
            $table->decimal('final_total', 10, 2);
            $table->json('warnings');
            $table->json('calculation_breakdown')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'start_date']);
            $table->index('destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
