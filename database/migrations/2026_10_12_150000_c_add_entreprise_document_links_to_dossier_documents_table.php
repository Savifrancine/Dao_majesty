<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            $table->json('linked_entreprise_document_ids')->nullable()->after('content');
            $table->json('excluded_entreprise_fichier_ids')->nullable()->after('linked_entreprise_document_ids');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            $table->dropColumn(['linked_entreprise_document_ids', 'excluded_entreprise_fichier_ids']);
        });
    }
};
