<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('types_documents')->where('nom', 'Attestation de catégorisation')->exists()) {
            DB::table('types_documents')->insert([
                'nom' => 'Attestation de catégorisation',
                'type_formulaire' => 'fichier',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('types_documents')->where('nom', 'Attestation de catégorisation')->delete();
    }
};
