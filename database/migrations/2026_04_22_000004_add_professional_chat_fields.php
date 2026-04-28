<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->string('priority')->default('normal')->after('status');
            $table->timestamp('admin_last_seen_at')->nullable()->after('last_message_at');
            $table->timestamp('customer_last_seen_at')->nullable()->after('admin_last_seen_at');
            $table->timestamp('closed_at')->nullable()->after('customer_last_seen_at');
            $table->timestamp('first_response_at')->nullable()->after('closed_at');
            $table->foreignId('first_response_admin_id')->nullable()->after('first_response_at')->constrained('users')->nullOnDelete();

            $table->index(['status', 'priority', 'last_message_at']);
            $table->index(['assigned_admin_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('first_response_admin_id');
            $table->dropIndex(['status', 'priority', 'last_message_at']);
            $table->dropIndex(['assigned_admin_id', 'status']);
            $table->dropColumn([
                'priority',
                'admin_last_seen_at',
                'customer_last_seen_at',
                'closed_at',
                'first_response_at',
            ]);
        });
    }
};
