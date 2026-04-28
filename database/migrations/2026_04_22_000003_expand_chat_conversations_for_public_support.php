<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('source_type')->default('customer')->after('assigned_admin_id');
            $table->string('guest_name')->nullable()->after('source_type');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('guest_phone')->nullable()->after('guest_email');
            $table->string('public_token', 64)->nullable()->after('guest_phone');
        });

        DB::table('chat_conversations')
            ->orderBy('id')
            ->get()
            ->each(function ($conversation) {
                DB::table('chat_conversations')
                    ->where('id', $conversation->id)
                    ->update([
                        'source_type' => $conversation->customer_id ? 'customer' : 'guest',
                        'public_token' => Str::random(40),
                    ]);
            });

        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable(false)->change();
            $table->unique('public_token');
            $table->index(['source_type', 'status']);
            $table->foreignId('customer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropIndex(['source_type', 'status']);
            $table->dropColumn(['source_type', 'guest_name', 'guest_email', 'guest_phone', 'public_token']);
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }
};
