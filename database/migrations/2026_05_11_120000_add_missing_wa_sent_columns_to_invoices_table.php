<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'is_wa_sent')) {
                $table->boolean('is_wa_sent')->default(false)->after('status');
            }

            if (!Schema::hasColumn('invoices', 'wa_sent_at')) {
                $table->timestamp('wa_sent_at')->nullable()->after('is_wa_sent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'wa_sent_at')) {
                $table->dropColumn('wa_sent_at');
            }

            if (Schema::hasColumn('invoices', 'is_wa_sent')) {
                $table->dropColumn('is_wa_sent');
            }
        });
    }
};
