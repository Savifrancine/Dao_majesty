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
        Schema::table('bordereaux', function (Blueprint $table) {
            if (!Schema::hasColumn('bordereaux', 'column_defs')) {
                $table->json('column_defs')->nullable()->after('designation_label');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereaux', function (Blueprint $table) {
            if (Schema::hasColumn('bordereaux', 'column_defs')) {
                $table->dropColumn('column_defs');
            }
        });
    }
};
