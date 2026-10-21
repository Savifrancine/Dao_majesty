<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('dossier_documents', 'designations_supprimees')) {
                $table->json('designations_supprimees')->nullable()->after('excluded_entreprise_fichier_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            if (Schema::hasColumn('dossier_documents', 'designations_supprimees')) {
                $table->dropColumn('designations_supprimees');
            }
        });
    }
};
