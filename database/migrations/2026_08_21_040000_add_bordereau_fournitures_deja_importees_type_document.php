<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('types_documents')->where('nom', 'Bordereau des prix des fournitures, déjà importées')->exists()) {
            DB::table('types_documents')->insert([
                'nom' => 'Bordereau des prix des fournitures, déjà importées',
                'type_formulaire' => 'bordereau',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('types_documents')->where('nom', 'Bordereau des prix des fournitures, déjà importées')->delete();
    }
};
