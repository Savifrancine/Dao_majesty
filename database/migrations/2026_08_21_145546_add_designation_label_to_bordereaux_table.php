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
            $table->string('designation_label')->nullable()->after('titre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereaux', function (Blueprint $table) {
            $table->dropColumn('designation_label');
        });
    }
};
