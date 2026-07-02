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
        Schema::table('dossier_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('dossier_documents', 'content')) {
                $table->text('content')->nullable()->after('statut');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            if (Schema::hasColumn('dossier_documents', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
};
