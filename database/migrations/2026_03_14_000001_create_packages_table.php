<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('profile'); // pppoe profile name or category
            $table->decimal('monthly_price', 12, 2);
            $table->decimal('installation_fee', 12, 2)->nullable(); // null = free
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('badge')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
