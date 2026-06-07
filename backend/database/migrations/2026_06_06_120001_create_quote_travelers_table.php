<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_travelers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->date('birth_date');
            $table->json('add_ons');
            $table->unsignedTinyInteger('age');
            $table->decimal('subtotal', 10, 2);
            $table->json('applied_add_ons');
            $table->timestamps();

            $table->index(['quote_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['quote_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_travelers');
    }
};
