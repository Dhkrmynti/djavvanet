<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->restrictOnDelete();
            $table->string('customer_code')->unique();
            $table->string('full_name');
            $table->string('whatsapp_number');
            $table->string('email')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('active_since');
            $table->integer('billing_cycle')->default(1)->comment('Tanggal tagihan dicetak setiap bulannya');
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
